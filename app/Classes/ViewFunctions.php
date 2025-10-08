<?php
declare(strict_types=1);

namespace App\Classes;

use App\Classes\ExtraFunctions;
use RuntimeException;
use const ENT_QUOTES;
use const EXTR_SKIP;

final class ViewFunctions
{
    /** Prevent instantiation */
    private function __construct()
    {
    }

    public static function render(string $template, array $data = []): string
    {
        // Resolve file path

        $view_file = __DIR__ . '/../Views/' . $template;

        if (!is_file($view_file)) {
            throw new RuntimeException("View not found: {$view_file}");
        }

        // Isolated scope: expose $data as local vars, don’t overwrite existing
        extract($data, EXTR_SKIP);
        $validate = $validate ?? false;
        $validation = $validate ? 'class="needs-validation"' : 'novalidate';
        $showButtons = $showButtons ?? true;

        ob_start();
        include $view_file;
        return (string)ob_get_clean();
    }

    /** Simple escape helper you can call inside templates */
    public static function e(string $v): string
    {
        return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    }


    public static function getFormFields($formFields): string
    {
        $output = [];
        foreach ($formFields as $row) {
            // add additional form types as needed
            // expected to have values added by JavaScript
            $output[] = self::getField($row, '');
        }
        return implode("\n", $output);
    }

    private static function getField($row, $class = ''): string
    {
        switch ($row['type']) {
            case 'input':
                return self::getInput($row, $class);

            case 'date':
                return self::getDateInput($row, $class);

            case 'select':
                return self::getSelect($row, $class);

            case 'hidden':
                return self::getHidden($row);

            case 'textarea':
                return self::getTextarea($row, $class);

            case 'row':
                $output = [];
                $class = 'col-12 col-sm-6';
                foreach ($row['fields'] as $field) {
                    $output[] = self::getField($field, $class);
                }
                return '<div class="row g-3">' . implode("\n", $output) . '</div>';
        }
        return '';
    }

    // only hidden gets a value and it's a constant
    private static function getHidden(array $vars): string
    {
        extract($vars);
        $value = $value ?? '';
        $fieldId = $fieldId ?? '';
        return "<input type='hidden' name='data[$fieldId]' value='$value'>";
    }

    private static function getSelect(array $vars, string $class = ''): string
    {
        extract($vars);
        return "<div class='$class mb-3'>
                <label for='$fieldId' class='form-label'>$label</label>
                <select id='$fieldId' name='data[$fieldId]' class='form-control'></select>
            </div>";
    }

    private static function getInput(array $vars, string $class = ''): string
    {
        extract($vars);
        $required = $required ?? false;
        $required_attr = $required ? 'required' : '';
        $required_html = $required ? '<div class="invalid-feedback">Required Field.</div>' : '';
        return "<div class='$class mb-3'>
                <label for='$fieldId' class='form-label'>$label</label>
                <input type='text' id='$fieldId' class='form-control'
                    name='data[$fieldId]' placeholder='$placeholder' $required_attr autocomplete='off'>
                $required_html
            </div>";
    }

    private static function getDateInput(array $vars, string $class = ''): string
    {
        $display_vars = $vars;
        $display_vars['fieldId'] .= '_display';
        $output = [
            self::getInput($display_vars, $class),
            self::getHidden($vars)
        ];

        return implode("\n", $output);
    }

    private static function getTextarea(array $vars, string $class = ''): string
    {
        extract($vars);

        $placeholder = $placeholder ?? '';
        return "<div class='$class mb-3'>
                    <label for='$fieldId' class='form-label'>$label</label>
                    <textarea class='form-control' id='$fieldId' name='data[$fieldId]' placeholder='$placeholder' rows='3'></textarea>
                </div>";
    }

    private static function getTabTab($paneId, $linkId, $label, $isActive): string
    {
        $active = $isActive ? ' active' : '';
        $aria_selected = $isActive ? 'true' : 'false';
        $output = ['<li class="nav-item" role="presentation">'];
        $output[] = "<a class='nav-link $active px-4' id='$linkId'
                    href='#$paneId' data-bs-toggle='tab'
                    role='tab' aria-controls='$paneId'
                     aria-selected='$aria_selected'>";
        $output[] = htmlspecialchars($label, ENT_QUOTES);
        $output[] = "<span id='{$paneId}Badge'
              class='translate-middle badge rounded-pill bg-gray ms-4'></span>";
        $output[] = '</a></li>';
        return implode('', $output);
    }

    private static function getTabBody($paneId, $filename, $isActive, $data): string
    {
        $output = [];
        $show_active = $isActive ? ' show active' : '';

        $output[] = "<div class='tab-pane fade $show_active' id='$paneId'
                                    role='tabpanel'
                                    aria-labelledby='{$paneId}-tab'
                                    tabindex='0'
                            >";

        $output[] = self::render($filename, $data);;
        $output[] = '</div>';
        return implode('', $output);
    }

    public static function getTabs($tabList, $active, $data): string
    {
        $tabs = [];
        $tab_body = [];
        foreach ($tabList as $tab):
            $is_active = ($tab['name'] === $active);
            $pane_id = 'tab-' . $tab['name'];
            $link_id = $pane_id . '-tab';
            $tabs[] = self::getTabTab($pane_id, $link_id, $tab['label'], $is_active);
            $tab_body[] = self::getTabBody($pane_id, $tab['filename'], $is_active, $data);
        endforeach;

        $show = [
            'tabs' => implode('', $tabs),
            'tab_body' => implode('', $tab_body)
        ];

        return self::render('components/tabs.php', $show);
    }

//string $label, string $cardId,
    public static function getCard(array $options, array $data): string
    {
        $class = '';
        if ($data['xerotenant_id']) {
            $tenancy = ExtraFunctions::getTenancyInfo($data['xerotenant_id']);
            $data['class'] = $tenancy['shortname'];
        }
        $data = array_merge($data, $options);
        $data['bodyHTML'] = self::render($options['filename'], $data);
        return self::render('components/card.php', $data);

    }
}

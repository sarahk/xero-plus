<?php
declare(strict_types=1);

namespace App\Views;

use RuntimeException;

final class ViewFunctions
{
    /** Prevent instantiation */
    private function __construct()
    {
    }

    public static function render(string $template, array $data = []): string
    {
        // Resolve file path

        $viewFile = __DIR__ . '/' . $template;

        if (!is_file($viewFile)) {
            throw new \RuntimeException("View not found: {$viewFile}");
        }

        // Isolated scope: expose $data as local vars, don’t overwrite existing
        extract($data, \EXTR_SKIP);

        ob_start();
        include $viewFile;
        return (string)ob_get_clean();
    }

    /** Simple escape helper you can call inside templates */
    public static function e(string $v): string
    {
        return htmlspecialchars($v, \ENT_QUOTES, 'UTF-8');
    }


    public static function getFormFields($formFields)
    {
        $output = [];
        foreach ($formFields as $row) {
            // add additional form types as needed
            // expected to have values added by javascript
            switch ($row['type']) {
                case 'input':
                    $output[] = self::getInput($row);
                    break;
                case 'select':
                    $output[] = self::getSelect($row);
                    break;
                case 'hidden':
                    $output[] = self::getHidden($row);
                    break;
            }
        }
        return implode("\n", $output);
    }

    private static function getHidden(array $vars): string
    {
        extract($vars);
        return "<input type='hidden' name='data[$fieldId]'>";
    }

    private static function getSelect(array $vars): string
    {
        extract($vars);
        return "<div class='mb-3'>
                <label for='$fieldId' class='form-label'>$label</label>
                <select id='$fieldId' name='data[$fieldId]' class='form-control'></select>
            </div>";
    }

    private static function getInput(array $vars): string
    {
        extract($vars);
        return "<div class='mb-3'>
                <label for='$fieldId' class='form-label'>$label</label>
                <input type='text' class='form-control' id='$fieldId' name='data[$fieldId]' placeholder='$placeholder'>
            </div>";
    }
}

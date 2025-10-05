<?php
declare(strict_types=1);

namespace App\classes;


class MenuBuilder
{

    public static function buildMenu(): string
    {
        $options = self::getMenuArray();

        $output = [
            self::getEnquiryButton(),
        ];
        foreach ($options as $option) {
            switch ($option['type']) {
                case 'dropdown':
                    $output[] = self::getDDMenuItem($option);
                    break;
                case 'simple':
                    $output[] = self::simpleMenuItem($option['action'], $option['icon'], $option['label']);
                    break;
                case 'full':
                    $output[] = self::fullMenuItem($option['url'], $option['id'], $option['icon'], $option['label']);
                    break;
                case 'heading':
                    $output[] = self::getSubCategory($option['label']);
                    break;
            }

        }
        $output[] = self::getJSMenuItems();
        return implode('', $output);
    }


    private static function getMenuArray(): array
    {
        return [
            ['type' => 'heading', 'label' => 'Main'],
            ['type' => 'simple', 'action' => '', 'icon' => 'fa-solid fa-house', 'label' => 'Dashboard'],
            ['type' => 'simple', 'action' => '100', 'icon' => 'fa-regular fa-circle-question', 'label' => 'Enquiries'],
            ['type' => 'simple', 'action' => '90', 'icon' => 'fa-solid fa-file-invoice-dollar', 'label' => 'Invoices &amp; Payments'],
            ['type' => 'dropdown', 'action' => '16', 'label' => 'Outstanding Rents', 'icon' => 'fa-solid fa-circle-exclamation',
                'children' => [
                    ['type' => 'simple', 'action' => '16', 'icon' => 'fa-solid fa-bell', 'label' => 'Reminders'],
                    ['type' => 'simple', 'action' => '168', 'icon' => 'fa-solid fa-phone', 'label' => 'Management'],
                ]],
            ['type' => 'simple', 'action' => '13', 'icon' => 'fa-solid fa-square', 'label' => 'Cabins'],
            ['type' => 'simple', 'action' => '17', 'icon' => 'fa-regular fa-message', 'label' => 'Message Templates'],
            ['type' => 'simple', 'action' => '18', 'icon' => 'fa-solid fa-message', 'label' => 'Messages Sent'],
            ['type' => 'simple', 'action' => '5', 'icon' => 'fa-solid fa-person', 'label' => 'Customers'],
            ['type' => 'simple', 'action' => '11', 'icon' => 'fa-solid fa-map-location-dot', 'label' => 'Cabin Locations'],
            ['type' => 'full', 'url' => '/index.php?action=logoff', 'icon' => 'fa-solid fa-power-off', 'label' => 'Log Off'],
            ['type' => 'heading', 'label' => 'Admin', 'title' => 'Sarah Only'],
            ['type' => 'simple', 'action' => '6', 'icon' => 'fa-solid fa-triangle-exclamation', 'label' => 'Get JWT Claims'],
            ['type' => 'simple', 'action' => '1', 'icon' => 'fa-solid fa-triangle-exclamation', 'label' => 'Get Organisation'],
            ['type' => 'simple', 'action' => '9', 'icon' => 'fa-solid fa-triangle-exclamation', 'label' => 'Invoices'],
        ];
    }

    private static function getDDMenuItem($option): string
    {
        $output = [];
        $output[] = "
        <li class='slide has-sub is-expanded'>
            <a class='side-menu__item' data-bs-toggle='slide'
               href='/page.php?action={$option['action']}'><i
                    class='side-menu__icon {$option['icon']}'></i><span
                    class='side-menu__label'>{$option['label']}</span><i
                    class='angle fa fa-angle-right'></i></a>
            <ul class='slide-menu open'>";

        // todo: remove the list-item-style
        foreach ($option['children'] as $child) {

            $output[] = self::simpleMenuItem($child['action'], '', $child['label']);
        }

        $output[] = '</ul></li>';
        return implode('', $output);
    }


    private static function simpleMenuItem($action, $icon, $label): string
    {
        return self::fullMenuItem("/page.php?action=$action", '', $icon, $label);
    }

    private static function fullMenuItem($url, $id, $icon, $label): string
    {
        $id_string = (empty($id)) ? '' : "id='$id'";
        $icon_html = (empty($icon)) ? '' : "<i class='side-menu__icon $icon'></i>";
        return "
        <li class='slide'>
            <a class='side-menu__item' $id_string href='$url'>$icon_html<span class='side-menu__label'>$label</span></a>
        </li>";
    }

    private static function getJSMenuItems(): string
    {
        ob_start();
        ?>

        <!-- this one has extra bits -->
        <li class="slide">
            <a class="side-menu__item" data-bs-toggle="slide" href="#" id="rebuildMTables"><span
                        class="side-menu__label">Refresh M Tables</span>
                <span class="badge bg-success side-badge d-none" id="rebuildSuccess"><i
                            class="fa-solid fa-check"></i></span>
                <span class="badge bg-error side-badge d-none" id="rebuildError"><i
                            class="fa-solid fa-xmark"></i></span>
            </a>
        </li>
        <?php
        return (string)ob_get_clean();
    }

    private static function getEnquiryButton(): string
    {
        ob_start();
        ?>

        <li class="slide">
            <a href="/page.php?action=10"
               class="btn btn-success d-block tracking-wide" role="button">
                <i class="side-menu__icon fa-solid fa-plus"></i>
                <span class='side-menu__label fw-semibold'><strong>Add An Enquiry</strong></span></a></li>
        <!-- btn-block m-3 p-2 btn-success -->
        <?php
        return (string)ob_get_clean();
    }

    private static function getSubCategory($label, $title = ''): string
    {
        return "<li class='sub-category' title='$title'><h3>$label</h3></li>";
    }
}










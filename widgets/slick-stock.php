<?php
function slickStockWidget($row): string
{
    //'<div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">',
    $output = [
        '<div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">',
        "<div class='card' data-hash='{$row['id']}'>",
        '<div class="card-body">'
    ];

    $output[] = slickStockWidgetRow(
        "<span class='fs-18'>{$row['label']}</span>",
        [
            '<i class="fa-solid fa-right-to-bracket"></i>',
            '<i class="fa-solid fa-right-from-bracket"></i>',
            '<i class="fa-solid fa-inbox"></i>'
        ],
        'black');
    //debug(json_decode(TENANCIES, true));
    foreach ($row['vals'] as $vals) {
        $output[] = slickStockWidgetRow(
            ucfirst($vals['shortname']),
            $vals['data'],
            $vals['colour']);
    }

    $output[] = '</div></div></div>';

    return implode($output);
}

function slickStockWidgetRow($label, $vals, $colour): string
{
    return "<div class='row'>
                  <div class='col-6 text-{$colour} border-bottom-{$colour}'>{$label}</div>
                  <div class='col-2 border-bottom-{$colour}'>{$vals[0]}</div>
                  <div class='col-2 border-bottom-{$colour}'>{$vals[1]}</div>
                  <div class='col-2 border-bottom-{$colour}'>{$vals[2]}</div>
      </div>";
}

?>
<style>
    .slick-prev:before, .slick-next:before {
        font-family: 'slick';
        font-size: 30px;
        color: var(--bs-blue);
    }
</style>
<div class="row mx-2">
    <div class="slick-stock">
        <?php
        foreach ($stock as $row)
            echo slickStockWidget($row);
        ?>
    </div>
</div>

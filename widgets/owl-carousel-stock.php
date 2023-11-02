<?php
function stockWidget($row): string
{
    //'<div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">',
    $output = [
        "<div class='card' data-hash='{$row['id']}'>",
        '<div class="card-body">'
    ];

    $output[] = stockWidgetRow(
        "<span class='fs-18'>{$row['label']}</span>",
        [
            '<i class="fa-solid fa-right-to-bracket"></i>',
            '<i class="fa-solid fa-right-from-bracket"></i>',
            '<i class="fa-solid fa-inbox"></i>'
        ],
        'black');
    //debug(json_decode(TENANCIES, true));
    foreach ($row['vals'] as $vals) {
        $output[] = stockWidgetRow(
            ucfirst($vals['shortname']),
            $vals['data'],
            $vals['colour']);
    }

    $output[] = '</div></div>';

    return implode($output);
}

function stockWidgetRow($label, $vals, $colour): string
{
    return "<div class='row'>
                  <div class='col-6 text-{$colour} border-bottom-{$colour}'>{$label}</div>
                  <div class='col-2 border-bottom-{$colour}'>{$vals[0]}</div>
                  <div class='col-2 border-bottom-{$colour}'>{$vals[1]}</div>
                  <div class='col-2 border-bottom-{$colour}'>{$vals[2]}</div>
      </div>";
}

?>
<div class="owl-carousel">
    <?php
    foreach ($stock as $row)
        echo stockWidget($row);
    ?>
    <div class="owl-nav">
        <div class="owl-prev">prev</div>
        <div class="owl-next">next</div>
    </div>
    <div class="owl-dots">
        <div class="owl-dot active"><span></span></div>
        <div class="owl-dot"><span></span></div>
        <div class="owl-dot"><span></span></div>
    </div>
</div>

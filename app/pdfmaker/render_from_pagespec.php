<?php
// render_from_pagespec.php
// composer require tecnickcom/tcpdf setasign/fpdi-tcpdf
require __DIR__ . '/vendor/autoload.php';
use setasign\Fpdi\Tcpdf\Fpdi;

$spec = json_decode(file_get_contents(__DIR__ . '/pagespec.json'), true);
$pages = $spec['pages'] ?? [];

$pdf = new Fpdi('P','mm');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false);

$pt2mm = function($pt){ return $pt * 25.4 / 72.0; };
$fieldFill   = [235,235,235];
$fieldBorder = [180,180,180];

foreach ($pages as $page) {
    list($pw_pt, $ph_pt) = $page['size_pt'];
    $pdf->AddPage('P', [$pt2mm($pw_pt), $pt2mm($ph_pt)]);

    // rectangles
    foreach ($page['rects'] as $r) {
        list($x0,$y0,$x1,$y1) = $r['rect'];
        $pdf->SetLineWidth($r['width'] ?? 0.3);
        $pdf->SetDrawColor($r['stroke'][0], $r['stroke'][1], $r['stroke'][2]);
        if (!empty($r['fill'])) $pdf->SetFillColor($r['fill'][0], $r['fill'][1], $r['fill'][2]);
        $style = !empty($r['fill']) ? 'DF' : 'D';
        $pdf->Rect($pt2mm($x0), $pt2mm($y0), $pt2mm($x1-$x0), $pt2mm($y1-$y0), $style);
    }

    // text
    foreach ($page['text_spans'] as $s) {
        list($x0,$y0,$x1,$y1) = $s['bbox'];
        $size = (float)$s['size']; if ($size <= 8) $size = 9;
        $pdf->SetFont('helvetica','',$size);
        $pdf->SetTextColor($s['color'][0], $s['color'][1], $s['color'][2]);
        $x = $pt2mm($x0);
        $y = $pt2mm($y1) - ($size * 0.3528 * 0.25);
        $pdf->Text($x, $y, $s['text'], false, false, true, 0, 0, '', 0);
    }

    // fields
    foreach ($page['fields'] as $f) {
        list($x0,$y0,$x1,$y1) = $f['rect'];
        $x=$pt2mm($x0); $y=$pt2mm($y0); $w=$pt2mm($x1-$x0); $h=$pt2mm($y1-$y0);
        $pdf->SetFillColor($fieldFill[0],$fieldFill[1],$fieldFill[2]);
        $pdf->SetDrawColor($fieldBorder[0],$fieldBorder[1],$fieldBorder[2]);
        $pdf->Rect($x,$y,$w,$h,'DF');
        $fs = isset($f['font_size']) ? (float)$f['font_size'] : 9.0;
        if ($fs <= 8) $fs = 9;
        $pdf->SetFont('helvetica','',$fs);
        $name = $f['name'] ?: ('field_'.uniqid());
        $type = strtolower($f['type'] ?? 'text');
        if ($type === 'checkbox') {
            $pdf->CheckBox($name, min($w,$h), false, [
                'borderColor'=>[180,180,180],
                'fillColor'=>[235,235,235],
                'textColor'=>[0,0,0]
            ], [], $x, $y);
        } else {
            $pdf->TextField($name, $w, $h, [
                'borderColor'=>[180,180,180],
                'fillColor'=>[235,235,235],
                'textColor'=>[0,0,0]
            ], [], $x, $y);
        }
    }
}

$pdf->Output(__DIR__ . '/recreated.pdf', 'F');
echo "OK\n";

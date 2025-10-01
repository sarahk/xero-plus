<?php
// render_with_rhythm.php
// Enforces uniform field height for single-line fields and snaps rows to a vertical rhythm.
// Usage: php render_with_rhythm.php
// Requires: composer require tecnickcom/tcpdf setasign/fpdi-tcpdf

require __DIR__ . '/vendor/autoload.php';
use setasign\Fpdi\Tcpdf\Fpdi;

$spec = json_decode(file_get_contents(__DIR__ . '/pagespec.json'), true);
$pages = $spec['pages'] ?? [];

$pdf = new Fpdi('P','mm');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false);

// ----- Tunables -----
$pt2mm = function($pt){ return $pt * 25.4 / 72.0; };

$FIELD_HEIGHT_MM       = 7.0;   // enforced height for single-line text fields
$MULTILINE_CUTOFF_MM   = 12.0;  // fields taller than this are treated as multiline and left as-is
$RHYTHM_MM             = 8.0;   // baseline grid step (vertical rhythm)
$RHYTHM_TOP_MARGIN_MM  = 20.0;  // where the grid starts from the top of the page
$SNAP_THRESHOLD_MM     = 3.0;   // only snap if within this distance to the nearest grid line

$fieldFill   = [235,235,235];
$fieldBorder = [180,180,180];

foreach ($pages as $page) {
    list($pw_pt, $ph_pt) = $page['size_pt'];
    $pdf->AddPage('P', [$pt2mm($pw_pt), $pt2mm($ph_pt)]);

    // --- draw rectangles (as in the base renderer) ---
    foreach ($page['rects'] as $r) {
        list($x0,$y0,$x1,$y1) = $r['rect'];
        $pdf->SetLineWidth($r['width'] ?? 0.3);
        $pdf->SetDrawColor($r['stroke'][0], $r['stroke'][1], $r['stroke'][2]);
        if (!empty($r['fill'])) $pdf->SetFillColor($r['fill'][0], $r['fill'][1], $r['fill'][2]);
        $style = !empty($r['fill']) ? 'DF' : 'D';
        $pdf->Rect($pt2mm($x0), $pt2mm($y0), $pt2mm($x1-$x0), $pt2mm($y1-$y0), $style);
    }

    // --- text spans (bump <=8pt to 9pt) ---
    foreach ($page['text_spans'] as $s) {
        list($x0,$y0,$x1,$y1) = $s['bbox'];
        $size = (float)($s['size'] ?? 9);
        if ($size <= 8) $size = 9;
        $pdf->SetFont('helvetica','',$size);
        $pdf->SetTextColor($s['color'][0], $s['color'][1], $s['color'][2]);
        $x = $pt2mm($x0);
        $y = $pt2mm($y1) - ($size * 0.3528 * 0.25);
        $pdf->Text($x, $y, $s['text'], false, false, true, 0, 0, '', 0);
    }

    // --- interactive fields with uniform height + rhythm snap ---
    foreach ($page['fields'] as $f) {
        list($x0,$y0,$x1,$y1) = $f['rect'];
        $x = $pt2mm($x0); $y = $pt2mm($y0);
        $w = $pt2mm($x1 - $x0); $h = $pt2mm($y1 - $y0);

        $type = strtolower($f['type'] ?? 'text');
        $isSingleLineCandidate = ($type === 'text') && ($h <= $MULTILINE_CUTOFF_MM);

        // Enforce uniform height for single-line fields
        if ($isSingleLineCandidate) {
            // Keep vertical center the same to preserve relation to labels
            $cy = $y + $h/2.0;
            $h  = $FIELD_HEIGHT_MM;
            $y  = $cy - $h/2.0;
        }

        // Snap top to the nearest rhythm line if within threshold
        $offsetFromTop = $y - $RHYTHM_TOP_MARGIN_MM;
        if ($offsetFromTop >= 0) {
            $nearestIndex = round($offsetFromTop / $RHYTHM_MM);
            $snapY = $RHYTHM_TOP_MARGIN_MM + $nearestIndex * $RHYTHM_MM;
            if (abs($snapY - $y) <= $SNAP_THRESHOLD_MM) {
                // Shift box so its TOP aligns to the grid
                $y = $snapY;
            }
        }

        // Background + border (uniform style)
        $pdf->SetFillColor($fieldFill[0],$fieldFill[1],$fieldFill[2]);
        $pdf->SetDrawColor($fieldBorder[0],$fieldBorder[1],$fieldBorder[2]);
        $pdf->Rect($x,$y,$w,$h,'DF');

        // Field itself
        $fs = isset($f['font_size']) ? (float)$f['font_size'] : 9.0;
        if ($fs <= 8) $fs = 9;
        $pdf->SetFont('helvetica','',$fs);
        $name = $f['name'] ?: ('field_'.uniqid());

        if ($type === 'checkbox') {
            // Keep checkbox square; align to rhythm via top-left
            $size = min($w,$h);
            $pdf->CheckBox($name, $size, false, [
                'borderColor'=>$fieldBorder,
                'fillColor'=>$fieldFill,
                'textColor'=>[0,0,0],
            ], [], $x, $y);
        } else {
            $pdf->TextField($name, $w, $h, [
                'borderColor'=>$fieldBorder,
                'fillColor'=>$fieldFill,
                'textColor'=>[0,0,0],
            ], [], $x, $y);
        }
    }
}

$pdf->Output(__DIR__ . '/recreated_rhythm.pdf', 'F');
echo "OK\n";

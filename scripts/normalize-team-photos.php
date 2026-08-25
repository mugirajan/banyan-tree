<?php
/**
 * Normalise every Our Team photo to a 3:4 portrait WITHOUT cropping any person.
 *
 * The seven shots arrived with aspect ratios from 0.47:1 (team-5, full length)
 * to 1:1 (team-4). A single card shape can only fill edge-to-edge without
 * cropping if the files themselves already match it, so this squares them up:
 *
 *   ratio > 3:4  centre-crop, which removes only surplus side background
 *   ratio < 3:4  WIDEN, never crop - the outermost 8px of each edge is
 *                stretched outward and blurred, so the flat studio backdrops
 *                continue seamlessly and no pixel of a person is lost
 *
 * Mirroring the edge strip was tried first and ghosted team-5's arms into the
 * padding; stretching the very edge cannot duplicate a subject that does not
 * touch the frame edge, so it is used for both tall shots.
 *
 * Reads assets/images/about/originals/ (untouched shots) and writes
 * assets/images/about/. Safe to re-run. Run from the project root:
 *     php -d extension=gd scripts/normalize-team-photos.php
 */
$dir    = 'assets/images/about/originals/';
$out    = 'assets/images/about/';
$target = 3 / 4;
$strip  = 8;   // px of edge sampled for the stretch
$blur   = 8;   // gaussian passes over the added padding

foreach (glob($dir . '*.png') as $src) {
    $name = basename($src);
    $im   = imagecreatefrompng($src);

    $w    = imagesx($im);
    $h    = imagesy($im);
    $tw   = (int) round($h * $target);

    if ($tw === $w) {
        imagepng($im, $out . $name, 9);
        echo "$name {$w}x{$h} already 3:4\n";
        continue;
    }

    $canvas = imagecreatetruecolor($tw, $h);

    if ($tw < $w) {
        $x = (int) round(($w - $tw) / 2);
        imagecopy($canvas, $im, 0, 0, $x, 0, $tw, $h);
        echo "$name {$w}x{$h} -> {$tw}x{$h} cropped " . ($w - $tw) . "px of side background\n";
    } else {
        $pad  = (int) floor(($tw - $w) / 2);
        $padR = $tw - $w - $pad;
        imagecopy($canvas, $im, $pad, 0, 0, 0, $w, $h);

        $ext = function ($srcX, $destX, $width) use ($canvas, $im, $h, $strip, $blur) {
            // Stretch the edge strip sideways to fill the padding, then blur it
            // hard. GD's gaussian is a fixed 3x3 kernel - far too tight to soften
            // anything at this resolution - so the blur is done by shrinking the
            // strip, filtering it small, and scaling it back up. That turns the
            // furniture behind team-5 into a soft wash instead of a hard-edged
            // desk running off both sides of the frame.
            $p = imagecreatetruecolor($width, $h);
            imagecopyresampled($p, $im, 0, 0, $srcX, 0, $width, $h, $strip, $h);

            $sw = max(2, (int) ($width / 12));
            $sh = max(2, (int) ($h / 12));
            $small = imagecreatetruecolor($sw, $sh);
            imagecopyresampled($small, $p, 0, 0, 0, 0, $sw, $sh, $width, $h);
            for ($i = 0; $i < $blur; $i++) imagefilter($small, IMG_FILTER_GAUSSIAN_BLUR);
            imagecopyresampled($p, $small, 0, 0, 0, 0, $width, $h, $sw, $sh);

            imagecopy($canvas, $p, $destX, 0, 0, 0, $width, $h);
        };
        $ext(0, 0, $pad);                       // left edge stretched left
        $ext($w - $strip, $pad + $w, $padR);    // right edge stretched right

        echo "$name {$w}x{$h} -> {$tw}x{$h} widened {$pad}px/{$padR}px, no crop\n";
    }

    imagepng($canvas, $out . $name, 9);
}

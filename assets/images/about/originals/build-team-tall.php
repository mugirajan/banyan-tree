<?php
// Builds tems-tall.webp: the team group shot on a taller canvas so the
// homepage About Us frame can be covered edge to edge without the crop
// reaching the people at either end. Run from the project root:
//   php -d extension=gd assets/images/about/originals/build-team-tall.php
$src = imagecreatefrompng('assets/images/about/tems.png');
$w = imagesx($src); $h = imagesy($src);      // 1376 x 768
// More of the added height goes above the group than below: ceiling is
// what a tall frame should fill with, empty floor just reads as a gap.
$top = 712; $bot = 120; $fade = 70;          // -> 1376 x 1600, ratio 0.86
$dh = $h + $top + $bot;
$dst = imagecreatetruecolor($w, $dh);

// Top fill: the ceiling above them, built by tiling the photo's own ceiling.
//
// Rows 0-54 are pure ceiling - the recessed downlights, the top of the
// curtains on the left, bare wall on the right, and nothing else; the window
// frames and the back row of heads all start below that. The fill is a PLAIN
// continuation of it: one colour per column, held all the way to the top edge.
//
// The colour is the median of the band's 55 rows, not the average. The lights
// are bright outliers and a median throws them out, so what is left per column
// is the surface behind them - warm curtain on the left, plain white ceiling
// and wall across the rest. Holding one colour per column is what keeps it
// crisp: the curtain's vertical stripes carry straight up, since a vertical
// feature stretched vertically is indistinguishable from more of itself.
//
// Three earlier attempts are recorded here so they are not repeated. Copying
// the band as-is and stretching it turned every light into a hard bar running
// the full height. Smearing the band down to a few dozen pixels and blowing it
// back up removed the bars but took the curtain edge with them, and read as a
// blurred photo. Tiling the band upwards at growing scale reproduced the light
// rows convincingly, but was rejected as busy - a plain ceiling is wanted here.
$bandH = 55;
$fill = imagecreatetruecolor($w, $top + $fade);

for ($x = 0; $x < $w; $x++) {
  $r = []; $g = []; $b = [];
  for ($y = 0; $y < $bandH; $y++) {
    $c = imagecolorat($src, $x, $y);
    $r[] = ($c >> 16) & 0xFF; $g[] = ($c >> 8) & 0xFF; $b[] = $c & 0xFF;
  }
  sort($r); sort($g); sort($b);
  $m = (int) ($bandH / 2);
  $col = imagecolorallocate($fill, $r[$m], $g[$m], $b[$m]);
  imageline($fill, $x, 0, $x, $top + $fade, $col);
}

// One pass to take the edge off any column-to-column jitter. The fill has no
// vertical detail to lose, so this softens nothing that matters.
imagefilter($fill, IMG_FILTER_GAUSSIAN_BLUR);
imagecopy($dst, $fill, 0, 0, 0, 0, $w, $top + $fade);

// Bottom fill: the last 8 rows are clear floor tile, below everyone's
// feet, so they stretch down the same way.
$floor = imagecrop($src, ['x' => 0, 'y' => $h - 8, 'width' => $w, 'height' => 8]);
$tinyF = imagecreatetruecolor((int) round($w / 24), 3);
imagecopyresampled($tinyF, $floor, 0, 0, 0, 0, imagesx($tinyF), 3, $w, 8);
for ($i = 0; $i < 4; $i++) { imagefilter($tinyF, IMG_FILTER_GAUSSIAN_BLUR); }
$down = imagecreatetruecolor($w, $bot + $fade);
imagecopyresampled($down, $tinyF, 0, 0, 0, 0, $w, $bot + $fade, imagesx($tinyF), 3);
for ($i = 0; $i < 4; $i++) { imagefilter($down, IMG_FILTER_GAUSSIAN_BLUR); }
// It starts exactly at the photo's bottom edge, with no crossfade back over
// it. It is built from the photo's own last rows, so the two already meet
// seamlessly - and an earlier version that did fade it upwards washed a
// pale film across everyone's feet.
imagecopy($dst, $down, 0, $top + $h, 0, 0, $w, $bot);

// The photo, whole, with only its top $fade rows crossfaded in - 0 -> 100%
// - so the fabricated ceiling dissolves into the real one with no join.
imagecopy($dst, $src, 0, $top + $fade, 0, $fade, $w, $h - $fade);
for ($i = 0; $i < $fade; $i++) {
  imagecopymerge($dst, $src, 0, $top + $i, 0, $i, $w, 1, (int) round(100 * $i / $fade));
}

imagewebp($dst, 'assets/images/about/tems-tall.webp', 86);
echo imagesx($dst), "x", imagesy($dst), " ", filesize('assets/images/about/tems-tall.webp'), " bytes\n";

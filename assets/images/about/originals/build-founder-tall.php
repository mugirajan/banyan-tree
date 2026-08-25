<?php
// Builds founder-story-tall.webp: the founder portrait on a taller canvas so
// the Our Story image column can match the text column's height without the
// crop eating into him. Run from the project root:
//   php -d extension=gd assets/images/about/originals/build-founder-tall.php
$src = imagecreatefromwebp('assets/images/about/founder-story.webp');
$w = imagesx($src); $h = imagesy($src);      // 780 x 785
$top = 515; $fade = 90;                      // -> 780 x 1300, ratio 0.60
$dh = $h + $top;
$dst = imagecreatetruecolor($w, $dh);

// The first 80 rows are clear of his head - plain wall - so they stretch up
// to the new top edge and blur out into an even field. Drawn past the join
// (top + fade) so the photo can be faded in over it.
$band = imagecrop($src, ['x' => 0, 'y' => 0, 'width' => $w, 'height' => 80]);
$up = imagecreatetruecolor($w, $top + $fade);
imagecopyresampled($up, $band, 0, 0, 0, 0, $w, $top + $fade, $w, 80);
for ($i = 0; $i < 10; $i++) { imagefilter($up, IMG_FILTER_GAUSSIAN_BLUR); }
imagecopy($dst, $up, 0, 0, 0, 0, $w, $top + $fade);

// The photo, with its first $fade rows blended in a row at a time, 0 -> 100%,
// so the fabricated wall dissolves into the real one with no join to see.
imagecopy($dst, $src, 0, $top + $fade, 0, $fade, $w, $h - $fade);
for ($i = 0; $i < $fade; $i++) {
  imagecopymerge($dst, $src, 0, $top + $i, 0, $i, $w, 1, (int) round(100 * $i / $fade));
}

imagewebp($dst, 'assets/images/about/founder-story-tall.webp', 88);
echo imagesx($dst), "x", imagesy($dst), " ", filesize('assets/images/about/founder-story-tall.webp'), " bytes\n";

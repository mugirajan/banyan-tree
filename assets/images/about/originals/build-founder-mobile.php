<?php
// Builds assets/images/about/founder-story-mobile.webp - the Our Story
// portrait with extra wall above him, so the "25+ Years" badge in the
// top-right corner clears his head on phones. Run from the project root.
//
// The badge is a fixed ~84px tall, while the wall above his head scales
// with the column: 90px of the source photo's 785 is only ~46px once the
// photo is drawn 400px wide. 180px more takes it to ~139px at 425px wide
// and ~102px at 320px, both clear of the badge.
$src = imagecreatefromwebp('assets/images/about/founder-story.webp');
$w = imagesx($src); $h = imagesy($src);      // 780 x 785
$top = 180; $fade = 110;                     // -> 780 x 965

$dst = imagecreatetruecolor($w, $h + $top);

// The wall behind him is a flat vertical gradient with soft vertical
// streaks, so its top band stretches upward without showing - the streaks
// just carry on. Drawn past the join so the photo can fade in over it.
$band = imagecrop($src, ['x' => 0, 'y' => 0, 'width' => $w, 'height' => 60]);
$up = imagecreatetruecolor($w, $top + $fade);
imagecopyresampled($up, $band, 0, 0, 0, 0, $w, $top + $fade, $w, 60);
for ($i = 0; $i < 3; $i++) { imagefilter($up, IMG_FILTER_GAUSSIAN_BLUR); }
imagecopy($dst, $up, 0, 0, 0, 0, $w, $top + $fade);

// The photo, its first rows blended in one at a time so there is no join.
imagecopy($dst, $src, 0, $top + $fade, 0, $fade, $w, $h - $fade);
for ($i = 0; $i < $fade; $i++) {
  imagecopymerge($dst, $src, 0, $top + $i, 0, $i, $w, 1, (int) round(100 * $i / $fade));
}

imagewebp($dst, 'assets/images/about/founder-story-mobile.webp', 88);
echo imagesx($dst), "x", imagesy($dst), " ", filesize('assets/images/about/founder-story-mobile.webp'), " bytes\n";

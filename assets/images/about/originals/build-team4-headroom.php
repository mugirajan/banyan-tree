<?php
// Rebuilds assets/images/about/team-4.png with headroom above her hair.
//
// The photo as supplied (880x1216) is framed so tightly that the top of her
// hair touches the frame edge, and the card is 3:4 - so `cover` trimmed a
// further ~20px off the top and the head read as cut. The backdrop is a flat
// studio blue, so the fix is simply to give her some: the canvas is built at
// exactly 3:4, the top $pad rows filled with that same blue, and the photo
// dropped below it. The surplus comes off the bottom of her clothing, which
// the name badge covers anyway.
//
// Reads the untouched copy in originals/team4/ so it is safe to re-run - it
// never feeds its own output back in. Run from the project root:
//   php -d extension=gd assets/images/about/originals/build-team4-headroom.php
$src = imagecreatefrompng('assets/images/about/originals/team4/team-4.png');
$w = imagesx($src); $h = imagesy($src);      // 880 x 1216
$pad = 70;                                   // headroom, ~6% of the card
$dh = (int) round($w * 4 / 3);               // 1173 - exactly 3:4, so the card crops nothing

$dst = imagecreatetruecolor($w, $dh);
$bg = imagecolorat($src, 5, 5);              // the flat backdrop, sampled top-left
imagefilledrectangle($dst, 0, 0, $w, $dh, $bg);
imagecopy($dst, $src, 0, $pad, 0, 0, $w, $dh - $pad);

imagepng($dst, 'assets/images/about/team-4.png', 9);
echo $w, "x", $dh, " (3:4), ", $pad, "px headroom, ",
     filesize('assets/images/about/team-4.png'), " bytes\n";

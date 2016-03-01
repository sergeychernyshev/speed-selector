<?php

$results = file_get_contents($argv[1]);
$mask = $argv[2];

$image_width = 652;
$image_height = 544;
$image_left = 6;
$image_top = 36;

$scrollbar_width = 8;

// mask size
$frame_width = $image_width + $image_left;
$frame_height = $image_height + $image_top;

$image_right = $frame_width;
$image_bottom = $frame_height;

$image_info = explode(':', preg_replace('|^.*<selector-boundaries>|s', '', $results));
$viewport_width = $image_info[0];
$scale = $image_width / $viewport_width;

$boundaries = preg_replace('|</selector-boundaries>.*$|s', '', $image_info[1]);
$boundaries = explode(';', rtrim($boundaries, ';'));
$boundaries = array_splice($boundaries, 2);

#$boundaries = array("0,0,1280,1024"); # debugging override
var_export($boundaries);

$boundaries = array_map(function ($box) {
  global $viewport_width, $scale, $image_width, $image_height, $scrollbar_width;

  $quad = explode(',', $box);
  $x1 = $quad[0];
  $y1 = $quad[1];
  $x2 = $quad[2];
  $y2 = $quad[3];

  return array(
    round(($x2 - $x1) * $scale), // width
    round(($y2 - $y1) * $scale), // height
    round($x1 * $scale), // left
    round($y1 * $scale), // top
  );
}, $boundaries);

var_export($boundaries);

$command = "convert -size ${frame_width}x${frame_height} xc:transparent ";
$command .= " -fill yellow -draw 'rectangle ${image_left},${image_top} ${image_right},${image_bottom}' ";

foreach ($boundaries as $b) {
  $w = $b[0];
  $h = $b[1];
  $l = $b[2] + $image_left;
  $t = $b[3] + $image_top;

  $command .= "-alpha set -region ${w}x${h}+${l}+${t} -alpha transparent +region ";
}
$command .= $mask;

echo $command . "\n";
passthru($command);

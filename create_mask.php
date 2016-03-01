<?php

// reference screenshot identifying
$reference_image = $argv[1];

// video frame file to get measurements from
$video_frame = $argv[2];

$results = file_get_contents($argv[3]);
$mask = $argv[4];

$image_size = getimagesize($reference_image);
$image_width = $image_size[0];
$image_height = $image_size[1];

// mask size
$frame_size = getimagesize($video_frame);
$frame_width = $frame_size[0];
$frame_height = $frame_size[1];

// center the image in the frame
$image_left = round(($frame_width - $image_width) / 2);
$image_top = 36; // fixed offset from the top

# create mask frame
$command = "convert -size ${frame_width}x${frame_height} xc:transparent ";

$image_right = $image_width + $image_left;
$image_bottom = $image_height + $image_top;

# draw full frame video mask (to be substracted from later)
$command .= " -fill yellow -draw 'rectangle ${image_left},${image_top} ${image_right},${image_bottom}' ";

# measured from the video frame
$scrollbar_width = 9;
$scrollbar_left = $image_right - $scrollbar_width;
$command .= " -fill green -draw 'rectangle ${scrollbar_left},${image_top} ${image_right},${image_bottom}' ";

$image_info = explode(':', preg_replace('|^.*<selector-boundaries>|s', '', $results));
$viewport_width = $image_info[0];
$scale = ($image_width - $scrollbar_width) / $viewport_width;

$boundaries = preg_replace('|</selector-boundaries>.*$|s', '', $image_info[1]);
$boundaries = explode(';', rtrim($boundaries, ';'));

$boundaries = array_splice($boundaries, 2, 1); # just one box for debugging

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

foreach ($boundaries as $b) {
  $w = $b[0];
  $h = $b[1];
  $l = $b[2] + $image_left;
  $t = $b[3] + $image_top;

  # make important areas of the mask transparent
  $command .= "-alpha set -region ${w}x${h}+${l}+${t} -alpha transparent +region ";
}

# file to be generated
$command .= $mask;

echo $command . "\n";
passthru($command);

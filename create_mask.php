<?php
/**
 * Creates a mask based on original frame size and data from speed slector custom metric
 */

// video frame file to get measurements from
$video_frame = $argv[1];

$results = file_get_contents($argv[2]);
$mask = $argv[3];

// calculating mask dimentions based on original frame size
$image_size = getimagesize($video_frame);
$image_width = $image_size[0];
$image_height = $image_size[1];

# create mask frame
$command = "convert -size ${image_width}x${image_height} xc:white ";

// extracting JSON-encoded value from XML
$metric_value = preg_replace('|^.*<selector-boundaries>|s', '', $results);
$metric_value = preg_replace('|</selector-boundaries>.*$|s', '', $metric_value);
$metric_value = html_entity_decode(html_entity_decode($metric_value));

// decoding JSON payload
$image_info = json_decode($metric_value, true);
$viewport_width = $image_info['viewport_width'];
$scrollbar_width = $image_info['scrollbar_width'];
$scale = $image_width / ($viewport_width + $scrollbar_width);

#$scrollbar_left = $image_width - $scrollbar_width * $scale;
#$command .= " -fill grey -draw 'rectangle ${scrollbar_left},0 ${image_width},${image_height}' ";

$boundaries = array_map(function ($box) {
  global $scale;

  return array(
    round(($box['right'] - $box['left']) * $scale), // width
    round(($box['bottom'] - $box['top']) * $scale), // height
    round($box['left'] * $scale), // left
    round($box['top'] * $scale), // top
  );
}, $image_info['boundaries']);

#var_export($boundaries);

foreach ($boundaries as $b) {
  $w = $b[0];
  $h = $b[1];
  $l = $b[2];
  $t = $b[3];

  # make important areas of the mask transparent
  $command .= "-alpha set -region ${w}x${h}+${l}+${t} -alpha transparent +region ";
}

# file to be generated
$command .= $mask;

echo $command . "\n";
passthru($command);

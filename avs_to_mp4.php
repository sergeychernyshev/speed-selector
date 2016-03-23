<?php

// a file with Avisynth script
$avs = $argv[1];

// base path to frame files
$frame_directory = dirname($avs);

// filename of the resulting video
$output_video = $argv[2];

// ffmpeg -loop 1 -f image2 -r 10
// -i results/160311_1Y_1C7W/frames/frame_0016.jpg -t 16
// -c:v libx264 -pix_fmt yuv420p out.mp4

echo "ffmpeg -loop 1 -r 10 -f image2 ";

// Frame entries in the format like the following
// ImageSource("frame_0000.jpg", start = 1, end = 16, fps = 10)
$frames = file($argv[1]);
foreach ($frames as $frame) {
  $frame = preg_replace('/.*\((.*)\).*/', '$1', $frame);
  $params = explode(',', $frame);

  // first is a file name
  $filename = trim(array_shift($params), '" ');

  echo " -i \"${frame_directory}/{$filename}\" ";

  foreach ($params as $param) {
    $pair = explode('=', $param);
    $named_params[trim($pair[0])] = trim($pair[1]);

  }

  echo "-t " . ($named_params['end'] - $named_params['start'] + 1) / $named_params['fps'];
}

echo " -vcodec libx264 -pix_fmt yuv420p ${output_video}\n";

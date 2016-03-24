<?php
/**
 * Overlays diff images over corresponding video frames
 *
 * Usage: php diff_overlay.php /path/to/video.avs /path/to/diffs_folder/ /path/to/output_overlay_folder/
 */

// a file with Avisynth script
$avs = $argv[1];

// base path to frame files
$frame_directory = dirname($avs);

// directory of diff frames
$diff_directory = $argv[2];

// directory of output overlayed frames
$overlay_directory = $argv[3];

// Frame entries in the format like the following
// ImageSource("frame_0000.jpg", start = 1, end = 16, fps = 10)
$frames = file($avs);

foreach ($frames as $frame) {
    $frame = preg_replace('/.*\((.*)\).*/', '$1', $frame);
    $params = explode(',', $frame);

    // first is a file name
    $filename = trim(array_shift($params), '" ');

    passthru("convert -negate ${diff_directory}/${filename} ${overlay_directory}/${filename}");

    passthru("composite -blend 70 ${overlay_directory}/${filename} ${frame_directory}/${filename} ${overlay_directory}/${filename}");
}

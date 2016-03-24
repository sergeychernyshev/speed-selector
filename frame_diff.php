<?php
/**
 * Creates a folder with video diffs given a directory of video frames
 *
 * Usage: php frame_diff.php /path/to/frames/video.avs /path/to/frames/ /path/to/output_diffs_folder/
 */

// a file with Avisynth script
$avs = $argv[1];

// base path to frame files
$frame_directory = $argv[2];

// directory of output diff frames
$diff_directory = $argv[3];

// Frame entries in the format like the following
// ImageSource("frame_0000.jpg", start = 1, end = 16, fps = 10)
$frames = file($avs);

// used for generating diffs (initial frame has nothing to diff to)
$previous_frame_filename = null;

foreach ($frames as $frame) {
    $frame = preg_replace('/.*\((.*)\).*/', '$1', $frame);
    $params = explode(',', $frame);

    // first is a file name
    $filename = trim(array_shift($params), '" ');

    // create pdiff image
    if (is_null($previous_frame_filename)) {
      passthru("convert ${frame_directory}/${filename} -alpha transparent -alpha extract ${diff_directory}/${filename}");
    } else {
      passthru("perceptualdiff ${frame_directory}/${previous_frame_filename} ${frame_directory}/${filename} -output ${diff_directory}/${filename} && convert ${frame_directory}/${filename} -alpha transparent -alpha extract ${diff_directory}/${filename}");
    }

    $previous_frame_filename = $filename;
}

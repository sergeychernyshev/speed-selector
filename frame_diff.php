<?php
/**
 * Creates a folder with video diffs given a directory of video frames
 *
 * Usage: php frame_diff.php /path/to/frames/video.avs /path/to/frames/ /path/to/output_diffs_folder/
 */

require __DIR__ . '/classes/Frame.php';

$video_frames = Frame::getFromAvysinth($argv[1], $argv[2]);

// directory of output diff frames
$diff_directory = $argv[3];

// used for generating diffs (initial frame has nothing to diff to)
$previous_frame = null;

foreach ($video_frames as $frame) {
    $frame->diffFrom($diff_directory, $previous_frame);

    $previous_frame = $frame;
}

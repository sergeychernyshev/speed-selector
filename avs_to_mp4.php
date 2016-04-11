<?php
/**
 * Creates a video from an Avisynth script and a folder of frame images
 *
 * Usage: php avs_to_mp4.php /path/to/video.avs /path/to/frames_folder/ /path/to/output_video.mp4
 */

require __DIR__ . '/classes/Video.php';

$mp4 = new Video($argv[1], $argv[2], $argv[3]);

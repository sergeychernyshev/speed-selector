<?php
/**
 * Applies a mask to all frames in avisynch script
 *
 * Usage: php apply_mask.php /path/to/mask.png /path/to/frames/video.avs 1 /path/to/frames/ /path/to/output_masked_folder/
 */

require __DIR__ . '/classes/Mask.php';

$mask = new Mask($argv[1]);
$mask->apply($argv[2], $argv[3], $argv[4], $argv[5]);

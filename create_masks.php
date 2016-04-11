<?php
/**
 * Creates a mask based on original frame size and data from speed slector custom metric.
 */

require __DIR__ . '/classes/MaskMaker.php';

$mask_maker = new MaskMaker($argv[1], $argv[2], $argv[3]);
$masks = $mask_maker->create_masks();

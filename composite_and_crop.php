<?php

// reference screenshot identifying
$reference_image = $argv[1];

// video frame file to mask and crop
$video_frame = $argv[2];

// mask file
$mask_image = $argv[3];

// result
$masked_frame = $argv[4];

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

# create masked frame
$command = "composite $mask_image $video_frame $masked_frame";
echo $command;
passthru($command);

# clip the resulting frame_height
$command = "convert $masked_frame -crop ${image_width}x${image_height}+${image_left}+${image_top} $masked_frame";
echo $command;
passthru($command);

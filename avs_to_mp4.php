<?php

// a file with Avisynth script
$avs = $argv[1];

// base path to frame files
$frame_directory = dirname($avs);

// filename of the resulting video
$output_video = $argv[2];

// filename of the resulting diff video
$output_diff_video = $argv[3];

// ffmpeg -loop 1 -f image2 -r 10
// -i results/160311_1Y_1C7W/frames/frame_0016.jpg -t 16
// -c:v libx264 -pix_fmt yuv420p out.mp4

// Frame entries in the format like the following
// ImageSource("frame_0000.jpg", start = 1, end = 16, fps = 10)
$frames = file($argv[1]);

// array of resulting frame files
$video_files = array();

// array of diff frame files
$video_diff_files = array();

// used for generating diffs (initial frame has nothing to diff to)
$previous_frame_filename = null;

foreach ($frames as $frame) {
    $frame = preg_replace('/.*\((.*)\).*/', '$1', $frame);
    $params = explode(',', $frame);

    // first is a file name, the rest are
    $filename = trim(array_shift($params), '" ');

    // create pdiff image
    $diff_filename = preg_replace('/\..*$/', '_diff.jpg', $filename);
    if (is_null($previous_frame_filename)) {
      passthru("convert ${frame_directory}/${filename} -alpha transparent -alpha extract ${frame_directory}/${diff_filename}");
    } else {
      passthru("~/pdiff/perceptualdiff ${frame_directory}/${previous_frame_filename} ${frame_directory}/${filename} -output ${frame_directory}/${diff_filename}");
    }

    $previous_frame_filename = $filename;

    foreach ($params as $param) {
        $pair = explode('=', $param);
        $named_params[trim($pair[0])] = trim($pair[1]);
    }

    $duration = ($named_params['end'] - $named_params['start'] + 1) / $named_params['fps'];

    // generate video chunks for each frame
    $video_filename = preg_replace('/\..*$/', '.mp4', $filename);
    passthru("ffmpeg -loop 1 -r 10 -f image2 -i \"${frame_directory}/{$filename}\" -t ${duration} -vcodec libx264 -pix_fmt yuv420p ${frame_directory}/${video_filename}\n");

    // generate video chunks for each diff
    $video_diff_filename = preg_replace('/\..*$/', '_diff.mp4', $filename);
    passthru("ffmpeg -loop 1 -r 10 -f image2 -i \"${frame_directory}/{$diff_filename}\" -t ${duration} -vcodec libx264 -pix_fmt yuv420p ${frame_directory}/${video_diff_filename}\n");

    $video_files[] = "file '${video_filename}'";
    $video_diff_files[] = "file '${video_diff_filename}'";
}

file_put_contents("${frame_directory}/video_frames.txt", implode("\n", $video_files) . "\n");
file_put_contents("${frame_directory}/video_diff_frames.txt", implode("\n", $video_diff_files) . "\n");

passthru("ffmpeg -f concat -i ${frame_directory}/video_frames.txt -vcodec libx264 -pix_fmt yuv420p  ${output_video}");
passthru("ffmpeg -f concat -i ${frame_directory}/video_diff_frames.txt -vcodec libx264 -pix_fmt yuv420p  ${output_diff_video}");

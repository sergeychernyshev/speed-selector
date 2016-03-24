<?php
/**
 * Creates a video from an Avisynth script and a folder of frame images
 *
 * Usage: php avs_to_mp4.php /path/to/video.avs /path/to/frames_folder/ /path/to/output_video.mp4
 */

// a file with Avisynth script
$avs = $argv[1];

// base path to frame files
$frame_directory = $argv[2];

// filename of the resulting video
$output_video = $argv[3];

// Frame entries in the format like the following
// ImageSource("frame_0000.jpg", start = 1, end = 16, fps = 10)
$frames = file($avs);

// array of resulting frame files
$video_files = array();

foreach ($frames as $frame) {
    $frame = preg_replace('/.*\((.*)\).*/', '$1', $frame);
    $params = explode(',', $frame);

    // first is a file name, the rest are
    $filename = trim(array_shift($params), '" ');

    foreach ($params as $param) {
        $pair = explode('=', $param);
        $named_params[trim($pair[0])] = trim($pair[1]);
    }

    $duration = ($named_params['end'] - $named_params['start'] + 1) / $named_params['fps'];

    // generate video chunks for each frame
    $video_filename = preg_replace('/\..*$/', '.mp4', $filename);
    passthru("ffmpeg -y -loop 1 -r 10 -f image2 -i \"${frame_directory}/{$filename}\" -t ${duration} -vcodec libx264 -pix_fmt yuv420p -vf \"scale=trunc(iw/2)*2:trunc(ih/2)*2\" ${frame_directory}/${video_filename}\n");

    $video_files[] = "file '${video_filename}'";
}

file_put_contents("${frame_directory}/video_frames.txt", implode("\n", $video_files) . "\n");

passthru("ffmpeg -y -f concat -i ${frame_directory}/video_frames.txt -vcodec libx264 -pix_fmt yuv420p  ${output_video}");

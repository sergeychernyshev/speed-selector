<?php

require __DIR__ . '/Frame.php';

class Video {
  /**
   * Creates a video from an Avisynth script and a folder of frame images
   *
   * @param string $avs                   File with Avisynth script
   * @param string $frame_directory       Base path to frame files
   * @param string $output_video_filename Filename of the resulting video
   */
  function __construct($avs, $frame_directory, $output_video_filename) {
    $video_frames = Frame::getFromAvysinth($avs, $frame_directory);

    // array of instructions for ffmpeg to combine separate files
    $video_files = array();

    foreach ($video_frames as $frame) {
      $video_filename = $frame->generateVideoChunk();
      $video_files[] = "file '${video_filename}'";
    }

    file_put_contents("${frame_directory}/video_frames.txt", implode("\n", $video_files) . "\n");

    passthru("ffmpeg -y -f concat -i ${frame_directory}/video_frames.txt -vcodec libx264 -pix_fmt yuv420p  ${output_video_filename}");
  }
}

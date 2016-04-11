<?php

class Frame {
  /**
   * Folder that contains the frame file
   *
   * @var string
   */
  private $folder_name;

  /**
   * Name of the file containing the frame
   * @var string
   */
  private $file_name;

  /**
   * Name of the video chunk file
   * @var string
   */
  private $video_chunk_file_name;

  /**
   * Duration of the frame (in seconds)
   * @var int
   */
  private $duration;

  private function __construct($folder_name, $file_name, $duration) {
    $this->folder_name = $folder_name;
    $this->file_name = $file_name;
    $this->video_chunk_file_name = preg_replace('/\..*$/', '.mp4', $file_name);
    $this->duration = $duration;
  }

  public function getFullFileName() {
    return $this->folder_name . '/' . $this->file_name;
  }

  public function getVideoChunkFullFileName() {
    return $this->folder_name . '/' . $this->video_chunk_file_name;
  }

  /**
   * Creates an array of frames based on Avisynth script and a folder of frame images
   *
   * @param string $avs                   File with Avisynth script
   * @param string $frames_folder         Base path to frame files
   *
   * @return Frame[]                      Returns an array of frames
   */
  public static function getFromAvysinth($avs, $frames_folder) {
    // Frame entries in the format like the following
    // ImageSource("frame_0000.jpg", start = 1, end = 16, fps = 10)
    $avs_frames = file($avs);

    $video_frames = array();

    foreach ($avs_frames as $frame) {
        $frame = preg_replace('/.*\((.*)\).*/', '$1', $frame);
        $params = explode(',', $frame);

        // first is a file name, the rest are
        $filename = trim(array_shift($params), '" ');

        foreach ($params as $param) {
            $pair = explode('=', $param);
            $named_params[trim($pair[0])] = trim($pair[1]);
        }

        $duration = ($named_params['end'] - $named_params['start'] + 1) / $named_params['fps'];

        $video_frames[] = new Frame($frames_folder, $filename, $duration);
    }

    return $video_frames;
  }

  /**
   * Generate video chunk file for the frame
   *
   * @return string File path to resulting video chunk
   */
  public function generateVideoChunk() {
    $full_filename = $this->getFullFileName();
    $video_full_filename = $this->getVideoChunkFullFileName();

    $command = 'ffmpeg -y -loop 1 -r 10 -f image2 -i "' . $full_filename . '"';
    $command .= " -t " . $this->duration;
    $command .= ' -vcodec libx264 -pix_fmt yuv420p -vf "scale=trunc(iw/2)*2:trunc(ih/2)*2"';
    $command .= ' "' . $video_full_filename . '"';

    passthru($command);

    return $this->video_chunk_file_name;
  }

  /**
   * Creates a diff in the folder specified from a previous frame (which can be null if it's a first frame)
   *
   * @param  string     $diff_directory Path to diff folder
   * @param  Frame|null $previous_frame Previous frame (or null if first frame)
   * @return string                     Resulting filename
   */
  public function diffFrom($diff_directory, $previous_frame = null) {
    $output_filename = $diff_directory . '/' . $this->file_name;

    $frame_full_filename = $this->getFullFileName();

    if (is_null($previous_frame)) {
      $command = "convert ${frame_full_filename} -alpha transparent -alpha extract ${output_filename}";
      echo "$command\n";
      passthru($command);
    } else {
      $previous_full_frame_filename = $previous_frame->getFullFileName();

      $diff_command = "perceptualdiff ${previous_full_frame_filename} ${frame_full_filename}";
      $diff_command .= " -output ${output_filename}";
      echo "$diff_command\n";
      passthru($diff_command);
    }

    return $output_filename;
  }
}

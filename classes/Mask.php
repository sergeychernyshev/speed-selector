<?php

class Mask {
  /**
   * Mask file name
   *
   * @var string
   */
  private $mask_filename;

  /**
   * Creates a mask object based on a mask file
   *
   * @param string $mask_filename  Mask file name
   */
  public function __construct($mask_filename) {
    $this->mask_filename = $mask_filename;
  }

  /**
   * Applies a mask to all frames in avisynch script using passed blend level
   *
   * @param  string $avs              a file with Avisynth script
   * @param  int    $blend            blend level (float between 0-1)
   * @param  string $frame_directory  base path to frame files
   * @param  string $masked_directory directory of output masked frames
   * @return [type]                   [description]
   */
  public function apply($avs, $blend, $frame_directory, $masked_directory) {
    // Frame entries in the format like the following
    // ImageSource("frame_0000.jpg", start = 1, end = 16, fps = 10)
    $frames = file($avs);

    foreach ($frames as $frame) {
        $frame = preg_replace('/.*\((.*)\).*/', '$1', $frame);
        $params = explode(',', $frame);

        // first is a file name
        $filename = trim(array_shift($params), '" ');

        $command = "convert " . $this->mask_filename;
        $command .= " -alpha set -channel a -evaluate multiply ${blend}";
        $command .= " ${frame_directory}/${filename}";
        $command .= " -compose overlay -composite ${masked_directory}/${filename}";

        passthru($command);
    }
  }
}

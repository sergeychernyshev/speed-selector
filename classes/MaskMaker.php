<?php

require __DIR__ . '/Mask.php';

class MaskMaker
{
  private $masks_folder;
  private $viewport_width;
  private $scrollbar_width;
  private $image_width;
  private $image_height;
  private $scale;
  private $zones;

  /**
   * Initializes Mask Maker object.
   *
   * @param string $video_frame  Video frame file to get image measurements from
   * @param string $results_file WebPageTest XML results file
   * @param string $masks_folder Output folder to create masks in
   */
  public function __construct($video_frame, $results_file, $masks_folder)
  {
      $this->masks_folder = $masks_folder;

      // calculating mask dimentions based on original frame size
      $image_size = getimagesize($video_frame);
      $this->image_width = $image_size[0];
      $this->image_height = $image_size[1];

      // extracting JSON-encoded value from XML
      $results = file_get_contents($results_file);
      $metric_value = preg_replace('|^.*<selector-boundaries>|s', '', $results);
      $metric_value = preg_replace('|</selector-boundaries>.*$|s', '', $metric_value);
      $metric_value = html_entity_decode(html_entity_decode($metric_value));

      // decoding JSON payload
      $image_info = json_decode($metric_value, true);
      $this->viewport_width = $image_info['viewport_width'];
      $this->scrollbar_width = $image_info['scrollbar_width'];
      $this->scale = $this->image_width / ($this->viewport_width + $this->scrollbar_width);

      $this->zones = $image_info['zones'];
  }

  public function create_masks()
  {
    $masks = array();

    var_export($this->zones); exit;

    foreach ($this->zones as $zone) {
        $mask_filename = $this->masks_folder.'/'.$zone['slug'].'.png';
        $masks[] = $this->create_mask($mask_filename, $zone['boundaries']);
    }
  }

  public function create_mask($mask_filename, $measured_boxes)
  {
    # create mask frame
    $command = 'convert -size '.$this->image_width.'x'.$this->image_height.' xc:white ';

    #$scrollbar_left = $this->image_width - $this->scrollbar_width * $scale;
    #$command .= " -fill grey -draw 'rectangle ${scrollbar_left},0 ".$this->image_width.",".$this->image_height."' ";

    $boundaries = array();

    foreach ($measured_boxes as $box) {
      $boundaries[] = array(
        round(($box['right'] - $box['left']) * $this->scale), // width
        round(($box['bottom'] - $box['top']) * $this->scale), // height
        round($box['left'] * $this->scale), // left
        round($box['top'] * $this->scale), // top
      );
    }

    foreach ($boundaries as $b) {
      $w = $b[0];
      $h = $b[1];
      $l = $b[2];
      $t = $b[3];

      # make important areas of the mask transparent
      $command .= " -alpha set -region ${w}x${h}+${l}+${t} -alpha transparent +region ";
    }

    # file to be generated
    $command .= " $mask_filename";
    echo $command."\n";
    passthru($command);

    return new Mask($mask_filename);
  }
}

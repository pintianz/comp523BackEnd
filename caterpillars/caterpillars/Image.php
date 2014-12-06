<?php
/* * File: SimpleImage.php * Author: Simon Jarvis * Copyright: 2006 Simon Jarvis * Date: 08/11/06  * */
class Image
{
    var $image;
    var $image_type;
    
    private function __construct($image, $image_type){
		$this->image = $image;
		$this->image_type = $image_type;
    }
    
    public static function getByFilename($filename)
    {
        $image_info       = getimagesize($filename);
        $image_type = $image_info[2];
        
        if ($image_type == IMAGETYPE_JPEG) {
            $image = imagecreatefromjpeg($filename);
        } elseif ($image_type == IMAGETYPE_GIF) {
            $image = imagecreatefromgif($filename);
        } elseif ($image_type == IMAGETYPE_PNG) {
            $image = imagecreatefrompng($filename);
        } else {
            return -1;
        }
        return new Image($image, $image_type);
    }

    public function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
    {
        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image, $filename, $compression);
        } elseif ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image, $filename);
        } elseif ($image_type == IMAGETYPE_PNG) {
            imagepng($this->image, $filename);
        } else {
            return -1;
        }
        if ($permissions != null) {
            chmod($filename, $permissions);
        }
        return 1;
    }
    private function output($image_type = IMAGETYPE_JPEG)
    {
        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image);
        } elseif ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image);
        } elseif ($image_type == IMAGETYPE_PNG) {
            imagepng($this->image);
        }
    }
    public function getWidth()
    {
        return imagesx($this->image);
    }
    public function getHeight()
    {
        return imagesy($this->image);
    }
    public function resizeToHeight($height)
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }
    public function resizeToWidth($width)
    {
        $ratio  = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width, $height);
    }
    public function scale($scale)
    {
        $width  = $this->getWidth() * $scale / 100;
        $height = $this->getheight() * $scale / 100;
        $this->resize($width, $height);
    }
    public function resize($width, $height)
    {
        $new_image = imagecreatetruecolor(intval($width), intval($height));
        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
    }
    public function getImage()
    {
        return $this->image;
    }
    public function getImage_type()
    {
        return $this->image_type;
    }
}
?>
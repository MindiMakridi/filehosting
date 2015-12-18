<?php
namespace MyModels;
class Thumbnail
{
    protected $fileName;
    protected $path;
    protected $url;
    protected $thumbWidth;
    protected $thumbHeight;
    protected $extension;
    protected $imageSize;
    protected $imageFormat;
    
    public function __construct($fileName, $path, $maxWidth, $maxHeight = NULL)
    {
        $this->fileName   = $fileName;
        $this->thumbWidth = $maxWidth;
        $this->path       = $path;
        if ($maxHeight == NULL) {
            $maxHeight = $maxWidth;
        }
        $this->thumbHeight = $maxHeight;
        
        $this->imageSize = getimagesize($this->getSrcImagePath());
        
        if (!$this->imageSize) {
            throw new PreviewGenerationException("Incorrect file extension");
        }
        $this->imageFormat = $this->imageSize[2];
        
    }
    
    
    protected function getThumbPath()
    {
        $thumbPath = $this->path . "/thumbs/" . $this->fileName;
        return $thumbPath;
    }
    protected function getSrcImagePath()
    {
        $srcImagePath = $this->path . "/files/" . $this->fileName;
        return $srcImagePath;
    }
    
    
    protected function getExtension()
    {
        
        
        switch ($this->imageFormat) {
            case IMAGETYPE_GIF:
                return "gif";
            
            case IMAGETYPE_JPEG:
                return "jpeg";
            
            case IMAGETYPE_PNG:
                return "png";
            
            default:
                throw new PreviewGenerationException("Incorrect file extension");
                
        }
    }
    
    
    protected function getImageFunction()
    {
        $imageFunction = "image" . $this->getExtension();
        return $imageFunction;
    }
    
    protected function getImageCreateFunction()
    {
        $imageCreateFunction = "imagecreatefrom" . $this->getExtension();
        return $imageCreateFunction;
    }
    
    protected function getMime()
    {
        $size = $this->imageSize;
        
        return $size['mime'];
    }
    
    
    public function createThumbnail()
    {
        if (!file_exists($this->getSrcImagePath())) {
            throw new PreviewGenerationException("File doesn't exist");
        }
        
        $image = call_user_func($this->getImageCreateFunction(), $this->getSrcImagePath());
        imagealphablending($image, true);
        
        $width  = imagesx($image);
        $height = imagesy($image);
        $scaleX = $this->thumbWidth / $width;
        $scaleY = $this->thumbHeight / $height;
        $scale  = min($scaleX, $scaleY);
        if ($scale > 1) {
            $scale = 1;
        }
        $newWidth  = floor($width * $scale);
        $newHeight = floor($height * $scale);
        $tmpImage  = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($tmpImage, false);
        imagesavealpha($tmpImage, true);
        
        
        imagecopyresampled($tmpImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        call_user_func($this->getImageFunction(), $tmpImage, $this->getThumbPath());
        return true;
        
        
    }
    
    
    public function showThumbnail()
    {
        
        $this->createThumbnail();
        $image = file_get_contents($this->getThumbPath());
        
        
        header("Content-Type: {$this->getMime()}");
        echo $image;
        
        
    }
    
    
    
    
}
<?php
namespace Filehosting;
class Thumbnail
{
    protected $src;
    protected $dst;
    protected $url;
    protected $thumbWidth;
    protected $thumbHeight;
    protected $extension;
    protected $imageSize;
    protected $imageFormat;
    
    public function __construct($id, $src, $root, $maxWidth, $maxHeight = NULL)
    {
        
        $this->thumbWidth = $maxWidth;
        $this->src = $src;
        
        
        if ($maxHeight == NULL) {
            $maxHeight = $maxWidth;
        }
        $this->thumbHeight = $maxHeight;
        
        $this->imageSize = getimagesize($this->src);
        
        if (!$this->imageSize) {
            throw new Filehosting\Exceptions\PreviewGenerationException("Incorrect file extension");
        }
        if(!file_exists($root."/thumbs/".$id)){
            mkdir($root."/thumbs/".$id);
        }
        
        $this->imageFormat = $this->imageSize[2];
        $this->dst = $root."/thumbs/".$id."/thumb.".$this->getExtension();
        
    }
    
    
    
    
    
    protected function getExtension()
    {
        
        
        switch ($this->imageFormat) {
            case IMAGETYPE_GIF:
                return "gif";
            
            case IMAGETYPE_JPEG:
                return "jpg";
            
            case IMAGETYPE_PNG:
                return "png";
            
            default:
                throw new Filehosting\Exceptions\PreviewGenerationException("Incorrect file extension");
                
        }
    }
    
    
    protected function getImageFunction()
    {
    	$imageFunction = NULL;
      switch ($this->getExtension()) {
      	case 'jpg':
      		$imageFunction = "imagejpeg";
      		break;
      	case 'png':
      		$imageFunction = "imagepng";
      		break;
      	case 'gif':
      		$imageFunction = "imagegif";
      		break;		
      	
      	default:
      		throw new Filehosting\Exceptions\PreviewGenerationException("Incorrect file extension");
      }
        return $imageFunction;
    }
    
    protected function getImageCreateFunction()
    {
        $imageCreateFunction = NULL;
         switch ($this->getExtension()) {
      	case 'jpg':
      		$imageCreateFunction = "imagecreatefromjpeg";
      		break;
      	case 'png':
      		$imageCreateFunction = "imagecreatefrompng";
      		break;
      	case 'gif':
      		$imageCreateFunction = "imagecreatefromgif";
      		break;		
      	
      	default:
      		throw new Filehosting\Exceptions\PreviewGenerationException("Incorrect file extension");
      }
        
        return $imageCreateFunction;
    }
    
    protected function getMime()
    {
        $size = $this->imageSize;
        
        return $size['mime'];
    }
    
    
    public function createThumbnail()
    {
        if (!file_exists($this->src)) {
            throw new \Filehosting\Exceptions\PreviewGenerationException("File doesn't exist");
        }
        
        $image = call_user_func($this->getImageCreateFunction(), $this->src);
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
        call_user_func($this->getImageFunction(), $tmpImage, $this->dst);
        return true;
        
        
    }
    
    
    public function showThumbnail()
    {
        
        $this->createThumbnail();
        $image = file_get_contents($this->dst);
        
        
        header("Content-Type: {$this->getMime()}");
        echo $image;
        
        
    }
    
    public static function isExtensionAllowed($file){
        if(!$imagetype=getimagesize($file)){
        	return false;
        }
        switch ($imagetype[2]) {
            case IMAGETYPE_GIF:
                return true;
            
            case IMAGETYPE_JPEG:
                return true;
            
            case IMAGETYPE_PNG:
                return true;

            default: return false;
    }
    }
    
    
}
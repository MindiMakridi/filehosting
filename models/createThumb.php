<?php
if(file_exists($dir."/public/files/".$filename)){

	try {
		 $thumb = new Thumbnail($filename, $dir, 250, 250);
		 $thumb->showThumbnail();

        
    }
    catch (PreviewGenerationException $e) {
        header("HTTP/1.0 500 Internal Server Error");
        echo $e->getErrorMessage();
        die("error");
    }

}

else{
	header("HTTP/1.0 404 Not Found");
    die("Incorrect url");
}
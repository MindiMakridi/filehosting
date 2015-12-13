<?php
$files = new FilesMapper($app->PDO);
if(!$file = $files->fetchFile($id)){
	$app->notFound();
}
$file->setPath($dir);
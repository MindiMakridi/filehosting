<?php
$files = new FilesMapper($DBH);
if(!$file = $files->fetchFile($id)){
	$app->redirect('/404');
}
$file->setPath($dir);
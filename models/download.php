<?php
if(file_exists($path)){
	$filename = mb_substr($filename, stripos($filename, $id)+strlen(strval($id)));
	header("Content-Description: File Transfer");
	header("Content-Type: application/octet-stream");
	header("Content-disposition:attachment; filename=$filename");
	readfile($path);
}
else{
	$app->redirect('/404');
}


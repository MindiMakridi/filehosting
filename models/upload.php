<?php

$file  = new File;
$files = new FilesMapper($DBH);
if (isset($_POST['comment'])) {
    $id = $_POST['id'];
    $file->setid($id);
    $file->setComment($_POST['comment']);
    $files->editFile($file);
}

else {
    
    
    $extension = pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
    if (preg_match("/php|html/i", $extension)) {
        $fileName = rtrim($_FILES['userfile']['name'], $extension);
        $file->setFileName($fileName . "txt");
    } else {
        $file->setFileName($_FILES['userfile']['name']);
    }
    $file->setSize($_FILES['userfile']['size']);
    $file->setEvent(date("Y-m-d H:i:s"));
    $file->setComment('');
    $file->setToken($app->getCookie('token'));
    
    $id = $files->addFile($file);
    move_uploaded_file($_FILES['userfile']['tmp_name'], "public/files/$id{$file->getFileName()}");
}
$app->redirect("/files/$id");
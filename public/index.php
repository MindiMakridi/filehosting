<?php

require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../misc/config.php";

$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig(),
    "dbhost" => $dbHost,
    "dbname" => $dbName,
    "username" => $userName,
    "pass" => $pass,
    "maxsize" => $maxSize * 1000000,
    'safeExtensions' => $safeExtensions
        ));


$view = $app->view();
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension()
);
$view->setTemplatesDirectory(__DIR__ . "/../templates/");
$twig = $view->getEnvironment();

$app->container->singleton('PDO', function() use ($app) {
    return new PDO("mysql:host=" . $app->config('dbhost') . ";dbname=" . $app->config('dbname'), $app->config('username'), $app->config('pass'));
});

$app->container->singleton('filesMapper', function() use ($app) {
    return new Filehosting\Mappers\FilesMapper($app->PDO);
});

$app->container->singleton('commentsMapper', function() use($app) {
    return new Filehosting\Mappers\CommentsMapper($app->PDO);
});

$app->container->singleton('filesHelper', function() use ($app) {
    return new Filehosting\Helpers\FilesHelper(__DIR__, $app->filesMapper, $app->config('safeExtensions'), $app->config('maxsize'));
});



if (!$app->getCookie('token')) {
    $app->setCookie('token', Filehosting\Helpers\FilesHelper::generateToken(), '90 days');
}

$token = $app->getCookie('token');
$view->setData('filesHelper', $app->filesHelper);

$app->map("/", function() use ($app) {
    $error = "";
    if ($_FILES) {
        $files = $app->filesMapper;
        $file = new Filehosting\File;
        $postData = array(
            'name' => $_FILES['userfile']['name'],
            'size' => $_FILES['userfile']['size'],
            'tmp_name' => $_FILES['userfile']['tmp_name'],
            'error' => $_FILES['userfile']['error']);
        $error = $app->filesHelper->validateFileUpload($postData);
        if (!$error) {
            $app->filesHelper->uploadFile($file, $postData, $app->getCookie('token'));
            $id = $file->getId();
            $app->redirect("/files/$id");
        }
    }

    $app->render("index.html.twig", array(
        'maxSize' => $app->config('maxsize'),
        'error' => $error
    ));
})->via('GET', 'POST');

$app->get("/main", function() use ($app) {
    $lastUploadedFiles = $app->filesMapper->fetchLastUploadedFiles();
    $app->render("main.html.twig", array(
        'files' => $lastUploadedFiles
    ));
});


$app->map("/files/:id", function($id) use($app, $token) {
    $files = $app->filesMapper;
    if (!$file = $files->fetchFile($id)) {
        $app->notFound();
    }
    if($app->request->isGet()){
    $app->setCookie("time", time());
    }
    $error = "";
    $name = NULL;
    $commentaries = $app->commentsMapper->fetchComments($id);
    $isImage = Filehosting\Thumbnail::isExtensionAllowed($app->filesHelper->getPathToFile($file));
    $canEdit = false;
    if ($file->getToken() == $token) {
        $canEdit = true;
    }
    $comment = $file->getComment();
    if ($app->request->post('comment')) {
        $file->setComment(trim($app->request->post('comment')));

        if (!$error = Filehosting\Helpers\FilesHelper::validateEditorialForm($file, $app->request->post('token'))) {
            $files->editFileComment($file->getComment(), $id);
            $app->redirect("/files/$id");
        }
    }


    if ($app->request->post('commentary')) {
        if ($app->request->post('token') != $token){
            throw new Exception("Tokens don't match");
        }
        $commentary = new Filehosting\Commentary;
        $commentary->setFileId($id);
        $commentary->setText($app->request->post('commentary'));
        $commentary->setName($app->request->post('name'));
        if($app->request->post('name')) {
            $app->setCookie('name', $commentary->getName());
        }
        $commentary->setDate(time());
        $path = NULL;
        if ($app->request->post('path')) {
            $path = $app->request->post('path');
        }
        $commentary->setPath($path);
        $app->commentsMapper->addComment($commentary);
        
    }
    if($app->getCookie('name')){
        $name = $app->getCookie('name');
    }
    $app->render("filePage.html.twig", array(
        'file' => $file,
        "token" => $token,
        "commentaries" => $commentaries,
        "error" => $error,
        "isImage" => $isImage,
        "canEdit" => $canEdit,
        "name"    => $name
    ));
})->via('GET', 'POST');

$app->get("/download/:id/:originalFilename", function($id, $originalFilename) use ($app) {
    $file = $app->filesMapper->fetchFile($id);
    $path = $app->filesHelper->getPathToFile($file);
    if (file_exists($path)) {
        if(in_array('mod_xsendfile', apache_get_modules())){
            header("X-SendFile: " . realpath($path));
            header("Content-Type: application/octet-stream");
            header("Content-disposition:attachment");
        $app->stop();
        }
        else{
            header("Content-Description: File Transfer");
            header("Content-Type: application/octet-stream");
            header("Content-disposition:attachment");
 -          readfile($path);
        }
    } else {
        $app->notFound();
    }
});


$app->get("/view/:id", function($id) use ($app) {
    $file = $app->filesMapper->fetchFile($id);
    $path = $app->filesHelper->getPathToFile($file);
    if ($size = getimagesize($path)) {
        $app->response()->header('Content-Type', $size['mime']);
        readfile($path);
    } else {
        $app->notFound();
    }
});
$app->get("/thumbs/:id/:fileName", function($id, $fileName) use ($app) {
    $file = $app->filesMapper->fetchFile($id);
    $thumbName = $app->filesHelper->getThumbName($file);

    if (file_exists($app->filesHelper->getPathToFile($file)) && $thumbName == $fileName) {

        $thumb = new Filehosting\Thumbnail($id, $app->filesHelper->getPathToFile($file), $app->filesHelper->getRootDirectory(), 250, 250);
        $thumb->showThumbnail();
    } else {

        $app->notFound();
    }
});

$app->get("/newComments/:id", function($id) use($app){

    $app->response()->header('Content-type: application/json');
    $time = intval($app->getCookie("time"));
    $app->setCookie("time", time());
    $newComments = $app->commentsMapper->fetchComments($id, $time);
    $json = array();
    foreach($newComments as $newComment){
        $json[] = array('name'=>$newComment->getName(),
            'date'=>$newComment->getFormattedDate(),
            'text'=>$newComment->getText(),
            'path'=>$newComment->getPath(),
            'depth'=>$newComment->getDepth());
    }
    $json = json_encode($json);
    echo $json;

});
$app->run();

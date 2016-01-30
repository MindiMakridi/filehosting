<?php
require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../misc/config.php";

$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig(),
    "dbhost" => $dbHost,
    "dbname" => $dbName,
    "username" => $userName,
    "pass" => $pass,
    "maxsize" => $maxSize * 1000000
));


$view                   = $app->view();
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension()
);
$view->setTemplatesDirectory(__DIR__ . "/../templates/");
$twig = $view->getEnvironment();

$app->container->singleton('PDO', function() use ($app)
{
    return new PDO("mysql:host=" . $app->config('dbhost') . ";dbname=" . $app->config('dbname'), $app->config('username'), $app->config('pass'));
});

$app->container->singleton('filesMapper', function() use ($app)
{
    return new Filehosting\FilesMapper($app->PDO);
});

$app->container->singleton('filesHelper', function() use ($app)
{
    return new Filehosting\Helpers\FilesHelper(__DIR__);
});


if (!$app->getCookie('token')) {
    $app->setCookie('token', Filehosting\Helpers\FilesHelper::generateToken(), '90 days');
}

$token = $app->getCookie('token');

$app->map("/", function() use ($app)
{
    $error = "";
    if($_FILES){
        $files = $app->filesMapper;
        $file  = new Filehosting\File;
    
        if (!$error=$app->filesHelper->validateFileUpload($_FILES, $app->config('maxsize'))) {
            $app->filesHelper->uploadFile($file, $files, $_FILES, $app->getCookie('token'));
            $id = $file->getId();
            $app->redirect("/files/$id");
        } 
    }
    
    $app->render("index.html.twig", array(
        'maxSize' => $app->config('maxsize'),
        'error' => $error
    ));
})->via('GET', 'POST');

$app->get("/main", function() use ($app)
{
    $lastUploadedFiles = $app->filesMapper->fetchLastUploadedFiles();
    $app->render("main.html.twig", array(
        'files' => $lastUploadedFiles,
        'filesHelper' => $app->filesHelper
    ));
});


$app->map("/files/:id", function($id) use($app, $token){
    $files = $app->filesMapper;
    if (!$file = $files->fetchFile($id)) {
        $app->notFound();
    }
    $error = "";
    $comment = $file->getComment();
    if ($app->request->post('comment')) {
        $file->setComment($app->request->post('comment'));
        $file->setToken($app->request->post('token'));
        
        if (!$error = Filehosting\Helpers\FilesHelper::validateEditorialForm($file, $token)) {
            $files->editFileComment($file->getComment(), $id);
            $app->redirect("/files/$id");
        }
        
    }
    
    
    
    $app->render("filePage.html.twig", array(
        'file' => $file,
        "token" => $token,
        "error" => $error,
        "filesHelper" => $app->filesHelper
    ));
})->via('GET', 'POST');

$app->get("/download/:id/:originalFilename", function($id, $originalFilename) use ($app)
{
    $file = $app->filesMapper->fetchFile($id);
    $path = $app->filesHelper->getPathToFile($file);
    if (file_exists($path)) {
        header("X-SendFile: " . realpath($path));
        header("Content-Type: application/octet-stream");
        header("Content-disposition:attachment");
        $app->stop();
    } else {
        $app->notFound();
    }
    
});



$app->get("/thumbs/:id/:fileName", function($id, $fileName) use ($app)
{
    $file      = $app->filesMapper->fetchFile($id);
    $thumbName = $app->filesHelper->getThumbName($file);
    
    if (file_exists($app->filesHelper->getPathToFile($file)) && $thumbName == $fileName) {
        
        $thumb = new Filehosting\Thumbnail($id, $app->filesHelper->getPathToFile($file), $app->filesHelper->getRootDirectory(), 250, 250);
        $thumb->showThumbnail();
    }
    
    else {
        
        $app->notFound();
    }
});

$app->run();
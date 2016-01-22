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
    return new Filehosting\helpers\FilesHelper(__DIR__);
});


if (!$app->getCookie('token')) {
    $app->setCookie('token', Filehosting\helpers\FilesHelper::generateToken(), '90 days');
}

$token = $app->getCookie('token');

$app->get("/", function() use ($app)
{
    $app->render("index.html.twig", array(
        'maxSize' => $app->config('maxsize')
    ));
});

$app->get("/main", function() use ($app)
{
    $files             = $app->filesMapper;
    $lastUploadedFiles = $files->fetchLastUploadedFiles();
    $app->render("main.html.twig", array(
        'files' => $lastUploadedFiles,
        'filesHelper' => $app->filesHelper
    ));
});


$pageFunc = function($id) use ($app, $token)
{
    $files = $app->filesMapper;
    $error = "";
    if ($app->request->post('comment')) {
        $file = new Filehosting\File;
        $file->setComment($app->request->post('comment'));
        $file->setToken($app->request->post('token'));
        
        if (!$error = Filehosting\helpers\FilesHelper::validateEditorialForm($file, $token)) {
            $files->editFileComment($file->getComment(), $id);
            $app->redirect("/files/$id");
        }
        
        $comment = $file->getComment();
    }
    
    if (!$file = $files->fetchFile($id)) {
        $app->notFound();
    }
    if (!isset($comment)) {
        $comment = $file->getComment();
    }
    $app->render("filePage.html.twig", array(
        'file' => $file,
        "token" => $token,
        "comment" => $comment,
        "error" => $error,
        "filesHelper" => $app->filesHelper
    ));
};

$app->get("/files/:id", $pageFunc);


$app->post("/files/:id", $pageFunc);

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
    $thumbName = "thumb." . $app->filesHelper->getFileExtension($file);
    
    if (file_exists($app->filesHelper->getPathToFile($file)) && $thumbName == $fileName) {
        
        $thumb = new Filehosting\Thumbnail($id, $app->filesHelper->getPathToFile($file), $app->filesHelper->getRootDirectory(), 250, 250);
        $thumb->showThumbnail();
    }
    
    else {
        
        $app->notFound();
    }
});

$app->post("/", function() use ($app)
{
    
    $files = $app->filesMapper;
    $file  = new Filehosting\File;
    if ($_FILES['userfile']['error'] == UPLOAD_ERR_OK && $_FILES['userfile']['size'] <= $app->config('maxsize')) {
        $file->setFileName($_FILES['userfile']['name']);
        $file->setToken($app->getCookie('token'));
        $file->setUploadtime(time());
        $file->setSize($_FILES['userfile']['size']);
        $file->setComment('');
        $files->beginTransaction();
        $id = $files->addFile($file);
        $file->setId($id);
        $tmpName = $_FILES['userfile']['tmp_name'];
        if ($app->filesHelper->saveFile($tmpName, $file)) {
            $files->commit();
        } else {
            $files->rollBack();
        }
    } else {
        throw new Filehosting\UploadException($_FILES['userfile']['error']);
        
    }
    
    
    $app->redirect("/files/$id");
    
    
});





$app->run();
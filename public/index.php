<?php
require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../lib/config.php";

$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig(),
    "host" => $host,
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
$sizeFunction = new Twig_SimpleFunction('getFormattedSize', function($size)
{
    return Filehosting\helpers\FilesHelper::getFormattedSize($size);
});
$isImageFunction = new Twig_SimpleFunction('isImage', function($path)
{
    return Filehosting\helpers\FilesHelper::isImage($path);
});

$canEditFunction = new Twig_SimpleFunction('canEdit', function($firstToken, $secondToken)
{
    return Filehosting\helpers\FilesHelper::canEdit($firstToken, $secondToken);
});
$twig->addFunction($sizeFunction);
$twig->addFunction($isImageFunction);
$twig->addFunction($canEditFunction);

$app->container->singleton('PDO', function() use ($app)
{
    return new PDO("mysql:host=" . $app->config('host') . ";dbname=" . $app->config('dbname'),
     $app->config('username'), $app->config('pass'));
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
    $app->render("index.html", array(
        'maxSize' => $app->config('maxsize')
    ));
});

$app->get("/main", function() use ($app)
{
    $files             = $app->filesMapper;
    $lastUploadedFiles = $files->fetchLastUploadedFiles();
    $app->render("main.html", array(
        'files' => $lastUploadedFiles
    ));
});

$app->get("/files/:id", function($id) use ($app, $token)
{
    
    $files = $app->filesMapper;
    if (!$file = $files->fetchFile($id)) {
        $app->notFound();
    }
    $comment         = $file->getComment();
    $pathToFile      = $app->filesHelper->getPathToFile($id . $file->getFileName());
    $relPathToFile   = $app->filesHelper->getPathToFile($id . $file->getFileName(), true);
    $relPathToThumb  = $app->filesHelper->getPathToThumb($file->getFileName(), $id, true);
    $relDownloadPath = $app->filesHelper->getDownloadPath($file->getFileName(), $id);
    $app->render("filePage.html", array(
        'file' => $file,
        "token" => $token,
        "comment" => $comment,
        "pathToFile" => $pathToFile,
        "relPathToFile" => $relPathToFile,
        "relPathToThumb" => $relPathToThumb,
        "relDownloadPath" => $relDownloadPath
    ));
});


$app->post("/files/:id", function($id) use ($app, $token)
{
    $files = $app->filesMapper;
    $file  = new Filehosting\File;
    $file->setComment($app->request->post('comment'));
    $file->setToken($app->request->post('token'));
    $error = "";
    if (!$error = Filehosting\helpers\FilesHelper::validateEditorialForm($file, $token)) {
        $files->editFile($file->getComment(), $id);
    }
    
    $comment = $file->getComment();
    
    if (!$file = $files->fetchFile($id)) {
        $app->notFound();
    }
    
    $pathToFile      = $app->filesHelper->getPathToFile($id . $file->getFileName());
    $relPathToFile   = $app->filesHelper->getPathToFile($id . $file->getFileName(), true);
    $relPathToThumb  = $app->filesHelper->getPathToThumb($file->getFileName(), $id, true);
    $relDownloadPath = $app->filesHelper->getDownloadPath($file->getFileName(), $id);
    
    
    $app->render("filePage.html", array(
        'file' => $file,
        "token" => $token,
        "comment" => $comment,
        "pathToFile" => $pathToFile,
        "relPathToFile" => $relPathToFile,
        "relPathToThumb" => $relPathToThumb,
        "relDownloadPath" => $relDownloadPath
    ));
    
    
});

$app->get("/download/:id/:originalFilename", function($id, $originalFilename) use ($app)
{
    $fileName = $app->filesMapper->fetchFileName($id);
    $fileName = $fileName['filename'];
    $path     = $app->filesHelper->getPathToFile($id . $fileName);
    if (file_exists($path)) {
        header("X-SendFile: " . realpath($path));
        header("Content-Type: application/octet-stream");
        header("Content-disposition:attachment");
        exit;
    } else {
        $app->notFound();
    }
    
});



$app->get("/thumbs/:id/:file", function($id) use ($app)
{
    $fileName = $app->filesMapper->fetchFileName($id);
    $fileName = $fileName['filename'];
    if (file_exists($app->filesHelper->getPathToFile($id . $fileName)) && 
        !file_exists($app->filesHelper->getRootDirectory() . "/thumbs/" . $id)) {
        mkdir($app->filesHelper->getRootDirectory() . "/thumbs/" . $id);
        $thumb = new Filehosting\Thumbnail($fileName, $app->filesHelper->getPathToFile($id . $fileName),
         $app->filesHelper->getPathToThumb($fileName, $id), 250, 250);
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
    if ($_FILES['userfile']['error'] == UPLOAD_ERR_OK) {
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

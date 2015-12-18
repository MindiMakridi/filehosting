<?php
error_reporting(0);
require __DIR__."/../vendor/autoload.php";
require_once __DIR__."/../lib/config.php";


$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig(),
    'templates.path' => __DIR__."/../templates/"
));



$app->container->singleton('PDO', function() use($userName, $pass, $host, $dbName){
    
    return new PDO("mysql:host=$host; dbname=$dbName", $userName, $pass);
});

$app->container->singleton('filesMapper', function() use($app){
    return new MyModels\FilesMapper($app->PDO);
});

$app->container->singleton('maxSize', function() use($maxSize){
    $maxSize*= 1000000;
    return $maxSize;
});


if (!$app->getCookie('token')) {
    $app->setCookie('token', File::generateToken(), '90 days');
}

$app->get("/", function() use ($app)
{
    $app->render("index.html",  array('maxSize' => $app->maxSize));
});

$app->get("/main", function() use ($app)
{
    $files = $app->filesMapper;
    $files = $files->fetchLastUploadedFiles();
    $app->render("main.html", array(
        'files' => $files
    ));
});

$app->get("/files/:id", function($id) use ($app)
{
    $dir = __DIR__;
    $files = $app->filesMapper;
    if(!$file = $files->fetchFile($id)){
        $app->notFound();
    }
    $file->setPath($dir);
    
    $app->render("filePage.html", array(
        'file' => $file,
        "token" => $app->getCookie('token')
    ));
});

$app->get("/download/:id/:filename", function($id, $filename) use ($app)
{
    $path = __DIR__ . "/files/{$id}$filename";
    if(file_exists($path)){
        $filename = mb_substr($filename, stripos($filename, $id)+strlen(strval($id)));
        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-disposition:attachment;");
        readfile($path);
    }
    else{
        $app->notFound();
    }

});

$app->get("/thumbs/:filename", function($filename) use ($app)
{
    $dir = __DIR__;
    if(file_exists($dir."/files/".$filename)){

        try {
            $thumb = new MyModels\Thumbnail($filename, $dir, 250, 250);
            $thumb->showThumbnail();

        
        }
        catch (MyModels\PreviewGenerationException $e) {
            header("HTTP/1.0 500 Internal Server Error");
            echo $e->getErrorMessage();
            die("error");
        }

    }

    else{
        header("HTTP/1.0 404 Not Found");
        die("Incorrect url");
    }
});

$app->post("/", function() use ($app)
{
    $path = __DIR__;
    $file  = new MyModels\File;
    $files = $app->filesMapper;
    if (isset($_POST['comment'])) {
        $id = $_POST['id'];
        $file->setid($id);
        $file->setComment($_POST['comment']);
        $files->editFile($file);
    }
    else {
        $token = $app->getCookie('token');
        $file->setMaxSize($app->maxSize);
        try{
            $file->prepareToUpload($_FILES, $token);
            $id = $files->addFile($file);
            $file->upload($_FILES['userfile']['tmp_name'], $path, $id);
    }
    catch(Exception $e){
        $app->render('error.html', array('errorMessage'=> $e->getMessage()));
        exit;
    }
    }
    $app->redirect("/files/$id");


});




$app->run();




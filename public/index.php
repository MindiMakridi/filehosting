<?php

require __DIR__."/../vendor/autoload.php";


spl_autoload_register(function($class){
    $modelPath = __DIR__."/../models/".$class.".php";
    $libPath = __DIR__."/../lib/".$class.".php";
    if(file_exists($modelPath)){
        require_once $modelPath;
    }
    elseif (file_exists($libPath)) {
        require_once $libPath;
    }

});



$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig(),
    'templates.path' => __DIR__."/../templates/"
));

$app->container->singleton('PDO', function(){
    $user = Settings::USER;
    $pass = Settings::PASS;
    $host = Settings::HOST;
    $dbname = Settings::DBNAME;
    return new PDO("mysql:host=$host; dbname=$dbname", $user, $pass);
});


if (!$app->getCookie('token')) {
    $app->setCookie('token', File::generateToken(), '90 days');
}

$app->get("/", function() use ($app)
{
    $app->render("index.html");
});

$app->get("/main", function() use ($app)
{
    $files = new FilesMapper($app->PDO);
    $files = $files->fetchLastUploadedFiles();
    $app->render("main.html", array(
        'files' => $files
    ));
});

$app->get("/files/:id", function($id) use ($app)
{
    $dir = __DIR__;
    require __DIR__ . "/../models/filePage.php";
    
    $app->render("filePage.html", array(
        'file' => $file,
        "dir" => $dir,
        "token" => $app->getCookie('token')
    ));
});

$app->get("/download/:id/:filename", function($id, $filename) use ($app)
{
    $path = __DIR__ . "/files/$filename";
    if(file_exists($path)){
        $filename = mb_substr($filename, stripos($filename, $id)+strlen(strval($id)));
        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-disposition:attachment; filename=$filename");
        readfile($path);
    }
    else{
        $app->notFound();
    }

});

$app->get("/thumbs/:filename", function($filename) use ($app)
{
    $dir = __DIR__;
    require __DIR__ . "/models/createThumb.php";
});

$app->post("/", function() use ($app)
{
    $path = __DIR__;
    $file  = new File;
    $files = new FilesMapper($app->PDO);
    if (isset($_POST['comment'])) {
        $id = $_POST['id'];
        $file->setid($id);
        $file->setComment($_POST['comment']);
        $files->editFile($file);
    }
    else {
        $token = $app->getCookie('token');
        $file->prepareToUpload($_FILES, $token);
        $id = $files->addFile($file);
        $file->upload($_FILES['userfile']['tmp_name'], $path, $id);
    }
    $app->redirect("/files/$id");

});




$app->run();




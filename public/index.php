<?php
require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../lib/config.php";

$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig(),
    'templates.path' => __DIR__ . "/../templates/",
    "host" => $host,
    "dbname" => $dbName,
    "username" => $userName,
    "pass" => $pass,
    "maxsize" => $maxSize * 1000000,
    "rootdirectory" => __DIR__,
    "filesdirectory" => __DIR__ . "/files/"
));


$app->container->singleton('PDO', function() use ($app)
{
    return new PDO("mysql:host=" . $app->config('host') . ";dbname=" . $app->config('dbname'), $app->config('username'), $app->config('pass'));
});

$app->container->singleton('filesMapper', function() use ($app)
{
    return new Filehosting\FilesMapper($app->PDO);
});


if (!$app->getCookie('token')) {
    $app->setCookie('token', Filehosting\File::generateToken(), '90 days');
}

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

$app->get("/files/:id", function($id) use ($app)
{
    
    $files = $app->filesMapper;
    if (!$file = $files->fetchFile($id)) {
        $app->notFound();
    }
    
    
    $app->render("filePage.html", array(
        'file' => $file,
        "token" => $app->getCookie('token')
    ));
});

$app->get("/download/:id/:filename", function($id, $filename) use ($app)
{
    $path = $app->config("filesdirectory") . $id . $filename;
    if (file_exists($path)) {
        header("X-SendFile: " . realpath($path));
        header("Content-Type: application/octet-stream");
        header("Content-disposition:attachment; filename=" . $filename);
        exit;
    } else {
        $app->notFound();
    }
    
});

$app->get("/thumbs/:filename", function($filename) use ($app)
{
    if (file_exists($app->config("filesdirectory") . $filename)) {
        $thumb = new Filehosting\Thumbnail($filename, $app->config("rootdirectory"), 250, 250);
        $thumb->showThumbnail();
        
        
        
    }
    
    else {
        $app->notFound();
    }
});

$app->post("/", function() use ($app)
{
    $file  = new Filehosting\File;
    $files = $app->filesMapper;
    if ($app->request->post('comment')) {
        $id = $app->request->post('id');
        $file->setid($id);
        $file->setComment($app->request->post('comment'));
        $files->editFile($file);
    } else {
        if ($_FILES['userfile']['error'] == UPLOAD_ERR_OK) {
            $file->setFileName($_FILES['userfile']['name']);
            $file->setToken($app->getCookie('token'));
            $file->setUploadtime(time());
            $file->setSize($_FILES['userfile']['size']);
            $file->setComment('');
            $id = $files->addFile($file);
            move_uploaded_file($_FILES['userfile']['tmp_name'], $app->config('filesdirectory') . $id . $file->getFileName());
        } else {
            throw new Filehosting\UploadException($_FILES['userfile']['error']);
            
        }
    }
    
    $app->redirect("/files/$id");
    
    
});




$app->run();



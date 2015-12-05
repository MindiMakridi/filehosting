<?php

require "vendor/autoload.php";
require __DIR__ . "/lib/config.php";
require __DIR__ . "/lib/PDO.php";
require __DIR__ . "/models/File.php";
require __DIR__ . "/models/FilesMapper.php";
require __DIR__ . "/models/Thumbnail.php";
require __DIR__ . "/lib/PreviewGenerationException.php";

$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig()
));

if (!$app->getCookie('token')) {
    $app->setCookie('token', File::generateToken(), '90 days');
}

$app->get("/", function() use ($app)
{
    $app->render("index.html");
});

$app->get("/main", function() use ($app, $DBH)
{
    require __DIR__ . "/models/main.php";
    $app->render("main.html", array(
        'files' => $files
    ));
});

$app->get("/files/:id", function($id) use ($app, $DBH)
{
    $dir = __DIR__;
    require __DIR__ . "/models/filePage.php";
    
    $app->render("filePage.html", array(
        'file' => $file,
        "dir" => $dir,
        "token" => $app->getCookie('token')
    ));
});

$app->get("/download/:id/:filename", function($id, $filename) use ($app, $DBH)
{
    $path = __DIR__ . "/public/files/$filename";
    require __DIR__ . "/models/download.php";
});

$app->get("/public/thumbs/:filename", function($filename) use ($app, $DBH)
{
    $dir = __DIR__;
    require __DIR__ . "/models/createThumb.php";
});

$app->post("/", function() use ($app, $DBH)
{
    require __DIR__ . "/models/upload.php";
});




$app->run();




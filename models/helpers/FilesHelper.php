<?php
namespace Filehosting\helpers;

class FilesHelper
{
    protected $rootDirectory;
    
    public function __construct($root)
    {
        $this->rootDirectory = $root;
    }
    
    public static function getFormattedSize($size)
    {
        if ($size / 1000000 >= 1) {
            $size = round($size / 1000000, 1);
            return "$size Мб";
        }
        
        if ($size / 1024 >= 1) {
            $size = round($size / 1000, 1);
            return "$size Кб";
        }
        return $size . " байт";
    }
    
    public static function generateToken()
    {
        $string = "abcdefghijklmnopqrstuvwxyz1234567890";
        $length = mb_strlen($string);
        $cypher = "";
        for ($i = 0; $i <= 20; $i++) {
            $cypher .= mb_substr($string, mt_rand(0, $length - 1), 1);
        }
        $salt1 = "BlackBrier";
        $salt2 = "ThreadStone";
        
        
        return md5($salt1 . $cypher . $salt2);
    }
    
    public static function isImage($path)
    {
        if (getimagesize($path)) {
            return true;
        }
        return false;
    }
    
    public function getRootDirectory()
    {
        return $this->rootDirectory;
    }
    
    public function getPathToFile($fileName, $relative = false)
    {
        if ($relative == true) {
            return "/files/" . $fileName;
        }
        return $this->rootDirectory . "/files/" . $fileName;
    }
    
    public function getPathToThumb($fileName, $id, $relative = false)
    {
        if ($relative == true) {
            return "/thumbs/" . $id . "/" . $fileName;
        }
        return $this->rootDirectory . "/thumbs/" . $id . "/" . $fileName;
    }
    
    public function getDownloadPath($fileName, $id)
    {
        return "/download/" . $id . "/" . $fileName;
    }
    
    public static function validateEditorialForm(\Filehosting\File $file, $token)
    {
        $file->setComment(trim($file->getComment()));
        if ($file->getToken() != $token) {
            return "токены не совпадают";
        }
        if (mb_strlen($file->getComment()) > 150) {
            return "Комментарий не должен превышать 150 символов";
        }
        
        return false;
        
    }
    
    public function saveFile($tmpName, \Filehosting\File $file)
    {
        $fileName = $file->getId() . $file->getFileName();
        if (!move_uploaded_file($tmpName, $this->getPathToFile($fileName))) {
            return false;
        }
        return true;
    }
    
    public static function canEdit($firstToken, $secondToken)
    {
        if ($firstToken == $secondToken) {
            return true;
        }
        return false;
    }
    
}
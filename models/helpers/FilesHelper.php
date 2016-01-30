<?php
namespace Filehosting\Helpers;

class FilesHelper
{
    protected $root;
    
    public function __construct($root)
    {
        $this->root = $root;
    }
    
    public function getFormattedSize(\Filehosting\File $file)
    {
        $size = $file->getSize();
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
    
    public function getFormattedTime(\Filehosting\File $file)
    {
        $time = $file->getUploadTime();
        return date("H:i M d, Y", $time);
    }
    
    public static function generateToken()
    {
        $string = "abcdefghijklmnopqrstuvwxyz1234567890";
        $length = mb_strlen($string);
        $cypher = "";
        for ($i = 0; $i <= 20; $i++) {
            $cypher .= mb_substr($string, mt_rand(0, $length - 1), 1);
        }
        
        
        
        return $cypher;
    }
    
    public function isImage(\Filehosting\File $file)
    {
        $path = $this->getPathToFile($file);
        if (getimagesize($path) && \Filehosting\Thumbnail::isExtensionAllowed(getimagesize($path)[2])) {
            return true;
        }
        return false;
    }
    
    public function getRootDirectory()
    {
        return $this->root;
    }
    
    public function getPathToFile(\Filehosting\File $file, $relative = false)
    {
        $fileName = $file->getId() . $file->getFileName();
        if ($relative == true) {
            return "/files/" . $fileName;
        }
        
        return $this->root . "/files/" . $fileName;
    }
    
    public function getPathToThumb(\Filehosting\File $file, $relative = false)
    {
        
        $fileName = "thumb." . $this->getFileExtension($file);
        $id       = $file->getId();
        if ($relative == true) {
            return "/thumbs/" . $id . "/" . $fileName;
        }
        return $this->root . "/thumbs/" . $id . "/" . $fileName;
    }
    
    public function getDownloadPath(\Filehosting\File $file)
    {
        $fileName = $file->getOriginalName();
        $id       = $file->getId();
        return "/download/" . $id . "/" . $fileName;
    }
    
    public function getFileExtension(\Filehosting\File $file)
    {
        $info = new \SplFileInfo($this->getPathToFile($file));
        return $info->getExtension();
    }

    public function getThumbName(\Filehosting\File $file){
        $name = "thumb.".$this->getFileExtension($file);
        return $name;
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
        if (!move_uploaded_file($tmpName, $this->getPathToFile($file))) {
            return false;
        }
        return true;
    }


    public function uploadFile(\Filehosting\File $file, \Filehosting\FilesMapper $files, $filePostData, $token){
        $file->setFileName($filePostData['userfile']['name']);
        $file->setToken($token);
        $file->setUploadtime(time());
        $file->setSize($filePostData['userfile']['size']);
        $file->setComment('');
        $files->beginTransaction();
        $file->setId($files->addFile($file));
        $tmpName = $filePostData['userfile']['tmp_name'];
        if ($this->saveFile($tmpName, $file)) {
            $files->commit();
        } else {
            $files->rollBack();
        }
    }

    public function validateFileUpload($filePostData, $maxSize){
        $error = '';
        if($filePostData['userfile']['error'] == UPLOAD_ERR_OK && $filePostData['userfile']['size'] <= $maxSize){
            return false;
        }
        else{
            switch ($filePostData['userfile']['error']) { 
            case UPLOAD_ERR_INI_SIZE: 
                $error = "Превышен максимально допустимый размер файла"; 
                break; 
            case UPLOAD_ERR_FORM_SIZE: 
                $error = "Превышен максимально допустимый размер файла";
                break; 
            case UPLOAD_ERR_PARTIAL: 
                $error = "Файл не был до конца загружен"; 
                break; 
            case UPLOAD_ERR_NO_FILE: 
                $error = "Файл не выбран"; 
                break; 
            case UPLOAD_ERR_NO_TMP_DIR: 
                $error = "Ошибка загрузки"; 
                break; 
            case UPLOAD_ERR_CANT_WRITE: 
                $error = "Ошибка загрузки"; 
                break; 
            case UPLOAD_ERR_EXTENSION: 
                $error = "Файл не был загружен"; 
                break; 

            default: 
                $error = "Файл не был загружен"; 
                break; 
        }
        var_dump($maxSize);
        return $error; 
        }
    }
    
    public function canEdit($token, \Filehosting\File $file)
    {
        if ($token == $file->getToken()) {
            return true;
        }
        return false;
    }
    
}
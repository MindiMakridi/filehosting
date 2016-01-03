<?php
namespace Filehosting;
class File
{
    protected $id;
    protected $filename;
    protected $size;
    protected $upload_time;
    protected $comment;
    protected $token;
    
    public function getId()
    {
        if (isset($this->id)) {
            return $this->id;
        }
        
        throw new Exception("Property 'id' of File is not set", 1);
        
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getFileName()
    {
        if (isset($this->filename)) {
            return $this->filename;
        }
        
        throw new Exception("Property 'filename' of File is not set", 1);
    }
    
    public function setFileName($name)
    {
        $name           = preg_replace("/\.php|\.html|\.htaccess/", ".txt", $name);
        $this->filename = $name;
    }
    
    
    public function getSize()
    {
        if (isset($this->size)) {
            return $this->size;
        }
        
        throw new Exception("Property 'size' of File is not set", 1);
    }
    
    public function setSize($size)
    {
        $this->size = $size;
    }
    
    
    
    
    public function getFormattedSize()
    {
        if (!isset($this->size)) {
            throw new Exception("Property 'size' of File is not set", 1);
        }
        
        if ($this->size / 1000000 >= 1) {
            $size = round($this->size / 1000000, 1);
            return "$size Мб";
        }
        
        if ($this->size / 1000 >= 1) {
            $size = round($this->size / 1000, 1);
            return "$size Кб";
        }
        return $this->size . " байт";
    }
    
    public function getUploadTime()
    {
        if (isset($this->upload_time)) {
            return $this->upload_time;
        }
        throw new Exception("Property 'upload_time' of File is not set", 1);
    }
    
    public function setUploadTime($upload_time)
    {
        $this->upload_time = $upload_time;
    }
    
    public function getComment()
    {
        if (isset($this->comment)) {
            return $this->comment;
        }
        throw new Exception("Property 'comment' of File is not set", 1);
    }
    
    public function setComment($comment)
    {
        $this->comment = $comment;
    }
    
    
    
    public function getToken()
    {
        if (isset($this->token)) {
            return $this->token;
        }
        throw new Exception("Property 'token' of File is not set", 1);
    }
    
    public function setToken($token)
    {
        $this->token = $token;
    }
    
    public function isImage($path)
    {
        if (getimagesize($path)) {
            return true;
        }
        return false;
    }
    
    static function generateToken()
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
    
}
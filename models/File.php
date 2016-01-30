<?php
namespace Filehosting;
class File
{
    protected $id;
    protected $filename;
    protected $original_name;
    protected $size;
    protected $upload_time;
    protected $comment;
    protected $token;
    
    public function getId()
    {
        return $this->id;    
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
        
        throw new \Exception("Property 'filename' of File is not set", 1);
    }
    
    public function setFileName($name)
    {
        $this->original_name = $name;
        $name                = preg_replace("/\.php|\.html|\.htaccess/", ".txt", $name);
        $this->filename      = $name;
    }
    
    
    public function getOriginalName()
    {
        if (isset($this->original_name)) {
            return $this->original_name;
        }
        
        throw new \Exception("Property 'original_name' of File is not set", 1);
    }
    
    
    public function getSize()
    {
        if (isset($this->size)) {
            return $this->size;
        }
        
        throw new \Exception("Property 'size' of File is not set", 1);
    }
    
    public function setSize($size)
    {
        $this->size = $size;
    }
    
    
    public function getUploadTime()
    {
        if (isset($this->upload_time)) {
            return $this->upload_time;
        }
        throw new \Exception("Property 'upload_time' of File is not set", 1);
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
        throw new \Exception("Property 'comment' of File is not set", 1);
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
        throw new \Exception("Property 'token' of File is not set", 1);
    }
    
    public function setToken($token)
    {
        $this->token = $token;
    }
    
    
    
    
    
}
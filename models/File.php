<?php
class File
{
    protected $id;
    protected $filename;
    protected $size;
    protected $event;
    protected $comment;
    protected $path;
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
        return $this->filename;
    }
    
    public function setFileName($name)
    {
        $this->filename = $name;
    }
    
    
    public function getSize()
    {
        return $this->size;
    }
    
    public function setSize($size)
    {
        $this->size = $size;
    }
    
    public function getFormattedSize()
    {
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
    
    public function getEvent()
    {
        return $this->event;
    }
    
    public function setEvent($event)
    {
        $this->event = $event;
    }
    
    public function getComment()
    {
        return $this->comment;
    }
    
    public function setComment($comment)
    {
        $this->comment = $comment;
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function setPath($rootDirectory)
    {
        $this->path = $rootDirectory . "/public/files/{$this->id}{$this->filename}";
    }
    
    public function getToken()
    {
        return $this->token;
    }
    
    public function setToken($token)
    {
        $this->token = $token;
    }
    
    public function isImage()
    {
        if (getimagesize("$this->path")) {
            return true;
        }
        return false;
    }
    
    static function generateToken()
    {
        $string = "abcdefghijklmnopqrstuvwxyz1234567890";
        $length = mb_strlen($string);
        $cypher = "";
        for ($i = 0; $i <= 10; $i++) {
            $cypher .= mb_substr($string, mt_rand(0, $length - 1), 1);
        }
        $salt1 = "BlackBrier";
        $salt2 = "ThreadStone";
        
        
        return md5($salt1 . $cypher . $salt2);
    }
    
    }
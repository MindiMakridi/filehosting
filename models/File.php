<?php
class File
{
    protected $id;
    protected $filename;
    protected $size;
    protected $upload_time;
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
    
    public function getUploadTime()
    {
        return $this->upload_time;
    }
    
    public function setUploadTime($upload_time)
    {
        $this->upload_time = $upload_time;
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
        $this->path = $rootDirectory . "/files/{$this->id}{$this->filename}";
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


    public function prepareToUpload($postData, $token){
        $extension = pathinfo($postData['userfile']['name'], PATHINFO_EXTENSION);
        if (preg_match("/php|html/i", $extension)) {
            $fileName = rtrim($postData['userfile']['name'], $extension);
            $this->setFileName($fileName . "txt");
        } 
        else {
            $this->setFileName($postData['userfile']['name']);
        }
        $this->setSize($_FILES['userfile']['size']);
        $this->setupload_time(date("Y-m-d H:i:s"));
        $this->setComment('');
        $this->setToken($token);
    }

    public function upload($file, $path, $id){
        move_uploaded_file($file, $path."/files/$id{$this->filename}");
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
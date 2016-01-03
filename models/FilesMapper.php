<?php
namespace Filehosting;
class FilesMapper
{
    protected $DBH;
    
    function __construct(\PDO $DBH)
    {
        $this->DBH = $DBH;
    }
    
    public function addFile(File $file)
    {
          
        $STH = $this->DBH->prepare("INSERT INTO files(filename, size, upload_time, comment, token) VALUES(:filename, :size, :upload_time, :comment, :token)");
        $STH->bindValue(":filename", $file->getFileName());
        $STH->bindValue(":size", $file->getSize());
        $STH->bindValue(":upload_time", date("Y-m-d H:i:s", $file->getUploadTime()));
        $STH->bindValue(":comment", $file->getComment());
        $STH->bindValue(":token", $file->getToken());
        $STH->execute();
        $id = $this->DBH->lastInsertId();
        return $id;
        
        
        
        
    }
    
    
    public function fetchFile($id)
    {
        $STH = $this->DBH->prepare("SELECT*FROM files WHERE id=:id");
        $STH->bindValue(":id", $id);
        $STH->execute();
        $STH->setFetchMode(\PDO::FETCH_CLASS, "Filehosting\File");
        $result = $STH->fetch();
        return $result;
    }
    
    public function fetchLastUploadedFiles()
    {
        $STH = $this->DBH->prepare("SELECT*FROM files ORDER BY id DESC LIMIT 0, 100");
        $STH->execute();
        $STH->setFetchMode(\PDO::FETCH_CLASS, "Filehosting\File");
        $result = $STH->fetchAll();
        return $result;
    }
    
    public function editFile($file)
    {
        $STH = $this->DBH->prepare("UPDATE files SET comment = :comment WHERE id=:id");
        $STH->bindValue(":comment", $file->getComment());
        $STH->bindValue(":id", $file->getId());
        $STH->execute();
    }
}
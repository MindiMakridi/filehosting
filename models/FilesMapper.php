<?php

class FilesMapper
{
    protected $DBH;
    
    function __construct(PDO $DBH)
    {
        $this->DBH = $DBH;
    }
    
    public function addFile($file)
    {
        
        try {
            $this->DBH->beginTransaction();
            $STH = $this->DBH->prepare("INSERT INTO files VALUES(NULL, :filename, :size, :event, :comment, :token)");
            $STH->bindValue(":filename", $file->getFileName());
            $STH->bindValue(":size", $file->getSize());
            $STH->bindValue(":event", $file->getEvent());
            $STH->bindValue(":comment", $file->getComment());
            $STH->bindValue(":token", $file->getToken());
            $STH->execute();
            $id = $this->DBH->lastInsertId();
            $this->DBH->commit();
            
            return $id;
        }
        
        catch (Exception $e) {
            $this->DBH->rollBack();
            echo $e->getMessage();
        }
        
    }
    
    
    public function fetchFile($id)
    {
        $STH = $this->DBH->prepare("SELECT*FROM files WHERE id=:id");
        $STH->bindValue(":id", $id);
        $STH->execute();
        $STH->setFetchMode(PDO::FETCH_CLASS, "File");
        $result = $STH->fetch();
        return $result;
    }
    
    public function fetchLastUploadedFiles()
    {
        $STH = $this->DBH->prepare("SELECT*FROM files ORDER BY id DESC LIMIT 0, 100");
        $STH->execute();
        $STH->setFetchMode(PDO::FETCH_CLASS, "File");
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
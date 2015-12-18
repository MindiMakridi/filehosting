<?php
namespace MyModels;
class FilesMapper
{
    protected $DBH;
    
    function __construct(\PDO $DBH)
    {
        $this->DBH = $DBH;
    }
    
    public function addFile(File $file)
    {
        
        $this->DBH->beginTransaction();
        try {
            
            $STH = $this->DBH->prepare("INSERT INTO files(filename, size, upload_time, comment, token) VALUES(:filename, :size, :upload_time, :comment, :token)");
            $STH->bindValue(":filename", $file->getFileName());
            $STH->bindValue(":size", $file->getSize());
            $STH->bindValue(":upload_time", $file->getUploadTime());
            $STH->bindValue(":comment", $file->getComment());
            $STH->bindValue(":token", $file->getToken());
            $STH->execute();
            $id = $this->DBH->lastInsertId();
            $this->DBH->commit();
            
            return $id;
        }
        
        catch (\Exception $e) {
            $this->DBH->rollBack();
            error_log($e->getMessage());
            throw new \Exception("Something went wrong", 1);
            
        }
        
    }
    
    
    public function fetchFile($id)
    {
        $STH = $this->DBH->prepare("SELECT*FROM files WHERE id=:id");
        $STH->bindValue(":id", $id);
        $STH->execute();
        $STH->setFetchMode(\PDO::FETCH_CLASS, "MyModels\File");
        $result = $STH->fetch();
        return $result;
    }
    
    public function fetchLastUploadedFiles()
    {
        $STH = $this->DBH->prepare("SELECT*FROM files ORDER BY id DESC LIMIT 0, 100");
        $STH->execute();
        $STH->setFetchMode(\PDO::FETCH_CLASS, "MyModels\File");
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
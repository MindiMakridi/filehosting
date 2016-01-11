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
          
        $STH = $this->DBH->prepare("INSERT INTO files(filename, size, upload_time, comment, token, original_name) VALUES(:filename, :size, :upload_time, :comment, :token, :original_name)");
        $STH->bindValue(":filename", $file->getFileName());
        $STH->bindValue(":size", $file->getSize());
        $STH->bindValue(":upload_time", date("Y-m-d H:i:s", $file->getUploadTime()));
        $STH->bindValue(":comment", $file->getComment());
        $STH->bindValue(":token", $file->getToken());
        $STH->bindValue(":original_name", $file->getOriginalName());
        $STH->execute();
        $id = $this->DBH->lastInsertId();
        return $id;
        
        
        
        
    }

    public function beginTransaction(){
        $this->DBH->beginTransaction();
    }

    public function commit(){
        $this->DBH->commit();
    }

    public function rollBack(){
        $this->DBH->rollBack();
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

    public function fetchFileName($id){
        $STH = $this->DBH->prepare("SELECT filename FROM files WHERE id=:id");
        $STH->bindValue(":id", $id);
        $STH->execute();
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
    
    public function editFile($comment, $id)
    {
        $STH = $this->DBH->prepare("UPDATE files SET comment = :comment WHERE id=:id");
        $STH->bindValue(":comment", $comment);
        $STH->bindValue(":id", $id);
        $STH->execute();
    }
}
<?php
namespace Filehosting;
class FilesMapper
{
    protected $dbh;
    
    function __construct(\PDO $dbh)
    {
        $this->dbh = $dbh;
    }
    
    public function addFile(File $file)
    {
          
        $sth = $this->dbh->prepare("INSERT INTO files(filename, size, upload_time, comment, token, original_name)
         VALUES(:filename, :size, :upload_time, :comment, :token, :original_name)");
        $sth->bindValue(":filename", $file->getFileName());
        $sth->bindValue(":size", $file->getSize());
        $sth->bindValue(":upload_time",  $file->getUploadTime());
        $sth->bindValue(":comment", $file->getComment());
        $sth->bindValue(":token", $file->getToken());
        $sth->bindValue(":original_name", $file->getOriginalName());
        $sth->execute();
        $id = $this->dbh->lastInsertId();
        return $id;
        
        
        
        
    }

    public function beginTransaction(){
        $this->dbh->beginTransaction();
    }

    public function commit(){
        $this->dbh->commit();
    }

    public function rollBack(){
        $this->dbh->rollBack();
    }
    
    
    public function fetchFile($id)
    {
        $sth = $this->dbh->prepare("SELECT*FROM files WHERE id=:id");
        $sth->bindValue(":id", $id);
        $sth->execute();
        $sth->setFetchMode(\PDO::FETCH_CLASS, "Filehosting\File");
        $result = $sth->fetch();
        return $result;
    }
    
    public function fetchLastUploadedFiles()
    {
        $sth = $this->dbh->prepare("SELECT*FROM files ORDER BY id DESC LIMIT 0, 100");
        $sth->execute();
        $sth->setFetchMode(\PDO::FETCH_CLASS, "Filehosting\File");
        $result = $sth->fetchAll();
        return $result;
    }
    
    public function editFileComment($comment, $id)
    {
        $sth = $this->dbh->prepare("UPDATE files SET comment = :comment WHERE id=:id");
        $sth->bindValue(":comment", $comment);
        $sth->bindValue(":id", $id);
        $sth->execute();
    }
}
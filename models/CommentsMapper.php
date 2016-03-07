<?php
namespace Filehosting;

class CommentsMapper{
	protected $dbh;

	public function __construct($dbh){
		$this->dbh = $dbh;
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

	public function addComment(Commentary $comment){
		$sth = $this->dbh->prepare("INSERT INTO comments(text, posted_at, file_id, path, name, number) 
			VALUES(:text, :posted_at, :file_id, :path, :name, :number)");
		$sth->bindValue(":text",      $comment->getText());
		$sth->bindValue(":posted_at", date("Y-m-d H:i:s", $comment->getDate()));
		$sth->bindValue(":file_id",   $comment->getFileId());
		$sth->bindValue(":path",      $comment->getPath());
		$sth->bindValue(":name",      $comment->getName());
		$sth->bindValue(":number",    $comment->getNumber());
		$sth->execute();
	}

	public function fetchComments($id){
		$sth = $this->dbh->prepare("SELECT id, text, posted_at AS date, file_id AS fileID, name, path 
			FROM comments WHERE file_id = :id ORDER BY path");
		$sth->bindValue(":id", $id);
		$sth->execute();
		$sth->setFetchMode(\PDO::FETCH_CLASS, "Filehosting\Commentary");
		$result = $sth->fetchAll();
		return $result;
	}

	public function getLastNumber($id, $path = NULL){
		if($path == NULL){
			$path = "___";
		}
		else{
			$path.=".___";
		}
		$sth = $this->dbh->prepare("SELECT MAX(number) FROM comments WHERE file_id = :id AND path LIKE :path ");
		$sth->bindValue(":id", $id);
		$sth->bindValue(":path", $path);
		$sth->execute();
		$result = $sth->fetchColumn();
		if(!$result){
			return 1;
		}
		return $result+1;
	}
}
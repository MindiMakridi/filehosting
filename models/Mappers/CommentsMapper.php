<?php
namespace Filehosting\Mappers;

class CommentsMapper{
	protected $dbh;

	public function __construct($dbh){
		$this->dbh = $dbh;
		$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
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

	public function addComment(\Filehosting\Commentary $comment){
                $this->beginTransaction();
                $comment->setNumber($this->getLastNumber($comment->getFileId(), $comment->getPath()));
                $comment->setPath(self::createNewPath($comment->getNumber(), $comment->getPath()));
		$sth = $this->dbh->prepare("INSERT INTO comments(text, posted_at, file_id, path, name, number) 
			VALUES(:text, :posted_at, :file_id, :path, :name, :number)");
		$sth->bindValue(":text",      $comment->getText());
		$sth->bindValue(":posted_at", date("Y-m-d H:i:s", $comment->getDate()));
		$sth->bindValue(":file_id",   $comment->getFileId());
		$sth->bindValue(":path",      $comment->getPath());
		$sth->bindValue(":name",      $comment->getName());
		$sth->bindValue(":number",    $comment->getNumber());
		$sth->execute();
                $this->commit();
	}

	public function fetchComments($id, $time = 0){
		$sth = $this->dbh->prepare("SELECT id, text, UNIX_TIMESTAMP(posted_at) AS date, file_id AS fileID, name, path 
			FROM comments WHERE file_id = :id HAVING date>:time ORDER BY path");
		$sth->bindValue(":id", $id);
                $sth->bindValue(":time", $time);
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
        
        public static function createNewPath($number, $path = NULL){
            	$string = str_pad(strval($number), 3, "0", STR_PAD_LEFT);
		if($path != NULL){
			$string = $path.".".$string;
		}
		return $string;
        }
}
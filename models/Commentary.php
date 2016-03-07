<?php
namespace Filehosting;

class Commentary{
	protected $id;
	protected $fileId;
	protected $name;
	protected $text;
	protected $date;
	protected $path;
	protected $number;


	public function setId($id){
		$this->id = $id;
	}

	public function getId(){
		return $this->id;
	}

	public function setFileId($fileId){
		$this->fileId = $fileId;
	}

	public function getFileId(){
		return $this->fileId;
	}

	public function setName($name){
		if(!$name){
			$this->name = "Аноним";
		}
		else{
		$this->name = $name;
		}
	}

	public function getName(){
		return $this->name;
	}

	public function setText($text){
		$this->text = $text;
	}

	public function getText(){
		return $this->text;
	}

	public function setDate($date){
		$this->date = $date;
	}

	public function getDate(){
		return $this->date;
	}

	public function setPath($path){
		$this->path = $path;
	}

	public function setPathString($path, $number){
		$string = str_pad(strval($number), 3, "0", STR_PAD_LEFT);
		if($path != NULL){
			$string = $path.".".$string;
		}
		$this->path = $string;
	}

	public function getPath(){
		return $this->path;
	}

	public function setNumber($number){
		$this->number = $number;
	}

	public function getNumber(){
		return $this->number;
	}

	public function getDepth(){
		$depth = preg_split("/\\./", $this->path);
		$depth = (count($depth)-1)*25;
		return $depth;
	}
}
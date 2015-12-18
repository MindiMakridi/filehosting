<?php
namespace MyModels;
class PreviewGenerationException extends Exception {
	public function getErrorMessage(){
		$errorMsg = "Error on line ".$this->getLine().' in' . $this->getFile(). " : <b>". $this->getMessage(). "</b>";
		return $errorMsg;
	}
}
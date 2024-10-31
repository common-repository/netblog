<?php

class stringVariance {
	public function __construct($string) {
		$this->string = $string;
		$this->Analyze();
	}
	
	public function Analyze() {
		$t = str_split($this->string);
		foreach($t as $c)
			$this->chars[$c] = !isset($this->chars[$c]) ? 1 : $this->chars[$c]++; 
		
	}
	
	public function GetVariance() {
		
	}
	
	public function CountDiffChars() {
		return sizeof($this->chars);
	}
	
	
	private $string = '';
	private $chars = array();
}
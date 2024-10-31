<?php
/**
 * A small helper utility for printing infoboxes
 *
 */
class sidebox {
	public function __construct($message) {
		$this->msg = $message;
	}
	
	/**
	 * print out the infobox
	 *
	 */
	public function display() {
		if(strlen($this->msg)>0) {
			echo '<div class="netblog-container"><div class="sidebox">';
			echo $this->msg;
			echo '</div></div>';
		}
	}
	
	private $msg = '';
}
?>
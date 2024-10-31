<?php
/**
 * A small helper utility for printing infoboxes
 *
 */
class infobox {
	public function __construct($message) {
		$this->msg = $message;
	}
	
	/**
	 * print out the infobox
	 *
	 */
	public function display() {
		if(strlen($this->msg)>0) {
			echo '<div class="nb-updated"><p>';
			echo $this->msg;
			echo '</p></div>';
		}
	}
	
	private $msg = '';
}
?>
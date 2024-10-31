<?php
require_once 'nbClass.php';

class nbLink extends nbClass { 
	protected function __construct($uri,$title) {
		$this->uri = $uri;
		$this->title = $title;
	}
	
	/**
	 * Gets the link's uri
	 * @return string Returns the URI of this link
	 */
	public function GetUri() {
		return $this->uri;
	}
	
	/**
	 * Gets the link's title
	 * @return string Returns the title of this link
	 */
	public function GetTitle() {
		return $this->title;
	}
	
	/**
	 * Sets the link's uri
	 * @param string $uri The new URI for this link
	 */
	public function SetUri( $uri ) {
		$this->SetVar($uri, $this->uri);
	}
	
	/**
	 * Sets the link's title
	 * @param string $title The new title for this link
	 */
	public function SetTitle( $title ) {
		$this->SetVar($title, $this->title);
	}
	
	
	private $title = 'Untitled';
	private $uri = 'http://';
}
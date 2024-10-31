<?php
require_once 'DataTransfer.php';

/**
 * Connect, create and retrieve footprints from server.
 * 
 * @author Benjamin Sommer
 * @version 1.0
 */
class footprintConnect
{
	
	/**
	 * Create a footprint for uri and title; id, uri and title will be stored in this object.
	 *
	 * @param string $uri A valid uri.
	 * @param string $title The uri's title.
	 * @return boolean TRUE on success, FALSE on failure.
	 */
	public function create( $uri, $title )
	{
		if( strlen($uri) == 0 || strlen($title) == 0 ) return false;		
		$c = $this->send('',$uri,$title,'create');		
		if( strlen($c) == 0 || $c == '0' ) return false;		
		$this->parse($c);
		return $this->status < 400;
	}
	
	
	/**
	 * Update an existing footprint's uri and title.
	 *
	 * @param string $id Footprint id.
	 * @param string $uri Valid uri.
	 * @param string $title The uri's title.
	 * @return boolean TRUE if updated, FALSE otherwise.
	 */
	public function update( $id, $uri, $title )
	{
		if( strlen($uri) == 0 || strlen($title) == 0 || strlen($id) == 0 ) return false;
		$c = $this->send($id,$uri,$title,'update');
		if( strlen($c) == 0 || $c == '0' ) return false;
		$this->parse($c);
		return $this->status < 400;
	}
	
	
	/**
	 * Get current uri and title of given footprint.
	 *
	 * @param string $id Footprint id.
	 * @return boolean TRUE if found, FALSE if not.
	 */
	public function get( $id )
	{
		if( strlen($id) == 0 ) return false;
		$c = $this->send($id,'','','get');
		if( strlen($c) == 0 || $c == '0' ) return false;
		$this->parse($c);
		return $this->status < 400;		
	}
	
	
	/**
	 * Find Footprint for given URI and retrieve meta data.
	 *
	 * @param string $uri A valid uri.
	 * @return boolean TRUE if found, FALSE if not.
	 */
	public function find( $uri )
	{
		if( strlen($uri) == 0 ) return false;
		$c = $this->send('',$uri,'','get');
		if( strlen($c) == 0 || $c == '0' ) return false;
		$this->parse($c);
		return $this->status < 400;
	}

	
	/**
	 * Choose how to send the data to server.
	 *
	 * @param string $method Any of POST | GET.
	 */
	public function method( $method = 'post' )
	{
		$this->post = $method != 'get';		
	}
	
	
	/**
	 * Send data to footprint server.
	 *
	 * @param string $id Footprint id.
	 * @param string $uri Valid uri.
	 * @param string $title The uri's title.
	 * @param string $type Any of CREATE | UPDATE | GET.
	 * @return string
	 */
	private function send( $id, $uri, $title, $type )
	{
		$type = strtolower($type);
		$t = array( 'create', 'update', 'get' );
		if( !in_array($type,$t) ) return '';
		$result = array();
		
		if( $this->post ) {
			$engine_uri = $this->host . "$type.php";
			$fields = array('uri'=>$uri, 'title'=>$title, 'id'=>$id, 'version'=>'1.0');
			$result = DataTransfer::SubmitPost($engine_uri, $fields);
//			$context = stream_context_create( 
//							array('http'=>array(
//								    'method'=>"POST",
//								    'header'  => "Content-type: application/x-www-form-urlencoded\r\n", 
//     								'content' => http_build_query(array('uri'=>$uri, 'title'=>$title, 'id'=>$id, 'version'=>'1.0')), 
//								  )
//							)
//					 );			
		} else {
			$q = 'id='. urlencode($id) .'&uri='. urlencode($uri) .'&title='. urlencode($title) .'&ver=1.0';
			$engine_uri = $this->host . "$type.php?". htmlentities($q);
			$result = DataTransfer::RetrieveUrl($engine_uri);
//			$context = stream_context_create( 
//							array('http'=>array(
//								    'method'=>"GET",
//								    'header'=>"Accept-language: en\r\n"
//								  )
//							)
//					 );
		}

		// CONTACT SERVER AND RETRIEVE RESPONSE
		if($result['content']) {		
			return $result['content'];
		} else if($result['error']) {
			$this->errno = $result['errno'];
			$this->error = $result['error'];
		} else {
			$this->errno = 503;
			$this->error = 'Service Unavailable';
		}	
		return '';
	}
	
	
	/**
	 * Parse special-formatted string from server response.
	 *
	 * @param string $string Server response.
	 */
	private function parse( $string )
	{				
		if( strlen($string) == 0 || $string == '0' ) return;	
		$string = base64_decode($string);
		
		// TEST FOR ERRORS
		if( ($perr=stripos($string,'error:')) !== false ) {
			for($perrno=$perr+6; $perrno<strlen($string) && $string[$perrno] == ' '; $perrno++ ) {}
			$this->errno = $this->status = (int) trim(substr( $string, $perrno, ($e=strpos($string,' ',$perrno))-$perrno ));
			$this->error = $this->msg = trim(substr( $string, $e, ($n=strpos($string,'.',$e)) !== false ? $n-$e : strlen($string)-$e ));
			return;
		}
		
		// TEST FOR OK-STATUS
		if( ($pok=stripos($string,'ok:')) !== false ) {
			for($pokno=$pok+3; $pokno<strlen($string) && $string[$pokno] == ' '; $pokno++ ) {}
			$this->status = (int) trim(substr( $string, $pokno, ($e=strpos($string,' ',$pokno))-$pokno ));
			$this->msg = trim(substr( $string, $e, ($n=strpos($string,'.',$e)) !== false ? $n-$e : strlen($string)-$e ));
		}		
		
		// PARSE OK-RESPONSE
		$pid = strpos($string, 'id:' );
		$puri = strpos($string, 'uri:' );
		$ptit = strpos($string, 'title:' );
		
		if( !( $pid < $puri && $puri < $ptit ) ) return;
		
		$this->id = trim( substr($string,$pid+3,$puri-$pid-3) );
		$this->uri = trim( substr($string, $puri+4,$ptit-$puri-4) );
		$this->title = trim( substr($string,$ptit+6) );
	}
	
	
	public $id = null;
	public $uri = null;
	public $title = null;
	
	public $errno = null;
	public $error = null;
	
	/**
	 * REQUEST STATUS NUMBER
	 * These codes are like HTTP-error status codes.
	 * 
	 * errno		corresponding error
	 * 
	 * SUCCESS
	 * 201			Created	
	 * 208			Found Footprint
	 * 209			Footprint Update
	 * 
	 * CLIENT ERROR
	 * 400			Bad Request
	 * 401			Footprint Exists
	 * 
	 * SERVER ERROR
	 * 503			Service Unavailable
	 * 511			Footprint Creation Error
	 * 512			Footprint Creation Temporarily Closed
	 * 513			Footprint Update Error
	 *
	 * 
	 * @var int
	 */
	public $status = null;
	public $msg = null;
	
	private $post = true;	
	private $host = 'http://netblog2.benjaminsommer.com/footprints/'; //'http://localhost/weblogFootprint/';
}




?>
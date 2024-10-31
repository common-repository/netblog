<?php
require_once 'nbLink.php';

class nbLinkExtern extends nbLink {

	/**
	 * Constructs an instance of nbLinkExtern
	 * 
	 * @param string $uri The URI for this link
	 * @param string $title The URI's title
	 * @param int $id The internal id retrieved from the database for the given uri
	 * @param int $flag The link's flag retrieved form the database
	 * @param int $refs The number of references retrived from the database, i.e. how often this link has been used
	 */
	protected function __construct($uri, $title, $id=0, $flag=null, $refs=0) {		
		parent::__construct($uri,$title);		
		$this->id = $id;
		$this->flag = $flag?$flag:self::FLAG_NONE;
		$this->refs = $refs;
		$this->changed = false;
	
		if(strlen($title)==0) {
			$this->RecoverTitle();
			$this->Save();
		}
	}
	
	/**
	 * Loads an external uri for a given uri
	 * @param string $uri
	 * @return nbLinkExtern Returns nbLinkExtern if uri has been found in the database, null otherwise.
	 */
	static public function LoadByUri( $uri ) {
		global $wpdb; 
		$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
		$uri = addslashes($uri);
		if( $r=$wpdb->get_row("SELECT * FROM `$ext` WHERE uri = '$uri'") )
			return new nbLinkExtern($r->uri, $r->uri_title, $r->uri_id, $r->flag, $r->refs);
		return null;
	}
	
	/**
	 * Loads an external uri for a given uri id
	 * @param int $uri_id
	 * @return nbLinkExtern Returns nbLinkExtern if uri has been found in the database, null otherwise.
	 */
	static public function LoadByID( $uri_id ) {
		global $wpdb; 
		$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
		$uri = addslashes($uri);
		if( $r=$wpdb->get_row("SELECT * FROM `$ext` WHERE uri_id = '$uri_id'") )
			return new nbLinkExtern($r->uri, $r->uri_title, $r->uri_id, $r->flag, $r->refs);
		return null;
	}
	
	/**
	 * Creates an external link in the database
	 * @param string $uri
	 * @param string $title
	 * @return nbLinkExtern Returns nbLinkExtern if link has been created, false otherwise
	 */
	static public function Create($uri, $title) {	
		if( $u=self::LoadByUri($uri) )
			return $u;
		if(strlen($uri) == 0 || strlen($title)==0)
			return null;		
		global $wpdb;
		$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
		$uri = trim(addslashes($uri));
		$uriTitle = trim(addslashes( strip_tags($title) ));
		$q = "INSERT INTO `$ext` (uri,uri_title)
			VALUES ('$uri','$uriTitle')";
		if( $wpdb->query($q) )
			return new nbLinkExtern($uri, $title, $wpdb->insert_id);
		return null;
	}
	
	/**
	 * Tries to find an uri id for a given URI.
	 * @param string $uri A valid URI for which a corresponding uri id should be find
	 * @return int Returns the found uri id or -1 otherwise 
	 */
	static public function FindUriID($uri) {
		global $wpdb; 
		$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
		$uri = addslashes($uri);
		if( $r=$wpdb->get_row("SELECT * FROM `$ext` WHERE uri = '$uri'") )
			return $r->uri_id;
		return -1;
	}
	
	/**
	 * Get an array of URI's that match certain criteria.
	 *
	 * @param mixed $orderBy A comma separated string of valid uri field names, like 'id,title'
	 * @param int $limit The maximum number of return uris; cannot be 0.
	 * @param int $flag The flag which all returned uris should have, which must be a valid nbLinkExtern::FLAG_* constant
	 * @param int $refs The number of references the returned uris should have
	 * @return array Returns an array of nbLinkExtern objects if at least one link has been found, null otherwise
	 */
	static public function FindUris( $orderBy, $limit = -1, $flag = null, $refs = 0  )
	{
		global $wpdb;
		$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
		$desc = strtoupper($desc);
		
		// VERIFY
		if( is_numeric($orderBy) ) return false;
		if( is_string($orderBy) )
			$orderBy = explode(',', $orderBy);
				
		$q = "SELECT * FROM `$ext`";
		
		$order = Array();
		$validTbl = array('uri_id'=>'uri_id','id'=>'uri_id','title'=>'uri_title','uri_title'=>'uri_title','refs'=>'refs','flag'=>'flag');
		foreach( $orderBy as $by ) {
			if( is_string($by) ) $by = explode(' ',$by);					
			if( !isset($by[0]) || !isset($validTbl[$by[0]]) || isset($order[$validTbl[$by[0]]]) ) continue;
			
			$t = $validTbl[$by[0]];
			if( isset($by[1]) && strtolower($by[1]) == 'desc' )
				$t .= ' DESC';
			$order[$validTbl[$by[0]]] = $t;
		}
				
		// BUILD SQL-CMD
		$where = false;
		if( $flag != null ) {
			$q .= " WHERE flag = '$flag'";
			$where = true;
		}
		if( $refs > 0 ) {
			if( $where ) $q .= " AND refs = '$refs'";
			else $q .= " WHERE refs = '$refs'";
			$where = true;
		}
		if( sizeof($order) > 0 )
			$q .= " ORDER BY ".implode(', ',$order);
		if( is_numeric($limit) && $limit > 0 )
			$q .= " LIMIT $limit";

		$o = array();
		if( is_array($r=$wpdb->get_results($q)) )
			foreach($r as $rs) {
				$o[] = new nbLinkExtern($rs->uri, $rs->uri_title, $rs->uri_id, $rs->flag, $rs->refs);
			}
		return sizeof($o)>0 ? $o : null;
	}
	
	/**
	 * Convert this object to its appropriate xml format
	 * @param SimpleXMLElement $xml
	 * @return SimpleXMLElement Returns the SimpleXMLElement for this link so that child element can be added if needed
	 */
	public function AsXML($xml) {
		$item = $xml->addChild('LINK');
		$item->addAttribute('type', 'extern');
		$item->addAttribute('id', $this->id);
		$item->addAttribute('uri', $this->GetUri()!=''?$this->GetUri():'http://');
		$item->addAttribute('title', $this->GetTitle()!=''?$this->GetTitle():'Untitled');
		$item->addAttribute('permalink', $this->GetUri());
		$item->addAttribute('removable', $this->flag!=self::FLAG_LOCK?'true':'false');
		$item->addAttribute('flag', self::InterpretFlagCode($this->flag));
		$item->addAttribute('flag_code', $this->flag);
		$item->addAttribute('references', $this->refs);
		return $item;
	}
	
	/**
	 * Gets the internal id for this link
	 * @return int Returns the interal link id
	 */
	public function GetID() {
		return $this->id;
	}
	
	/**
	 * Gets the flag for this link
	 * @return int Returns the link's flag
	 */
	public function GetFlag() {
		return $this->flag;
	}
	
	/**
	 * Get the number of references, i.e. how oven this link has been used, no matter of this parent
	 */
	public function GetRefs() {
		return $this->refs;
	}
	
	/**
	 * Increments the reference counter by 1
	 */
	public function RefIncrement() {
		$this->SetVar( $this->refs+1, $this->refs);
	}
	
	/**
	 * Decrements the refernce counter by 1
	 */
	public function RefDecrement() {
		$this->SetVar( max(0,$this->refs-1), $this->refs);
	}
	
	/**
	 * Resets the reference counter to 0
	 */
	public function RefReset() {
		$this->SetVar(0, $this->refs);
	}
	
	/**
	 * Setter for this flag
	 * @param int $flag
	 */
	public function SetFlag( $flag ) {
		$this->SetVar($flag, $this->flag);
	}
	
	/**
	 * Setter for this object's references
	 * @param int $refs
	 */
	public function SetRefs( $refs ) {
		$this->SetVar($refs, $this->refs);
	}
	
	/**
	 * Saves this extern link. Note that this method also returns false if nothing needs to be saved. In those cases,
	 * check for this->HasChanged().
	 * 
	 * @return bool True if link has been saved in database, false otherwise
	 */
	public function Save() {
		if(!$this->HasChanged())
			return false;
			
		global $wpdb;
		$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
		$uri = ($this->GetUri());
		$uri_title = strip_tags($this->GetTitle());
		if(strlen($uri)==0) return false;
	
		if($this->id>0)
			$q = "UPDATE `$ext` 
				SET uri = '".mysql_real_escape_string($uri)."',
					uri_title = '".mysql_real_escape_string($uri_title)."',
					flag = '$this->flag',
					refs = '$this->refs'
				WHERE uri_id = '$this->id'";
		else $q = "INSERT INTO `$ext` (uri,uri_title,flag,refs)
			VALUES ('$uri','$uri_title','$this->flag','$this->refs')";

		$wpdb->query($q);
		if($this->id==0)
			$this->id = $wpdb->insert_id;
		return $wpdb->rows_affected==1;
	}
	
	/**
	 * Removes this extern link from database.
	 * @return bool Returns true if link has been removed from database, false otherwise.
	 */
	public function Remove() {
		if($this->id==0)
			return false;
		global $wpdb;
		$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		$b = (bool)$wpdb->query("DELETE FROM `$ext` WHERE uri_id = '$this->id'");
		$wpdb->query("DELETE FROM `$rel` WHERE uri_id = '$this->id'");
		return $b;
	}
	
	/**
	 * Try to recover title from its online document's title tag
	 */
	public function RecoverTitle() {
		if(!$this->GetUri() || strlen($this->GetUri())==0)
			return;
			
		require_once 'nbUri.php';
		require_once 'DataTransfer.php';
		$title = '';
		$doc = DataTransfer::RetrieveUrl($this->GetUri());
		if(strlen($doc['error'])>0 || !($title=nbUri::GrabTitle($doc['content'])))
			$title = nbUri::FindTitleByWebsearch($this->GetUri());
		if(is_string($title) && strlen($title)>0)
			$this->SetTitle($title);
	}
	
	/**
	 * Returns the flag look up table for conversion between nicename und code
	 * @return array
	 */
	public static function GetFlagLUT() {
		return array('normal'=>self::FLAG_NONE,
					'online'=>self::FLAG_NONE,
					'offline'=>self::FLAG_OFFLINE,
					'trash'=>self::FLAG_TRASH,
					'trashed'=>self::FLAG_TRASH,
					'erase'=>self::FLAG_ERASE,
					'restore'=>self::FLAG_RESTORE,
					'lock'=>self::FLAG_LOCK,
					'locked'=>self::FLAG_LOCK,
					'unlock'=>self::FLAG_UNLOCK,
					'unlocked'=>self::FLAG_UNLOCK,
		);
	}
	
	/**
	 * Get the corresponding constant FLAG_* for a given flag nicename.
	 * 
	 * @param string $nicename The nicename for a given flag
	 * @return int The corresponding FLAG_* constant if found, -1 otherwise.
	 */
	public static function InterpretFlagName( $nicename ) {
		$a = self::GetFlagLUT();
		return isset($a[$nicename]) ? $a[$nicename] : -1;
	}
	
	/**
	 * Similar to InterpretFlagName(), but handles nicename as case-insensitive
	 * @param string $nicename The nicename for a given flag
	 * @return int The corresponding FLAG_* constant
	 */
	public static function InterpretFlagIName( $nicename ) {
		$a = self::GetFlagLUT();
		return isset($a[$n=strtolower($nicename)]) ? $a[$n] : -1;
	}
	
	/**
	 * Interprets a flag constant code/value and returns its corresponding nicename. This method is the
	 * opposite to InterpretFlagName().
	 * 
	 * @param int $number_code The FLAG_* constant
	 * @return string The corresponding nicename for a given flag if the number code is found, false otherwise.
	 */
	public static function InterpretFlagCode( $number_code ) {
		$a = self::GetFlagLUT();
		return array_search( $number_code, $a );
	}
	
	private $id = 0;
	private $flag = self::FLAG_NONE;
	private $refs = 0;
	
	private static $inst = array();
	
	const FLAG_NONE = 0;
	const FLAG_OFFLINE = 1;
	const FLAG_TRASH = 2;
	const FLAG_ERASE = 3;
	const FLAG_RESTORE = 4;
	const FLAG_CHECK_STATUS = 10;
	const FLAG_UPDATE_TITLE = 11;
	const FLAG_LOCK = 99;
	const FLAG_UNLOCK = 100;
}

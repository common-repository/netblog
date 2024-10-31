<?php
require_once 'nbCaptionType.php';
require_once 'nbCaptionNumber.php';

/**
 * 
 * nbCaption is used to create, rename, remove captions and update their settings. This class also contains 
 * a couple of static public methods mainly used to load or find captions. It is also used in conjunction with
 * nbCaptionType.
 * 
 * @since Netblog 2.0
 *
 */
class nbCaption {
	/**
	 * Constructor to initialize a new caption or to load an existing one by given caption name and caption type name.
	 * This method tries to recover a caption solely by its caption name; its caption type name will be automatically loaded then.
	 *
	 * @param string $captionName
	 * @param string $captionTypeName
	 */
	public function __construct($captionName, $captionTypeName) {
		$this->name = $captionName;	
		
		global $wpdb;
		$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
		$q = "SELECT * FROM `$capt` WHERE name = '$captionName' AND type = '$captionTypeName'";
		if( ($c=$wpdb->get_row($q))==null ) 
			$q = "SELECT * FROM `$capt` WHERE name = '$captionName'";
				
		if( ($c=$wpdb->get_row($q))!=null && $wpdb->num_rows == 1 ) {
			$this->id = $c->id;
			$this->hostID = $c->host;
			$this->hostOrder = $c->host_order;
			$this->localOrder = $c->local_order;
			$this->title = $c->title;
			$this->number = $c->num;
			
			$this->type = new nbCaptionType($c->type);
			if(!$this->type->ExistsInDatabase()) {
				$this->type->SetDisplayFormat($c->print);
			}
			$this->changed = false;
		}
		
		if(!$this->type)
			$this->type = new nbCaptionType($captionTypeName);
	}
	
	/**
	 * Renames a unique caption name. This method returns true, if this caption has been renamed, false otherwise, i.e.
	 * if the new caption name already exists for the given caption type.
	 *
	 * @param string $newName
	 * @return bool Returns true if caption has been renamed, false otherwise.
	 */
	public function Rename($newName) {
		if(!self::HasCaption($newName, $this->type->getName())) {
			$this->SetVar($newName, $this->name);
			return false;
		}
			
		global $wpdb;
		$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
		$q = "UPDATE `$capt` 
				SET name = '".addcslashes($newName,'\\')."' 
				WHERE name = '$this->name'";
		$wpdb->query($q);
		return $wpbdb->rows_affected>0;
	}
	
	/**
	 * Saves this caption to the database, i.e. this method either creates a new caption or updates an existing one
	 * in the database. Note that this method returns false if this caption has not been changed, since
	 * nothing had to be written to the database.
	 *
	 * @return bool Returns true if this caption has been created or saved, false otherwise
	 */
	public function Save() {
		if(strlen($this->name)==0)
			return false;
			
		$this->localOrder = self::GetNextCaptionOrder($this->hostID);
		$this->number = $this->GetNextCaptionNumber();

		if(!$this->changed)
			return false;

		global $wpdb;
		$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();		
		if($this->id>0)
			$q = "UPDATE `$capt`
					SET num = '$this->number',
						host = '$this->hostID',
						local_order = '$this->localOrder',
						host_order = '$this->hostOrder',
						title = '".addslashes($this->title)."',
						print = '".addslashes($this->type->displayFormat)."'
					WHERE id = '$this->id'";
		else {
			$this->hostOrder = self::GetNextHostOrder($this->hostID);
						
			$q = "INSERT INTO `$capt`
					(name,type,num,host,local_order,host_order,title,print)
				VALUES ('".addslashes($this->name)."','".addslashes($this->type->getName())."','$this->number',
						'$this->hostID','$this->localOrder','$this->hostOrder',
						'".addcslashes($this->title,'\\')."','".addcslashes($this->type->displayFormat,'\\')."')";
		}
		$wpdb->query($q);
		if($this->id==0)
			$this->id = $wpdb->insert_id;
		return $wpbdb->rows_affected>0;
	}
	
	/**
	 * Removes this caption from the database
	 * @return bool True if caption has been removed, false otherwise
	 */
	public function Remove() {
		if($this->id==0) 
			return false;
			
		global $wpdb;
		$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();		
		if( $d=$wpdb->query("DELETE FROM `$capt` WHERE id = $this->id") ) {
			$this->id = 0;
			$this->changed = true;
		}		
		return $d>0;			
	}
	
	/**
	 * Loads all captions which are currently stored in the database.
	 * 
	 * @return array Returns an array of nbCaption instances, null if none are found
	 */
	public static function LoadAll() {
		$out = array();
		
		global $wpdb;
		$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
		$q = "SELECT * FROM `$capt` ORDER BY host, local_order";
		$res = $wpdb->get_results($q);
		if(is_array($res))
			foreach($res as $c)
				$out[] = new nbCaption($c->name, $c->type);
		
		return sizeof($out)>0 ? $out : null;
	}
	
	/**
	 * Loads all captions for a given wp host/post which are currently stored in the database.
	 * 
	 * @param int $host The wp post id for which the captions should be returned
	 * @return array Returns an array of nbCaption instances, null if none are found
	 */
	public static function LoadByHost( $host ) {
		$out = array();
		
		global $wpdb;
		$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
		$q = "SELECT * FROM `$capt` WHERE host = '$host' ORDER BY local_order";
		$res = $wpdb->get_results($q);
		if(is_array($res))
			foreach($res as $c)
				$out[] = new nbCaption($c->name, $c->type);
		
		return sizeof($out)>0 ? $out : null;
	}
	
	
	/**
	 * Returns whether this caption already exists in the database, i.e. if this caption has ever been saved.
	 * This method does not return whether this caption needs to be saved.
	 * 
	 * @return bool
	 */
	public function ExistsInDatabase() {
		return $this->id>0;
	}
	
	/**
	 * Returns whether this caption has been edited/changed since the last load. Note that new captions are always marked
	 * as changed. Use this method so Save() is only called when needed.
	 * 
	 * @return bool
	 */
	public function HasChanged() {
		return $this->changed;
	}
	
	/**
	 * Reloads this caption from the database. All object data will be overwritten. This method only returns true
	 * if some data are reloaded/read from the database, and false otherwise (caption is either new or has not been changed)
	 * 
	 * @return bool True if data has been loaded from database, false otherwise
	 */
	public function Reload() {
		if($this->id==0 || !$this->changed)
			return false;
			
		global $wpdb;
		$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
		$q = "SELECT * FROM `$capt` WHERE id = '$this->id'";
		if( ($c=$wpdb->get_row($q))!=null ) {
			$this->id = $c->id;
			$this->hostID = $c->host;
			$this->hostOrder = $c->host_order;
			$this->localOrder = $c->local_order;
			$this->title = $c->title;
			$this->number = $c->num;
			if(!$this->type->exists) {
				$this->type->displayFormat = $c->print;
			}
			$this->changed = false;
			return true;
		}	
		return false;	
	}
	
	/**
	 * Gets the wp host/post id for which this caption is attached to.
	 * @return int Returns wp post id
	 */
	public function GetHost() {
		return $this->hostID;
	}
	
	/**
	 * Gets the caption number
	 * @return mixed Integer or character string for caption number
	 */
	public function GetNumber() {
		return $this->number;
	}
	
	/**
	 * Gets the unique caption name
	 * @return string Returns unqiue caption name
	 */
	public function GetName() {
		return $this->name;
	}
	
	/**
	 * Gets the caption type for this caption
	 * @return nbCaptionType
	 */
	public function GetType() {
		return $this->type;
	}
	
	/**
	 * Gets the optional caption title
	 * @return string Returns the optional caption title
	 */
	public function GetTitle() {
		return $this->title;
	}
	
	/**
	 * Gets the host order index
	 * @return int Returns the order index for the parent host/post
	 */
	public function GetHostOrderIndex() {
		return $this->hostOrder;
	}
	
	/**
	 * Gets the order index for this caption within its parent post/host
	 * @return int
	 */
	public function GetCaptionOrderIndex() {
		return $this->localOrder;
	}
	
	/**
	 * Sets the title for this caption
	 * @param string $title
	 */
	public function SetTitle( $title ) {
		$this->SetVar($title, $this->title);
	}
	
	/**
	 * Checks whether a given caption (unique caption name and caption type name) exists in database.
	 * 
	 * @param string $captionName
	 * @param string $captionTypeName
	 * @return bool True if caption exists in database, false otherwise
	 */
	static public function HasCaption($captionName, $captionTypeName) {
		global $wpdb;
		$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
		$q = "SELECT * FROM `$capt` WHERE name = '$captionName'";
		if(strlen($captionTypeName)>0)
			$q .= " AND type = '$captionTypeName'";
		return $wpdb->get_row($q)!=null; 
	}
	
	/**
	 * Get the order number for a given host or post. Hosts are numbered/ordered site-wide.
	 * @param int $hostID The wp post/page id
	 * @return int Returns order number if host has been found, -1 otherwise.
	 */
	static private function GetHostOrderNum($hostID) {
		global $wpdb;
		$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
		$r = $wpdb->get_row("SELECT host_order FROM `$capt` WHERE host = '$hostID' LIMIT 1");	
		return $r!=null ? $r->host_order : -1;
	}
	
	/**
	 * Get the maximum order number for all hosts in the database. Note that order numbers start with 1.
	 * 
	 * @return int Returns the maximum order number for hosts, 0 otherwise.
	 */
	static private function GetHostOrderMaxNum() {
		global $wpdb;
		$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
		$r = $wpdb->get_row("SELECT host_order FROM `$capt` ORDER BY host_order DESC LIMIT 1");
		return $r!=null ? $r->host_order : 0;
	}
	
	/**
	 * Get the next host order number for a given host, e.g. each new caption for a new host/post requires to 
	 * set this number appropriately.
	 * 
	 * @param int $hostID The wp post/page id
	 * @return int Returns the next host order number, always starting with 1.
	 */
	static private function GetNextHostOrder($hostID) {
		return $t=self::GetHostOrderNum($hostID)>0 ? $t : self::GetHostOrderMaxNum()+1; 
	}
	
	/**
	 * Get the next caption order number for a given wp host/post id and internally increments the counter.
	 * 
	 * @param int $hostID
	 * @return int Returns caption order number starting with 1
	 */
	static private function GetNextCaptionOrder($hostID) {
		return isset(self::$captionOrderCur[$hostID]) ? 
				++self::$captionOrderCur[$hostID] : self::$captionOrderCur[$hostID]=1;
	}
	
	/**
	 * Count the number of captions either in one given host/post or in the whole database (i.e. wp site)
	 * @param int $hostID The wp post id
	 * @return int The number of captions counted
	 */
	static public function CountCaptions($hostID=0) {
		global $wpdb;
		$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
		$q = "SELECT id FROM `$capt`";
		if($hostID>0)
			$q = " WHERE host = '$hostID'";			
		$wpdb->query($q);
		return $wpdb->num_rows;	
	}
	
	
	/**
	 * Get the next caption number incremented and formatted as is required by this caption type. This method
	 * takes into account the number pattern, i.e. whether to number by post, by type, by both or none. For example,
	 * if caption number in each different post and for each different caption type should restart again, then set the public
	 * static variable nbCaption::NumberPattern to NUMBER_PATTERN_BYPOST_TYPE. If caption numbers should only restart
	 * for different caption types within this wp site, then use NUMBER_PATTERN_BYTYPE.
	 * 
	 * @return mixed Integer caption number or character string for captin number.
	 */
	private function GetNextCaptionNumber() {
		$host = $type = 0;
		if(self::$NumberPattern == self::NUMBER_PATTERN_BYPOST || self::$NumberPattern == self::NUMBER_PATTERN_BYPOST_TYPE)
			$host = $this->hostID;
		if(self::$NumberPattern == self::NUMBER_PATTERN_BYTYPE || self::$NumberPattern == self::NUMBER_PATTERN_BYPOST_TYPE)			
			$type = $this->type->getName();
	
		$n = isset(self::$captionNumberCur[$host][$type]) ?
				++self::$captionNumberCur[$host][$type] : self::$captionNumberCur[$host][$type]=1;
		
		return nbCaptionNumber::Increment($n, $this->type->GetNumberFormatNicename(),0);
	}
	
	/**
	 * Setter for a variable and modifies this->changed appropriately
	 * 
	 * @param mixed $var The variable to be copied
	 * @param mixed $dest The destination variable where var should be copied to.
	 */
	private function SetVar( $var, &$dest ) {
		if($var!=$dest) {
			$dest = $var;
			$this->changed = true;
		}
	}
	
	/**
	 * Builds a render tag conversion table and returns its result
	 * @return array Returns a render tag conversion table
	 */
	private function BuildRenderTagConversion() {
		$o = array( '<name>'=>$this->name,
					'<number>'=>$this->number,
					'<post>'=>$this->hostID,
					'<type>'=>ucfirst($this->type->getName()),
					'<title>'=>$this->title
				);
		return $o;
	}
	
	/**
	 * Renders this caption and returns the result
	 * @return string Returns the result of the rendering
	 */
	public function Render() {
		$f = $this->type->GetPrintFormat();
		$v = $this->BuildRenderTagConversion();
		foreach($v as $tag=>$val)
			$f=str_replace($tag, $val, $f);
		foreach(self::$CustomRenderTags as $tag=>$val)
			$f=str_replace($tag, $val, $f);
		return $f;
	}
	
	private $id = 0;
	private $hostID = 0;
	private $name = '';
	private $number = 0;
	private $title = '';
	private $localOrder = 0;
	private $hostOrder = 0;
	
	private $changed = true;
	private static $captionOrderCur = array();
	private static $captionNumberCur = array();
	
	public static $NumberPattern = self::NUMBER_PATTERN_BYTYPE;
	
	private static $CustomRenderTags = array();
	
	/**
	 * Caption type
	 *
	 * @var nbCaptionType
	 */
	private $type = null;
	
	const NUMBER_PATTERN_NONE = 0;
	const NUMBER_PATTERN_BYPOST = 1;
	const NUMBER_PATTERN_BYTYPE = 2;
	const NUMBER_PATTERN_BYPOST_TYPE = 3;
}

?>
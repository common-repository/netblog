<?php
require_once 'nbBibliographyItem.php';

class nbBibliographyReference {
	/**
	 * constructor
	 *
	 * @param int $id The internal database refID
	 */
	public function __construct($id=0) {
		$this->id = $id;
		$this->shortcode_include_ids = Netblog::options()->getBibShortCodeInclIds();
	}
	
	/**
	 * Saves a bibliographic reference to local database. Either creates a new one or updates the old one,
	 * based upon its reference ID or its name
	 *
	 * @return bool
	 */
	public function save() {
		global $wpdb;
		$tbl = $wpdb->prefix . Netblog::options()->getServerTableBibReference();		
		if($this->update())
			return true;
		else if(!$this->load()) {			
			if($this->typeID == 0 || strlen($this->styleName)==0 || strlen($this->name)==0
				|| $this->userID <= 0)
				return false;
			$q = "INSERT INTO `$tbl` (typeID,style,name,userID,`time`,excerpt,hide_inline)
				VALUES ('".$this->typeID."','".($this->styleName)."','".addslashes($this->name)."',
						'".$this->userID."','".($time=time())."','".($this->excerpt)."','".($this->hideInlineCitation?'1':'0')."')";
			$wpdb->query($q);
		 	if($wpdb->rows_affected==0)
		 		return false;
		 	$this->id = $wpdb->insert_id;
		 	$this->usage = 1;
		 	$this->time = $time;
		 	
		 	$rels = $wpdb->prefix . Netblog::options()->getServerTableBibReferenceRel();
		 	foreach($this->fields as $field) {
		 		if( !$field->save() )
		 			continue;
		 		$q = "INSERT INTO `$rels` (refID,itemID)
		 				VALUES ('".$this->id."','".$field->getID()."')";
		 		$wpdb->query($q);
		 		if($wpdb->rows_affected==0)
		 			return false;
		 	}
		} else {
			$q = "UPDATE `$tbl` 
					SET `usage` = `usage` + 1
					WHERE refID = '".$this->id."'";
			$wpdb->query($q);
			if($wpdb->rows_affected==0)
				return false;
			$this->usage++; 
		}
		return true;
	}
	
	/**
	 * Updates this reference based on its id or unique name
	 *
	 * @return bool
	 */
	private function update() {
		global $wpdb;
		$tbl = $wpdb->prefix . Netblog::options()->getServerTableBibReference();
		$rels = $wpdb->prefix . Netblog::options()->getServerTableBibReferenceRel();
			
		if($this->userID <= 0) {
			global $current_user;
      		get_currentuserinfo();
      		$this->userID = $current_user->ID;
		}
			
		$q = "UPDATE `$tbl` 
			SET	typeID = '".$this->typeID."',
				style = '".$this->styleName."',
				userID = '".$this->userID."',
				`time` = '".time()."',
				excerpt = '".($this->excerpt)."',
				hide_inline = '".($this->hideInlineCitation?'1':'0')."'
			WHERE (refID = '".$this->id."' OR name = '".addcslashes($this->name,'\\')."')";
		$wpdb->query($q);
		if($wpdb->rows_affected==0) {
			$this->error = $wpdb->last_error;
			return false;
		}

		$q = "SELECT * FROM `$tbl` 
				WHERE (refID = '".$this->id."' OR name = '".addcslashes($this->name,'\\')."')";
		if( ($row=$wpdb->get_row($q))==null ) {
			$this->error = "Reference $this->id/$this->name not found.";
			return false;
		}
		$this->id = $row->refID;
		$this->typeID = $row->typeID;
		$this->styleName = $row->style;
		$this->userID = $row->userID;
		$this->time = $row->time;	
		$this->usage = $row->usage;
		$this->name = $row->name;
		$this->excerpt = $row->excerpt;
		$this->hideInlineCitation = $row->hide_inline=='1';

		$item = $wpdb->prefix . Netblog::options()->getServerTableBibitem();
		$q = "UPDATE `$rels` r, `$item` i 
				SET i.usage = i.usage+1
				WHERE r.refID = '".$this->id."'
				 AND r.itemID = i.itemID";
		
		$q = "DELETE FROM `$rels` WHERE refID = '".$this->id."'";
		$wpdb->query($q);
		
		foreach($this->fields as $field) {
	 		if( !$field->save() ) {
	 			$this->error = $wpdb->last_error;
				return false;
	 		}
	 		$q = "INSERT INTO `$rels` (refID,itemID)
	 				VALUES ('".$this->id."','".$field->getID()."')";
	 		$wpdb->query($q);
	 		if($wpdb->rows_affected==0) {
				$this->error = $wpdb->last_error;
				return false;
			}
	 	}
	 	
	 	return true;
	}
	
	/**
	 * Loads the current reference from database for given reference id
	 *
	 * @return bool
	 */
	public function load() {
		global $wpdb;
		$tbl = $wpdb->prefix . Netblog::options()->getServerTableBibReference();
		$q = "SELECT * FROM `$tbl` 
				WHERE (refID = '".$this->id."' OR name = '".$this->name."')";
				
		if( ($row=$wpdb->get_row($q))==null ) {
			$this->error = $wpdb->last_error;
			return false;
		}
		$this->id = $row->refID;
		$this->typeID = $row->typeID;
		$this->styleName = $row->style;
		$this->userID = $row->userID;
		$this->time = $row->time;	
		$this->usage = $row->usage;
		$this->name = $row->name;
		$this->excerpt = $row->excerpt;
		$this->hideInlineCitation = $row->hide_inline=='1';
		
		$this->fields = array();
		$items = $wpdb->prefix . Netblog::options()->getServerTableBibitem();
		$rels = $wpdb->prefix . Netblog::options()->getServerTableBibReferenceRel();
		$q = "SELECT * FROM `$rels` r, `$items` i
				WHERE r.refID = '".$this->id."'
				 AND r.itemID = i.itemID";
		if( ($rows=$wpdb->get_results($q))!=null)
		foreach($rows as $row) {
			$this->fields[] = new nbBibliographyItem($row->fieldValue, $row->fieldID, $row->itemID, $row->usage);
		}
		return true;	
	}
	
	/**
	 * Parses an array and tries to fill this reference object
	 *
	 * @param array $arr
	 * @return bool
	 */
	public function parseArray($arr) {		
		if(!isset($arr['typeName']) || !$this->parseReferenceType($arr['typeName']) || $this->typeID==0)
			return false;
			
		if(isset($arr['styleName']))
			$this->styleName = $arr['styleName'];
		if(isset($arr['refName']))
			$this->name = $arr['refName'];
		if(isset($arr['refID']))
			$this->id = $arr['refID'];
		if(isset($arr['printFormatInline']))
			$this->printFormatInline = $arr['printFormatInline'];
		if(isset($arr['excerpt']))
			$this->excerpt = $arr['excerpt'];
		if(isset($arr['hideInlineCitation']))
			$this->hideInlineCitation = (bool)$arr['hideInlineCitation'];
			
		$this->parseFieldArray($arr);
	}
	
	/**
	 * Tries so set the reference type from a given literal name
	 * @param string $name
	 * @return bool True if given name has been found, false otherwise
	 */
	public function parseReferenceType($name) {
		$map = array( 	'booklet'=>'book_section',
						'inbook'=>'book_section',
						'proceedings'=>'conference',
						'phdthesis'=>'thesis',
						'techreport'=>'',
						'unpublished'=>'',
						'misc'=>'',
						'techreport'=>''
				);
				
		$oClass = new ReflectionClass(__CLASS__);
		if( isset($name) && $oClass->hasConstant($t=strtoupper($name)) )
			$this->typeID = $oClass->getConstant($t);
		else {
			$this->error = 'Unknown reference type "'.$name.'"';
			return false;
		}
		return true;
	}
	
	/**
	 * Parses an of fields (author, year, title etc) and returns an array of unrecognized fields
	 * @param array $arr
	 * @return array
	 */
	public function parseFieldArray($arr) {
		$na = array();
		$this->convert_field_name($arr, 'abstract', 'excerpt',true);
		if(isset($arr['excerpt'])) {
			$this->excerpt = $arr['excerpt'];
			unset($arr['excerpt']);
		}
		
		$this->fields = array();
		$iClass = new ReflectionClass('nbBibliographyItem');
		$defaultFieldVals = nbcstyle::getDftAttsNamed();
		foreach($arr as $k=>$v) {
			if( $iClass->hasConstant($f=strtoupper($k)) && isset($defaultFieldVals[$k])
				&& $defaultFieldVals[$k]!=stripslashes($v) ) {
				$this->fields[] = new nbBibliographyItem($v,$iClass->getConstant($f));
			} else $na[$k]=$v;
		}
		return $na;
	}
	
	/**
	 * Convert a field name 
	 * @param array $arr The array of fields with key=>value, e.g. book=>"a book..."
	 * @param string $old The old field name
	 * @param string $new The new field name
	 * @param bool $warning Whether to register the conversion as a warning
	 */
	private function convert_field_name(&$arr,$old,$new,$warning=false) {
		if(isset($arr[$old])) {
			$arr[$new]=$arr[$old];
			$this->warnings[] = 'Converted field "'.$old.'" to "'.$new.'"';
			unset($arr[$old]);
		}
	}
	
	/**
	 * Renders this reference as being seen by the user
	 *
	 * @return bool
	 */
	public function render() {
		if( ($mod=nbcs::loadModule($this->styleName))==null )
			return false;
			
		$mod->addReference($this);
		$this->renderResultInline = $mod->printInline();
		$this->renderResultListElement = $mod->printBiblio();
	}
	
	/**
	 * Render this reference for WP shortcodes, used in WP articles.
	 *
	 */
	public function renderShortcode() {
		$tag = Netblog::options()->getCiteShortcode();
		$short = '['.$tag.' refID="'.$this->id.'" refName="'.$this->name.'"';
		$this->renderResultShortcodeShort = $short.']';
		
		$ext = $short.' type="'.strtolower($this->getConstName($this->typeID)).'"';
		if($this->printFormatInline!='')
			$ext .= ' print_custom="'.$this->printFormatInline.'"';
		foreach($this->fields as $field) {
			$ext .= ' '.strtolower($field->getConstName($field->fieldID)).'="'.
					($field->getID()>0&&$this->shortcode_include_ids ? '__'.$field->getID().':' : '').$field->fieldValue.'"';
		}
		if(strlen($this->excerpt)>0)
			$ext .= ' excerpt="'.(str_ireplace('"', "''", $this->excerpt)).'"';
		if($this->hideInlineCitation)
			$ext .= ' hide_inline="true"';
		$this->renderResultShortcodeExtended = $ext.']';		
	}
	
	/**
	 * Get constant name for given unique value
	 *
	 * @param string $value
	 * @return string
	 */
	public function getConstName($value) {
		$oClass = new ReflectionClass(__CLASS__);
		$consts = $oClass->getConstants();
		foreach($consts as $k=>$v)
			if($v==$value)
				return $k;
		return '';
	}
	
	/**
	 * Get a list of matches based on this objects properties and some paramter
	 *
	 * @param int $limit
	 * @param string $sort
	 * @return array
	 */
	public function getMatches($limit = 10, $sort = 'DESC') {
		$r = array();
		global $wpdb;
		
		$tbl = $wpdb->prefix . Netblog::options()->getServerTableBibReference();
		$q = "SELECT * FROM `$tbl` ";
		
		$where = array();
		if(strlen($this->name)>0)
			$where[] = " name LIKE '".addcslashes($this->name,'\\')."'";
		if(strlen($this->styleName)>0)
			$where[] = " style LIKE '".addcslashes($this->styleName,'\\')."'";	
			
		if(sizeof($where)>0) {
			foreach($where as $k=>$v)
				$where[$k] = "($v)";
			$q .= ' WHERE ('.implode(' AND ',$where).')';
		}
		$sort = strtoupper($sort);
		if($sort == 'DESC' || $sort == 'ASC')
			$q .= " ORDER BY name $sort, style $sort";
		if($limit>0)
			$q .= " LIMIT $limit";

		if( ($rows=$wpdb->get_results($q))==null )
			return array();
		foreach($rows as $row) {
			$e = new nbBibliographyReference($row->refID);
			$e->load();
			$r[] = $e;
		}
		return $r;
	}
	
	/**
	 * Get this reference as an xml format
	 *
	 * @param SimpleXMLElement $xmlElement
	 */
	public function asXML($xmlElement) {
		$xref = $xmlElement->addChild('Reference');
		$xref->addAttribute('id',$this->id);
		$xref->addAttribute('name',$this->name);
		$xref->addAttribute('styleName',$this->styleName);
		$xref->addAttribute('typeID',$this->typeID);
		$xref->addAttribute('typeName',strtolower($this->getConstName($this->typeID)));
		$xref->addAttribute('userID',$this->userID);
		$xref->addAttribute('usage',$this->usage);
		$xref->addAttribute('time',$this->time);
		$xref->addAttribute('printFormatInline',$this->printFormatInline);
		$this->render();
		$this->renderShortcode();
		$xref->addAttribute('renderListElement',$this->renderResultListElement);
		$xref->addAttribute('renderInline',$this->renderResultInline);
		$xref->addAttribute('renderShortcodeExtended',$this->renderResultShortcodeExtended);
		$xref->addAttribute('renderShortcodeShort',$this->renderResultShortcodeShort);
		$xref->addAttribute('excerpt', $this->GetExcerptFormatted());
		$xref->addAttribute('excerptUnformatted', $this->GetExcerpt());
		$xref->addAttribute('hideInlineCitation', $this->hideInlineCitation?'true':'false');
		
		$xfields = $xref->addChild('Fields');
		$xfields->addChild('count',sizeof($this->fields));
		
		foreach($this->fields as $field)
			$field->asXML($xfields);
	}
	
	/**
	 * Get latest bibliographic references
	 * @param int $limit
	 * @return array of nbBibliographyReference objects
	 */
	public static function getLatest($limit=5) {
		global $wpdb;		
		$tbl = $wpdb->prefix . Netblog::options()->getServerTableBibReference();
		$q = "SELECT * FROM `$tbl` ORDER BY refID DESC LIMIT ".$limit;
		if( ($rows=$wpdb->get_results($q))==null )
			return null;
		$out = array();
		foreach($rows as $rs) {
			$t=new nbBibliographyReference($rs->refID);
			$t->load();
			$t->render();
			$out[]=$t;
		}
		return $out;
	}
	
	/**
	 * Get the excerpt (unformatted) for this reference
	 * @return string
	 */
	public function GetExcerpt() {
		return $this->excerpt;
	}
	
	/**
	 * Get the final html-like formatted excerpt for this reference
	 * @return string 
	 */
	public function GetExcerptFormatted() {
		return $this->excerpt;
	}
	
	/**
	 * Set the excerpt for this reference from an unformatted string
	 * @param string $text_unformatted
	 */
	public function SetExcerpt( $text_unformatted ) {
		$this->excerpt = strip_tags($text_unformatted);
	}
	
	/**
	 * Checks if an error exists.
	 *
	 * @return bool
	 */
	public function hasError() {
		return $this->error!='';
	}
	
	/**
	 * Get the interal database id for this reference.
	 *
	 * @return int
	 */
	public function getID() {
		return $this->id;
	}
	
	/**
	 * Get an array of media type nicenames
	 * @return array
	 */
	static public function GetTypeNicenames() {
		return array(
			self::BOOK => __('Book','netblog'),
			self::BOOK_SECTION => __('Book Sections','netblog'),
			self::BOOKLET => __('Booklet','netblog'),
			self::JOURNAL => __('Journal Article','netblog'),
			self::MAGAZINE => __('Magazine Article','netblog'),
			self::NEWSPAPER => __('Newspaper Article','netblog'),
			self::ENCYCLOPEDIA => __('Encyclopedia Article','netblog'),
			self::WIKI => __('Wikipedia Article','netblog'),
			self::CONFERENCE => __('Conference Proceedings','netblog'),
			self::REPORT => __('Report','netblog'),
			self::THESIS => __('PhD/Master/BSc/BA Thesis','netblog'),
			self::ERIC => __('ERIC Document','netblog'),
			self::WEBSITE => __('Online Website','netblog'),
			self::BLOG => __('Online Weblog','netblog'),
			self::VIDEO => __('Online Video','netblog'),
			self::POWERPOINT => __('PowerPoint Presentation','netblog'),
			self::ART => __('Art','netblog'),
			self::RECORDING => __('Sound Recording','netblog'),
			self::PERFORMANCE => __('Theatrical Performance','netblog'),
			self::FILM => __('Film/Movie','netblog'),
			self::PATENT => __('Patent','netblog'),
			self::STANDARD => __('Standard/ISO','netblog'),
			self::MAP => __('Geographical Map','netblog'),
			self::LAWCASE => __('Lawcase','netblog'),
			self::MANUAL => __('Technical Manual','netblog')
		);
	}
	
	/**
	 * Get an array of media type definitions
	 * @return array
	 */
	static public function GetTypeDefinitions() {
		return array(
			self::BOOK => __('A book with an explicit publisher','netblog'),
			self::BOOK_SECTION => __('A part of a book, which may be a chapter (or section or whatever) and/or a range of pages','netblog'),
			self::BOOKLET => __('A work that is printed and bound, but without a named publisher or sponsoring institution','netblog'),
			self::JOURNAL => __('An article from a journal','netblog'),
			self::MAGAZINE => __('An article from a magazine','netblog'),
			self::NEWSPAPER => __('An article from a newspaper, published written or online','netblog'),
			self::ENCYCLOPEDIA => __('An entry or article from a printed or online encyclopedia','netblog'),
			self::WIKI => __('An article from Wikipedia. Similar to Encyclopedia, but sometimes with a special markup format','netblog'),
			self::CONFERENCE => __('The proceedings of a conference','netblog'),
			self::REPORT => __('A report published by a school or institution, usually numbered within a series','netblog'),
			self::THESIS => __('A PhD or Master\'s thesis','netblog'),
			self::ERIC => __('An Eric document','netblog'),
			self::WEBSITE => __('An article, post, document or paragraph from a website','netblog'),
			self::BLOG => __('A post from a Weblog, like WordPress. Similar to website, but with special formatting','netblog'),
			self::VIDEO => __('An online or "printed" video, e.g. a video published by a company or by a personal use in YouTube','netblog'),
			self::POWERPOINT => __('A powerpoint, either to be found via given URL or by a given author or company or institution','netblog'),
			self::ART => __('A painting, photograph or whatever that is considered to be a proper art. Use "Film" for published movies','netblog'),
			self::RECORDING => __('An audio recording','netblog'),
			self::PERFORMANCE => __('A performance published by a theater, a group or an institution','netblog'),
			self::FILM => __('A film produced or published by a company, work group or institution','netblog'),
			self::PATENT => __('A patent requested or created by individuals or by an institution or company','netblog'),
			self::STANDARD => __('A standard by an institution or organisation, like ISO','netblog'),
			self::MAP => __('A map released or created by a work group, company or whatever','netblog'),
			self::LAWCASE => __('A specific case in law','netblog'),
			self::MANUAL => __('A technical documentation','netblog')
		);
	}
	
	/**
	 * List of all attached fields to this bibliographic reference
	 *
	 * @var nbBibliographyItem
	 */
	public $fields = array();
	
	/**
	 * The internal reference id from database
	 *
	 * @var unknown_type
	 */
	private $id = 0;
	
	/**
	 * A user friendly name for this reference
	 *
	 * @var string
	 */
	public $name = '';
	
	/**
	 * The citation type id, as a const from this class
	 *
	 * @var int
	 */
	public $typeID = 0;
	
	/**
	 * The citation style name (as codename) for which this was/is originally created
	 *
	 * @var string
	 */
	public $styleName = '';
	
	/**
	 * The custom print format for inline print of this reference
	 *
	 * @var string
	 */
	public $printFormatInline = '';
	
	/**
	 * How many times this reference has been used
	 *
	 * @var unknown_type
	 */
	public $usage = 0;
	
	/**
	 * The creation time
	 *
	 * @var timestamp
	 */
	public $time = 0;
	
	/**
	 * The WP user id that created this reference
	 *
	 * @var int
	 */
	public $userID = 0;
	
	/**
	 * The last result from rendering this reference as an element in a table of bibliography
	 *
	 * @var string
	 */
	public $renderResultListElement = '';
	
	/**
	 * The last result from rendering this reference as inline
	 *
	 * @var string
	 */
	public $renderResultInline = '';
	
	/**
	 * The last result from rendering this reference for WP shortcode used in WP articles,
	 * in extended format
	 *
	 * @var string
	 */
	public $renderResultShortcodeExtended = '';
	
	/**
	 * The last result from rendering this reference for WP shortcode used in WP articles,
	 * in short format
	 *
	 * @var string
	 */
	public $renderResultShortcodeShort = '';
	
	/**
	 * The optional unformatted excerpt for this reference. It might be a paragraph, detailed and long citation, description of the referenced media etc. 
	 * @var string
	 */
	private $excerpt = '';
	
	/**
	 * Whether to hide the inline citation
	 * @var bool
	 */
	public $hideInlineCitation = false;
	
	/**
	 * The error string for the last error
	 *
	 * @var string
	 */
	public $error = '';
	
	public $warnings = array();
	
	private $shortcode_include_ids = true;
	
	const BOOK = 1;
	const BOOKSECTION = 2;
	const JOURNAL = 3;
	const MAGAZINE = 4;
	const NEWSPAPER = 5;
	const ENCYCLOPEDIA = 6;
	const CONFERENCE = 7;
	const REPORT = 8;
	const THESIS = 9;
	const ERIC = 10;
	const WEBSITE = 11;
	const WIKI = 12;
	const BLOG = 13;
	const VIDEO = 14;
	const POWERPOINT = 15;
	const ART = 16;
	const RECORDING = 17;
	const PERFORMANCE = 18;
	const FILM = 19;
	const PATENT = 20;
	const STANDARD = 21;
	const MAP = 22;
	const LAWCASE = 23;
	const BOOKLET = 24;
	const MANUAL = 25;
}
?>
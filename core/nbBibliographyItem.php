<?php
class nbBibliographyItem {
	/**
	 * constructor
	 *
	 * @param string $value
	 * @param int $fieldID A valid constant from this class
	 */
	public function __construct($value = '', $fieldID = 0, $id = 0, $usage = 0) {
		$this->fieldValue = $value;
		if(is_int($fieldID))
			$this->fieldID = $fieldID;
		else if( ($v=$this->getConstValue($fieldID))!='' )
			$this->fieldID = $v;
		else $this->fieldID = intval($fieldID);
		
		$this->id = $id;
		$this->usage = $usage;
	}
	
	/**
	 * Saves this bibliographic item to the database and updates the existing one, if required
	 *
	 * @return unknown
	 */
	public function save() {
		global $wpdb;
		$tbl = $wpdb->prefix . Netblog::options()->getServerTableBibitem();
		if($this->update())
			return true;
		else if(!$this->load()) {
			if($this->fieldID == 0 || strlen($this->fieldValue)==0)
				return false;
		 	$q = "INSERT INTO `$tbl` (fieldID,fieldValue)
		 			VALUES ('".$this->fieldID."','".addcslashes($this->fieldValue,'\\')."')";
		 	$wpdb->query($q);
		 	if($wpdb->rows_affected==0)
		 		return false;
		 	$this->id = $wpdb->insert_id;
		 	$this->usage = 1;
		} else {
			$q = "UPDATE `$tbl` 
					SET `usage` = `usage` + 1
					WHERE itemID = '".$this->id."'";
			$wpdb->query($q);
			if($wpdb->rows_affected==0)
				return false;
			$this->usage++; 	
		}
		return true;
	}
	
	/**
	 * Updates this bibliographic item based on its id
	 *
	 * @return bool
	 */
	private function update() {
		global $wpdb;
		$tbl = $wpdb->prefix . Netblog::options()->getServerTableBibitem();
		$q = "UPDATE `$tbl` SET
				fieldID = '".$this->fieldID."',
				fieldValue = '".addcslashes($this->fieldValue,'\\')."'
			WHERE itemID = '".$this->id."'";
		$wpdb->query($q);
		return $wpdb->rows_affected > 0;
	}
	
	/**
	 * Loads this bibliographic item from database, either from 
	 * given id or from combination of fieldID and fieldValue
	 *
	 * @return bool
	 */
	public function load() {
		global $wpdb;
		$tbl = $wpdb->prefix . Netblog::options()->getServerTableBibitem();
		$q = "SELECT * FROM `$tbl` 
			WHERE (itemID = ".$this->id." 
				OR (fieldID = '".$this->fieldID."' AND fieldValue = '".addcslashes($this->fieldValue,'\\')."'))";
		if( ($row=$wpdb->get_row($q))==null )
			return false;
		
		$this->id = $row->itemID;
		$this->fieldID = $row->fieldID;
		$this->fieldValue = $row->fieldValue;
		$this->usage = $row->usage;		
		return true;
	}
	
	/**
	 * Get a list of matched nbBibliographyItem's that matches the criteria
	 *
	 * @param int $limit
	 * @param string $sort
	 * @return array
	 */
	public function getMatches($limit = 10, $sort = 'DESC') {
		$r = array();
		global $wpdb;
		$tbl = $wpdb->prefix . Netblog::options()->getServerTableBibitem();
		
		$q = "SELECT * FROM `$tbl` ";
		$where = array();
		if($this->fieldID > 0) {
			$where[] = "fieldID = '$this->fieldID'";
		}
		if(strlen($this->fieldValue)>0) {
			$where[] = "fieldValue LIKE '".addcslashes($this->fieldValue,'\\')."'";
		}
		if(sizeof($where)>0) {
			foreach($where as $k=>$v)
				$where[$k] = "($v)";
			$q .= ' WHERE ('.implode(' AND ',$where).')';
		}
		$sort = strtoupper($sort);
		if($sort == 'DESC' || $sort == 'ASC')
			$q .= " ORDER BY fieldValue $sort";
		if($limit>0)
			$q .= " LIMIT $limit";

		if( ($rows=$wpdb->get_results($q))==null )
			return array();
		foreach($rows as $row) {
			$r[] = new nbBibliographyItem($row->fieldValue,$row->fieldID,$row->itemID,$row->usage);
		}
		return $r;
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
	 * Get constant value for given constant name as string literal
	 *
	 * @param string $name
	 * @return int
	 */
	public function getConstValue($name) {
		$oClass = new ReflectionClass(__CLASS__);
		if($oClass->hasConstant($name))
			return $oClass->getConstant($name);
		return 0;
	}
	
	/**
	 * Get this bibliographic item as an xml element
	 *
	 * @param SimpleXMLElement $xmlElement
	 */
	public function asXML($xmlElement) {
		$xitem = $xmlElement->addChild('Item');
		$xitem->addAttribute('fieldID',$this->fieldID);
		$xitem->addAttribute('fieldName',strtolower($this->getConstName($this->fieldID)));
		$xitem->addAttribute('fieldValue',$this->fieldValue);
		$xitem->addAttribute('fieldTitle',ucfirst($this->fieldValue));
		$xitem->addAttribute('usage',$this->usage);
		$xitem->addAttribute('id',$this->id);
	}
	
	/**
	 * Get the database id for this bibliographic item.
	 *
	 * @return int
	 */
	public function getID() {
		return $this->id;
	}
	
	/**
	 * Get an array of field nicenames
	 * @return array
	 */
	static public function GetFieldNicenames() {
		return array(
				self::AUTHOR => __('Author','netblog'),
				self::TITLE => __('Title','netblog'),
				self::CITY => __('City','netblog'),
				self::PUBLISHER => __('Publisher','netblog'),
				self::PUBLISHER_PLACE => __('Place of publisher','netblog'),
				self::PAGES => __('Page(s)','netblog'),
				self::ISSUE => __('Issue/Number','netblog'),
				self::BOOK_AUTHOR => __('Book Author','netblog'),
				self::BOOK_TITLE => __('Book Title','netblog'),
				self::TITLE_PERIODICAL => __('Periodical\'s Title','netblog'),
				self::INITIALS => __('Author\'s Initials','netblog'),
				self::YEAR => __('Year','netblog'),
				self::MONTH => __('Month','netblog'),
				self::DAY => __('Day','netblog'),
				self::YEAR_ACCESS => __('Year of Access','netblog'),
				self::MONTH_ACCESS => __('Month of Access','netblog'),
				self::DAY_ACCESS => __('Day of Access','netblog'),
				self::URL => __('URL','netblog'),
				self::DOI => __('DOI Number','netblog'),
				self::CONFERENCE_NAME => __('Conference Name','netblog'),
				self::AWARD => __('Scientific Award','netblog'),
				self::YEAR_ORGANISATION => __('','netblog'),
				self::PAGES_ORGANISATION => __('','netblog'),
				self::COMPOSER => __('Composer','netblog'),
				self::CONDUCTOR => __('Conductor','netblog'),
				self::PERFORMER => __('Performer','netblog'),
				self::WRITER => __('','netblog'),
				self::THEATER => __('Theater','netblog'),
				self::DIRECTOR => __('Director','netblog'),
				self::MOVIE => __('Movie','netblog'),
				self::INVENTOR => __('Inventor','netblog'),
				self::PATENT_NUMBER => __('Patent Number','netblog'),
				self::CASE_NUMBER => __('Case Cumber','netblog'),
				self::COURT => __('Court','netblog'),
				self::SOURCE => __('','netblog'),
				self::SPECIAL_ENTRY => __('Misc #1','netblog'),
				self::SPECIAL_ENTRY2 => __('Misc #2','netblog'),
				self::WEBPAGE => __('Webpage','netblog'),
				self::WEBSITE => __('Website','netblog'),
				self::TYPE => __('Type','netblog'),
				self::PRINT_CUSTOM => __('Print Format','netblog'),
				self::ISBN => __('ISBN','netblog'),
				self::ISSN => __('ISSN','netblog'),
				self::LCCN => __('LCCN','netblog'),
				self::KEYWORDS => __('Keywords','netblog'),
				self::PRICE => __('Price','netblog'),
				self::COPYRIGHT => __('Copyright','netblog'),
				self::LANGUAGE => __('Language','netblog')
			);
	}
	
	/**
	 * Get an array of field definitions
	 * return array
	 */
	static public function GetFieldDefinitions() {
		return array(
				self::AUTHOR => __('The name(s) of the author(s)','netblog'),
				self::TITLE => __('The title of a book, newspaper, magazine, journal, conference or whatever','netblog'),
				self::CITY => __('The city or place where the referenced media has been published','netblog'),
				self::PUBLISHER => __('The publisher\'s name','netblog'),
				self::PUBLISHER_PLACE => __('City or place of the referenced media\'s publisher','netblog'),
				self::PAGES => __('One or more page numbers or range of numbers, such as 42--111 or 7,41,73--97 or 43+ (the `+\' in this last example indicates
								 pages following that don\'t form a simple range). To make it easier to maintain Scribe-compatible databases, the standard styles convert a 
								 single dash (as in 7-33) to the double dash used in TeX to denote number ranges (as in 7--33).','netblog'),
				self::VOLUME => __('The volume of a journal or multi-volume book.','netblog'),
				self::ISSUE => __('The number of an issue','netblog'),
				self::BOOK_AUTHOR => __('Author(s) of a book','netblog'),
				self::BOOK_TITLE => __('Title of a book','netblog'),
				self::TITLE_PERIODICAL => __('Title of a periodical, i.e. a media being published regularly, like a magazine or scientific report','netblog'),
				self::INITIALS => __('The author\'s initials','netblog'),
				self::YEAR => __('The year of publication or, for an unpublished work, the year it was written. Generally it should consist of four numerals, such as 1984, although the 
								standard styles can handle any year whose last four nonpunctuation characters are numerals, such as `\hbox{(about 1984)}\'.','netblog'),
				self::MONTH => __('The month of publication, in the format of either Jan or January','netblog'),
				self::DAY => __('The day of publication, formatted with/without leading zero','netblog'),
				self::YEAR_ACCESS => __('Year of access of an online media, formatted like Year','netblog'),
				self::MONTH_ACCESS => __('Month of access of an online media, formatted as Jan or January','netblog'),
				self::DAY_ACCESS => __('Day of access of an online media, formatted with/without leading zero','netblog'),
				self::URL => __('The WWW Universal Resource Locator that points to the item being referenced. This often is used for technical reports to point to the ftp site 
								where the postscript source of the report is located. ','netblog'),
				self::DOI => __('The DOI number for given referenced media','netblog'),
				self::CONFERENCE_NAME => __('Name of a conference','netblog'),
				self::AWARD => __('Title of an award, like PhD or Master','netblog'),
				self::YEAR_ORGANISATION => __('','netblog'),
				self::PAGES_ORGANISATION => __('','netblog'),
				self::COMPOSER => __('Name of the composer(s) for an audio recording','netblog'),
				self::CONDUCTOR => __('','netblog'),
				self::PERFORMER => __('Name of the performer(s) for a theatrical performance','netblog'),
				self::WRITER => __('','netblog'),
				self::THEATER => __('Name of the theater where performance has been (first) published','netblog'),
				self::DIRECTOR => __('Name of the conductor(s) for a film','netblog'),
				self::MOVIE => __('','netblog'),
				self::INVENTOR => __('','netblog'),
				self::PATENT_NUMBER => __('Patent number for a given "Patent"','netblog'),
				self::CASE_NUMBER => __('Case number for a given "Law Case"','netblog'),
				self::COURT => __('The place or name where a law case has been performed/published','netblog'),
				self::SOURCE => __('','netblog'),
				self::SPECIAL_ENTRY => __('When all others does not fit','netblog'),
				self::SPECIAL_ENTRY2 => __('When all others does not fil','netblog'),
				self::WEBPAGE => __('Title of a loaded webpage as being displayed by common webbrowsers','netblog'),
				self::WEBSITE => __('Name or publisher or owner of a website','netblog'),
				self::TYPE => __('','netblog'),
				self::PRINT_CUSTOM => __('','netblog'),
				self::ISBN => __('The International Standard Book Number','netblog'),
				self::ISSN => __('The International Standard Serial Number','netblog'),
				self::LCCN => __('The Library of Congress Call Number','netblog'),
				self::KEYWORDS => __('Key words used for searching or possibly for annotation','netblog'),
				self::PRICE => __('The price of the document','netblog'),
				self::COPYRIGHT => __('Copyright information','netblog'),
				self::LANGUAGE => __('The language the document is in','netblog')
			);
	}
	
	private $id = 0;
	public $fieldID = 0;
	public $fieldValue = '';
	public $usage = 0;
	
	
	const AUTHOR = 1;
	const TITLE = 2;
	const YEAR = 3;
	const CITY = 4;
	const PUBLISHER = 5;
	const PUBLISHER_PLACE = 6;
	const PAGES = 7;
	const VOLUME = 8;
	const ISSUE = 9;
	const BOOK_AUTHOR = 10;
	const BOOK_TITLE = 11;
	const TITLE_PERIODICAL = 12;
	const INITIALS = 13;
	const MONTH = 14;
	const DAY = 15;
	const YEAR_ACCESS = 16;
	const MONTH_ACCESS = 17;
	const DAY_ACCESS = 18;
	const URL = 19;
	const DOI = 20;
	const CONFERENCE_NAME = 21;
	const AWARD = 22;
	const YEAR_ORGANISATION = 23;
	const PAGES_ORGANISATION = 24;
	const COMPOSER = 25;
	const CONDUCTOR = 26;
	const PERFORMER = 27;
	const WRITER = 28;
	const THEATER = 29;
	const DIRECTOR = 30;
	const MOVIE = 31;
	const INVENTOR = 32;
	const PATENT_NUMBER = 33;
	const CASE_NUMBER = 34;
	const COURT = 35;
	const SOURCE = 36;
	const SPECIAL_ENTRY = 37;
	const SPECIAL_ENTRY2 = 38;
	const WEBPAGE = 39;
	const WEBSITE = 40;
	const TYPE = 41;
	const PRINT_CUSTOM = 42;
	const ISBN = 43;
	const ISSN = 44;
	const LCCN = 45;
	const KEYWORDS = 46;
	const PRICE = 47;
	const COPYRIGHT = 48;
	const LANGUAGE = 49;
}
?>
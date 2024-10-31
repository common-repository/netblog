<?php

/**
 * nbCaptionType describes the creation, settings update and removal of caption types, e.g. figure, equation, chapter.
 * This class replaced most static methods in nbdb.php beginning with cptg_ (abbreviation for global caption) to
 * make the code clearer and more self-explanatory (and more oop).
 * 
 * @since Netblog 2.0
 * 
 * 
 */
class nbCaptionType {
	
	/**
	 * Loads a caption type
	 *
	 * @param string $name The unique type name
	 */
	public function __construct($name) {
		$cpts = Netblog::options()->getCaptionGlobals();
		$this->name = $name;
		if(isset($cpts[$name])) {
			$c = $cpts[$name];
			$this->printFormat = self::SecurizePrintFormat($c['printFormat']);
//$this->printFormat = '<strong><type> <number></strong>: <title> (<name>)';
			$this->numberFormat = self::InterpretNumberIName($c['numberFormat']);
			$this->displayFormat = self::InterpretDisplayFormatIName($c['display']);
			$this->isActive = is_bool($c['isactive']) ? $c['isactive'] : false;
			$this->exists = true;
			$this->changed = false;
		}
	}
	
	/**
	 * Saves the caption type via nboption. This method updates an existing caption type or it creates a new
	 * caption type in the database.
	 * If object flag or method's parameter flag is set to FLAG_REMOVE, this caption type will be removed.
	 * If flag is set to FLAG_CLEAN, this method will restore the caption type's settings to the default ones.
	 * In case this method fails, use the public error and errno variables to get more information about failure.
	 *
	 * Renamed method from save() to Save() with version 2.0.b6
	 *
	 * @return bool Returns false on failure of either update or creation, true otherwise
	 */
	public function Save() {
		if(!$this->changed) 
			return false;
			
		if(strlen($this->name)==0) {
			$this->error = 'Cannot Save Caption (missing name)';
			$this->errno = 500;
			return false;
		}
		
		if($this->flag == FLAG_REMOVE) {
			$this->Remove();
			return true;
		}
		
		if($this->flag == FLAG_CLEAN)
			$this->ResetToDefault();
		
		if(!$this->exists && !current_user_can(Netblog::options()->getCaptionPrivGadd()) ) {
			$this->error = 'Missing Privilege: cannot create new caption type';
			$this->errno = 600;
			return false;
		}
			
		$cpts = Netblog::options()->getCaptionGlobals();
		$o = array();
		$o['printFormat'] = $this->printFormat;
		$o['numberFormat'] = $this->numberFormat;
		$o['display'] = $this->displayFormat;
		$o['isactive'] = $this->isActive;		
		$cpts[$this->name] = $o;
		return true;
	}
	
	
	/**
	 * Removes this caption type from database, if it exists. This method is equivalent to calling save(FLAG_REMOVE)
	 */
	public function Remove() {
		$cpts = Netblog::options()->getCaptionGlobals();
		if(isset($cpts[$this->name])) {
			unset($cpts[$this->name]);
			Netblog::options()->setCaptionGlobals($cpts);
		}
		$this->errno = 0;
	}
	
	/**
	 * Resets the settings to its default values, but it does not save them to the database. In order to save them
	 * call save() afterwards, which is equivalent to calling save(FLAG_CLEAN).
	 */
	public function ResetToDefault() {
		$this->printFormat = '($number)';
		$this->numberFormat = NUMBER_DECIMAL;
		$this->displayFormat = DISPLAY_INLINE;
		$this->isActive = false;
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
	 * Get caption type name
	 *
	 * @return string
	 */
	public function GetName() {
		return $this->name;
	}
	
	/**
	 * Get caption type id
	 *
	 * @return integer
	 */
	public function GetID() {
		return $this->id;
	}
	
	/**
	 * Get the print format for this caption
	 * @return string
	 */
	public function GetPrintFormat() {
		return $this->printFormat;
	}
	
	/**
	 * Whether this caption type has already been saved to the database.
	 * @return bool 
	 */
	public function ExistsInDatabase() {
		return $this->exists;
	}
		
	/**
	 * Sets the display format. The argument can be either the nicename or a const as defined in this class.
	 * @param string $format
	 */
	public function SetDisplayFormat( $format ) {
		$this->SetVar( is_string($format)?self::InterpretDisplayFormatIName($format):$format, $this->displayFormat);
	}
	
	/**
	 * Sets the number format. The argument can be either the nicename or a const as defined in this class.
	 * @param string $format
	 */
	public function SetNumberFormat( $format ) {
		$this->SetVar( is_string($format)?self::InterpretNumberIName($format):$format, $this->numberFormat);
	}
	
	/**
	 * Sets the print format.
	 * @param string $format
	 */
	public function SetPrintFormat( $format ) {
		$this->SetVar( self::SecurizePrintFormat($format), $this->printFormat);
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
	 * If this caption type already exists in database, this methods returns whether this caption overrides all
	 * local definitions of the same type, i.e. where the user used the same caption type in a post but with
	 * different settings. 
	 * @return bool
	 */
	public function OverrideLocalSettings() {
		return $this->isActive;
	}
	
	/**
	 * Loads all saved caption types from database. This method replaces nbdb::cptg_getTypes().
	 * @return array of nbCaptionType
	 */
	public static function LoadAll() {
		$out = array();
		$cpts = Netblog::options()->getCaptionGlobals();
		if(is_array($cpts)) 
			foreach($cpts as $name=>$vars)
				$out[] = new nbCaptionType($name);
		return sizeof($out)>0 ? $out : null;
	}
	
	/**
	 * Returns number look up table for conversion between nicename und code for numbering type
	 * @return array
	 */
	public static function GetNumberLUT() {
		return array('decimal'=>self::NUMBER_DECIMAL,
					'lower-alpha'=>self::NUMBER_ALPHA_LOWER,
					'upper-alpha'=>self::NUMBER_ALPHA_UPPER,
					'lower-roman'=>self::NUMBER_ROMAN_LOWER,
					'upper-roman'=>self::NUMBER_ROMAN_UPPER,
					'lower-greek'=>self::NUMBER_GREEK_LOWER);
	}
	
	/**
	 * Get the corresponding constant NUMBER_* for a given number nicename.
	 * Valid nicenames are decimal, lower-alpha, upper-alpha, lower-roman, upper-roman or lower-greek.
	 * 
	 * @param string $nicename The nicename for a given number format
	 * @return int The corresponding NUMBER_* constant if found, -1 otherwise.
	 */
	public static function InterpretNumberName( $nicename ) {
		$a = self::GetNumberLUT();
		return isset($a[$nicename]) ? $a[$nicename] : -1;
	}
	
	/**
	 * Similar to InterpretNumberName(), but handles nicename as case-insensitive
	 * @param string $nicename The nicename for a given number format
	 * @return int The corresponding NUMBER_* constant
	 */
	public static function InterpretNumberIName( $nicename ) {
		$a = self::GetNumberLUT();
		return isset($a[$n=strtolower($nicename)]) ? $a[$n] : -1;
	}
	
	/**
	 * Interprets a number constant code/value and returns its corresponding nicename. This method is the
	 * opposite to InterpretNumberName().
	 * 
	 * @param int $number_code The NUMBER_* constant
	 * @return string The corresponding nicename for a given number format if the number code is found, false otherwise.
	 */
	public static function InterpretNumberCode( $number_code ) {
		$a = self::GetNumberLUT();
		return array_search( $number_code, $a );
	}
	
	/**
	 * Returns the display look up table (array) for conversion between nicename and code for display format
	 * @return array
	 */
	public static function GetDisplayLUT() {
		return array(	'inline'=>self::DISPLAY_INLINE,
						'left'=>self::DISPLAY_LEFT,
						'right'=>self::DISPLAY_RIGHT);
	}
	
	/**
	 * Get the corresponding constant DISPLAY_* for a given display format name.
	 * Valid nicenames are inline, left and right.
	 * 
	 * @param string $nicename The nicename for display format
	 * @return int The corresponding DISPLAY_* constant if found, -1 otherwise.
	 */
	public static function InterpretDisplayFormatName( $nicename ) {
		$a = self::GetDisplayLUT();
		return isset($a[$nicename]) ? $a[$nicename] : -1;
	}
	
	/**
	 * Similar to InterpretDisplayFormatName(), but handles parameter nicename as case-insensitive
	 * @param string $nicename The nicename for display format
	 * @return int The corresponding DISPLAY_* constant if found, -1 otherwise.
	 */
	public static function InterpretDisplayFormatIName( $nicename ) {
		$a = self::GetDisplayLUT();
		return isset($a[$n=strtolower($nicename)]) ? $a[$n] : -1;
	}
	
	/**
	 * Interprets a display format constant code and returns its corresponding nicename. This method is the
	 * opposite to InterpretDisplayFormatName().
	 * 
	 * @param string $display_format_code The DISPLAY_* constant
	 * @return string The corresponding nicename for display format if found, false otherwise
	 */
	public static function InterpretDisplayCode( $display_format_code ) {
		$a = self::GetNumberLUT();
		return array_search( $number_code, $a );
	}
	
	/**
	 * A look up table for all reserved print format tags.
	 * @return array Returns the LUT for print format tags
	 */
	private static function GetPrintLUT() {
		return array(	'$number'=>'<number>',
						'$title'=>'<title>',
						'$post'=>'<post>' );
	}
	
	/**
	 * This method cleans and securizes the print format so that no php code variables from older versions appear any more,
	 * or from faulty user input. Older hard coded variable names like $number are replaced with more secure and more
	 * customizable tags like <number> which are now required by nbCaption to render its output properly.
	 * 
	 * @param string $print_format
	 * @return string Returns the cleaned and securized print_format
	 */
	public static function SecurizePrintFormat( $print_format ) {
		$t = self::GetPrintLUT();
		foreach($t as $code=>$tag)
			if(stripos($print_format, $code)!==false)
				$print_format = str_ireplace($code, $tag, $print_format);
		return $print_format;
	}
	
	/**
	 * Gets the number format as constant value (integer)
	 * @return int Returns the number constant format for this captin type
	 */
	public function GetNumberFormat() {
		return $this->numberFormat;
	}
	
	/**
	 * Gets the number format converted into a nicename. This method is equivalent to calling
	 * nbCaptionType::InterpretNumberCode(nbCaptionType->GetNumberFormat());
	 * @return string Returns the nicename equivalent for the number format of this caption type.
	 */
	public function GetNumberFormatNicename() {
		return self::InterpretNumberCode($this->numberFormat);
	}
	
	/**
	 * Gets the display format as const value (integer)
	 * @return int Returns the display format constant for this caption type
	 */
	public function GetDisplayFormat() {
		return $this->displayFormat;
	}
	
	/**
	 * Gets the display format converted into a nicename. This method is equivalent to calling
	 * nbCaptionType::InterpretDisplayCode(nbCaptionType->GetDisplayFormat());
	 * @return string Returns the nicename equivalent for the display format of this caption
	 */
	public function GetDisplayFormatNicename() {
		return self::InterpretDisplayCode($this->displayFormat);
	}
	
	/**
	 * Get the default caption types
	 * @return array Returns an array of default caption types
	 */
	static function GetDefaultCaptionTypes() {
		return array( 	__('equation','netblog'),
						__('figure','netblog'),
						__('table','netblog'),
						__('chapter','netblog'),
						__('headline','netblog')  );
	}
	
	private $changed = true;
	private $id = 0;
	private $name = '';
	private $printFormat = '(<number>)';
	private $numberFormat = self::NUMBER_DECIMAL;	
	private $displayFormat = self::DISPLAY_INLINE;
	private $isActive = false;
	
	private $exists = false;
	public $flag = self::FLAG_NONE;
	public $error = '';
	public $errno = 0;
	
	const DISPLAY_INLINE = 1;
	const DISPLAY_LEFT = 2;
	const DISPLAY_RIGHT = 3;
	const NUMBER_DECIMAL = 10;
	const NUMBER_ALPHA_LOWER = 11;
	const NUMBER_ALPHA_UPPER = 12;
	const NUMBER_ROMAN_LOWER = 13;
	const NUMBER_ROMAN_UPPER = 14;
	const NUMBER_GREEK_LOWER = 15;
	const FLAG_NONE = 30;
	const FLAG_REMOVE = 31;
	const FLAG_CLEAN = 32;
}
?>
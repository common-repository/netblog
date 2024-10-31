<?php

/**
 * A collection of methods to manage Caption Feature for Wordpress
 *
 * @author Benjamin Sommer
 * @todo Rename this class to something more meaningful, e.g. nbCaptionFilter
 */
final class nbcpt {
	
	
/**
 * Caption Shortcode Handler.
 *
 * @param array $atts Array of attributes.
 * @param string $content String of innerTag.
 * @return string Generated code from tag attributes.
 */
static public function shortcode( $atts, $content = null ) {
	
	// PREPARE INPUT
	$a = self::filterAtts($atts);
	extract($a);
	
	// FIRST CALL SAVES SESSION SETTINGS (for each type)
	if( strlen($type) > 0 ) {
		self::registerType($a);
		extract( self::getType($type) != null ? self::getType($type) : array() );
	}

	// VERIFY FORMAT
	if( $name == '' && $ref == '' ) return;
	
	$format = self::filterFormat($format);
	$name = self::filterName($name);
	
	// REGISTER NEW CAPTION AND INCREMENT NUMBERING
	self::register($a);
	$number = self::getCaptNumber( $name, $type );

	// UPDATE DATABASE
	if( !$local && Netblog::$isSavePost && strlen($name) > 0 && strlen($type) > 0 )
		return nbdb::cpt_add( $name, $type, $_POST['post_ID'], $number,
						self::$position++, $title, $print, $format, $display );
		
	// PRINT CAPTION
	if( $name != '' ) {
		if(empty($title)) 
			$title = "NO_CAPTION_TITLE[$name]";
		if (empty($print)) {
			$g = nbdb::cptg_get($type);
			$print = $g['printFormat'];
		}
		$c = '<a name="nbcaption-'.$name.'" title="'.ucfirst($type).' '.$number.': '.ucfirst($title).'">'.
			 '<strong>'.eval("return \"$print\"; ").'</strong></a>';
		if( $display == 'right' )
			$c = '<div style="float:right; padding-left: 10px">'.$c.'</div>';
		else if( $display == 'left' )
			$c = '<div style="float:left; padding-right: 10px">'.$c.'</div>';
		return $c;
	}
		
	// REFERENCE CAPTION
	if( $ref != '' ) 
	{
		if( ($c=self::fetch( $ref, $type )) == null )
			return '<font style="color:red; font-style:italic">['.__('Invalid caption numbering','netblog').']</font>';
		extract($c);
		
		if(strlen($title)==0) $title = "No title ($name)";
		$url = self::existsInSession($name,$type) ? '' : get_permalink($host);		
		
		return '<a href="'.$url.'#nbcaption-'.$name.'" style="text-decoration:none" title="'.ucfirst($type)
				.' '.$number.': '.ucfirst($title).'">'.eval("return \"$print\"; ").'</a>';						
	}
						
	return '<font style="color:red; font-style:italic">['.__('Invalid caption numbering','netblog').']</font>';
}
	
	
/**
 * Increment a given caption number.
 *
 * @deprecated This method has been replaced with nbCaptionNumber::Increment() since Netblog 2.0.b6
 * @param mixed $num UINT caption number; ASTRING alpha-string, like abcw. 
 * @param string $format A human-readable number type; see nbdb::cptg_numberCode().
 * @param int $step Number of steps to increment or decrement.
 * @return mixed Integer number or character string.
 */ 
static public function increment( $num, $format = 'decimal', $step = 1 )
{
	// PREPARE, VERIFY
	if( ($format == 'alpha' || $format == 'lower-alpha' || $format == 'upper-alpha') && is_numeric($num) ) {
		if( $num < 1 ) $step = 0;
		$num = self::dec2alpha($num);		
		if( $format == 'upper-alpha' ) $num = strtoupper($num);	
	}
	
	// DECIMAL, ALPHA
	if( $format == 'alpha' || $format == 'decimal' || $format == 'lower-alpha' || $format == 'upper-alpha' ) {
		$t = explode('.',$num);
		if( $step == 1 )
			$t[sizeof($t)-1]++;
		else if( $step == -1 )
			$t[sizeof($t)-1]--;
		return implode('.',$t);
	}
	
	// GREEK
	if( $format == 'lower-greek' || $format == 'upper-greek' || $format == 'greek' ) {
		if( $step == 1 ) $num++;
		else if( $step == -1 ) $num--;

		$greek = array(1=>'alpha',2=>'beta',3=>'gamma',4=>'delta',5=>'epsilon',6=>'zeta',7=>'eta',
				8=>'theta',9=>'iota',10=>'kappa',11=>'lambda',12=>'mu',13=>'nu',14=>'xi',15=>'omicron',
				16=>'pi',17=>'rho',18=>'sigma',19=>'tau',20=>'upsilon',21=>'phi',22=>'chi',23=>'psi',24=>'omega');
				
		if( !is_numeric($num) && ctype_alpha($num) )	
			$num = self::alpha2dec($num);
		else if( !is_numeric($num) ) return;
		
		$alphaint = self::splitInt2Base( $num, sizeof($greek) );
		
		$str = '';	
		foreach( $alphaint as $i ) {
			if( $format == 'upper-greek' ) $greek[$i] = ucfirst($greek[$i]);			
			$str .= '&'. $greek[$i] .';';
		}
		return $str;
	}
	
	// ROMAN
	if( $format == 'lower-roman' || $format == 'upper-roman' || $format == 'roman' ) {
		if( !is_numeric($num) && ctype_alpha($num) ) {
			$n = self::alpha2dec($num);
		} else if( !is_numeric($num) ) return 0;
		
		if( $step == 1 ) $num++;
		if( $step == -1 ) $num--;
		$r = self::dec2roman( $num );
		if( $format == 'lower-roman' ) $r = strtolower($r);
		return $r;
	}
}


/**
 * Filter a name; generates a save name.
 *
 * @param string $name An unsave caption name, as from user input.
 * @return string A save name.
 */
static public function filterName( $name )
{
	return preg_replace("/[^a-zA-Z0-9_]/", "", $name);
}




/**
 * Filter a type; generates a save name.
 *
 * @param string $type An unsave caption type, as from user input.
 * @return string A save caption type.
 */
static public function filterType( $type )
{
	return preg_replace("/[^a-zA-Z0-9_]/", "", $type);
}


/**
 * Filter local attribute.
 *
 * @param string $local Local attribute.
 * @return string Save attribute.
 */
static public function filterLocal( $local )
{
	return $local == 'true';
}


/**
 * Filter a format.
 *
 * @param string $format A string representing a format.
 * @return string A valid and filtered format.
 */
static public function filterFormat( $format )
{
	$f = array( '1'=>'decimal',
		'a'=>'lower-alpha',
		'A'=>'upper-alpha',
		'I'=>'upper-roman',
		'i'=>'lower-roman',
		'alpha'=>'lower-greek');
	if( isset($f[$format])) return $f[$format];
	else if( ($k=array_search($format,$f) !== false) ) return $f[$k];
	else return ''; 
}


/**
 * Check if a given array of attributes is valid for further processing; 
 * It tries to recover the caption type if missing.
 * 
 *
 * @param array $atts An array of attributes.
 * @return boolean TRUE if valid, FALSE if not.
 */
static public function isValidAtts( $atts )
{
	return ( (isset($atts['name']) && isset($atts['type']) 
					&& strlen($atts['name']) > 3 && strlen($atts['type']) > 3 )
			|| (isset($atts['name']) && strlen($atts['name']) > 3 
					&& ($atts['type']=nbdb::cpt_recoverType($atts['name'])) != '' )
			|| (isset($atts['ref']) && isset($atts['type'])
					&&  strlen($atts['ref']) > 3 && strlen($atts['type']) > 3 )
			|| (isset($atts['ref']) &&  strlen($atts['ref']) > 3
					&& ($atts['type']=nbdb::cpt_recoverType($atts['ref'])) != '' )
	);
}


/**
 * Convert an alpha-string to a decimal.
 *
 * @deprecated This method has been replaced with nbCaptionNumber::Alpha2dec() since Netblog 2.0.b6
 * @param string $alpha An alpha-string
 * @return int Decimal of converted alpha-characters; 0 on failure.
 */
static public function alpha2dec( $alpha )
{
	$t = array('a'=>1,'b'=>2,'c'=>3,'d'=>4,'e'=>5,'f'=>6,'g'=>7,'h'=>8,'i'=>9,'j'=>10,'k'=>11,'l'=>12,'m'=>13,'n'=>14,'o'=>15,
			'p'=>16,'q'=>17,'r'=>18,'s'=>19,'t'=>20,'u'=>21,'v'=>22,'w'=>23,'x'=>24,'y'=>25,'z'=>26);
	
	if( !ctype_alpha($alpha) ) return 0;
		
	if( strlen($alpha) == 1 ) {
		return isset($t[$alpha]) ? $t[$alpha] : 0;
	} else {
		$a = str_split($alpha);
		$a = array_reverse($a);
		$num = 0; $b = sizeof($t);
		for($i=0; $i<sizeof($a); $i++ )
			$num += (int) ( pow($b,$i) * $t[$a[$i]] );
		return $num;
	}
}


/**
 * Convert a decimal to an alpha-string.
 *
 * @deprecated This method has been replaced with nbCaptionNumber::Dec2alpha() since Netblog 2.0.b6
 * @param uint $dec An integer to be converted; required $dec>0.
 * @return string An alpha-string from a decimal.
 */
static public function dec2alpha( $dec )
{
	$t = 'a';
	for( $i=1; $i<$dec; $i++ )
		$t++;
	return $t;	
}


/**
 * Convert a decimal to a roman-number.
 *
 * @deprecated This method has been replaced with nbCaptionNumber::Dec2roman() since Netblog 2.0.b6
 * @param int $dec A decimal to be converted; requires $dec>0.
 * @return string A roman number for given decimal.
 */
static public function dec2roman( $dec )
{
    $n = intval($dec);
    $res = '';
 
    $roman_numerals = array(
                'M'  => 1000,
                'CM' => 900,
                'D'  => 500,
                'CD' => 400,
                'C'  => 100,
                'XC' => 90,
                'L'  => 50,
                'XL' => 40,
                'X'  => 10,
                'IX' => 9,
                'V'  => 5,
                'IV' => 4,
                'I'  => 1);
 
    foreach ($roman_numerals as $roman => $number) 
    {
        // DEVIDE TO GET MATCHES
        $matches = intval($n / $number);
 
        // ASSIGN ROMAN CHAR * $matches
        $res .= str_repeat($roman, $matches);
 
        // SUBSTRACT FROM NUMBER
        $n = $n % $number;
    } 
    return $res;	
}


/**
 * Split an integer into an array of integers, with each 0 <= value <= base
 * E.g.: base = 24; 24 => 0.24, 25 => 1.1, 49 => 2.1, 96 => 3.24
 *
 * @deprecated This method has been replaced with nbCaptionNumber::SplitInt2Base() since Netblog 2.0.b6
 * @param int $int An integer with int > 0
 * @param int $base An integer with base > 0
 * @return array An array of integer, wich each 0 <= value <= base
 */
static public function splitInt2Base( $int, $base )
{
	$t = array();
	$i = $int;
	$b = 24; $l = 0; $p = 1;
	while($i>0) {
		$t[] = $i%$b;
		$i = (int) ($i/$b);
	}
	for($i=0; $i<sizeof($t)-1; $i++ )
		if( $t[$i] == 0 ) { 
			$t[$i] += $b; 
			$t[$i+1]--; 
		}
	if( $t[sizeof($t)-1] == 0 ) unset($t[sizeof($t)-1]);
	return array_reverse($t);
}


/**
 * Get default Wordpress Tag caption attributes.
 *
 * @return array An array of attributes, with [attr] => [value].
 */
static public function getAttrDft()
{
	return array(
		'name' => '',
		'type' => '',
		'ref' => '',
		'title' => '',
		'format' => 'decimal',
		'display' => 'inline',
		'local' => 'false',
		'print' => '($number)'
	);
}


/**
 * Reset interal counter and book-keeping.
 * 
 * @return void
 */
static public function reset()
{
	self::$captions = self::$types = array();
	self::$position = 0;
}


/**
 * Check if a given caption exists in current session.
 *
 * @param string $name Caption name.
 * @param string $type Caption type.
 * @return boolean TRUE if exists, FALSE if not.
 */
static private function existsInSession( $name, &$type )
{
	if(isset( self::$captions[$type][$name] ))
		return true;
	if( strlen($name)==0 ) return false;
		
	$i = 0; $lastType = '';
	foreach(self::$captions as $types=>$nms)
		foreach($nms as $nm=>$opts)
			if($nm==$name) { $i++; $lastType = $types; } 
	
	if($i==1) $type = $lastType;
		
	return $i==1;
}


/**
 * Register a new caption in current session and increment numbering.
 *
 * @param array $atts Array of attributes.
 * @return boolean TRUE of registered, FALSE if not.
 */
static private function register( $atts )
{	
	if( !self::existsInSession($atts['name'], $atts['type'])
		 && self::isValidAtts($atts) ) {		 
		 if( !isset(self::$types[ $atts['type'] ]) && !self::registerType($atts) )
		 	return false;
		 extract($atts);
		 
		 self::$captions[$type][$name] = $atts;
		 self::$captions[$type][$name]['num'] = self::increment( self::getNumber($type), $format );
		 self::setNumber( $type, self::getNumber($type)+1 );
		 
		 return true;
	} else return false;		
}


/**
 * Get a caption in current session.
 *
 * @param string $name Caption name.
 * @param string $type Caption type.
 * @param string $output Optional. Any of ARRAY_A | OBJECT.
 * @return mixed An array or object of attributes.
 */
static private function fetch( $name, $type, $output = ARRAY_A ) {
	$c = null;
	if (empty($name)) 
		return null;
		
	// LOOK IN SESSION
	if( self::existsInSession( $name, $type ) && !Netblog::options()->useGlobalCaptions() )
		$c = self::$captions[$type][$name];
	
	// LOOK IN DATABASE
	else if( nbdb::cpt_exists( $name, $type ) )
		$c = nbdb::cpt_get( $name, $type );
	
	// TRY TO RECOVER TYPE AND LOOK IN DATABASE
	else if( ($type=nbdb::cpt_recoverType($name)) != '' )
		$c = nbdb::cpt_get( $name, $type );	
	
	if( $c == null ) {echo '<font color="red">'."[caption '$name' ($type) cannot be resolved]</font>"; return $c; }
	
	if( isset($c['num']) )
		$c['number'] = $c['num'];
	if (empty($c['number']))
		$c['number'] = 1;
	return $output == ARRAY_A ? $c : self::array2obj($c);
}


/**
 * Register first occurence of caption type in current session.
 *
 * @param array $atts An array of attributes.
 * @return boolean TRUE of registered, FALSE if not.
 */
static private function registerType( $atts )
{
	if( self::isValidAtts($atts) && !isset(self::$types[$atts['type']]) ) {
			unset( $atts['name'], $atts['ref'], $atts['title'] );
			self::$types[$atts['type']] = $atts;
			self::$types[$atts['type']]['num'] = 0;
			return true;
		}
	else return false;
}


/**
 * Filter unsave attributes and look for global captions.
 *
 * @param array $atts An array of attributes, like user-input.
 * @return array Filtered attributes.
 */
static private function filterAtts( $atts )
{
	$a = shortcode_atts( self::getAttrDft(), $atts );
	$a['type'] = strtolower($a['type']);
	extract($a);
	$a['name'] = self::filterName($name);
	$a['type'] = self::filterType($type);
	$a['local'] = self::filterLocal($local);
	
	if( nbdb::cptg_isActive($a['type']) ) {
		$c = nbdb::cptg_get($type);
		$a['format'] = $c['number_format'];
		$a['display'] = $c['display'];
		$a['print'] = $c['print_format'];
		$a['local'] = false;	
	}
	
	return $a;
}


/**
 * Get caption type attributes in current session.
 *
 * @param string $type Caption type.
 * @param string $output Optional. Anything of ARRAY_A | OBJECT.
 * @return array An array of attributes; NULL if type not found.
 */
static private function getType( $type, $output = ARRAY_A )
{
	if( $output == ARRAY_A )
		return isset(self::$types[$type]) ? self::$types[$type] : null;
	else if( $output == OBJECT )
		return isset(self::$types[$type]) ? self::array2obj(self::$types[$type]) : null;
	return null;
}


/**
 * Get the last/current numbering of a given caption type in this session.
 *
 * @param string $type A caption type of current session.
 * @return int Current numbering of caption type.
 */
static private function getNumber( $type )
{
	return self::getType($type) != null ? self::getType($type,OBJECT)->num : 0;
}


/**
 * Get caption numbering in current session.
 *
 * @param string $name Caption name.
 * @param string $type Caption type.
 * @return int Caption number; 0 if not found.
 */
static public function getCaptNumber( $name, $type )
{
	$c = self::fetch($name,$type);
	return $c != null ? $c['number'] : -1; 
}


/**
 * Set the numbering of a given caption type in current session.
 *
 * @param string $type A caption type.
 * @param numeric $num Caption numbering.
 * @return boolean TRUE on success, FALSE if type not found.
 */
static private function setNumber( $type, $num )
{
	if( isset(self::$types[$type]) ) {
		self::$types[$type]['num'] = $num;
		return true;
	}
	return false;
}


/**
 * Convert an array to an object.
 *
 * @param array $array An array.
 * @return object An object from given array.
 */
static private function array2obj( $array )
{
    if ( is_array($array) && !empty($array) ) 
    {
        $data = false;
        foreach ($array as $k=>$v )
            $data -> {$k} = $v;
        return $data;
    }
    return false;
}


static private $position = 0;
static public $captions = array();
static public $types = array();

}


//---------------------------------------------------------------------------------------------------------------------
// REGISTER CAPTION - NBCAPTION
//---------------------------------------------------------------------------------------------------------------------
add_shortcode( Netblog::options()->getCaptionShortcode(), 'nbcpt::shortcode');


?>
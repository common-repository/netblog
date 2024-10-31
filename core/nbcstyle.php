<?php

/**
 * A class of common methods for custom citation styles and filter.
 *
 * @author Benjamin Sommer
 * @package netblog
 * @version 1.5
 * @since 1.5
 * @uses Wordpress 2.8+
 */
class nbcstyle {
	
	/**
	 * Add a custom filter for a custom citation style/
	 * 
	 * @uses Netblog::options()->setCiteStyleCustom()
	 * 
	 * @param string $style A custom citation style
	 * @param string $type A custom filter type
	 * @param string $cmd A string with attributes to format the output of the filter, without $ to convert to safe-php-parsable.
	 * @return boolean TRUE on success, FALSE on failure (style is protected, style/type name too long, cmd too short)
	 */
	static public function addFilter( $style, $type, $cmd )
	{
		if( strlen($style) < 3 || strlen($type) < 3 ) return false;
		
		if( self::$styles == null )
			self::$styles = Netblog::options()->getCiteStyleCustom();
		
		$default = self::getDftStyles();		
		if( isset( $default[strtolower($style)] ) ) return false;
		
		$cmdP = self::parseCMD($cmd);	
		if( strlen($cmdP) < 3 ) return false;
		
		if( !isset(self::$styles[$style]) )
			self::$styles[$style] = array();
		
		$save = !isset(self::$styles[$style][$type]['cmdphp']) || !isset(self::$styles[$style][$type]['cmd']) 
				|| self::$styles[$style][$type]['cmdphp'] != $cmdP || self::$styles[$style][$type]['cmd'] != $cmd;
		
		self::$styles[$style][$type]['cmd'] = $cmd;
		self::$styles[$style][$type]['cmdphp'] = $cmdP;

		if($save)
			return Netblog::options()->setCiteStyleCustom(self::$styles);
		return false;
	}
		
	
	/**
	 * Remove a custom filter
	 *
	 * @uses get_option, delete_option
	 * 
	 * @param string $style A custom style
	 * @param string $type A custom filter name to be removed
	 * @return boolean TRUE on success, FALSE on failure (removal failed, type not found)
	 */
	static public function rmFilter( $style, $type )
	{
		if( self::$styles == null )
			self::$styles = Netblog::options()->getCiteStyleCustom();
		
		if( isset(self::$styles[$style][$type]) ) {
			unset( self::$styles[$style][$type] );
			if( sizeof(self::$styles[$style]) == 0 )
				unset( self::$styles[$style]);
			return Netblog::options()->setCiteStyleCustom(self::$styles);
		} else return false;
	}
	
	
	/**
	 * Get a custom filter command, as safe php-parsable code for eval().
	 *
	 * @uses get_option
	 * 
	 * @param string $style A custom style name
	 * @param string $type A custom type name for this style
	 * @param bool $formatted If to return safe php-parsable filter command or the raw command
	 * @return string The safe php-parsable filter command
	 */
	static public function getFilterCMD( $style, $type, $formatted = true )
	{
		if( self::$styles == null )
			self::$styles = Netblog::options()->getCiteStyleCustom();
		
		if(!isset(self::$styles[$style]) || !isset(self::$styles[$style][$type]))
			return '';
			
		if($formatted)
			return isset(self::$styles[$style][$type]['cmdphp']) ? self::$styles[$style][$type]['cmdphp'] : '';
		else return isset(self::$styles[$style][$type]['cmd']) ? self::$styles[$style][$type]['cmd'] : '';
	}
	
	
	/**
	 * Get all filter for a given citation style, with type => cmd.
	 *
	 * @param string $style
	 * @param bool[optional] $formatted TRUE if safe php-parsable, FALSE if raw user input
	 * @return array
	 */
	static public function getFilter( $style, $formatted = false )
	{
		if( self::$styles == null )
			self::$styles = Netblog::options()->getCiteStyleCustom();
		
		$out = array();
		if(isset(self::$styles[$style]) && is_array(self::$styles[$style]))
		foreach(self::$styles[$style] as $type=>$param)
			$out[$type] = $formatted ? $param['cmdphp'] : $param['cmd'];

		return $out;
	}
	
	
	/**
	 * Get custom style names
	 *
	 * @param string $output Any of STRING | ARRAY_N
	 * @return mixed ARRAY_N or a STRING of custom comma-separated style names.
	 */
	static public function getStyles( $output = STRING )
	{
		if( self::$styles == null )
			self::$styles = Netblog::options()->getCiteStyleCustom();

		$out = array();
		
		if( is_array(self::$styles) )
		foreach(self::$styles as $style=>$o) 
			$out[$style] = $style;
			
		if($output == ARRAY_N || $output == ARRAY_A)			
			return $out;
		else
			return implode(', ', $out);

	}
	
	
	/**
	 * Get an array of default/built-in citation styles, with short name => nice name.
	 *
	 * @return array An array of default styles
	 */
	static public function getDftStyles()
	{
		$t = array('apa'=>'APA','turabian'=>'Turabian','mla'=>'MLA','chicago'=>'Chicago');
		
		// MATCH nbcs_*.php
		$d = scandir( dirname(__FILE__) );
		foreach($d as $k=>$f ) {
			if( substr($f,0, 5) == 'nbcs_' && substr($f,-4) == '.php' && !isset( $t[$s=strtolower(substr($f,5,-4))] ) && $s != 'custom' ) {		
				$t[$s] = ucfirst($s);
			}
		}
		return $t;
	}
	
	
	/**
	 * Check if name is a valid style name.
	 *
	 * @param string $name
	 * @param string[optional] $type Get type of stylename
	 * @param string[optional] $classname
	 * @return bool
	 */
	static public function is_style( $name, &$type = null, &$classname = null )
	{
		if( self::isCustomStyle($name) ) {
			if($type!==null) $type = 'custom'; 
			if($classname!==null && file_exists($path=dirname(__FILE__)."/nbcs_custom.php") )
				 $classname = 'nbcs_custom';
			return true;
		}
		if( self::isDftStyle($name) ) {
			if($type!==null) $type = 'default';
			$name = strtolower($name);
			if($classname!==null && file_exists($path=dirname(__FILE__)."/nbcs_$name.php") ) 
				$classname = "nbcs_$name";
				
			return true;
		}
		return false;
	}
	
	
	/**
	 * Get an array of default attributes, with attr => default value
	 *
	 * @return array An array of default attributes
	 */
	static public function getDftAtts()
	{
		return array(
		'author' => '',
		'title' => '',
		'year' => '',
		'city' => '',
		'publisher' => '',
		'publisher_place' => '',
		'pages' => '',	
		'volume' => '',
		'issue' => '',
		'book_author' => '',
		'book_title' => '',
		'title_periodical' => '',
		'initials' => '',
		'month' => '',
		'day' => '',
		'year_access' => '',
		'month_access' => '',
		'day_access' => '',
		'url' => '',
		'doi' => '',
		'conference_name' => '',
		'award' => '',
		'year_organisation' => '',
		'pages_organisation' => '',
		'composer' => '',
		'conductor' => '',
		'performer' => '',
		'writer' => '',
		'theater' => '',
		'director' => '',
		'movie' => '',
		'inventor' => '',
		'patent_number' => '',
		'case_number' => '',
		'court' => '',
		'source' => '',
		'special_entry' => '',
		'special_entry2' => '',
		'webpage' => '',			//=title
		'website' => '',			//=publisher
		'type' => '',
		'print' => '',
		'print_sections' => 'false',
		'print_headline' => '',
		'print_custom' => '',
		'refID' => '',
		'refName' => '',
		'excerpt' => '',
		'hide_inline' => 'false'
      );
	}
	
	
	/**
	 * Get an array of default attributes with their name
	 *
	 * @return array An Array of default attributes, with attr => name
	 */
	static public function getDftAttsNamed()
	{
		return array(
			'author' => __('Author','netblog'),
			'title' => __('Title','netblog'),
			'year' => __('Year','netblog'),
			'city' => __('City','netblog'),
			'publisher' => __('Publisher','netblog'),
			'publisher_place' => __('Publisher Place','netblog'),
			'pages' => __('Pages','netblog'),	
			'volume' => __('Volume','netblog'),
			'issue' => __('Issue or Number','netblog'),			
			'book_author' => __('Book Author','netblog'),
			'book_title' => __('Book Title','netblog'),
			'title_periodical' => __('Title Periodical','netblog'),
			'initials' => __('Author\'s Initials','netblog'),			
			'month' => __('Month','netblog'),
			'day' => __('Day','netblog'),
			'year_access' => __('Year accessed','netblog'),
			'month_access' => __('Month accessed','netblog'),
			'day_access' => __('Day accessed','netblog'),
			'url' => __('URL','netblog'),
			'doi' => __('DOI','netblog'),
			'conference_name' => __('Conference name','netblog'),
			'award' => __('Award, e.g. PhD thesis','netblog'),
			'year_organisation' => __('Books','netblog'),
			'pages_organisation' => __('Books','netblog'),
			'composer' => __('Composer','netblog'),
			'conductor' => __('Conductor','netblog'),
			'performer' => __('Performer','netblog'),
			'writer' => __('Writer','netblog'),
			'theater' => __('Theater','netblog'),
			'director' => __('Director','netblog'),
			'movie' => __('Movie','netblog'),
			'inventor' => __('Inventor','netblog'),
			'patent_number' => __('Patent num.','netblog'),
			'case_number' => __('Case num.','netblog'),
			'court' => __('Court','netblog'),
			'source' => __('Source','netblog'),
			'special_entry' => __('Special Entry 1','netblog'),
			'special_entry2' => __('Special Entry 2','netblog'),
			'webpage' => __('Webpage','netblog'),			//=title
			'website' => __('Website','netblog'),			//=publisher
			'type' => __('Type','netblog'),
			'print' => __('Print','netblog'),
			'print_sections' => __('Print Sections','netblog'),
			'print_headline' => __('Print Headline','netblog'),
			'print_custom' => __('Print Custom','netblog'),
			'refID' => __('Unique Reference ID','netblog'),
			'refName' => __('Unique Reference Name','netblog')
     	 );
	}
	
	
	/**
	 * Get an array of default atts with their help.
	 *
	 * @return array
	 */
	static public function getDftAttsHelp()
	{
		return array(
			'author' => '',
			'title' => '',
			'year' => __('e.g. 2010','netblog'),
			'city' => __('e.g. New York','netblog'),
			'publisher' => __('Publisher','netblog'),
			'publisher_place' => __('Publisher Place','netblog'),
			'pages' => __('Pages','netblog'),	
			'volume' => __('Volume','netblog'),
			'issue' => __('Issue or Number','netblog'),			
			'book_author' => __('Book Author','netblog'),
			'book_title' => __('Book Title','netblog'),
			'title_periodical' => __('Title Periodical','netblog'),
			'initials' => __('Author\'s Initials','netblog'),			
			'month' => __('Month','netblog'),
			'day' => __('Day','netblog'),
			'year_access' => __('Year accessed','netblog'),
			'month_access' => __('Month accessed','netblog'),
			'day_access' => __('Day accessed','netblog'),
			'url' => __('URL','netblog'),
			'doi' => __('DOI','netblog'),
			'conference_name' => __('Conference name','netblog'),
			'award' => __('Award, e.g. PhD thesis','netblog'),
			'year_organisation' => __('Books','netblog'),
			'pages_organisation' => __('Books','netblog'),
			'composer' => __('Composer','netblog'),
			'conductor' => __('Conductor','netblog'),
			'performer' => __('Performer','netblog'),
			'writer' => __('Writer','netblog'),
			'theater' => __('Theater','netblog'),
			'director' => __('Director','netblog'),
			'movie' => __('Movie','netblog'),
			'inventor' => __('Inventor','netblog'),
			'patent_number' => __('Patent num.','netblog'),
			'case_number' => __('Case num.','netblog'),
			'court' => __('Court','netblog'),
			'source' => __('Source','netblog'),
			'special' => __('Special','netblog'),
			'webpage' => __('Webpage','netblog'),			//=title
			'website' => __('Website','netblog'),			//=publisher
			'type' => __('Type','netblog'),
			'print' => __('Print','netblog'),
			'print_sections' => __('Print Sections','netblog'),
			'print_headline' => __('Print Headline','netblog'),
			'print_custom' => __('Print Custom','netblog'),
			'refID' => __('Unique Reference ID','netblog'),
			'refName' => __('Unique Reference Name','netblog')
     	 );
	}
	
	
	/**
	 * Get an array of default types of default citation styles
	 *
	 * @return array Array of default types, with type => name
	 */
	static public function getDftTypes()
	{
		return array(
			'book' => __('Books','netblog'),
			'booksection' => __('Book Sections','netblog'),
			'journal'=> __('Journal Articles','netblog'),
			'magazine' => __('Magazine Articles','netblog'),
			'newspaper' => __('Newspaper Articles','netblog'),
			'encyclopedia' => __('Encyclopedia Articles','netblog'),
			'conference' => __('Conference Proceedings','netblog'),
			'report' => __('Report','netblog'),
			'thesis' => __('Thesis','netblog'),
			'eric' => __('ERIC Document','netblog'),
			'website' => __('Websites','netblog'),
			'wiki' => __('Wiki','netblog'),
			'blog' => __('Blog','netblog'),
			'video' => __('Internet Video','netblog'),
			'powerpoint' => __('PowerPoint Presentation','netblog'),
			'art' => __('Art','netblog'),
			'recording' => __('Sound Recording','netblog'),
			'performance' => __('Performance','netblog'),
			'film' => __('Film','netblog'),
			'patent' => __('Patent','netblog'),
			'standard' => __('Standard (ISOs)','netblog'),
			'map' => __('Map','netblog'),
			'case' => __('Case','netblog')
		);
	}
	
	
	/**
	 * Convert a standard string into a parsable php-string, so that matched valid attributes may be parsed, such as 'author (year)' to '$author ($year)'.
	 *
	 * @uses ctype_alnum
	 * 
	 * @param string $cmd A string with attributes and without $
	 * @return string A safe php-parsable code to be used in eval()
	 */
	static public function parseCMD( $cmd )
	{
		$cmd = str_replace("$",'',$cmd);
		$cmd = strip_tags( stripslashes($cmd) ).' ';		
		
		// VALID VARS
		$dft = self::getDftAtts();
		$parsed = '';
		$hist = '';
		for( $i=0; $i<strlen($cmd); $i++ )
		{
			if( !ctype_alnum($char=$cmd[$i]) && $char != '_' ) {
				$parsed .= (strlen($hist)>0 && isset($dft[$hist]) ? "$$hist" : $hist) . $char;
				$hist = '';
			} else $hist .= $char;
		}
		$cmd = $parsed;	
		
		// VALID FORMAT
		$f = array( '-b-' => 'b', '-i-' => 'i', '-u-' => 'u', '-sub-'=>'sub', '-sup-'=>'-sup', '-c-'=>'lower', '-C-'=>'upper', '-Ca-' =>'cap' );
		foreach( $f as $code=>$tag ){
			$e = explode($code,' '.$cmd.' ');
			if( sizeof($e)%2 == 0 || sizeof($e) < 3 ) { $cmd = implode('',$e); continue; }
			
			for( $i=1; $i<sizeof($e); $i+=2 ) {
				if( $tag == 'lower' ) $e[$i] = "<span style=\"text-transform: lowercase;\">$e[$i]</span>";
				else if( $tag == 'upper' ) $e[$i] = "<span style=\"text-transform: uppercase;\">$e[$i]</span>";
				else if( $tag == 'cap' ) $e[$i] = "<span style=\"text-transform: capitalize;\">$e[$i]</span>";
				else if( $tag == 'u' ) $e[$i] = "<span style=\"text-decoration:underline\">$e[$i]</span>";
				else $e[$i] = "<$tag>$e[$i]</$tag>";
			}
			$cmd = implode('', $e); 
		}
		
		return addslashes($cmd);		
	}
	
	/**
	 * Get Format html codes for custom filter
	 *
	 * @return array With [input_encoding]=>array(html_tag_begin,html_tag_end)
	 */
	static public function getFormatHTMLCodes() {
		return array( '-b-' => array('<b>','</b>'), 
					'-i-' => array('<i>','</i>'), 
					'-u-' => array('<span style="text-decoration: underline;">','</span>'), 
					'-sub-'=> array('<sub>','</sub>'), 
					'-sup-'=> array('<sup>','</sup>'), 
					'-c-'=> array('<span style="text-transform: lowercase;">','</span>'), 
					'-C-'=> array('<span style="text-transform: uppercase;">','</span>'), 
					'-Ca-' => array('<span style="text-transform: capitalize;">','</span>') );
	}
	
	
	/**
	 * Get Format html codes for custom filters, with their nice names.
	 *
	 * @return array With [input_encoding]=>[nice_name]
	 */
	static public function getFormatHTMLCodesNamed() {
		return array( '-b-' => 'Bold', '-i-' => 'Italics', '-u-' => 'Underline', '-sub-'=>'Sub', '-sup-'=>'Sup',
				 '-c-'=>'Lower-Case', '-C-'=>'Upper-Case', '-Ca-' =>'Capitalize' );
	}
	
	/**
	 * Preview php-safe filter command
	 *
	 * @param string $parsedCMD
	 * @return mixed STRING | FALSE
	 */
	static public function previewFilter( $parsedCMD )
	{
		if( !is_string($parsedCMD) )
			return false;
			
		$dft = self::getDftAtts();
		foreach( $dft as $k=>$v )
			$dft[$k] = strtoupper($k);//ucfirst($k);
		extract($dft);
		
		if(strpos($parsedCMD,"\'") === false || strpos($parsedCMD,'\"') === false)
			$parsedCMD = addslashes($parsedCMD);
		$r = eval("return \"$parsedCMD\" ;");
		if(is_string($r))
			return stripslashes($r);
		return false;
	}
	

	/**
	 * Checks if a name is a custom citation style.
	 *
	 * @param string $name The name which might be a custom citation style
	 * @return boolean TRUE if name is a valid custom citation style, FALSE otherwise
	 */
	static public function isCustomStyle( $name )
	{
		if( self::$styles == null )
			self::$styles = Netblog::options()->getCiteStyleCustom();
			
		return is_array(self::$styles) && sizeof(self::$styles)>0 && isset(self::$styles[$name]);		
	}

	
	/**
	 * Checks if a name is a default citation style.
	 *
	 * @param string $name The name which might be a default citation style
	 * @return boolean TRUE if name is a valid default citation style, FALSE otherwise
	 */
	static public function isDftStyle( $name )
	{
		$d = self::getDftStyles();
		return isset($d[strtolower($name)]);
	}
	
	
	static private $styles = null;	
}


?>
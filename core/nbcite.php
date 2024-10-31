<?php

//---------------------------------------------------------------------------------------------------------------------
// RUN AND PARSE NBNOTE
//---------------------------------------------------------------------------------------------------------------------

/**
 * A class for handling WP shortcodes for Netblog citations
 *
 */
class nbcite
{
	/**
	 * Initialize Netblog citation shortcode handler
	 *
	 */
	static public function init()
	{
		if( strlen(Netblog::options()->getBibHeadline()) > 0 )
			self::$defaultHeadline = Netblog::options()->getBibHeadline();
		
		if( (int)Netblog::options()->getBibMaxNum() > 0 )
			self::$maxBibliosPerPost = (int)Netblog::options()->getBibMaxNum();
	}
	
	/**
	 * WP shortcode handler for citations
	 *
	 * @param array $atts
	 * @param string[optional] $content
	 * @return string The filtered string
	 */
	static public function shortcode( $atts, $content = null )
	{
		require_once 'nbcstyle.php';
		require_once 'nbcs_apa.php';
		
		$a = array();
		$dft = nbcstyle::getDftAtts();
		foreach($dft as $k=>$v) 
			if(isset($atts[$k]))
				$a[$k] = $atts[$k];
			else if(isset($atts[strtolower($k)]))
				$a[$k] = $atts[strtolower($k)];
			else if(isset($atts[strtoupper($k)]))
				$a[$k] = $atts[strtoupper($k)];
			else $a[$k] = $v;
			 
		$a['type'] = strtolower($a['type']);
			
		try {
		
		// REGISTER REFERENCE AND TRIGGER INLINE PRINT
		if( $a['print'] == '' ) {
			if( !self::hasCiteWPL() || is_null(self::getCitesWPL()) || !is_object(self::getCitesWPL()) )
				self::setCiteWPL( new nbcs_apa() );		// general type - will be cast later
			self::getCitesWPL()->add($a);
			return self::getCitesWPL()->printInline($a);
			
		// TRIGGER TABLE OF BIBLIOGRAPHY PRINT
		} else if( self::$count[get_the_ID()] < self::$maxBibliosPerPost && self::hasCiteWPL() ) {
			if( strlen($a['print']) == 0 || $a['print'] == 'default' || Netblog::options()->getCiteStyleOverride() ) {
				$a['print'] = Netblog::options()->getCiteStyle();				
				$a['print_headline'] = Netblog::options()->getBibHeadline();			
			}

			$obj;			
			$style = strtolower($a['print']);
			if( nbcstyle::isCustomStyle($style) ) {
				require_once 'nbcs_custom.php';
				$obj = Netblog::castObj(self::getCitesWPL(),"nbcs_custom");
				if( !is_object($obj) ) { 
					if( is_object($o_=self::getCitesWPL()) )
						$src_style = Netblog::getObjClassname($o_);
					else $src_style = serialize($o_);
					Netblog::log("Failed to cast from $src_style to nbcs_custom"); 
					return ''; 
				}
				$obj->setStyle( $style );
			} else if( nbcstyle::isDftStyle($style) ) {
				require_once "nbcs_$style.php";
				$obj = Netblog::castObj(self::getCitesWPL(),"nbcs_$style");
				if( !is_object($obj) ) { 
					if( is_object($o_=self::getCitesWPL()) )
						$src_style = Netblog::getObjClassname($o_);
					else $src_style = serialize($o_);
					Netblog::log("Failed to cast from $src_style to nbcs_$style");
					return ''; 
				}
			} else return '';
			
			$print_headline = strlen($a['print_headline']) > Netblog::options()->getBibHeadlineMinlen() ? $a['print_headline'] : self::$defaultHeadline;
			$print_headline = Netblog::cstrip($print_headline, Netblog::options()->getBibHeadlineMaxLen(), Netblog::options()->getBibHeadlineAppendOnExceed() );

			self::$count[get_the_ID()]++;
			
			return is_Object($obj) ? '<!-- BEGIN TABLE OF BIBLIOGRAPHY -->'.$obj->printBiblio( $print_headline, false ).
				'<!-- END TABLE OF BIBLIOGRAPHY -->' : '';
		}
		
		} catch(Exception $e) {
			return '';
		}
	}
	
	
	/**
	 * Get Bibliography headline
	 *
	 * @return string
	 */
	static public function getHeadline() { return Netblog::options()->getBibHeadline(); }
	
	
	/**
	 * Get the maximum number of allowed bibliographies per article
	 *
	 * @return int
	 */
	static public function getBibsPerPost() { return (int)Netblog::options()->getBibMaxNum(); }
	
	/**
	 * Get citations for given post, using WP Loop.
	 *
	 * @return nbcs
	 */
	static private function getCitesWPL() { return self::$cites[get_the_ID()]; }
	
	/**
	 * Save cite for a post.
	 *
	 * @param nbcs $cite
	 */
	static private function setCiteWPL( $cite ) { self::$cites[get_the_ID()] = $cite; }
	
	
	/**
	 * Check if cite exists.
	 *
	 * @return bool
	 */
	static public function hasCiteWPL() { return isset(self::$cites[get_the_ID()]); }
	
	/**
	 * Check if table of bibliographies has been generated.
	 *
	 * @return bool
	 */
	static public function hasTableWPL() { return self::$count[get_the_ID()] > 0; }
	
	/*
	 * PRIVATE DATA
	 */
	
	/**
	 * Citation handler
	 *
	 * @var nbcs
	 */
	static private $cites = array();
	static private $count = array();
	static private $maxBibliosPerPost = 5;
	static private $defaultHeadline = 'References';
}
nbcite::init();


//---------------------------------------------------------------------------------------------------------------------
// REGISTER CITATION - NBCITE
//---------------------------------------------------------------------------------------------------------------------
add_shortcode( Netblog::options()->getCiteShortcode(), 'nbcite::shortcode');

?>
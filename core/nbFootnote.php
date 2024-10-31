<?php

//---------------------------------------------------------------------------------------------------------------------
// CLASS nbnote
//---------------------------------------------------------------------------------------------------------------------

/**
 * Netblog Footnote handler
 *
 */
class nbnote
{
	/**
	 * Initialize footnote handler
	 *
	 */
	static public function init()
	{
		self::$format = Netblog::options()->getNoteFormat();
	}
	
	
	/**
	 * WP shortcode handler
	 *
	 * @param array $atts
	 * @param string[optional] $content
	 * @return string
	 */
	static public function shortcode( $atts, $content = null )
	{
		extract( shortcode_atts( array('print' => false), $atts ) );	
	
	    if( $print == 'true' || $print == 'default' ) $print = true;
	    else $print = false;    
	            
	    $scope = 'footnote';
	    
		if( !$print ) {
			if( $content == null || strlen($content) == 0 ) return;
	
			if(!isset(self::$notes[get_the_ID()]))
				self::$notes[get_the_ID()] = array();
			
			$noteid = sizeof(self::$notes[get_the_ID()]); 
			$noteNum = nbcpt::increment($noteid++,self::$format);		
			self::$notes[get_the_ID()][$noteid] = $content;	
			
			$link = '<sup><a href="#'.$scope.'-dsp-'.get_the_ID().'.'.$noteid.'" name="'.$scope.'-'.get_the_ID().'.'.$noteid.'" 
							style="text-decoration:none">['.$noteNum.']</a></sup>';
			return $link;
		} else {
			if(!isset(self::$notes[get_the_ID()]) || !is_array(self::$notes[get_the_ID()]) || sizeof(self::$notes[get_the_ID()]) == 0)
				return "";

			$element_style = Netblog::options()->getFootnoteCssFormatting();
			$hrule_above = $hrule_below = false;
			switch(Netblog::options()->getFootnoteHorizontalRule()) {
				case 'above': $hrule_above = true; break;
				case 'below': $hrule_below = true; break;
				case 'above_below': $hrule_above = $hrule_below = true; break;
			}
			$hrule_code = '<hr style="padding-bottom:0px;margin-bottom:0px">';
			
			$o = '<!-- BEGIN TABLE OF FOOTNOTES -->';
			if($hrule_above) 
				$o.= $hrule_code;
			$o .= '<ol style="list-style-type: '.self::$format.'; '.($hrule_below?'padding-bottom: 0; margin-bottom: 0':'').'">';
			foreach( self::$notes[get_the_ID()] as $id => $ct ) 
				$o .= '<li style="'.$element_style.'"><a name="'.$scope.'-dsp-'.get_the_ID().'.'.$id.'"></a>'.trim(do_shortcode($ct)).' 
							<a href="#'.$scope.'-'.get_the_ID().'.'.$id.'"  style="text-decoration:none;font-weight:bold">^</a></li>';		
			$o .= '</ol>';
			if($hrule_below)
				$o.= $hrule_code;
			$o .= '<!-- END TABLE OF FOOTNOTES -->';
			self::setTablePost(get_the_ID());			
			return $o;
		}   
	}
	
	/**
	 * Check wether a table of footnotes has been created for a given post id.
	 *
	 * @param int $postid
	 * @return bool
	 */
	static public function postHasTable( $postid ) { return isset(self::$hasTablesOTF[$postid]); }
	
	/**
	 * Check wether a table of footnotes has been created for a post id, while bein in WP loop.
	 *
	 * @return bool
	 */
	static public function postHasTableWPLoop() { return self::postHasTable(get_the_ID()); }
	
	
	/**
	 * Save that a table of footnotes has been created for a given post id.
	 *
	 * @param int $postid
	 */
	static private function setTablePost( $postid ) { self::$hasTablesOTF[$postid] = true; }
	
	/**
	 * Get display options for horizontal rules
	 * @return array[key=>name]
	 */
	static function GetHorzRulesOpts() {
		return array(	'none'=>__('None','netblog'),
						'above'=>__('Above','netblog'),
						'below'=>__('Below','netblog'),
						'above_below'=>__('Above & Below','netblog') );
	}
	
	/*
	 * PRIVATE DATA
	 */
	static private $notes = array();
	static private $format = 'decimal';
	static private $numcur = 0;
	static private $hasTablesOTF = array();					/* for which articles table of footnotes has been generated on the fly */
}
nbnote::init();


//---------------------------------------------------------------------------------------------------------------------
// REGISTER FOOTNOTE - NBNOTE
//--------------------------------------------------------------------------------------------------------------------
add_shortcode( Netblog::options()->getNoteShortcode(), 'nbnote::shortcode');


?>
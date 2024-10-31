<?php

/* THIS FEATURE IS UNDER DEVELOPMENT FOR FUTURE VERSIONS */

/**
 * Netblog Code (programming code) handler, incl syntax highlighter
 *
 */
class nbcode
{
	
	/**
	 * Wordpress do_shortcode function to process nbcode-tags.
	 *
	 * @param array $atts Tag attributes.
	 * @param string $content Inner-tag content.
	 */
	static public function shortcode( $atts, $ct = null )
	{
		$a = shortcode_atts( self::attsDft(), $atts );
		extract($a);
		$ct = trim($ct);
		$out = '';
		
		// PREPARE
		if( strlen($file) == 0 && substr($ct,0,5) == 'file:' )
			$file = substr($ct, 5, ($e=strpos($ct,'|') ) !== false ? $e-5 : strlen($ct)-5 );
	
		if( strlen($file) > 0 ) 
			$ct = @file_get_contents($file);
		
		
		// PARSE CONTENT
		$ctF = nl2br($ct);
//var_dump( htmlspecialchars($ctF) );
		$ctF = str_ireplace('<p>','<br /><br />',$ctF);
		$ctF = str_ireplace('</p>','',$ctF);
		$ctFA = self::explode( array('<br>','<br />','<br >','\n','\r\n'), $ctF );
		
		$t = '{}();<>/*\\';
		
//var_dump($ctFA);	

		// CLEAR WHITE-LINES AT TOP
		for( $i=0; $i<sizeof($ctFA); $i++ ) {
			$line = $ctFA[$i];
			if( $line[0] != '' && !ctype_alnum($line[0]) && strpos($t,$line[0]) === false ) $line = substr($line,1);
			if( $line[0] != '' && !ctype_alnum($line[0]) && strpos($t,$line[0]) === false ) $line = substr($line,1);
			if( strlen($line) == 0 )
				unset($ctFA[$i]);
			else break;
		}
		
		// CLEAR WHITE-LINES AT BOTTOM
		for( $i=sizeof($ctFA); $i>=0; $i-- ) {
			$line = $ctFA[$i];
			if( $line[0] != '' && !ctype_alnum($line[0]) && strpos($t,$line[0]) === false ) $line = substr($line,1);
			if( $line[0] != '' && !ctype_alnum($line[0]) && strpos($t,$line[0]) === false ) $line = substr($line,1);
			if( strlen($line) == 0 )
				unset($ctFA[$i]);
			else break;
		}	

		// GET FORMATTING
		$keywords = self::keywords();
		$inComment = false;
		
		// MAKE OUTPUT
		foreach( $ctFA as $k=>$line ) {
			if( $line[0] != '' && !ctype_alnum($line[0]) && strpos($t,$line[0]) === false ) $line = substr($line,1);
			if( $line[0] != '' && !ctype_alnum($line[0]) && strpos($t,$line[0]) === false ) $line = substr($line,1);
			
//			echo "---$k---$line[0]";
//			var_dump( $line[0] != '' );
			//$out .= '<li'.($k%2==0 ? ' class="even"' : ' class="odd"').'><div class="code">'.htmlspecialchars($line).'</div></li>';
			
			$line = htmlspecialchars($line);
			
			if( $inComment && ($c=strpos($line,'*/')) !== false ) {
				$line = '<div class="comment">' . substr($line,0,$c+2) . '</div>' . substr($line,$c+2);
				$inComment = false;
			} else if( $inComment ) {
				$line = "<div class=\"comment\">$line</div>";
			} else if( ($c=strpos($line,'/*')) !== false ) {				
				if( ($e=strpos($line,'*/')) !== false )
					$ee = $e - $c + 2;
				else $ee = strlen($line) - $c;
				
				$line = substr($line,0,$c) . '<div class="comment">' . substr($line,$c,$ee) . '</div>' . substr($line,$c+$ee);
				$inComment = $e === false;
			}
			
			if( !$inComment && ($c=strpos($line,'//')) !== false ) {
				$line = substr($line,0,$c) . '<div class="comment">' . substr($line,$c) . '</div>';
			}
//			$count = 0;
//			$line = str_replace('/*','<div class="comment">/*',$line,$count);
//			if( $count > 0 ) $line .= '</div>';

			if( !$inComment && strpos($line,'class=') === false ) {
				$line =  preg_replace("/($keywords)/i",'<div class="keywords">$1</div>',$line);
				//$line =  preg_replace("/(\/\*.*?\*\/)/i",'<div class="comment">$1</div>',$line);
			}
			
			$out .= '<div class="left">'.($k+1).'</div><div class="line">'.$line.' </div><div class="clear"></div>';
		}
		
		
		
		$out = "<div class=\"nbcode\">
					<div class=\"subheader\">".basename($file)."</div>
					<div class=\"header\">$header</div>
					<div style=\"clear:both; float:none\"></div>
					<div class=\"body\">$out</div>
					
					<div class=\"subheader\">Download Source | Toogle Code</div>
					<div class=\"header\">&nbsp;</div>
					<div style=\"clear:both; float:none\"></div>
					
				</div>";
		
//die();	<ol>$out</ol>	
		return $out;
	}
	
	
	/**
	 * Get the default, valid tag attributes.
	 *
	 * @return array An array with attr => name.
	 */
	static public function attsDft()
	{
		return array( 
			'file' => '',
			//'attachement' => '',
			'header' => __('Code','netblog'),
			'style' => 'default'
		);
	}
	
	
	/**
	 * Get the named valid tag attributes.
	 *
	 * @return array An array with attr => name.
	 */
	static public function attsNamed()
	{
		return array( 
			'file' => __('File','netblog'),
			//'attachement' => __('Attachment ID','netblog'),
			'header' => __('Header','netblog'),
			'style' => __('Display Style','netblog')
		);		
	}
	
	
	
	/**
	 * Explodes a string from an array of delimiters.
	 *
	 * @param array $delimiterArray An array of delimiters.
	 * @param string $string A string to be exploded.
	 * @return array The exploded string.
	 */
	static public function explode( $delimiterArray, $string )
	{
		$in = (array) $string;
		
		foreach( $delimiterArray as $sep ) {
			$t = array();
			foreach( $in as $str )
				$t = array_merge($t, explode($sep,$str));
			$in = $t;
		}
		
		return $in;
	}
	
	
	static public function replace( $searches, $before, $after, $string )
	{
		$patterns = array(); $replacements = array();
		foreach( $searches as $v ) {
			$patterns[] = "/$v/";
			$replacements[] = "/$before $after";			 
		}
		
	}
	
	
	/**
	 * Get a | seperated string of system-keywords for formatting.
	 *
	 * @return string.
	 */
	static public function keywords()
	{
		return 'static|public|function|class|abstract|return|exit|array|'.
			'for|foreach|if|else|self|true|false|unset|unlink|final|empty|isset|break|switch|case|continue';
	}
	
}


//---------------------------------------------------------------------------------------------------------------------
// REGISTER CAPTION - NBCODE
//---------------------------------------------------------------------------------------------------------------------
add_shortcode( Netblog::options()->getCodeShortcode(), 'nbcode::shortcode');

?>
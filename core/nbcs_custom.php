<?php
require_once 'nbcs.php';

class nbcs_custom extends nbcs {
	
//---------------------------------------------------------------------------------------------------------------------
// MEMBER FUNCTIONS
//---------------------------------------------------------------------------------------------------------------------	

	public function printBiblio( $headline = null, $sections = false )
	{
		require_once 'nbcstyle.php';
		
		$o = array();

		foreach( $this->list as $id=>$atts ) {
			extract($atts);
			$print_custom = nbcstyle::getFilterCMD( $this->style, $type );
			if( strlen($print_custom) > 0 )
				$o[$type][$id] = eval("return \"$print_custom\" ;");
		}

		return $this->bprint( $headline, $o, $sections, 'h1', '', '<div style="height:50px"></div>' );
	}
	
	
	public function reqAtts( $type = null )
	{
		require_once 'nbcstyle.php';
		
		if( $type === null ) {			
			$f = nbcstyle::getFilter( $this->style );
			if(is_array($f)) 
				foreach($f as &$cmd)
					$cmd = $this->parse_reqAtts($cmd);
					
			return sizeof($f)>0 ? $f : '';
		} else {
			$f = nbcstyle::getFilterCMD( $this->style, $type );
			return $this->parse_reqAtts($f);
		}
	}
	
	/**
	 * Set a custom citation style name
	 *
	 * @param string $name A custom citation style name
	 * @return void
	 */
	public function setStyle( $name )
	{
		$this->style = $name;
	}
	
	
	/**
	 * Parse a php-parsable command into human-readable, encoded for reqAtts-format
	 *
	 * @param string $cmd
	 * @return string
	 */
	private function parse_reqAtts( $cmd )
	{
		$att = false; $o = '';
		for( $i=0; $i<strlen($cmd); $i++ )
			if( ($c=$cmd[$i]) == '$' && isset($cmd[$i+1]) && ctype_alpha($cmd[$i+1]) ) {
				if(strlen($o)>0)$o.=','; $att = true;
			} else if( $att && (ctype_alnum($c) || $c=='_') ) $o.=$c;
			else $att = false;
		return $o;
	}
	
	
//---------------------------------------------------------------------------------------------------------------------
// DATA MEMBER
//---------------------------------------------------------------------------------------------------------------------

	/**
	 * A custom citation style name to be used for bibliography
	 *
	 * @var string
	 */
	protected $style = '';
	
}
?>
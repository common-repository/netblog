<?php
require_once 'nbBibliographyReference.php';

/**
 * Base class for Netblog citations
 *
 */
abstract class nbcs {
	
//---------------------------------------------------------------------------------------------------------------------
// MEMBER FUNCTIONS
//---------------------------------------------------------------------------------------------------------------------	
	
	/**
	 * Adds a citation
	 *
	 * @param array $atts Array of attributes
	 * @return void
	 */
	final public function add( &$atts ) {
		//$this->filterAttributes($atts);
		
		$reqs = $this->getAttributes($atts['type']);
		foreach($reqs as $k=>$v) {
			if(strpos($v,'optional')!==false) continue;
			if(!isset($atts[$k]) || strlen($atts[$k])==0)
				$atts[$k] = '<font color="red">[NO '.(strtoupper($k)).']</font>';
		}
		$id = $this->mkid($atts);
		if( !isset($this->list[$id]) ) {
			$this->list[$id] = $atts;
			$this->iprints[$id] = 0;
			$this->numericIds[$id] = sizeof($this->list);
			$this->lastAddedID = $id;
		}
	}
	
	final protected function filterAttributes(&$atts) {
		$attsDb = array();

		if( (isset($atts['refID']) && !empty($atts['refID'])) || (isset($atts['refName']) && !empty($atts['refName'])) ) {			
			$ref = new nbBibliographyReference( isset($atts['refID'])?$atts['refID']:0 );
			$ref->name = isset($atts['refName'])?$atts['refName']:'';
			if($ref->load())
				$attsDb = $this->getAttributesFromReference($ref);
			else {
				$postid = get_the_ID();
				$wppost = get_post($postid); 
				$msg = 'Failed to load Reference (id:'.$ref->getID().',name:'.$ref->name.') on page "'.get_permalink($postid).'" (id:'.$postid.')';
				Netblog::logError($msg);
				echo " <font color=\"red\">$msg</font> ";
			}
		}
		
		// apply reference attributes loaded from database
		foreach($attsDb as $k=>$v) 
			$atts[$k] = $v;
		
		// clear internal ids in from of values
		foreach($atts as $k=>$v) {
			if(substr($v,0,2)=='__' && ctype_digit( substr($v,2,($p=strpos($v,':'))-2) ))
				$atts[$k] = substr($v,$p+1);
		}
	}
	
	/**
	 * Adds a reference to this citation object
	 *
	 * @param nbBibliographyReference $ref
	 */
	final public function addReference($ref) {
		$atts = $this->getAttributesFromReference($ref);		
		$this->add($atts);
	}
	
	/**
	 * Get attributes from bibliographic reference
	 *
	 * @param nbBibliographyReference $ref
	 * @return array
	 */
	final protected function getAttributesFromReference($ref) {
		$atts = array();
		$atts['type'] = strtolower($ref->getConstName($ref->typeID));
		$atts['print_custom'] = $ref->printFormatInline;
		$atts['excerpt'] = $ref->GetExcerpt();
		$atts['hide_inline'] = $ref->hideInlineCitation?'true':'false';
		foreach($ref->fields as $f) {
			if($f->fieldID > 0 && strlen($k=$f->getConstName($f->fieldID))>0 )
				$atts[strtolower($k)] = $f->fieldValue;
		}
		return $atts;
	}
	
	
	/**
	 * Prints the given citation inline, such as right within a paragraph.
	 *
	 * @param array $atts Array of attributes
	 * @return string A string of the formatted inline citation (on success), a formatted message (on error) or an empty string (citation not found)
	 */
	public function printInline( $atts = array() ) {
		if(!is_array($atts) || (sizeof($atts)==0 && strlen($this->lastAddedID)==0))
			return '';
		if( sizeof($atts)==0 )
			$atts = $this->list[$this->lastAddedID];
			
		$id = $this->mkid($atts);
		if( !isset($this->list[$id]) ) return '';
		
		$nums = $this->iprints[$id];
		$nid = $this->numericIds[$id];
		
		extract($atts);
		$out = '';
			
		if( $author == '' && $title == '' )
			return '<font style="color:red">'.__('Invalid Citation: missing author and/or title','netblog').'</font>';
		
		if(isset($hide_inline) && $hide_inline=='true')
			return;
		
		if( ($citformat=Netblog::options()->getCiteFormatOutput()) != 'literal') {
			if( strpos($citout=Netblog::options()->getCiteFormatCustomOutput(), '<output>')===false )
				$citout = '(<output>)';
			$citout = str_ireplace('<output>', nbCaptionNumber::Increment($nid,$citformat,0), $citout);
			$citcss = Netblog::options()->getCiteFormatStyle();
			return "<a name=\"cite-$nid\"></a><a href=\"#ref-$nid\" style=\"$citcss\">$citout</a>";
		} else {
			if( $author!='' ) {
				$t = explode(',', $author);
				if( sizeof($t) > 5 || (sizeof($t) > 2 && $nums > 0) )
					$author = $t[0].' et al.';
				$out = "$author";
			} else if( $title!='' ) $out = "\"$title\""; 
			
			if( $year!='' ) $out .= ", $year";
			else $out .= ", n.d.";						// no date
			
			if( $pages!='' ) $out .= ", $pages";
			
			$out = "($out)";
			
			// CUSTOM OUTPUT
			$outC = '';		
			if( $print_custom!='' && $outC=eval("return \"$print_custom\" ;") )
				$out = $outC;

			return "<a name=\"cite-$nid\"></a><i>$out</i>";
		}
	}
	
	
	/**
	 * Makes the list of bibliography.
	 *
	 * @param string $headline The headline for the bibliography
	 * @param boolean $sections Whether to print sections
	 * @return string
	 */
	abstract public function printBiblio( $headline = null, $sections = false );
	
	
	/**
	 * Get an array of attributes used by a type and their properties, such as optional or formatting 
	 *
	 * @param string $type A type of a resource, such as book, magazine, journal.
	 * @return array An array of attributes and their properties
	 */
	final public function getAttributes( $type )
	{
		if($type===null) {
			$o = array();
			foreach( $this->reqAtts(null) as $type=>$rsc )
				$o[$type] = $this->getAttributes($rsc);
			return $o;
		}
		
		$atts = $this->reqAtts($type);
		$a = explode(',', $atts );
		$o = array();
		foreach( $a as $k ) {
			$t = explode('|',$k);
			$k = $t[0];
			unset($t[0]);
			$v = implode('|',$t);
			$o[$k] = $v;
		}
		return sizeof($o)>0 ? $o : null;
	}
	
	
	/**
	 * Get a string of attributes required/used for a resource type. If type is NULL, an array for all types is returned.
	 *
	 * @param stirng $type
	 * @return string|array A string of attributes used for a type | array of all types required
	 */
	abstract public function reqAtts( $type = null );
	
	
	/**
	 * Makes id from array of attributes
	 *
	 * @param array $att Array of attributes
	 * @return string
	 */
	final private function mkid( $att ) {
		if (!empty($att['refID']))
			return md5($att['refID']);
		if (!empty($att['refName']))
			return md5($att['refName']);
		return md5($att['author'].$att['title'].$att['year'].$att['month'].$att['day'].$att['pages']); 
	}
	
	
	/**
	 * Generates a list of bibliography
	 *
	 * @param string $headline The headline for the bibliography, e.g. References.
	 * @param array $outputf A two dimensional array of the format $outputf[ $types ][].
	 * @param boolean $sections Whether to split the list into sections for each type of resource, such as for books, journals etc.
	 * @param string $title Tag The name of a valid XHTML-Tag, such as h1, h2, h3 etc. Has no effect if sections == true
	 * @param string $before A string literal or valid XHTML code to display BEFORE the bilbiography
	 * @param string $after A string literal or valid XHTML code to display AFTER the bilbiography
	 * @param string $rowF A valid string in CSS-Format to format each row/entry, such as hanging etc.
	 * @return string
	 */
	final protected function bprint( $headline, &$outputf, $sections = false, $titleTag = 'h3', $before = '', $after = '', $rowF = 'text-indent: -25px; padding-left: 25px; line-height: 200%' )
	{
		if( sizeof( $outputf ) == 0 ) return '';
		
		if(strlen($titleTag)==0) $titleTag = Netblog::options()->getBibHeadlineHtmlTag();
	
		if( $sections ) {
			$out = "<h2>$headline</h2>";
			ksort($outputf);
			foreach( $outputf as $type=>$cites )
				$out .= $this->bprint($type,$cites,false,'h2');
			return "<div class=\"biblio\">$before$out$after</div>";
		} else {
			$out = '';
			if($headline!=null && strlen($titleTag)>0)
				$out .= "<$titleTag>$headline</$titleTag>";
			
			// MERGE
			$all = array(); $depthMin = 2;
			foreach( $outputf as $cites )
				if( is_array($cites) )
					$all = array_merge($all,$cites);
				else { $all[] = $cites; $depthMin = 1; }
			
			// PREPARE KEYS FOR CORRECT SORT
			$cites = array();
			foreach($all as $k=>$c )
				$cites[$k] = strip_tags($c,'<i><b><u>');
			if( !$numberCits=(($citformat=Netblog::options()->getCiteFormatOutput())!='literal') )
				asort($cites);
			
			// PRINT LIST
			$element_style = Netblog::options()->getBibCssFormatting();
			$pr_list = $list_pre = $list_post = '';
			if($numberCits) {
				$list_pre = '<ol style="list-style-type: '.$citformat.'">';
				$list_post = '</ol>';
			}			
			foreach($cites as $id=>$c ) {
				$atts = $this->list[$id];
				$a_cite = $atts['hide_inline']!='true'?'<a href="#cite-'.$this->numericIds[$id].'"  style="text-decoration:none;font-weight:bold">^</a>':'';
				$a_ref = '<a name="ref-'.$this->numericIds[$id].'"></a>';
				$excerpt = '';
				if(is_string($atts['excerpt']) && strlen($atts['excerpt'])>0) {
					$excerpt = ' <a onclick="document.getElementById(\'nbbib-excerpt-'.$id.'\').style.display=\'block\'" style="cursor:pointer;text-decoration:none;font-weight:bold">Excerpt</a> '.
							'<div style="display:none; padding-bottom: 10px" id="nbbib-excerpt-'.$id.'"><strong>Excerpt:</strong> '.$atts['excerpt'].
							'<a onclick="document.getElementById(\'nbbib-excerpt-'.$id.'\').style.display=\'none\'" style="cursor:pointer;text-decoration:none;">Hide</a></div>';
				}
				if($numberCits)
					$pr_list .= '<li style="'.$element_style.'">'.$a_ref.$c.$a_cite.$excerpt.'</li>';
				else 
					$pr_list .= '<div style="'.$rowF.'; '.$element_style.'">'.$a_ref.$c.$a_cite.$excerpt.'</div>';	
			}
			$out .= $list_pre.$pr_list.$list_post;
			
			if($headline!=null && strlen($titleTag)>0)
				$out = $before.$out.$after;
			
			if( $depthMin > 1 )
				$out = "<div class=\"biblio\">$out</div>";
			
			$this->numBiblios++;
				
			return $out;
		}
	}
	
	/**
	 * Load an existing module
	 *
	 * @param string $styleName
	 * @return nbcs
	 */
	static public function loadModule($styleName) {
		$classname = '';
		if( nbcstyle::is_style($styleName,$type,$classname) ) {
			require_once 'nbcs_apa.php';
			require_once "$classname.php";
				
			$nbcs = new nbcs_apa();		
			$nbcs = Netblog::castObj($nbcs,$classname);
			if($nbcs===false) die();
			
			if($classname == 'nbcs_custom')
				$nbcs->setStyle($styleName);
			return $nbcs;
		} else {
			echo 'not found';
			return null;
		}
	}
	
	static public function getDefaultStyles() {
		return array('apa','chicago','harvard','mla','turabian');
	}
	
	static public function getSystemStyles() {
		return array('apa','chicago','harvard','mla','turabian','custom');
	}
	
	static public function getCustomStyles() {
		$l = scandir(dirname(__FILE__));
		$e = self::getDefaultStyles();
		$e[] = 'custom';
		$st = array();
		foreach ($l as $f) {
			if (strpos($f, 'nbcs_')!==false) {
				$s = substr($f, 5, -4);
				if (!in_array($s, $e))
					$st[] = $s;
			}
		}
		return $st;
	}
	
	static public function isValidStylename($s, $iscustom = true) {
		if ($iscustom)
			return !empty($s) && !in_array($s, self::getSystemStyles()) && ctype_alnum($s);
		else 
			return !empty($s) && $s != 'custom' && ctype_alnum($s);
	}
	
	static public function createStyle($name, $definition) {
		file_put_contents(dirname(__FILE__)."/nbcs_$name.php", $definition);
		return true;
	}
	
	static public function deleteStyle($name) {
		if (!in_array($name, self::getSystemStyles()) && file_exists(dirname(__FILE__)."/nbcs_$name.php")) {
			unlink(dirname(__FILE__)."/nbcs_$name.php");
			return true;
		} else return false;
	}
	
	/**
	 * Get the number of printed bibliographies.
	 *
	 * @return int
	 */
	final public function getNumBibs() { return $this->numBiblios; }
	
//---------------------------------------------------------------------------------------------------------------------
// DATA MEMBER
//---------------------------------------------------------------------------------------------------------------------	
	/**
	 * A list of registered attributes (citations). Need to make bibliography
	 *
	 * @var array
	 */
	protected $list = array();
	
	/**
	 * A list to keep track of the number of inline prints for each citation (the first, second, third... might look different)
	 *
	 * @var array
	 */
	protected $iprints = array();
	
	protected $numericIds = array();
	
	protected $numBiblios = 0;
	
	protected $lastAddedID = '';
}
?>
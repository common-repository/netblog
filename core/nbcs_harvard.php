<?php
require_once 'nbcs.php';

/**
 * HARVARD citation style for Wordpress
 * 
 * @package netblog
 * @subpackage NBCS
 * @version 1.5
 * @since 1.5
 * @uses Wordpress 2.8+
 *
 */
class nbcs_harvard extends nbcs {
	
//---------------------------------------------------------------------------------------------------------------------
// MEMBER FUNCTIONS
//---------------------------------------------------------------------------------------------------------------------	

	public function printBiblio( $headline = null, $sections = false )
	{
		$o = array();

		foreach( $this->list as $id=>$atts ) {
			extract($atts);
			if( strlen($year) == 0 ) $year = _x('n.d.','citeNoDate','netblog');
			switch($type){
				case 'book': 
					if($author!='') $o[$type][] = "$author, $initials $year, <i>$title</i>, $volume, $publisher, $publisher_place."; 
					else $o[$type][$id] = "<i>$title</i> $year, $volume, $publisher, $publisher_place.";
					break;
				case 'booksection':
					if($author!='') $o[$type][$id] = "$author, $initials $year, '$title', in $book_author (ed.), <i>$book_title</i>, $volume, $publisher, $publisher_place, $pages.";
					else $o[$type][$id] = "'$title' $year, in $book_author (ed.), <i>$book_title</i>, $volume, $publisher, $publisher_place, $pages."; break;
				case 'conference':
					$o[$type][$id] = "$author, $initials $year, '$title', <i>$title_periodical</i>, $publisher, $publisher_place, $pages". (strlen($url)>0 ? ", $month_access $day_access, $year_access, <$url>." : "."); break;				
				case 'journal': 
					$o[$type][$id] = "$author, $initials $year, '$title', <i>$title_periodical</i>, $volume, $issue, $pages". (strlen($url)>0 ? ", "._x('viewed','citeurl','netblog')." $month_access $day_access, $year_access, <$url>." : ".");;
					break;
				case 'thesis': 
					$o[$type][$id] = "$author, $initials $year, '$title', $award, $publisher, $publisher_place.";
					break;
				case 'report': case 'standard':
					$o[$type][$id] = "$author, $initials $year, <i>$title</i>, ". (strlen($issue)>0 ? "$issue, ": "") ."$publisher, $publisher_place". (strlen($url)>0 ? ", "._x('viewed','citeurl','netblog')." $month_access $day_access, $year_access, <$url>." : ".");;
					break;
				case 'magazine': case 'newspaper':
					if($author!='') $o[$type][$id] = "$author, $initials $year, '$title', <i>$title_periodical</i> $day $month, $pages."; 
					else $o[$type][$id] = "'$title' $year, <i>$title_periodical</i> $day $month, $pages."; break;	
				case 'website': case 'web':
					if($author!='') $o[$type][$id] = "$author, $year, <i>$title</i>, ". (strlen($special_entry)>0 ? "$special_entry, ":"") ."$day $month, ".(strlen($publisher)>0 ? "$publisher, ":"").""._x('viewed','citeurl','netblog')." $day_access $month_access $year_access, <$url>.";
					else $o[$type][$id] = "<i>$title</i> $year, ". (strlen($special_entry)>0 ? "$special_entry, ":"") ."$day $month, ".(strlen($publisher)>0 ? "$publisher, ":"").""._x('viewed','citeurl','netblog')." $day_access $month_access $year_access, <$url>."; break;
				case 'patent': 
					$o[$type][$id] = "$author, $initials $year, <i>$title</i>, $publisher_place "._x('Patent','cite','netblog')." $patent_number";
					break;
				case 'map':
					$o[$type][$id] = "$author $year, <i>$title</i>, ". (strlen($issue)>0 ? "$issue, ": "") ."$publisher, $publisher_place.";
					break;
			}
		}
		return $this->bprint( $headline, $o, $sections, '', '', '<div style="height:50px"></div>' );
	}
	
	
	public function reqAtts( $type = null )
	{
		$a = array(
			'book' => 'author|optional,initials|optional,year|as date:Y,title,volume,publisher,publisher_place',
			'booksection' => 'author|optional,initials|optional,year|as date:Y,title,book_author,book_title,pages,volume,publisher_place,publisher',
			'conference' => 'author,initials|optional,year|as date:Y,title,title_periodical,publisher,publisher_place,pages,url|as uri -optional,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',		
			'journal' => 'author,initials|optional,year|as date:Y,title,title_periodical,volume,issue,pages,url|as uri -optional,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'thesis' => 'author,initials|optional,year|as date:Y,title,award|-help PhD. Thesis,publisher,publisher_place',
			'report' => 'author,initials|optional,year|as date:Y,title,issue|-optional,publisher,publisher_place',
			'standard' => 'author,initials|optional,year|as date:Y,title,issue|-optional,publisher,publisher_place',
			'magazine' => 'author|optional,initials|optional,year|as date:Y,month|as date:M,title,title_periodical,pages',
			'newspaper' => 'author|optional,initials|optional,year|as date:Y,month|as date:M,title,title_periodical,pages',
			'website' => 'author|optional,title,year|as date:Y,month|as date:M,day|as date:D,special_entry|optional,publisher|optional,url|as uri -optional,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'patent' => 'author,initials|optional,year|as date:Y,title,publisher_place,patent_number',
			'map' => 'author,year|as date:Y,title,issue|optional -help Map/Place number,publisher,publisher_place'
		);
		return isset($a[$type]) ? $a[$type] : ($type === null ? $a : '');
	}
	

	
}
?>
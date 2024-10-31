<?php
require_once 'nbcs.php';

/**
 * TURABIAN citation style for Wordpress
 * 
 * @package netblog
 * @version 1.5
 * @since 1.5
 * @uses Wordpress 2.8+
 *
 */
class nbcs_turabian extends nbcs {
	
//---------------------------------------------------------------------------------------------------------------------
// MEMBER FUNCTIONS
//---------------------------------------------------------------------------------------------------------------------	

	public function printBiblio( $headline = null, $sections = false )
	{
		$o = array();

		foreach( $this->list as $id=>$atts ) {
			extract($atts);
			switch($type){
				case 'book': 
					if($author!='') $o[$type][$id] = "$author. $year. <i>$title</i> $publisher_place: $publisher."; 
					else $o[$type][$id] = "<i>$title</i> $year. $publisher_place: $publisher.";
					break;
				case 'journal': 
					if($author!='') $o[$type][$id] = "$author. $year. $title <i>$title_periodical</i> $volume ($month): $pages";
					else $o[$type][$id] = "$title $year. <i>$title_periodical</i> $volume ($month): $pages"; break;
				case 'magazine': case 'newspaper':
					if($author!='') $o[$type][$id] = "$author. $year. $title <i>$title_periodical</i> $day $month, $pages";
					else $o[$type][$id] = "$title $year. <i>$title_periodical</i> $day $month, $pages"; break;							 
				case 'encyclopedia':
					if($author!='') $o[$type][$id] = "$author, \"$title\", ". vsprintf( _x('in %s','citepublisher','netblog'), "<i>$publisher</i>") .", $year";
					else $o[$type][$id] = "\"$title\", in <i>$publisher</i>, $year"; break;		
				case 'booksection':
					if($author!='') $o[$type][$id] = "$author. $year. $title In <i>$book_title</i>, $book_author, $pages. $publisher_place: $publisher.";
					else $o[$type][$id] = "$title $year. In <i>$book_title</i>, $book_author, $pages. $publisher_place: $publisher."; break;
				case 'eric':
					if($author!='') $o[$type][$id] = "$author. $year. <i>$title</i> $publisher_place: $publisher. ERIC, $doi.";
					else $o[$type][$id] = "<i>$title</i> $year. $publisher_place: $publisher. ERIC, $doi."; break;
				case 'website': case 'web':
					if($author!='') $o[$type][$id] = "$author. $year. <i>$title</i> $publisher_place: $publisher. ".__('On-line. Available from Internet','netblog').", $url, "._x('accessed','url','netblog')." $day_access $month_access $year_access.";
					else $o[$type][$id] = "<i>$title</i> $year. $publisher_place: $publisher. ".__('On-line. Available from Internet','netblog').", $url, "._x('accessed','url','netblog')." $day_access $month_access $year_access."; break;
			}
		}
		return $this->bprint( $headline, $o, $sections, '', '', '<div style="height:50px"></div>' );
	}
	
	
	public function reqAtts( $type = null )
	{
		$a = array(
			'book' => 'author|optional,year|as date:Y,title,publisher_place,publisher',	
			'journal' => 'author,year|as date:Y,month|as date:M,title,title_periodical,volume,pages,doi',
			'magazine' => 'author|optional,year|as date:Y,month|as date:M,day|as date:D,title,title_periodical,pages',
			'newspaper' => 'author|optional,year|as date:Y,month|as date:M,day|as date:D,title,title_periodical,pages',
			
			'booksection' => 'author|optional,year|as date:Y,title,book_author,book_title,pages,publisher_place,publisher',
			'encyclopedia' => 'author|optional,year|as date:Y,title,publisher',
			'eric' => 'author|optional,year|as date:Y,title,publisher,publisher_place,doi',
			'website' => 'author|optional,title,year|as date:Y,month|as date:M,day|as date:D,publisher,publisher_place,month_access|as date:M,day_access|as date:D,year_access|as date:Y,url|as uri'
		);
		return isset($a[$type]) ? $a[$type] : ($type === null ? $a : '');
	}
	

	
}
?>
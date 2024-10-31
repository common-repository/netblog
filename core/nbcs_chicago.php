<?php
require_once 'nbcs.php';

/**
 * CHICAGO citation style for Wordpress
 * 
 * @package netblog
 * @version 1.5
 * @since 1.5
 * @uses Wordpress 2.8+
 *
 */
class nbcs_chicago extends nbcs {
	
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
					if($author!='') $o[$type][$id] = "$author. $year. $title <i>$title_periodical</i>, <i>$volume</i>($issue), ($month): $pages";
					else $o[$type][$id] = "$title $year. <i>$title_periodical</i>, <i>$volume</i>($issue), ($month): $pages"; break;
				case 'magazine': case 'newspaper':
					if($author!='') $o[$type][$id] = "$author. $year. $title <i>$title_periodical</i> $month $day.";
					else if($type=='newspaper') $o[$type][$id] = "<i>$title_periodical</i> $year. '$title' $month $day".(strlen($special_entry)>0 ? ", $special_entry." : "."); 
					else $o[$type][$id] = "$title $year. <i>$title_periodical</i> $month $day."; break;							 
				case 'encyclopedia':
					if($author!='') $o[$type][$id] = "$author, \"$title\", in <i>$publisher</i>, $year";
					else $o[$type][$id] = "\"$title\", in <i>$publisher</i>, $year"; break;		
				case 'booksection':
					if($author!='') $o[$type][$id] = "$author. $year. $title ". vsprintf( _x('In %s','citebooksection','netblog'), "<i>$book_title</i>") .", $book_author, $pages. $publisher_place: $publisher.";
					else $o[$type][$id] = "$title $year. ". vsprintf( _x('In %s','citebooksection','netblog'), "<i>$book_title</i>") .", $book_author, $pages. $publisher_place: $publisher."; break;
				case 'eric':
					if($author!='') $o[$type][$id] = "$author. $year. <i>$title</i> $publisher_place: $publisher, "._x('text-fiche','citeeric','netblog').", $doi.";
					else $o[$type][$id] = "<i>$title</i> $year. $publisher_place: $publisher. "._x('text-fiche','citeeric','netblog').", $doi."; break;
				case 'website': case 'web':
					if($author!='') $o[$type][$id] = "$author. $year. <i>$title</i> $publisher. $url ("._x('accessed','url','netblog')." $month_access $day_access, $year_access).";
					else $o[$type][$id] = "<i>$title</i> $year. $publisher. $url ("._x('accessed','url','netblog')." $month_access $day_access, $year_access)."; break;
			}
		}
		return $this->bprint( $headline, $o, $sections, '', '', '<div style="height:50px"></div>' );
	}
	
	
	public function reqAtts( $type = null )
	{
		$a = array(
			'book' => 'author|optional,year|as date:Y,title,publisher_place,publisher',		
			'journal' => 'author|optional,year|as date:Y,title,title_periodical,volume,issue,month,pages',
			'magazine' => 'author|optional,year|as date:Y,month,day,title,title_periodical',
			'newspaper' => 'author|optional,year|as date:Y,month,day,title,title_periodical,special_entry|optional',	
			'booksection' => 'author|optional,year|as date:Y,title,book_author,book_title,pages,publisher_place,publisher',
			'encyclopedia' => 'author|optional,title,publisher,year|as date:Y',
			'eric' => 'author|optional,title,year|as date:Y,publisher,publisher_place,doi',
			'website' => 'author|optional,title,year|as date:Y,month_access|as date:M,day_access|as date:D,year_access|as date:Y,publisher,url|as uri'
		);
		return isset($a[$type]) ? $a[$type] : ($type === null ? $a : '');
	}
	

	
}
?>
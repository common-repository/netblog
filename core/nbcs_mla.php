<?php
require_once 'nbcs.php';

/**
 * MLA citation style for Wordpress
 * 
 * @package netblog
 * @version 1.5
 * @since 1.5
 * @uses Wordpress 2.8+
 *
 */
class nbcs_mla extends nbcs {
	
//---------------------------------------------------------------------------------------------------------------------
// MEMBER FUNCTIONS
//---------------------------------------------------------------------------------------------------------------------	

	public function printBiblio( $headline = null, $sections = false )
	{
		$o = array();
	
		foreach( $this->list as $id=>$atts ) {
			extract($atts);
			if( trim($author) != '' ) $author .= '.';
			if( trim($special_entry) != '' ) $special_entry .= '.';  
			if( trim($publisher) == '' ) $publisher = __("N.p.",'no_publisher','netblog');
			switch($type){
				case 'journal':
					if( $source == 'web' ) $o[$type][$id] = "$author \"$title\" <i>$title_periodical</i> $volume ($year): $pages. $publisher. ".__('Web','netblog').". $day_access $month_access $year_access.";
					else if( $source == 'db' ) $o[$type][$id] = "$author \"$title\" <i>$title_periodical</i> $volume ($year): $pages. ".__('Web','netblog').". $day_access $month_access $year_access.";
					else $o[$type][$id] = "$author \"$title\" <i>$title_periodical</i> $volume ($year): $pages. ".__('Print','netblog').".";							
					break;
				case 'newspaper':
					if( $source == 'web' ) $o[$type][$id] = "$author \"$title\" <i>$title_periodical</i>. $publisher, $day $month $year. ".__('Web','netblog').". $day_access $month_access $year_access.";
					else if( $source == 'movie' ) /* translators: MLA citation style for newspaper, citing a movie (org. english: Rev. of MOVIE, dir. DIRECTOR) see http://www.cwpost.liunet.edu/cwis/cwp/library/workshop/citmla.htm */ 
						$o[$type][$id] = "$author \"$title\" ".vsprintf( __('Rev. of %s, dir. %s','netblog'), $movie, $director).". <i>$title_periodical</i> $day $month $year, sec. $pages. ".__('Print','netblog').".";
					else $o[$type][$id] = "$author \"$title\" <i>$title_periodical</i>. $publisher, $day $month $year: $pages. ".__('Print','netblog').".";
					break;
				case 'magazine':
					if( $source == 'web' ) $o[$type][$id] = "$author \"$title\" $special_entry <i>$title_periodical</i> $month $year: $pages. $publisher. ".__('Web','netblog').". $day_access $month_access $year_access.";
					else if( $source == 'db' ) $o[$type][$id] = "$author \"$title\" $special_entry <i>$title_periodical</i> $month $year: $pages. ".__('Web','netblog').". $day_access $month_access $year_access.";							
					else $o[$type][$id] = "$author \"$title\" $special_entry <i>$title_periodical</i> $month $year: $pages. ".__('Print','netblog').".";
					break;
				case 'book': 
					if( $source == 'web' || $source == 'db' ) 
						$o[$type][$id] = "$author <i>$title</i> $publisher_place: $publisher, $year. ".__('Web','netblog').". $day_access $month_access $year_access.";														
					else $o[$type][$id] = "$author <i>$title</i> $publisher_place: $publisher, $year. ".__('Print','netblog').".";
					break;
				case 'booksection':
					$o[$type][$id] = "$author \"$title\" <i>$book_title</i>. $book_author. $publisher_place: $publisher, $year. $pages. ".__('Print','netblog').".";
					break;
				case 'encyclopedia':
					if( $source == 'web' || $source == 'db' ) 
						$o[$type][$id] = "$author \"$title\" <i>$book_title</i>. $book_author. $volume. $publisher_place: $publisher, $year. ".__('Web','netblog').". $day_access $month_access $year_access.";														
					else $o[$type][$id] = "$author \"$title\" <i>$book_title</i>. $special_entry $year. ".__('Print','netblog').".";
					break;
				case 'gale': /* translators: MLA citation style for GALE - as Repeat in book_title. (org. engl.: Rpt in book_title) */
					$o[$type][$id] = "$author \"$title\" <i>$title_periodical</i> $day $month $year: $pages. ".vsprintf( __('Rpt in %s','netblog'), "<i>$book_title</i>").". Ed. $book_author. $volume. $publisher_place: $publisher, $year_organisation. $pages_organisation. Literature Criticism Online. ".__('Web','netblog').". $day_access $month_access $year_access.";
					break;
				case 'website': case 'web':
					$o[$type][$id] = "$author \"$title\" <i>$title_periodical</i> $publisher, $day $month $year. ".__('Web','netblog').". $day_access $month_access $year_access. ($url)";
					break;
				case 'blog':						
					$o[$type][$id] = "$author \"$title\" $special_entry <i>$title_periodical</i> $publisher, $day $month $year. ".__('Web','netblog').". $day_access $month_access $year_access. ($url)";
					break;
				case 'wiki':						
					$o[$type][$id] = "\"$title\". <i>".__('Wikipedia: The Free Encyclopedia','netblog').".</i> ".__('Wikimedia Foundation','netblog').", n.d. ".__('Web','netblog').". ".vsprintf( _x('From %s','url','netblog'),'url').". $day_access $month_access $year_access. ($url)";
					break;
				case 'video':
					$o[$type][$id] = "$author dir. \"$title\". <i>$title_periodical</i>. $publisher, $day $month $year. ".__('Web','netblog').". $url $day_access $month_access $year_access.";break;
				case 'powerpoint': case 'ppt':
					$o[$type][$id] = "$author \"$title\" $publisher, $day $month $year. <i>".__('Microsoft PowerPoint','netblog')."</i> "._x('file','citedatafile','netblog').". $url $day_access $month_access $year_access.";break;
				case 'eric':
					$o[$type][$id] = "$author \"$title\". $publisher_place: $publisher, $year. <i>ERIC</i>. ".__('Web','netblog').". $url $day_access $month_access $year_access".(strlen($doi)>0 ? " ($doi)." : ".");break;
				default: $o[$type][$id] = "$type is not defined in MLA";
			}
			
		}
		return $this->bprint( $headline, $o, $sections, '', '', '<div style="height:50px"></div>' );
	}
	
	
	public function reqAtts( $type = null )
	{
		$a = array(
			'journal' => 'author|optional,year|as date:Y,title,title_periodical,volume,pages,publisher|optional,source|optional -values web db print,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'magazine' => 'author|optional,year|as date:Y,month|as date:M,day|as date:D,title,title_periodical,pages|optional,publisher|optional,source|optional -values web db print,special_entry|optional,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'newspaper' => 'author,year|as date:Y,month,day,title,title_periodical,pages|optioanl,publisher|optional,source|optional -values web movie print,movie|optional,director|optional,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'book' => 'author|optional,title,year|as date:Y,publisher|optional,publisher_place,source|optional -values web print,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'booksection' => 'author|optional,title,year|as date:Y,book_author,book_title,pages,publisher|optional,publisher_place',
			'encyclopedia' => 'author|optional,title,year|as date:Y,book_author,book_title,volume|optional,special_entry|optional,publisher|optional,publisher_place,source|optional -values web print,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'gale' => 'author|optional,title,title_periodical,year|as date:Y,month|as date:M,day|as date:D,pages,book_title|-help Repeat in,book_author|-help Editor,volume,publisher_place,publisher|optional,year_organisation,pages_organisation,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y',		
			'eric' => 'author|optional,title,publisher,publisher_place,year|as date:Y,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional,doi|optional',		
			'website' => 'author|optional,title,title_periodical,publisher|optional,year|as date:Y,month|as date:M,day|as date:D,url|as uri,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y',		
			'wiki' => 'title,url|as uri,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y',
			'blog' => 'author|optional,title,special_entry|optional,title_periodical,publisher|optional,year|as date:Y,month|as date:M,day|as date:D,url|as uri,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y',
			'video' => 'author,title,title_periodical,publisher|optional,year|as date:Y,month|as date:M,day|as date:D,url|as uri,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y',
			'powerpoint' => 'author|optional,title,publisher|optional,year|as date:Y,month|as date:M,day|as date:D,url|as uri,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y'
		);
		return isset($a[$type]) ? $a[$type] : ($type === null ? $a : '');
	}
	

	
}
?>
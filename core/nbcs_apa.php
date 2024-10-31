<?php
require_once 'nbcs.php';

/**
 * APA citation style for Wordpress
 * 
 * @package netblog
 * @version 1.5
 * @since 1.5
 * @uses Wordpress 2.8+
 *
 */
class nbcs_apa extends nbcs {
	
//---------------------------------------------------------------------------------------------------------------------
// MEMBER FUNCTIONS
//---------------------------------------------------------------------------------------------------------------------	

	public function printBiblio( $headline = null, $sections = false )
	{
		$o = array();
	
		foreach( $this->list as $id=>$atts ) {
			extract($atts);
			$date = strlen($year)>0 ? (strlen($month)>0 ? (strlen($day)>0 ? "$year, $month $day" : "$year, $month") : $year) : 'n.d.';
			$t = '';
			switch($type){
				case 'journal': $t = "$author ($date). $title <i>$title_periodical</i>, <i>$volume</i>($issue), $pages."; break;
				case 'periodical': $t = "$author ($date). $title <i>$title_periodical</i>, <i>$volume</i>($issue), $pages."; break;
				case 'magazine': $t = "$author ($date). $title <i>$title_periodical</i>, <i>$volume</i>($issue), $pages."; break;
				case 'newspaper': 
					if($pages!='') $t = "$author ($date). $title <i>$title_periodical</i>, $pages.";
					else $t = "$author ($date). $title <i>$title_periodical</i>.";
					break;
				case 'book': 
					if($author!='') $t = "$author ($date). <i>$title</i>. $publisher_place: $publisher."; 
					else $t = "<i>$title</i> ($date). $publisher_place: $publisher.";
					break;
				case 'booksection': $t = "$author ($date). $title ". vsprintf( _x('In %s','citebooksection','netblog'), $book_author) .", <i>$book_title</i> ($pages). $publisher_place: $publisher."; break;
				case 'encyclopedia': $t = "$author ($date). $title ". vsprintf( _x('In %s','citebooksection','netblog'), $book_author) .", <i>$book_title</i> ($volume, $pages). $publisher_place: $publisher."; break;
				case 'eric': $t = "$author ($date). <i>$title</i> ".__('Retrieved from ERIC databse.','netblog')." ($doi)"; $doi=''; break;
				case 'website': case 'web':
					if($author=='') $t = "<i>$title</i> ($date).";
					else if( $author != $publisher && $publisher!='' ) $t = "$author ($date). <i>$title</i>. $publisher.";
					else if( $author == $publisher ) $t = "$author ($date). <i>$title</i>";
					else  $t = "$author ($date). <i>$title</i>";
					break;
				case 'wiki': $t = "$title (n.d.). "._x('In <i>Wikipedia</i>','cite','netblog')."."; break;
				case 'blog': $t = "$author ($date). $title."; break;
				//case 'blog': $t = "$author ($date). $title ["._x('Weblog message','netblog')."]."; break;
				case 'video': $t = "$author ($date). $title ["._x('Video file','netblog')."]."; break;
				case 'powerpoint': case 'ppt':  $t = "$author ($date). <i>$title</i> ["._('PowerPoint slides','netblog')."]."; break;
			}
			if($t!='') {
				if(strlen($doi)>0) $t .= " doi: $doi.";
				if(strlen($url)>0) {
					$date = strlen($year_access)>0 ? (strlen($month_access)>0 ? (strlen($day_access)>0 ? "$month_access $day_access, $year_access" : "$month_access $year_access") : $year_access) : '';					
					$t .= " "._x('Retrieved','url','netblog')." $date "._x('from','url','netblog')." ".(strlen($publisher)>0 ? "$publisher: " : '')."$url.";
				}
				$o[$type][$id] = $t;
			}
		}
		return $this->bprint( $headline, $o, $sections, '', '', '<div style="height:50px"></div>' );
	}
	
	
	public function reqAtts( $type = null )
	{
		$a = array(
			'journal' => 'author,year|as date:Y,month|as date:M -optional,day|as date:D -optional,title,title_periodical,volume,issue,pages,doi|optional,url|optional,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'magazine' => 'author,year|as date:Y,month|as date:M -optional,day|as date:D -optional,title,title_periodical,volume,issue,pages,doi|optional,url|optional,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'periodical' => 'author,year|as date:Y,month|as date:M -optional,day|as date:D -optional,title,title_periodical,volume,issue,pages,doi|optional,url|optional,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'newspaper' => 'author,year|as date:Y,month|as date:M -optional,day|as date:D -optional,title,title_periodical,pages|optional,doi|optional,url|optional,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'book' => 'author|optional,year|as date:Y,month|as date:M -optional,day|as date:D -optional,title,publisher_place,publisher,doi|optional,url|optional,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'booksection' => 'author,year|as date:Y,month|as date:M -optional,day|as date:D -optional,title,book_author,book_title,pages,publisher_place,publisher,doi|optional,url|optional,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'encyclopedia' => 'author,year|as date:Y,month|as date:M -optional,day|as date:D -optional,title,book_author,book_title,volume,pages,publisher_place,publisher,doi|optional,url|optional,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'eric' => 'author,year|as date:Y,month|as date:M -optional,day|as date:D -optional,title,doi,url|optional,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'website' => 'author|optional,title,year|as date:Y,month|as date:M,day|as date:D,publisher,url|as uri,doi|optional,month_access|as date:M,day_access|as date:D,year_access|as date:Y',
			'wiki' => 'title,url|as uri,doi|optional,month_access|as date:M,day_access|as date:D,year_access|as date:Y',
			'blog' => 'author,year|as date:Y,month|as date:M,day|as date:D,title,doi|optional,url,month_access|as date:M,day_access|as date:D,year_access|as date:Y',
			'video' => 'author,year|as date:Y,month|as date:M,day|as date:D,title,doi|optional,url|optional,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional',
			'powerpoint' => 'author,year|as date:Y,month|as date:M,day|as date:D,title,publisher,url|as uri,doi|optional,month_access|as date:M -optional,day_access|as date:D -optional,year_access|as date:Y -optional'
		);
		return isset($a[$type]) ? $a[$type] : ($type === null ? $a : '');
	}
		
}
?>
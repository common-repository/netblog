<?php

class nbUri {
	/** 
	 * Tries to grab/extract a document title from a valid html-like encoded string
	 * @param string $html
	 * @return string Returns the extracted title if found, null otherwise
	 */
	static function GrabTitle( $html ) {
		/*if (preg_match('/<title>(.*?)<\/title>/i', $html, $matches)) {
	        return $matches[1];
	    }
		return null;
		*/
	if( preg_match("#<title>(.+)<\/title>#iU", $html, $t))  {
		return trim($t[1]);
	} else {
		return false;
	}
	}
	
	/**
	 * Tries to find a document's title with the help of the default websearch engine
	 * @param string $uri 
	 * @return string Returns the extracted title if found in websearch, null otherwise
	 */
	static function FindTitleByWebsearch( $uri ) {
		$search_uri = Netblog::options()->getWebsearchUri($uri, Netblog::options()->getWebsearchMaxResults() );
		$rss = fetch_feed( $search_uri );	
			
		if( !is_wp_error($rss) ) {
			$max = Netblog::options()->getWebsearchMaxResults();
			$maxItems = $rss->get_item_quantity( max($max,0) );	// 0 := all items
			$rss_items = $rss->get_items(0, $maxItems);						
	
			if( $maxItems != 0 )
			foreach( $rss_items as $k=>$item ) 
				if( $item->get_permalink() == $uri ) 
					return $title = $item->get_title();
							
		}
		return null;
	}
}
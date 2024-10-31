<?php
require_once 'DataTransfer.php';

/**
 * Get and sync search APIs used for blogsearch and websearch features.
 * 
 * @version 1.0
 */
class nbsearch {	
	function loadDefinitions() {
		$result = DataTransfer::RetrieveUrl($this->defsURL);
		$ct = $result['content'];
		if( $ct == null || strlen($ct)==0 )
			return false;
			
		$pilot = new nbTestPilot();
		$pilot->searchServiceLoad();
		//$pilot->save();
			
		$ls = explode('<br />',nl2br($ct));
		for( $i=0; $i<sizeof($ls); $i++ ) {
			$ls[$i] = trim($ls[$i]);
			if( ($p=strpos($ls[$i],'new ')) !== false ){
				$o = substr($ls[$i],$p+4);				
				switch($o) {
					case 'provider':
						$this->parseProvider($ls,++$i);
						break;
				}
			}
		}
		
		return true;
	}
	
	function getBlogsearchs() {
		return isset($this->defs['blogsearch']) ? $this->defs['blogsearch'] : null; 
	}
	
	function getWebsearchs() {
		return isset($this->defs['websearch']) ? $this->defs['websearch'] : null;
	}
	
	function getById( $id ) {
		if(isset($this->defs['blogsearch']))
			foreach($this->defs['blogsearch'] as $s)
				if(isset($s['id']) && $s['id']==$id)
					return $s;
		if(isset($this->defs['websearch']))
			foreach($this->defs['websearch'] as $s)
				if(isset($s['id']) && $s['id']==$id)
					return $s;
		return null;
	}
	
	private function parseProvider( &$lineArray, &$index ) {
		$t = array();
			
		for( $i=$index; $i<sizeof($lineArray); $i++ ) {
			$lineArray[$i] = trim($lineArray[$i]);
		
			if(strlen($lineArray[$i])==0) continue;			
			if( ($p=strpos($lineArray[$i],' ')) == false ) continue;
		
			$cmd = substr($lineArray[$i],0,$p);
			$val = substr($lineArray[$i],$p+1);
			if(strlen($val)==0) continue;
			
			switch($cmd) {
				case 'type': case 'name': case 'urlprovider': case 'urlquery': case 'id': case 'apikey':
					$t[$cmd] = $val; break;
				case 'maxresults': 
					$t[$cmd] = intval($val); break;
				case 'new': 
					$index = $i; break 2;
			}
		}
		
		if(isset($t['type']) && isset($t['name']) && isset($t['urlprovider']) && isset($t['urlquery']) && isset($t['maxresults']) &&
		isset($t['id'])) {
			$this->defs[$t['type']][] = $t;
		}		
	}
	
	private $defsURL = 'http://netblog2.benjaminsommer.com/modules/nbsearchdefs.txt'; //'netblog.benjaminsommer.com/search.def';
	private $defs = array();
	
	
	/**
	 * Get links by Blogsearch
	 *
	 * @param string $query
	 * @param string $count
	 * @return array
	 */
	static function getLinksByBlogsearch( $query, $count = 25 ) {
		$q = Netblog::options()->getBlogsearchUri($query,$count);
		$rss = fetch_feed($q);
		if( !is_wp_error($rss) ) {
			$maxitems = $rss->get_item_quantity($count); 
			$rss_items = $rss->get_items(0, $maxitems);
			return $rss_items;
		}
		return null;
	}
}


//echo '<pre>';
//$s = new nbsearch();
//$s->loadDefinitions();
//var_dump($s->getBlogsearchs());
//var_dump($s->getWebsearchs());
//echo '</pre>';
//new provider
//type blogsearch
//name Google Blogsearch
//id google_blogsearch
//urlprovider http://blogsearch.google.com
//urlquery http://blogsearch.google.com/blogsearch_feeds?scoring=d&ie=utf-8&num=??count??&output=rss&partner=wordpress&q=??query??
//maxresults 10
//
//new provider
//type websearch
//name Websearch by Yahoo.com
//id yahoo_blogsearch
//urlprovider http://www.yahooapis.com
//urlquery http://search.yahooapis.com/WebSearchService/rss/webSearch.xml?appid=yahoosearchwebrss&query=??query??
//maxresults 10

?>
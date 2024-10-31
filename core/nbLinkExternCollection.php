<?php
require_once 'nbLinkExtern.php';

class nbLinkExternCollection {
	
	/**
	 * Initializes this link collection
	 * @param int $parent The wp post id
	 */
	private function __construct($parent) {
		$this->parent = $parent;
	}
	
	/**
	 * Loads all external links being attached to a wp post
	 * @param int $parent The parent wp post id for the links to be loaded
	 * @return nbLinkExternCollection
	 */
	static public function LoadByParent($parent) {
		global $wpdb; 
		$out = new nbLinkExternCollection($parent);
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		if( ($r=$wpdb->get_results("SELECT id, uri_id FROM `$rel` WHERE id = '$parent'"))==null )
			return $out;
		
		foreach($r as $rs) {
			if( $c=nbLinkExtern::LoadByID($rs->uri_id) )
				$out->links[] = $c;
		}
		return $out;
	}
	
	/**
	 * Loads one external link being attached to a wp post
	 * @param int $parent The parent wp post id for the links to be loaded
	 * @param int $uri_id The internal URI id
	 * @return nbLinkExternCollection
	 */
	static public function LoadByRelation($parent,$uri_id) {
		global $wpdb; 
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		if( ($r=$wpdb->get_results("SELECT id, uri_id FROM `$rel` WHERE id = '$parent' AND uri_id = '$uri_id'"))==null )
			return null;
			
		$out = new nbLinkExternCollection($parent);
		foreach($r as $rs) {
			if( $c=nbLinkExtern::LoadByID($rs->uri_id) )
				$out->links[] = $c;
		}
		return $out;
	}
	
	/**
	 * Loads one link for all wp post currently in the database
	 * @return array Returns an arrya of nbLinkExternCollection instances
	 */
	static public function LoadByUri( $uri_id ) {
		global $wpdb; 
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		$q = "SELECT id, uri_id FROM `$rel` WHERE uri_id = '$uri_id'  ORDER BY id";
		if( ($r=$wpdb->get_results($q))==null ) 
			return null;
			
		$out = array(); $last_id = 0; $cur = null;
		foreach($r as $rs) {
			if($last_id!=$rs->id) {
				if($cur && $cur->CountLinks()>0)
					$out[] = $cur;
				$cur = new nbLinkExternCollection($last_id=$rs->id);
			}				
			if( $c=nbLinkExtern::LoadByID($rs->uri_id) )
				$cur->links[] = $c;
		}
		if($cur && $cur->CountLinks()>0)
			$out[] = $cur;
		return sizeof($out)>0 ? $out : null;
	}
	
	/**
	 * Loads all external links for all wp post currently in the database
	 * @return array Returns an array of nbLinkExternCollection instances
	 */
	static public function LoadAll() {
		global $wpdb; 
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		$q = "SELECT id, uri_id FROM `$rel` ORDER BY id";
		if( ($r=$wpdb->get_results($q))==null ) 
			return null;
		
		$out = array(); $last_id = 0; $cur = null;
		foreach($r as $rs) {
			if($last_id!=$rs->id) {
				if($cur && $cur->CountLinks()>0)
					$out[] = $cur;
				$cur = new nbLinkExternCollection($last_id=$rs->id);
			}				
			if( $c=nbLinkExtern::LoadByID($rs->uri_id) )
				$cur->links[] = $c;
		}
		if($cur && $cur->CountLinks()>0)
			$out[] = $cur;
		return sizeof($out)>0 ? $out : null;
	}
	
	/**
	 * Loads latest external links for all wp post currently in the database
	 * @return array Returns an array of nbLinkExternCollection instances
	 */
	static public function LoadLatest($limit=5) {
		global $wpdb; 
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		$q = "SELECT id, uri_id FROM `$rel` ORDER BY id DESC,uri_id DESC LIMIT $limit";
		if( ($r=$wpdb->get_results($q))==null ) 
			return null;
		
		$out = array(); $last_id = 0; $cur = null;
		foreach($r as $rs) {
			if($last_id!=$rs->id) {
				if($cur && $cur->CountLinks()>0)
					$out[] = $cur;
				$cur = new nbLinkExternCollection($last_id=$rs->id);
			}				
			if( $c=nbLinkExtern::LoadByID($rs->uri_id) )
				$cur->links[] = $c;
		}
		if($cur && $cur->CountLinks()>0)
			$out[] = $cur;
		return sizeof($out)>0 ? $out : null;
	}
	
		/**
	 * Get an array of URI's that match certain criteria.
	 *
	 * @param mixed $orderBy A comma separated string of valid uri field names, like 'id,title'
	 * @param int $limit The maximum number of return uris; cannot be 0.
	 * @param int $flag The flag which all returned uris should have, which must be a valid nbLinkExtern::FLAG_* constant
	 * @param int $refs The number of references the returned uris should have
	 * @return array Returns an array of nbLinkExternCollection objects or null if no links has been found
	 */
	static public function FindUris( $orderBy, $limit = -1, $flag = null, $refs = 0  ) {
		$o = array();
		if( $l=nbLinkExtern::FindUris($orderBy, $limit, $flag, $refs) )
			foreach($l as $lk) {
				if( $t=self::LoadByUri($lk->GetID()) )
					$o[] = $t;
			}
		return sizeof($o)>0 ? $o : null;
	}
	
	/**
	 * Match URIs for keywords and additional criteria.
	 *
	 * @param string $match A string of words to be matched in uri and title.
	 * @param mixed $orderBy A comma separated string of valid uri field names, like 'id,title,title-desc'
	 * @param int $limit The maximum number of return uris; cannot be 0.
	 * @param int $flag The flag which all returned uris should have, which must be a valid nbLinkExtern::FLAG_* constant
	 * @param int $refs The number of references the returned uris should have
	 * @return array Returns an array of nbLinkExternCollection objects or null if no links has been found
	 */
	static public function MatchUri( $match, $orderBy, $limit = -1, $flag = NULL, $refs = 0 ) {
		global $wpdb;
		$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		
		// VERIFY
		if( is_numeric($orderBy) ) return false;
		if( is_string($orderBy) )
			$orderBy = explode(',', $orderBy);		
		$post = true;
		if($post) {
			$q = "	SELECT e.uri_id, e.uri, e.uri_title, e.flag, e.refs, p.post_title
					FROM `$ext` e, $wpdb->posts p, `$rel` rel
					WHERE rel.uri_id = e.uri_id
					AND rel.id = p.ID";
			$validTbl = array('uri_id'=>'e.uri_id','id'=>'e.uri_id','title'=>'e.uri_title','uri_title'=>'e.uri_title'
					,'refs'=>'e.refs','flag'=>'e.flag','post_title'=>'p.post_title','post_id'=>'p.ID');
		} else {
			$q = "	SELECT e.uri_id, e.uri, e.uri_title, e.flag, e.refs
					FROM `$ext` e, `$rel` rel
					WHERE rel.uri_id = e.uri_id";
			$validTbl = array('uri_id'=>'e.uri_id','id'=>'e.uri_id','title'=>'e.uri_title','uri_title'=>'e.uri_title'
					,'refs'=>'e.refs','flag'=>'e.flag');
		}
		
		if( is_string($match) ) {
			$match = addslashes($match);
			$q .= "
				AND (e.uri LIKE '%$match%' || e.uri_title LIKE '%$match%' ".($post?" || p.post_title LIKE '%$match%'":'')." )			
			";
		} else if( is_array($match) ) {
			$t = '';
			foreach($match as $query)
			if( isset($query['query']) && isset($query['logical']) ) {
				$m = addslashes($query['query']);
				
				$log = strtolower($query['logical']) == 'or' ? 'OR' : 'AND';
				if( strlen($t)==0 ) $log = '';
				
				$t .= "$log (e.uri LIKE '%$m%' || e.uri_title LIKE '%$m%' ".($post?"|| p.post_title LIKE '%$m%'":'').") ";
			}
			if( strlen($t) > 0 )
				$q .= " AND ($t) ";
		}
		
		$order = Array();
		foreach( $orderBy as $by ) {
			if( is_string($by) ) $by = explode('-',$by);					
			if( !isset($by[0]) || !isset($validTbl[$by[0]]) || isset($order[$validTbl[$by[0]]]) ) continue;
			
			$t = $validTbl[$by[0]];
			if( isset($by[1]) && strtolower($by[1]) == 'desc' )
				$t .= ' DESC';
			$order[$validTbl[$by[0]]] = $t;
		}
		
		// BUILD SQL-CMD
		if( $flag != null )
			$q .= " AND e.flag = $flag";
		if( $refs > 0 )
			" AND e.refs = '$refs'";
			
		$q .= " GROUP BY e.uri_id";
		
		if( sizeof($order) > 0 )
			$q .= " ORDER BY ".implode(', ',$order);
		if( is_numeric($limit) && $limit > 0 )
			$q .= " LIMIT $limit";
	
		$o = array();
		if( is_array($r=$wpdb->get_results($q)) )
			foreach($r as $rs) {
				$col = new nbLinkExternCollection($rs->id);
				$col->links[] = nbLinkExtern::LoadByID($rs->uri_id);
				$o[] = $col;
			}
		return sizeof($o)>0 ? $o : null;
	}
	
	/**
	 * Adds a link to this collection and updates the database
	 * @param nbLinkExtern $link
	 */
	public function Add( $link ) {
		if($this->HasLink($id=$link->GetID()))
			return false;
		global $wpdb;
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		$q = "INSERT INTO `$rel` (id,uri_id) 
				VALUES ('$this->parent','$id')";
		if( (bool)$wpdb->query($q) ) {
			$link->RefIncrement();
			$link->Save();
			$this->links[] = $link;
			return true;
		}
		return false;
	}
	
	/**
	 * Removes a link from this collection and updates the database. Note that the last element in the collection
	 * is moved to the position with the given index.
	 * 
	 * @param nbLinkExtern $link
	 */
	public function Remove( $index ) {
		if($index<0 || $index>=sizeof($this->links))
			return false;
		$link = $this->links[$index];
		$id = $link->GetID();
		global $wpdb;
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		$q = "DELETE FROM `$rel` WHERE id = '$this->parent' AND uri_id = '$id'";
		if( (bool)$wpdb->query($q) ) {
			$link->RefDecrement();
			$link->Save();
			$this->links[index] = array_pop($this->links);
			return true;
		}
		return false;
	}
	
	/**
	 * Get the parent post id for this extern links collection
	 * @return int Returns the parent wp post id
	 */
	public function GetParent() {
		return $this->parent;
	}
	
	/**
	 * Get the number of extern links for this post id
	 * @return int
	 */
	public function CountLinks() {
		return sizeof($this->links);
	}
	
	/**
	 * Get a link by its index
	 * @param int $index
	 * @return nbLinkExtern
	 */
	public function GetLink($index) {
		return $this->links[$index];
	}
	
	/**
	 * Check if this collection already has a given link
	 * @param int $link_id
	 * @return bool Returns true if collection already contains a given link, false otherwise.
	 */
	public function HasLink($link_id) {
		foreach($this->links as $lk)
			if($lk->GetID()==$link_id)
				return true;
		return false;
	}
	
	/**
	 * Attach post infos in xml format to a given link xml format
	 * @param nbLinkExtern $link
	 * @param SimpleXMLElement $xml
	 */
	static function AttachPostAsXML( $link, $xml ) {
		if( $par=nbLinkExternCollection::LoadByUri($link->GetID())) 
			foreach($par as $col)
				nbPost::AsXML($col->GetParent(), $xml);
	}
	
	private $parent = 0;
	private $links = array();
}

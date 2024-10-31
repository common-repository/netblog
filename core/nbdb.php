<?php

if(!defined('OBJECT')) define( 'OBJECT', 'OBJECT', true );
if(!defined('OBJECT_K')) define( 'OBJECT_K', 'OBJECT_K' );
if(!defined('ARRAY_A')) define( 'ARRAY_A', 'ARRAY_A' );
if(!defined('ARRAY_N')) define( 'ARRAY_N', 'ARRAY_N' );
if(!defined('STRING')) define( 'STRING', 'STRING' );
if(!defined('MIXED')) define( 'MIXED', 'MIXED' );


/**
 * A collection of functions to access and modify the database
 * 
 * @todo Split static methods into seperate classes, e.g. rsc_isAdj to nbLink::hasAdjacent(nbLink& obj).
 * @author Benjamin Sommer
 * @package Netblog
 * @version 1.5
 * @since 1.5
 */
class nbdb {
	
	
	
//----------------------------------------------------------------------------------
// LINKS - INTERN, EXTERN
//----------------------------------------------------------------------------------

/**
 * Check adjacency of two given resource ids
 *
 * @uses wpdb->get_row (since WP0.71)
 * 
 * @deprecated This method has been replaced with nbLinkIntern::Load() for internal links and with nbLinkExternCollection::LoadByRelation() since Netblog 2.0.b6
 * 
 * @param int $nodeID A post/page id from Wordpress (as root node)
 * @param mixed $childNode [int] A post/page id form Wordpress if extern == false, [string] a valid uri otherwise (might be adjacent to root node?)
 * @param boolean|null $extern Optional. Use to force whether childNode should be intern (FALSE) or extern (TRUE). if not set, parameter will be determined by var type of childNode (!is_numeric) 
 * @return boolean TRUE if childNode is adjacent to nodeID, FALSE otherwise
 */
static public function rsc_isAdj( $nodeID, $childNode, $extern = null ) 
{
	global $wpdb; 
	
	if( $extern == null ) 
		$extern = !is_numeric($childNode);
	
	if($extern) {
		$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		$childNode = addslashes($childNode);
		$query = "
			SELECT * FROM `$ext` e, `$rel` r
			WHERE r.uri_id = e.uri_id
			AND r.id = $nodeID
			AND e.uri = '$childNode'
		";
//		$q = $wpdb->get_results($query);
//		var_dump($query,$q);
		return $wpdb->get_results($query) != null;
	} else {
		$net = $wpdb->prefix . Netblog::options()->getServerTableNet();
		return $wpdb->get_results("SELECT * FROM `$net` WHERE id = $nodeID AND adj_id = $childNode") != null;
	}	
}



/**
 * Check whether a post/page as links to internal or external resources, known as adjacent nodes.
 *
 * @uses wpdb get_row
 * 
 * @deprecated This method has been replaced with nbLinkIntern::LoadByParent() for internal links and with nbLinkExternCollection::LoadByParent() for external links since Netblog 2.0.b6
 * 
 * @param int $nodeID A post/page ID in Wordpress
 * @param boolean $extern Whether to look for external resources, default is false (internal rsc).
 * @return boolean Whether a post has attached links (internal OR external)
 */
static public function rsc_hasAdj( $nodeID, $extern = false )
{
	global $wpdb; 
	if($extern) {
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		return $wpdb->get_row("SELECT * FROM `$rel` WHERE id = $nodeID LIMIT 1") != null;
	} else {
		$net = $wpdb->prefix . Netblog::options()->getServerTableNet();
		return $wpdb->get_row("SELECT * FROM `$net` WHERE id = $nodeID LIMIT 1") != null;
	}	
}


/**
 * Attach a resource to a post/page
 *
 * @uses wpdb->get_row (since WP0.71)
 * 
 * @deprecated This method has been replaced with nbLinkIntern::Create() for internal links and with nbLinkExternCollection::Add() for external links since Netblog 2.0.b6
 * @param int $nodeID A post/page id from Wordpress (as root node).
 * @param mixed $childNode [int] A post/page id form Wordpress if extern == false, [string] a valid uri otherwise (might be adjacent to root node?).
 * @param boolean|null $extern Optional. Use to force whether childNode should be intern (FALSE) or extern (TRUE). if not set, parameter will be determined by var type of childNode (!is_numeric).
 * @param string $uriTitle Optional. An uri's title. Only needed of extern is FALSE OR childNode is uri. 
 * @param string $footprint Optional. A unique footprint id, retrieved via class footprintConnect().
 * @return boolean TRUE if childNode was attached to nodeID, FALSE if childNode was already attached or it failed.
 */
static public function rsc_addAdj( $nodeID, $childNode, $extern = null, $uriTitle = '', $footprint = '' )
{
	global $wpdb;
	
	if( self::rsc_isAdj($nodeID,$childNode,$extern) )
		return false;
	
	if( $extern == null )
		$extern = !is_numeric($childNode);
		
	if($extern){		
		$childNode = addslashes($childNode);
		$uriTitle = addslashes( strip_tags($uriTitle) );
		if( strlen($uriTitle) == 0 ) return false;		
		
		self::log("APPEND extern resource ($childNode at $nodeID)");
		
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		
		$uri_id = self::uri_add($childNode,$uriTitle,$footprint);		
		$res = $wpdb->query("INSERT INTO `$rel` (id,uri_id) VALUES ('$nodeID','$uri_id')");
		
		return !($res == false || $res <= 0);
	} else {
		self::log("APPEND intern resource ($childNode at $nodeID)");
		
		$net = $wpdb->prefix . Netblog::options()->getServerTableNet();
		$res = $wpdb->query("INSERT INTO `$net` (id,adj_id) VALUES ('$nodeID','$childNode')");
		return !($res == false || $res <= 0);
	}
}


/**
 * Deattaches a link from a given post/page; link will be removed if it is attached to nothing. 
 *
 * @uses wpdb->get_row (since WP0.71)
 * 
 * @deprecated This method has been replaced with nbLinkIntern::Remove() for internal links and with nbLinkExternCollection::Remove() for external links since Netblog 2.0.b6
 * @param int $nodeID A post/page id from Wordpress (as root node)
 * @param mixed $childNode [int] A post/page id form Wordpress if extern == false, [string] a valid uri otherwise (might be adjacent to root node?)
 * @param boolean|null $extern Optional. Use to force whether childNode should be intern (FALSE) or extern (TRUE). if not set, parameter will be determined by var type of childNode (!is_numeric) 
 * @return boolean TRUE if removal was a success, FALSE if removal failed or link not found
 */
static public function rsc_rmAdj( $nodeID, $childNode, $extern = null )
{
	global $wpdb; 
	
	if( $extern == null ) 
		$extern = !is_numeric($childNode);
	
	if($extern){
		$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		
		$uriID = nbdb::uri_getID($childNode);
		$res = $wpdb->query("DELETE FROM `$rel` WHERE id = $nodeID AND uri_id = $uriID");
		$wpdb->query("UPDATE `$ext` SET refs = refs-1 WHERE uri_id = $uriID");
		
		if( $wpdb->query("SELECT * FROM `$rel` WHERE uri_id = $uriID") == null )
			$wpdb->query("DELETE FROM `$ext` WHERE uri_id = $uriID");
		
		return !($res == false || $res <= 0);		
	} else {
		$net = $wpdb->prefix . Netblog::options()->getServerTableNet();
		$res = $wpdb->query("DELETE FROM `$net` WHERE id = $nodeID AND adj_id = '$childNode'");
		return !($res == false || $res <= 0);
	} 	
}


/**
 * Detach all resources from a post/page and remove resources if required.
 *
 * @deprecated This method has been replaced with nbLinkIntern::LoadByParent() and nbLinkIntern::Remove() since Netblog 2.0.b6 for internal links
 * @param int $nodeID Wordpress post/page id.
 * @return boolean TRUE on success, FALSE on failure.
 */
static public function rsc_detachFromNode( $nodeID )
{
	// INTERN
	$a = self::rsc_getAdjs($nodeID, false);
	foreach( $a as $e )
		if( !self::rsc_rmAdj($nodeID, $e['ID']))
			return false;
	
	// EXTERN
	$a = self::rsc_getAdjs($nodeID, true);
	foreach( $a as $e )
		if( !self::rsc_rmAdj($nodeID, $e['uri'], true))
			return false;	
			
	return true;
}


/**
 * Detach a given resource node (post/page or uri) from all other resources it is currently attached to and remove it afterwards.
 *
 * @deprecated This method has been replaced with nbLinkIntern::LoadByParent() and nbLinkIntern::Remove() since Netblog 2.0.b6 for internal links
 *
 * @param int $nodeID A resource id.
 * @param boolean $extern Whether given nodeID is an external resource (uri).
 * @return boolean TRUE on success, FALSE on failure OR not found.
 */
static public function rsc_detachNode( $nodeID, $extern = false )
{
	$p = self::rsc_getParents($nodeID, $extern, OBJECT);
	foreach( $p as $r )
		if( !self::rsc_rmAdj( $r->parID, $r->childID, $extern ) )
			return false;
	return sizeof($p)>0;
}



/**
 * Get an array of parent ids.
 *
 * @deprecated This method has been replaced with nbLinkIntern::LoadByChild() and with nbLinkExternCollection::LoadByUri() since Netblog 2.0.b6
 *
 * @param int $childID A child id.
 * @param boolean $extern Whether to search for external resources.
 * @param string $output Any of ARRAY_A | OBJECT.
 * @return array A 2-dim array of parent ids, with [] => {[parID|childID] => [field value]}
 */
static public function rsc_getParents( $childID, $extern = false, $output = ARRAY_A )
{
	global $wpdb;
	
	if($extern){
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		return $wpdb->get_results("SELECT id as parID, uri_id as childID FROM `$rel` WHERE uri_id = '$childID'", $output);
	} else {
		$net = $wpdb->prefix . Netblog::options()->getServerTableNet();
		return $wpdb->get_results("SELECT id as parID, adj_id as childID FROM `$net` WHERE adj_id = '$childID'", $output);
	}
}


/**
 * Get an array of attached resources
 *
 * @deprecated This method has been replaced with nbLinkIntern::LoadByParent() and with nbLinkExternCollection::LoadByParent() since Netblog 2.0.b6
 *
 * @param int $nodeID A post/page id from Wordpress (as root node).
 * @param boolean $extern Whether to search for external resources.
 * @return array A 2-dim array of resources, with [row num] => { [field name] => [field value] }
 */
static public function rsc_getAdjs( $nodeID, $extern = false, $output = ARRAY_A )
{
	global $wpdb; 
	
	if($extern){
		$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		$query = "
			SELECT * FROM `$ext` e, `$rel` r
			WHERE r.uri_id = e.uri_id
			AND r.id = '$nodeID'
			ORDER BY e.uri
		";
		return $wpdb->get_results($query, $output);
	} else {
		$net = $wpdb->prefix . Netblog::options()->getServerTableNet();	
		$query = "
			    SELECT p.ID, post_title, user_nicename, post_date, post_status, post_type, post_mime_type
			    FROM $wpdb->posts p, `$net` n, $wpdb->users u
		      	WHERE n.id = $nodeID
		      	AND n.adj_id = p.ID
		      	AND p.post_author = u.ID
			    AND (p.post_type = 'post' || p.post_type = 'page' || p.post_type = 'attachment' )
			    ORDER BY p.post_title;
		";																				//UPDT_1.2 - page
		return $wpdb->get_results($query, $output);
	}	
}


/**
 * Get post IDs having internal links, either for outgoing or incoming links or both.
 *
 * @deprecated This method has been replaced with nbLinkIntern::LoadAll() since Netblog 2.0.b6
 *
 * @param string $dir Any of 'out', 'in' or 'both'.
 * @return array Array of post IDs with [postid]=>[count_links].
 */
static public function rsc_getPostsInternLinks( $dir = 'out' ) {	
	global $wpdb; 
	$net = $wpdb->prefix . Netblog::options()->getServerTableNet();
	$query = "SELECT * FROM `$net` n";
	$r = $wpdb->get_results($query, OBJECT);
	$l = array();
	
	if($r!=null && is_array($r))
	foreach($r as $o) {
		$id = 0;
		switch($dir) {
			case 'in': $id = $o->adj_id; break;
			case 'both': $id = $o->adj_id;
						 $l[$o->id] = isset($l[$o->id]) ? $l[$o->id]+1 : 1; break; 	
			case 'out': 
			default: $id = $o->id; break;
		}
		$l[$id] = isset($l[$id]) ? $l[$id]+1 : 1;
	}
	return $l;
}


/**
 * Get post IDs having external links.
 *
 * @deprecated This method has been replaced with nbLinkExternCollection::LoadAll() since Netblog 2.0.b6
 * @return array Array of post IDs with [postid]=>[count_links]
 */
static public function rsc_getPostsExternLinks() {
	global $wpdb; 
	$ext = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
	$query = "SELECT * FROM `$ext` e";
	$r = $wpdb->get_results($query, OBJECT);
	$l = array();
	
	if($r!=null && is_array($r))
	foreach($r as $o) {
		$l[$o->id] = isset($l[$o->id]) ? $l[$o->id]+1 : 1;
	}	
	return $l;
}


/**
 * Get an array of posts/pages having a certain resource attached to it.
 *
 * @deprecated This method has been replaced with nbLinkIntern::LoadByChild() and with nbLinkExternCollection::LoadByUri() since Netblog 2.0.b6
 *
 * @param int $childNode A post/page ID of Wordpress.
 * @param int $limit Optional. Default: -1; The maximum number of found posts, with -1 == unlimited.
 * @param boolean|null $extern Optional. Use to force whether childNode should be intern (FALSE) or extern (TRUE). if not set, parameter will be determined by var type of childNode (!is_numeric)
 * @param int $output Any of ARRAY_A, OBJECT, OBJECT_N, OBJECT_A 
 * @return array A 2-dimensional array of posts, with [row num] => { [field name] => [field value] }
 */
static public function rsc_getNodesByAdj( $childNode, $limit = -1, $extern = null, $output = ARRAY_A )
{
	global $wpdb;

	if( $extern == null )
		$extern = !is_numeric($childNode);
	
	if($extern){
		$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
		$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
		$uri = addslashes($childNode);
		
		$query = "
			SELECT ext.uri, ext.uri_title, ext.uri_id, p.ID, p.post_title, p.post_date, p.post_status, p.post_type
			FROM `$ext` ext, `$rel` rel, $wpdb->posts p
			WHERE ext.uri = '$uri'
			AND ext.uri_id = rel.uri_id
			AND rel.id = p.id
			ORDER BY p.post_date
		";
		if( $limit > 0 )
			$query .= " LIMIT $limit;";
			
		return $wpdb->get_results($query,$output);		
	} else {
		$net = $wpdb->prefix . Netblog::options()->getServerTableNet();
		$query = "
			SELECT *
			FROM `$net` n, $wpdb->posts p
			WHERE n.adj_id = '$childNode'
			AND n.id = p.ID
		";	
		if( $limit > 0 )
			$query .= " LIMIT $limit;";
				
		return $wpdb->get_results($query,$output);
	}	
}


/**
 * Get a comma separated string of posts/pages having a certain resource attached to it.
 *
 * @deprecated This method is not used anymore by Netblog
 * 
 * @param int $childNode A post/page ID of Wordpress.
 * @param int $limit Optional. Default: -1; The maximum number of found posts, with -1 == unlimited.
 * @param boolean|null $extern Optional. Use to force whether childNode should be intern (FALSE) or extern (TRUE). if not set, parameter will be determined by var type of childNode (!is_numeric) 
 * @return string A comma separated string of posts and pages.
 */
static public function rsc_getNodesByAdjAsString( $childNode, $limit = -1, $extern = null )
{
	$arr = self::rsc_getNodesByAdj($childNode, $limit, $extern );
	$out = Array();
	foreach( $arr as $row )
		if( isset($row['post_title']))
			$out[] = $row['post_title'];

	return implode( ', ', $out );
}


/**
 * Count the number of resources attached to a Wordpress post/page.
 *
 * @deprecated This method has been replaced with nbLinkIntern::Load*() since Netblog 2.0.b6
 *
 * @param int $nodeID Wordpress post/page id.
 * @param boolean $extern Any of TRUE | FALSE | NULL; use NULL to include both, external and internal resources.
 * @return int The counted number.
 */
static public function rsc_count( $nodeID, $extern = null )
{
	$n = 0;
	
	if($extern === true || $extern == null ) {
		if( ($a=self::rsc_getAdjs( $nodeID, true )) != null ) $n += count($a);
	}
	if( $extern === false || $extern == null ) {
		if( ($a=self::rsc_getAdjs( $nodeID, false )) != null ) $n += count($a);
	}
	
	return $n;	
}


/**
 * Parse adjacent resouces as a not-human-friendly, portable-string.
 *
 * @deprecated Not used any more. Will be removed in future versions.
 * 
 * @param int $nodeID Wordpress post/page id.
 * @param boolean $extern Any of TRUE | FALSE | NULL; use NULL to include both, external and internal resources.
 * @return string A portable-string; half-encoded.
 */
static public function rsc_parse4Export( $nodeID, $extern = null )
{	
	$o = array();
	
	if($extern === true || $extern == null ) {
		$a = self::rsc_getAdjs( $nodeID, true );
		foreach( $a as $r )
			$o[] = implode( md5('netblog-col'), array('ext',$r['uri'],$r['uri_title'],$r['flag']) );
	}
	if( $extern === false || $extern == null ) {
		$a = self::rsc_getAdjs( $nodeID, false );
		foreach( $a as $r )
			$o[] = implode( md5('netblog-col'), array('int',self::footprt_getID($r['ID'])) );
	}
	
	return implode( md5('netblog-row'), $o);
}


/**
 * Generate an (encoded) portable string with meta information; ready for export.
 *
 * @deprecated Not used any more. Will be removed in future versions.
 * 
 * @param int $nodeID The Wordpress post/page id.
 * @param boolean $encode Whether to decode output.
 * @return string String for export.
 */
static private function rsc_exportNodeData( $nodeID, $encode = true )
{
	$time = time();
	$countI = self::rsc_count($nodeID,false);
	$countE = self::rsc_count($nodeID,true);
	$count = $countI + $countE;
	$dataS = self::rsc_parse4Export($nodeID);
	$head = "meta{product:NetblogExport version:1.5 host:WP at:$time count:$count-$countI-$countE footprint:".self::footprt_getID($nodeID)."} ";
	$body = "data{".$dataS."} ";
	
	$final = $head.$body;
	if( $encode ) $final = self::encode($final);
	return $final;
}


/**
 * Save or update export information in post/page content.
 *
 * @deprecated Replaced with WP custom fields since Netblog 2.0
 * 
 * @param int $nodeID The Wordpress post/page id.
 * @param boolean $encode Whether to decode output.
 * @return boolean TRUE on success, FALSE on failure.
 */
static public function rsc_exportNode( $nodeID, $encode = true )
{
	// GET POST CONTENT
	if( ($ct=self::getPostInfo($nodeID,'full')) == null ) return false;
	$content = $ct['post_content'];
	
	// GENERATE EXPORT DATA
	$data = base64_encode(self::footprt_getID($nodeID));
	
	// BUILD EXPORT TAG
	$tag = "<!--{NETBLOG_EXPORT} $data -->";
	
	// REMOVE EXISTING TAG
	$s = stripos( $content, '<!--{NETBLOG_EXPORT}');
	if( $s !== false ) {
		$e = strpos( $content, '-->', $s );
		if( $s !== false && $e !== false )
			$content = substr( $content, 0, $s ) . substr( $content, $e+3 );
	}
	
	// ADD EXPORT TAG
	$content .= $tag;

	// SAVE TO DATABASE
	global $wpdb;
	$content = addslashes($content);
	$query = "UPDATE $wpdb->posts SET post_content = '$content' WHERE ID = '$nodeID'";
	$wpdb->query($query);
	return $wpdb->num_rows == 1;
}


/**
 * Rebuild embedded export data in all required posts and pages.
 * This data is only appended to articles to ensure correct import of netblog data to re-imported articles,
 * because importers to other blog technologies might not recognize WordPress custom fields!
 *
 * @deprecated Replaced with WP custom fields since Netblog 2.0
 * 
 * @param boolean $encode TRUE if to encoded content-embedded export data (strongly recommended -> privacy and security)
 * @return boolean|int TRUE on success, NUMBER of errors otherwise.
 */
static public function rsc_exportRebuild( $encode = true )
{
	global $wpdb;
	$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
	$net = $wpdb->prefix . Netblog::options()->getServerTableNet();
	$err = 0;
	$suc = 0;
	
	// Since only footprints are stored in posts, make sure all have a valid footprint
	if( !Netblog::options()->useFootprints() )
		return false;
	self::footprt_createAll();
	
	$query = "	SELECT p.ID FROM `$wpdb->posts` p, `$net` n, `$rel` e
				WHERE (p.ID = n.id OR p.ID = e.id)
				GROUP BY p.ID
				ORDER BY p.ID
	";
	$r = $wpdb->get_results($query);
	if(is_array($r))
		foreach( $r as $o ) 
			self::rsc_exportNode( $o->ID, $encode ) ? $suc++ : $err++;
	
	return $err == 0 ? true : $err;
}

/**
 * Rebuild eed for all suitable WP articles
 *
 */
static public function eed_rebuild() {
	self::rsc_exportRebuild(true);
}

/**
 * Remove eed from all WP articles
 *
 */
static public function eed_remove() {
	self::rsc_rmExportData();
}

/**
 * Decode and parse export data front (encoded) string.
 *
 * @deprecated Replaced with WP custom fields since Netblog 2.0
 * 
 * @param string $string Encoded export data per post.
 * @param int $nodeID Wordpress post/page id.
 * @return boolean TRUE on success, FALSE on failure.
 */
static private function rsc_importNodeData( $string, $nodeID )
{
	$fid = base64_decode(trim($string));
	if( !self::footprt_hasFootprint($nodeID) ) {
		self::footprt_insert($nodeID,$fid);
	}
	
	
//	$decode = false;
//	$metaAtts = array('product'=>'Netblog', 'version'=>'', 'host'=>'', 'at'=>'', 'count'=>'', 'footprint'=>'' );
	
//	// TRY TO DECODE
//	if( !strpos($string,'meta{') && !strpos($string,'data{') && !strpos($string,'NetblogExport') ) {
//		$decode = true; 
//		$string = self::decode($string);
//	}
//	
//	// ERROR - STOP
//	if( !strpos($string,'meta{') && !strpos($string,'data{') && !strpos($string,'NetblogExport') )
//		return false;
//		
//	// MINIMUM OK
//	$meta = substr($string, $m1=(strpos($string,'meta{')+5), $m2=(strpos($string,'}',$m1)-$m1) );
//	$data = substr($string, $d1=(strpos($string,'data{',$m2)+5), $d2=(strpos($string,'}',$d1)-$d1) );
//
//	
//	// PARSE META
//		$e = explode(' ',$meta);
//		foreach($e as $k)
//			if( isset($metaAtts[ $k2=substr($k, 0, $i=(strpos($k,':')) ) ]) ) $metaAtts[$k2] = substr($k,$i+1);
//			else return false;
//
//			
//	// SAVE FOOTPRINT FROM EXPORT/IMPORT
//	if( $metaAtts['footprint'] != '' ) { 
//		self::footprt_insert( $nodeID, $metaAtts['footprint'] );
//		self::footprt_update( $nodeID );									// LATEST IMPORT COUNTS
//	}
//	
//	// CREATE FOOTPRINT FOR IMPORTED ARTICLE
//	else self::footprt_create( $nodeID );
//
//	
//	// PARSE DATA
//		$e = explode( md5('netblog-row'), $data );
//		foreach($e as $k=>$v) {
//			$e[$k] = $v = explode( md5('netblog-col'), $v);
//			
//			// EXTERNAL RESOURCE
//			if($v[0]=='ext'){
//				self::rsc_addAdj( $nodeID, $v[1], true, $v[2] );
//				self::uri_update( $v[1], $v[2], $v[3]);
//				
//			// INTERNAL RESOURCE
//			} else if( $v[0]=='int'){
//				$id = self::footprt_getPostIDByFootprint( $v[1] );
//				if( $id != null )
//					self::rsc_addAdj( $nodeID, $id, false );
//				else {
//					// CANNOT FIND LINKED POST (NO FOOTPRINT FOUND)
//					// since import works on a per-post basis, try to look for this footprint
//					// at the end of complete import session; the post (and footprint) we are
//					// looking for might still be in the import to-do list (not yet imported)
//					// use self::importFinalize() to check again,
//					// in the meanwhile, save all required information
//					self::$tasks_import[] = array( 'parID'=>$nodeID, 'footprint'=> $v[1] );	
//					file_put_contents(dirname(__FILE__).'/import.log', "id:$nodeID footprint:$v[1] ", FILE_APPEND);				
//				}								
//			} else return false;
//		}
}


/**
 * Import a Wordpress post/page; read export data from imported and saved post content; use nbdb::rsc_importFinalize() at end of import session.
 *
 * @param int $nodeID Wordpress post/page id.
 * @return boolean TRUE on success, FALSE on failure.
 */
static public function rsc_importNode( $nodeID )
{
	self::log( "IMPORT post $nodeID" );
	
	// GET POST CONTENT
	if( ($ct=self::getPostInfo($nodeID,'full')) == null ) return false;
	$content = $ct['post_content'];
	
	// GET EXPORT DATA
	$s = stripos( $content, '<!--{NETBLOG_EXPORT}');
	$e = strpos( $content, '-->', $s );
	if( $s !== false && $e !== false )
		return self::rsc_importNodeData( substr($content, $s+20, $e-$s-20 ) , $nodeID );
	return false;
}


/**
 * Finalize an import session.
 * 
 * @deprecated Not used any more. Will be removed in future versions.
 * 
 * @return void.
 */
static public function rsc_importFinalize()
{
	// PREPARE
//	self::$clearImportMem = true;
//	
//	// CHECK FOR IMPORT-LOG FILE
//	$method = 1;		// a bit faster faster (about 0.01s for 1000 entries)
//	if( $method == 1 ) {	
//		$ct = @file_get_contents(dirname(__FILE__).'\import.log');
//		$pid = $pfoot = -1; $max = strlen($ct);
//		while( $pid !== false && $pfoot !== false ) {
//			$pid = strpos($ct,'id:',$pid+1);
//			$pfoot = strpos($ct,'footprint:',$pfoot+1);
//	
//			if( $pid === false || $pfoot === false ) break;
//	
//			//$id = substr($ct,$pid+3, strpos($ct,' ',$pid+3)-$pid-3);			
//			$id = trim(substr($ct,$pid+3, $pfoot-$pid-3));
//			$foot = trim(substr($ct,$pfoot+10, strpos($ct, ' ', $pfoot+10)-$pfoot-10));
//			 
//			if( strlen($id) > 0 && strlen($foot) > 0 )
//				self::$tasks_import[] = array( 'parID'=>$id, 'footprint'=> $foot );
//		}		
//	} else {
//		$ct = @file_get_contents(dirname(__FILE__).'\import.log');
//		$ar = explode(' ',$ct);
//		$id = $pfoot = '';
//		foreach($ar as $v ){
//			if( $id != '' && $pfoot != '' ) {
//				self::$tasks_import[] = array( 'parID'=>$id, 'footprint'=> $pfoot );
//				$id = $pfoot = '';
//			}
//			if( substr($v,0,3) == 'id:' ) //strpos($v,'id:') == 0 )
//				$id = trim(substr($v,3));
//			if( substr($v,0,10) == 'footprint:' )
//				$pfoot = trim(substr($v,10));
//		}			
//	}	
//	
//	// DO LAST IMPORT-TRY
//	foreach( self::$tasks_import as $atts ) {
//		$id = self::footprt_getPostIDByFootprint( $atts['footprint'] );
//		if( $id != null )
//			self::rsc_addAdj( $atts['parID'], $id, false );
//		else if( Netblog::options()->useFootprints() ) {
//			if( ($m=self::footprt_getMetaFromServer( $atts['footprint'] )) == null ) continue;
//			self::rsc_addAdj( $atts['parID'], $m['uri'], true, $m['title'], $atts['footprint'] );
//		}
//	}	
//	
//	self::log("IMPORT finalized");
//	
//	if( self::$clearImportMem ) {
//		self::$tasks_import = array();
//		file_put_contents(dirname(__FILE__).'/import.log', "");
//	}
}


/**
 * Import resources from embedded export data for all posts/pages.
 *
 * @param boolean $clear_before TRUE if to detached all resources of those links with embedded export data prior import.
 */
static public function rsc_importAll( $clear_before = false )
{
	global $wpdb;
	$query = "	SELECT p.ID FROM $wpdb->posts p
				WHERE p.post_content LIKE '%<!--{NETBLOG_EXPORT}%'
	";
	$r = $wpdb->get_results($query);
	foreach($r as $o ) {
		self::rsc_importNode($o->ID);
	}
}


/**
 * Remove all embedded Export Data in post and pages.
 *
 */
static public function rsc_rmExportData()
{
	global $wpdb;
	$query = "	SELECT p.ID, p.post_content FROM $wpdb->posts p
				WHERE p.post_content LIKE '%<!--{NETBLOG_EXPORT}%'
	";
	$r = $wpdb->get_results($query);
	
	foreach($r as $o ) {
		// GET EXPORT DATA
		$s = stripos( $o->post_content, '<!--{NETBLOG_EXPORT}');
		$e = strpos( $o->post_content, '-->', $s );
		if( $s !== false && $e !== false ) {
			$o->post_content = addslashes(substr($o->post_content,0,$s) . substr($o->post_content,$e+3));
			$query2 = "UPDATE `$wpdb->posts` p SET post_content = '$o->post_content' WHERE p.ID = '$o->ID'";
			$wpdb->query($query2);
		}		
	}
	
	Netblog::log("Removed EED from WP articles");
}


/**
 * Strip the export tag from a string literal and return cleared string.
 *
 * @param string $string String with export tag.
 * @return string Cleared string
 */
static public function rsc_stripExportTag( $string )
{
	$s = stripos( $string, '<!--{NETBLOG_EXPORT}');
	$e = strpos( $string, '-->', $s );
	if( $s !== false && $e !== false ) 
		$string = substr($string,0,$s) . substr($string,$e+3);
	return $string;
}


//----------------------------------------------------------------------------------
// URI
//----------------------------------------------------------------------------------


/**
 * Add an URI and get its ID.
 *
 * @deprecated This method has been replaced with nbLinkExtern::Create() since Netblog 2.0.b6
 * @param string $uri A valid URI.
 * @param string $title The title of the URI.
 * @param string $footprint Optional. Footprint id.
 * @return int The URI's id.
 */
static public function uri_add( $uri, $title, $footprint = '' )
{
	global $wpdb; 
	$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
	$uri = trim(addslashes($uri));
	$uriTitle = trim(addslashes( strip_tags($title) ));
		
	$id = self::uri_getID($uri);	
	if( $id == -1 ) {
		$wpdb->query("INSERT INTO `$ext` (uri,uri_title,footprint) VALUES ('$uri','$title','$footprint')");
		$id = $wpdb->insert_id;
	} else $wpdb->query("UPDATE `$ext` SET refs = refs+1 WHERE uri_id = $id");
	return $id;		
}


/**
 * Remove an Uri; all possible attachements to posts/pages will be removed.
 *
 * @deprecated This method has been replaced with nbLinkExtern::Remove() since Netblog 2.0.b6
 * @param string $uri A valid URI.
 * @return boolean TRUE on success, FALSE on failure OR uri not found.
 */
static public function uri_rm( $uri )
{
	global $wpdb;
	$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
	$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
	
	$id = self::uri_getID($uri);
	$wpdb->query("DELETE FROM `$rel` WHERE uri_id = $id");
	$res = $wpdb->query("DELETE FROM `$ext` WHERE uri_id = $id");
	return !($res == false || $res <= 0);		
}


/**
 * Detach a given URI from a single Wordpress post/page. Uri will be removed if required.
 * 
 * @param string $uri A valid Uri.
 * @param int $nodeID A valid Wordpress post/page id.
 * @return boolean TRUE of success, FALSE on failure OR uri not found.
 */
static public function uri_detach( $uri, $nodeID )
{
	return self::rsc_rmAdj( $nodeID, $uri, true );
}


/**
 * Update the title of a given uri.
 *
 * @deprecated This method has been replaced by nbLinkExtern::Save() since Netblog 2.0.b6
 * @param string $uri A valid uri.
 * @param string $title The new title of the given uri.
 * @param int $flag Optional. The new flag of the given uri.
 * @return boolean TRUE on success, FALSE on failure OR uri not found.
 */
static public function uri_update( $uri, $title, $flag = -1 )
{
	global $wpdb;
	$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
	$uri = addslashes($uri);
	$uriTitle = addslashes( strip_tags($uriTitle) );
	if( strlen($uriTitle) == 0 ) return false;	
	
	$flag>=0 ? $queryFlag = ", flag = '$flag'" : $queryFlag = '';
	
	$res = $wpdb->query("UPDATE `$ext` SET uri_title = '$uriTitle'$queryFlag WHERE uri = '$uri'");
	return !($res == false || $res <= 0);	
}


/**
 * Update all uri's properties with a unique uri id.
 *
 * @deprecated This method has been replaced by nbLinkExtern::Save() since Netblog 2.0.b6
 * @param int $uri_id The URI's id; use self::uri_getID() to get it's valid id.
 * @param string $uri The new URI address.
 * @param string $title The new title.
 * @param int|-1 $flag Optional. Update an Uri's flag. Use self::uri_flagID() to set a valid flag code. 
 * @return boolean TRUE on success, FALSE on failure OR uri not found.
 */
static public function uri_updateFull( $uri_id, $uri, $title, $flag = -1 )
{
	global $wpdb;
	$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
	$uri = trim(addslashes($uri));
	$flag = (int) $flag;
	$uriTitle = trim(addslashes( strip_tags($title) ));
	
	if( strlen($uriTitle) == 0 ) return false;	

	$res = array();
	if( $flag < 0 || !is_int($flag) )
		$res = $wpdb->query("UPDATE `$ext` SET uri_title = '$uriTitle', uri = '$uri' WHERE uri_id = $uri_id");
	else $res = $wpdb->query("UPDATE `$ext` SET uri_title = '$uriTitle', uri = '$uri', flag = '$flag' WHERE uri_id = $uri_id");
	return !($res == false || $res <= 0);
}


/**
 * Get an array of an URI's information.
 *
 * @deprecated This method has been replaced with nbLinkExtern::LoadByUri() since Netblog 2.0.b6
 * @param string $uri A valid URI.
 * @return array An array of the URI's information, with [field name] => [field value]; NULL if uri not found.
 */
static public function uri_get( $uri )
{
	global $wpdb; 
	$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
	$uri = addslashes($uri);
	return $wpdb->get_row("SELECT * FROM `$ext` WHERE uri = '$uri'", ARRAY_A);
	
}


/**
 * Get an array of URI's that match certain criteria.
 *
 * @deprecated This method has been replaced with nbLinkExtern::FindUris() since Netblog 2.0.b6
 * @param mixed $orderBy [STRING]: A comma separated string of valid uri field names, like 'id,title-desc,title'; [ARRAY]: an array of field names, with '-desc' optionally, like [] => [field name].
 * @param int|-1 $limit Optional. Limit the maximum number of return uris; cannot be 0.
 * @param mixed|null $flag Optional. The uris' flags, either as a valid flag id or as a valid flag name; use self::uri_flagID() to get a valid id.
 * @param int|0 $refs Optional. 
 * @return array A 2-dim array of uris and their information; null if nothing matched.
 */
static public function uri_filtered( $orderBy, $limit = -1, $flag = NULL, $refs = 0  )
{
	global $wpdb;
	$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
	$desc = strtoupper($desc);
	
	// VERIFY
	if( is_numeric($orderBy) ) return false;
	if( is_string($orderBy) )
		$orderBy = explode(',', $orderBy);
			
	$q = "SELECT * FROM `$ext`";
	
	$order = Array();
	$validTbl = array('uri_id'=>'uri_id','id'=>'uri_id','title'=>'uri_title','uri_title'=>'uri_title','refs'=>'refs','flag'=>'flag');
	foreach( $orderBy as $by ) {
		if( is_string($by) ) $by = explode(' ',$by);					
		if( !isset($by[0]) || !isset($validTbl[$by[0]]) || isset($order[$validTbl[$by[0]]]) ) continue;
		
		$t = $validTbl[$by[0]];
		if( isset($by[1]) && strtolower($by[1]) == 'desc' )
			$t .= ' DESC';
		$order[$validTbl[$by[0]]] = $t;
	}
	
	if( $flag != null && self::uri_flagID($flag) >= 0 )
		$flag = self::uri_flagID($flag);
	
	// BUILD SQL-CMD
	$where = false;
	if( $flag != null ) {
		$q .= " WHERE flag = '$flag'";
		$where = true;
	}
	if( $refs > 0 ) {
		if( $where ) $q .= " AND refs = '$refs'";
		else $q .= " WHERE refs = '$refs'";
		$where = true;
	}
	if( sizeof($order) > 0 )
		$q .= " ORDER BY ".implode(', ',$order);
	if( is_numeric($limit) && $limit > 0 )
		$q .= " LIMIT $limit";

	return $wpdb->get_results($q,ARRAY_A);	
}


/**
 * Match URI's for keywords and additional criteria.
 *
 * @deprecated This method has been replaced with nbLinkExternCollection::MatchUri() since Netblog 2.0.b6
 * @param string $match A string of words to be matched in uri and title.
 * @param mixed $orderBy [STRING]: A comma separated string of valid uri field names, like 'id,title-desc,title'; [ARRAY]: an array of field names, with '-desc' optionally, like [] => [field name].
 * @param int|-1 $limit Optional. Limit the maximum number of return uris; cannot be 0.
 * @param mixed|null $flag Optional. The uris' flags, either as a valid flag id or as a valid flag name; use self::uri_flagID() to get a valid id.
 * @param int|0 $refs Optional. 
 * @return array A 2-dim ARRAY of uris and their information; NULL if nothing matched.
 */
static public function uri_match( $match, $orderBy, $limit = -1, $flag = NULL, $refs = 0 )
{
	global $wpdb;
	$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
	$rel = $wpdb->prefix . Netblog::options()->getServerTableRelExtnodes();
	
	// VERIFY
	if( is_numeric($orderBy) ) return false;
	if( is_string($orderBy) )
		$orderBy = explode(',', $orderBy);		
	
	$q = "	SELECT e.uri_id, e.uri, e.uri_title, e.flag, e.refs, p.post_title
			FROM `$ext` e, $wpdb->posts p, `$rel` rel
			WHERE rel.uri_id = e.uri_id
			AND rel.id = p.ID";
			
	if( is_string($match) ) {
		$match = addslashes($match);
		$q .= "
			AND (e.uri LIKE '%$match%' || e.uri_title LIKE '%$match%' || p.post_title LIKE '%$match%' )			
		";
	} else if( is_array($match) ) {
		$t = '';
		foreach($match as $query)
		if( isset($query['query']) && isset($query['logical']) ) {
			$m = addslashes($query['query']);
			
			$log = strtolower($query['logical']) == 'or' ? 'OR' : 'AND';
			if( strlen($t)==0 ) $log = '';
			
			$t .= "$log (e.uri LIKE '%$m%' || e.uri_title LIKE '%$m%' || p.post_title LIKE '%$m%') ";
		}
		if( strlen($t) > 0 )
			$q .= " AND ($t) ";
	}
	
	$order = Array();
	$validTbl = array('uri_id'=>'e.uri_id','id'=>'e.uri_id','title'=>'e.uri_title','uri_title'=>'e.uri_title'
			,'refs'=>'e.refs','flag'=>'e.flag','post_title'=>'p.post_title','post_id'=>'p.ID');
	foreach( $orderBy as $by ) {
		if( is_string($by) ) $by = explode('-',$by);					
		if( !isset($by[0]) || !isset($validTbl[$by[0]]) || isset($order[$validTbl[$by[0]]]) ) continue;
		
		$t = $validTbl[$by[0]];
		if( isset($by[1]) && strtolower($by[1]) == 'desc' )
			$t .= ' DESC';
		$order[$validTbl[$by[0]]] = $t;
	}
	
	if( $flag != null && self::uri_flagID($flag) >= 0 )
		$flag = self::uri_flagID($flag);
	
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
//var_dump($match,$q);
	return $wpdb->get_results($q,ARRAY_A);	
}


/**
 * Get an URI's ID.
 *
 * @deprecated This method has been replaced with nbLinkExtern::LoadByUri() and nbLinkExtern::GetID() since Netblog 2.0.b6
 * @param string $uri A valid URI to be found in the database.
 * @return int The URI's id; -1 if uri was not found.
 */
static public function uri_getID( $uri )
{
	global $wpdb; 
	$ext = $wpdb->prefix . Netblog::options()->getServerTableExt();
	$uri = addslashes($uri);
	$r = $wpdb->get_row("SELECT * FROM `$ext` WHERE uri = '$uri'",ARRAY_A);
	if( $wpdb->num_rows > 0 ) return $r['uri_id'];
	else return -1;	
}


/**
 * Get a valid Uri flag code/id from a given nicename.
 *
 * @deprecated This method has been replaced with nbLinkExtern::GetFlagLUT() since Netblog 2.0.b6
 * @param string $nicename An human-readable equivalent for a flag code/id. 
 * @return int The flag id for a given flag name; -1 if invalid flag name.
 */
static public function uri_flagID( $nicename )
{
	$c = array(
		'normal' => 0,
		'online' => 0,
		'offline' => 1,
		'trash' => 2,
		'trashed' => 2,
		'erase' => 3,
		'restore' => 4,
		'lock' => 99,
		'locked' => 99,
		'unlock' => 100,
		'unlocked' => 100
	);
	return isset($c[$nicename]) ? $c[$nicename] : -1;
}





//----------------------------------------------------------------------------------
// CAPTION
//----------------------------------------------------------------------------------

/**
 * Attach a caption to a given host.
 *
 * @deprecated Replaced with safer nbCaption::__construct($name,$type) and nbCaption::Save() since Netblog 2.0.b6
 * @param string $name A site-wide unique name for this caption.
 * @param string $type The labelled type for this caption, e.g. equation, table, figure, chapter.
 * @param int $host The host id this caption should be attached to; equivalent to post/page id in Wordpress.
 * @param mixed $figure The number for this caption, e.g. an integer or alpha-characters like 'aacw'.
 * @param int|-1 $position The position in a post/page; if -1, caption will be at the end of the currrent posts list.
 * @param string $title The title of the new caption; a nicename and human-readable.
 * @param string $printFormat A valid php-coded string with '$number' to format the print out of the caption, e.g. '($number)'. 
 * @param string|'decimal' $numberFormat Optional; The numbering format of the given caption; might be automatically overriden.
 * @param string|'inline' $display Optional; where to print the caption; might be automatically overriden.
 * @return boolean TRUE on success OR update, FALSE on failure.
 */
static public function cpt_add ($name, $type, $host, $figure, $position = '-1', $title, $printFormat, $numberFormat = 'decimal', $display = 'inline' )
{
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
		
	if( $type == '' ) $type = self::cpt_recoverType($name);	
	
	// APPLY GLOBAL STYLE
	$optG = self::cptg_get($type);
	if( $optG != null && $optG['active'] ) {
		$printFormat = $optG['print_format'];
		$numberFormat = $optG['number_format'];		
	}

	// PREPARE
	if( strlen($name) == 0 || strlen($type) == 0 || strlen($host) == 0 || strlen($figure) == 0 || strlen($position) == 0
		|| strlen($numberFormat) == 0 || strlen($display) == 0 ) 
		return false;
	
	
	// CONVERT DECIMAL FIGURE TO NUMBER FORMAT
	if( is_numeric($figure) )
		$figure = nbcpt::increment($figure,$numberFormat,0);
	
	
	// AUTOMATICALLY ADD NEW TYPE STYLES
	if( $optG == null && current_user_can( Netblog::options()->getCaptionPrivGadd() ) ) {
		self::cptg_set($type, $numberFormat, $printFormat, $display );
	}
	if( self::cpt_exists($name,$type) ) {
		self::cpt_update($name,$type, $host, $figure,$title,$printFormat);
		return self::cpt_reorder($name,$type,$host,$position);
	}
	
	$name = addslashes($name);
	$type = addslashes($type);
	$figure = addslashes($figure);
	$title = addslashes($title);
	$printFormat = addslashes($printFormat);
	
	
	// NEW CAPTION
	$count = self::cpt_countInHost($host);
	if( $position < 0 )
		$position = $count;
	else if( $position >= $count ) $position = $count;
	else {
		$wpdb->query("UPDATE `$capt` SET local_order = local_order+1 WHERE host = $host AND type = '$type' AND local_order >= $position");
	}
	
	// HOST ORDER - INSERT AT BOTTOM
	$host_order = self::cpt_getHostOrder($host);
	if( $host_order == -1 )
		$host_order = self::cpt_getHostOrderMax() + 1;
	
	$figure = $host_order . '.' . $figure;
		
	$query = "INSERT INTO `$capt`	(name,`type`,num,host,local_order,title,print,host_order) 
				VALUES ('$name','$type','$figure','$host','$position','$title','$printFormat','$host_order');
	";

	$wpdb->query($query);	
	if( $wpdb->num_rows == 0 ) return false;
		 
	return true;	
}


/**
 * Update an existing caption.
 *
 * @deprecated Replaced with safer nbCaption::Save() since Netblog 2.0.b6
 * @param string $name A caption name.
 * @param string $type The type of the caption; name and type together are unique for each caption.
 * @param int $host The Wordpress post/page id.
 * @param mixed $numbering The caption number, integer or alpha-characters with points being allowed.
 * @param string $title The caption title.
 * @param string $printFormat A valid php-coded string with '$number' to format the print out of the caption, e.g. '($number)'.
 * @return boolean TRUE on success, FALSE on failure OR nothing updated.
 */
static public function cpt_update( $name, $type, $host, $numbering, $title, $printFormat )
{	
	$c = self::cpt_get($name,$type);
	if( $c == null ) return false;
	$id = $c['id'];
	
	$name = addslashes($name);
	$type = addslashes($type);
	$numbering = addslashes($numbering);
	$title = addslashes($title);
	$printFormat = addslashes($printFormat);

	$host_order = self::cpt_getHostOrder($host);
	$numbering = $host_order . '.' . $numbering;
	
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();	
	$wpdb->query("UPDATE `$capt` SET num = '$numbering', title = '$title', print = '$printFormat' WHERE id = $id");	
	return true;
}


/**
 * Remove an existing caption.
 *
 * @deprecated Replaced with safer nbCaption::Remove() since Netblog 2.0.b6
 * @param string $name A caption name.
 * @param string $type The type of the caption; name and type together are unique for each caption.
 * @return boolean TRUE on success, FALSE on failure or not found.
 */
static public function cpt_rm( $name, $type )
{
	if( !self::cpt_exists($name,$type) ) return false;
	
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();

	extract( self::cpt_get($name,$type) );
	$wpdb->query("UPDATE `$capt` SET local_order = local_order-1 WHERE host = $host AND type = '$type' AND local_order > $local_order");
	$d = $wpdb->query("DELETE FROM `$capt` WHERE id = $id");
	if( self::cpt_countInHost($host) == 0 )
		$wpdb->query("UPDATE `$capt` SET host_order = host_order-1 WHERE host_order > $host_order");
		
	return $d > 0;	
}


/**
 * Remove all captions within a given host; no rebuild of caption numbers.
 *
 * @deprecated Replaced with safer class nbCaption::LoadByHost() and nbCaption::Remove() since Netblog 2.0.b6
 * @param int $host A Wordpress post/page id.
 * @param boolean $rebuild TRUE if to rebuild after success.
 * @return boolean TRUE on success, FALSE on failure OR nothing removed OR host not found.
 */
static public function cpt_rmByHost( $host, $rebuild = false )
{
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
	$h = $wpdb->get_row("SELECT * FROM `$capt` WHERE host = '$host' LIMIT 1", ARRAY_A);
	if( $h == null ) return false;
	extract($h);
	
	$d = $wpdb->query("DELETE FROM `$capt` WHERE `host` = '$host'");
	$wpdb->query("UPDATE `$capt` SET host_order = host_order-1 WHERE host_order > $host_order");
	if($d>0)
		
	return $d>0 && $rebuild ? self::cptg_rebuild() : $d>0;
}


/**
 * Get the attributes of a given caption.
 *
 * @deprecated Replaced with safer nbCaption::__construct($name,$type) since Netblog 2.0.b6
 * @param string $name The name of the caption.
 * @param string $type The type of the caption; name and type together are unique for each caption.
 * @return array An array of the caption's information, with [field name] => [field value]; NULL if caption not found AND/OR could not be recovered from name.
 */
static public function cpt_get( $name, $type )
{
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
	
	$c = $wpdb->get_row("SELECT * FROM `$capt` WHERE name = '$name' AND type = '$type'", ARRAY_A);
	if( $c == null ) {
		$typeRec = self::cpt_recoverType($name);
		if( $typeRec != $type )
			return $wpdb->get_row("SELECT * FROM `$capt` WHERE name = '$name' AND type = '$typeRec'", ARRAY_A);
	} else return $c;
}


/**
 * Get an array of all used caption types.
 * 
 * @deprecated Replaced with safer class nbCaptionType::LoadAll() since Netblog 2.0.b5
 * @return ARRAY[typename]{count,names{},hosts{}}|NULL
 */
static public function cpt_getTypes()
{
	$r = array();
	
	$a = self::cpt_getAll();
	if($a!=null && is_array($a) )
	foreach($a as $row){
		$t = isset($r[$row['type']]) ? $r[$row['type']] : array('count'=>0,'names'=>array(),'hosts'=>array()); 
		
		$t['count']++;
		$t['names'][] = $row['name'];
		$t['hosts'][$row['host']] = $row['host'];
		
		$r[$row['type']] = $t;
	}
	
	return sizeof($r)>0 ? $r : null;	
}

/**
 * Get an array of all captions.
 *
 * @deprecated Replaced with safer nbCaption::LoadAll() since Netblog 2.0.b6
 * @param int $output ARRAY_A | OBJECT  
 * @return array A 2-dim array of all captions, with [] => { [field name] => [field value] }
 */
static public function cpt_getAll( $output = ARRAY_A )
{
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
	return $wpdb->get_results("SELECT * FROM `$capt` ORDER BY name, id", $output );
}


/**
 * Get an array of all captions within a given host.
 *
 * @deprecated Replaced with safer nbCaption::LoadByHost() since Netblog 2.0.b6
 * @param int $host A Wordpress post/page id.
 * @return array A 2-dim array of all captions, with [] => { [field name] => [field value] }
 */
static public function cpt_getByHost( $host )
{
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
	return $wpdb->get_results("SELECT * FROM `$capt` WHERE host = '$host'", ARRAY_A );
}


/**
 * Checks whether a given caption exists.
 *
 * @deprecated Replaced with safer nbCaption::HasCaption() since Netblog 2.0.b6
 * @param string $name A caption name.
 * @param string $type The type of the caption; name and type together are unique for each caption.
 * @return boolean TRUE if found, FALSE if not found.
 */
static public function cpt_exists( $name, $type )
{
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();

	return $wpdb->get_row("SELECT * FROM `$capt` WHERE name = '$name' AND type = '$type'") != null;
}


/**
 * Checks whether a given caption exists.
 *
 * @deprecated Replaced the same way as nbdb::cpt_exists() has been replaced, since Netblog 2.0.b6
 * @param string $name A caption name.
 * @return boolean TRUE if found, FALSE if not found.
 */
static public function cpt_existsName( $name )
{
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();

	return $wpdb->get_row("SELECT * FROM `$capt` WHERE name = '$name'") != null;
}


/**
 * Checks whether a given caption exists in a given hosts, possibly at a certain position.
 *
 * @deprecated Replaced with safer nbCaption::__construct($name,$type) and nbCaption::GetHost() since Netblog 2.0.b6
 * @param string $name A caption name.
 * @param string $type The type of the caption; name and type together are unique for each caption.
 * @param int $host The Wordpress post/page id.
 * @param int|-1 $position Optional; the positional order of the caption. 
 * @return boolean TRUE if exists, FALSE if criteria are not met.
 */
static public function cpt_existsInHost( $name, $type, $host, $position = -1 )
{
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
	
	if( $position < 0 )
		return $wpdb->get_row("SELECT * FROM `$capt` WHERE name = '$name' AND host = $host AND type = '$type'") != null;
	else  return $wpdb->get_row("SELECT * FROM `$capt` WHERE name = '$name' AND host = $host AND type = '$type' AND local_order = $position") != null;	
}


/**
 * Get the order number of a given caption.
 *
 * @deprecated Replaced with safer nbCaption::GetHostOrderIndex() or nbCaption::GetCaptionOrderIndex() since Netblog 2.0.b6
 * @param string $name The name of the caption.
 * @param string $type The type of the caption; name and type together are unique for each caption.
 * @return int The order number; -1 if caption not found.
 */
static public function cpt_getOrder( $name, $type )
{
	$r = self::cpt_get($name,$type);
	if( isset($r['local_order']) )
		return $r['local_order'];
	else return -1;	
}


/**
 * Check whether a given caption has a certain order number.
 *
 * @deprecated Replaced with safer nbCaption::GetHostOrderIndex() or nbCaption::GetCaptionOrderIndex() since Netblog 2.0.b6
 * @param string $name The name of the caption.
 * @param string $type The type of the caption; name and type together are unique for each caption.
 * @param int $orderNum The order number to check for.
 * @return boolean TRUE of condition is met, FALSE if not.
 */
static public function cpt_hasOrder( $name, $type, $orderNum )
{
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();	
	return $wpdb->get_row("SELECT * FROM `$capt` WHERE name = '$name' AND type = '$type' AND local_order = '$orderNum'") != null;
}


/**
 * Reorder an existing caption within a its host.
 *
 * @deprecated Not used anymore with class nbCaption since Netblog 2.0.b6
 * @param string $name The name of the caption.
 * @param string $type The type of the caption; name and type together are unique for each caption.
 * @param int $host The Wordpress post/page id.
 * @param int $position The new order number.
 * @return boolean TRUE on success, FALSE on failure.
 */
static public function cpt_reorder( $name, $type, $host, $position )
{
	if( self::cpt_existsInHost($name,$type,$host,$position) ) return false;	
	if( !self::cpt_existsInHost($name,$type,$host) ) return false;
		
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
	
	$count = self::cpt_countInHost($host);

	if( $position < 0 ||  $position >= $count )
		$position = $count-1;
	
	$caption = self::cpt_get($name,$type);
	$posOld = $caption['local_order'];
	$captionID = $caption['id'];
	
	$wpdb->query("UPDATE `$capt` SET local_order = local_order-1 WHERE host = $host AND type = '$type' AND local_order > $posOld");	
	$wpdb->query("UPDATE `$capt` SET local_order = local_order+1 WHERE host = $host AND type = '$type' AND local_order >= $position");
	$wpdb->query("UPDATE `$capt` SET local_order = $position WHERE id = $captionID");	

	return true;	
}


/**
 * Try to recover the caption type of a given caption name.
 *
 * @deprecated Replaced with safer nbCaption::__construct() since Netblog 2.0.b6
 * @param string $name The caption name.
 * @return string The recovered caption type on success; an empty string if caption name not found or ambiguous.
 */
static public function cpt_recoverType( $name )
{
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();

	$c = $wpdb->get_results("SELECT * FROM `$capt` WHERE name = '$name' LIMIT 2", ARRAY_A);
	if( $wpdb->num_rows == 1 ) return $c[0]['type'];
	else return '';	
}


/**
 * Count the number of caption found in a given host.
 *
 * @deprecated Replaced with safer nbCaption::CountCaptions() since Netblog 2.0.b6
 * @param int $host A Wordpress post/page id.
 * @return int The number of caption in $host.
 */
static public function cpt_countInHost( $host )
{
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
	$wpdb->query("SELECT id FROM `$capt` WHERE host = $host");
	return $wpdb->num_rows;	
}


/**
 * Get the order number of a given host; hosts are numbered/ordered site-wide.
 *
 * @deprecated Replaced with safer nbCaption::GetHostOrderNum() since Netblog 2.0.b6
 * @param int $host A Wordpress post/page id.
 * @return int The found order number of $host.
 */
static public function cpt_getHostOrder( $host )
{
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
	$r = $wpdb->get_row("SELECT host_order FROM `$capt` WHERE host = '$host' LIMIT 1", ARRAY_A);	
	if( $wpdb->num_rows > 0 )
		return $r['host_order'];
	else return -1;		
}


/**
 * Get the maximum order number of registered hosts.
 *
 * @deprecated Replaced with nbCaption::GetHostOrderMaxNum() since Netblog 2.0.b6
 * @return int The maximum order number; 0 if nothing found; order numbers start with 1.
 */
static public function cpt_getHostOrderMax ()
{
	global $wpdb;
	$capt = $wpdb->prefix . Netblog::options()->getServerTableCaptions();
	$r = $wpdb->get_row("SELECT host_order FROM `$capt` ORDER BY host_order DESC LIMIT 1", ARRAY_A);
	if( $wpdb->num_rows > 0 )
		return $r['host_order'];
	else return 0;	
}


//----------------------------------------------------------------------------------
// GLOBAL CAPTIONS (LIKE TEMPLATES)
//----------------------------------------------------------------------------------

/**
 * Set a (new) caption type.
 *
 * @deprecated Replaced with safer nbCaptionType::__construct() and nbCaptionType::Save() since Netblog 2.0.b4
 * @param string $type The type of the caption, e.g. equation, figure, table.
 * @param string $numberFormat A human-readable number format, e.g. decimal, upper-alpha.
 * @param string $printFormat A valid php-code with $number to format the caption number.
 * @param string $display A human-readable display format, e.g. inline, left, right.
 * @param boolean $active Whether to make this caption as globally active, thus override all local formatting.
 * @param boolean $updateOnly Set TRUE to prevent creation of new global captions.
 * @return void
 */
static public function cptg_set( $type, $numberFormat = 'decimal', $printFormat = '($number)', $display = 'inline', $active = true, $updateOnly = false )
{
	// VERIFY
	if( self::cptg_numberCode($numberFormat) == -1 ) 
		$numberFormat = 'decimal';
	if( self::cptg_displayCode($display) == -1 ) 
		$display = 'inline';
	if( !is_bool($active) ) $active = true;	
	$type = strtolower($type);
	if( strlen($type) == 0 ) return;
	
	$set = Netblog::options()->getCaptionGlobals();
	
	if( $updateOnly && !isset($set[$type]) )
		return;
		
	$set[$type] = array( 'numberFormat' => $numberFormat,
						'printFormat' => $printFormat,
						'display' => $display,
						'isactive' => (bool)$active	);
	
	Netblog::options()->setCaptionGlobals($set);	
}


/**
 * Get attributes of a global caption.
 *
 * @deprecated Replaced with safer nbCaptionType::__construct() since Netblog 2.0.b4
 * @param string $type A global caption's type.
 * @return array An array of attributes, with [attribute name] => [attribute value]; NULL if caption type not found.
 */
static public function cptg_get( $type )
{
	$set = Netblog::options()->getCaptionGlobals();
	return isset($set[$type]) ? $set[$type] : array();
//	
//	$type = strtolower($type);
//	$opt = get_option( self::cptg_mkOptionName($type) );
//	if( !$opt ) return null;
//	return self::cptg_parse($opt);	
}


/**
 * Check if a given global caption type is set to be active, thus overriding all local instances.
 *
 * @deprecated Replaced with safer nbCaptionType::OverrideLocalSettings() since Netblog 2.0.b6
 * @param string $type Global caption type.
 * @return boolean TRUE if active, FALSE if not.
 */
static public function cptg_isActive( $type )
{
	$set = Netblog::options()->getCaptionGlobals();
	return isset($set[$type]['isactive']) ? $set[$type]['isactive'] : false;
}


/**
 * Get a list of all caption types.
 *
 * @deprecated Replaced with safer nbCaptionType::LoadAll() since Netblog 2.0.b6
 * @param boolean $includeParsedOptions TRUE if to include an array of caption attributes, FALSE if just to include caption type names.
 * @return array An array of caption types; with [type] => [type] OR [type] => { [attr name] => [attr value] }
 */
static public function cptg_getTypes( $includeParsedOptions = false )
{
	$set = Netblog::options()->getCaptionGlobals();
	if( $includeParsedOptions ) return $set;
	
	$out = array();
	foreach($set as $type=>$o)
		$out[$type] = $type;
	return $out;
}


/**
 * Check if a given caption type exists globally.
 *
 * @deprecated Replaced with safer nbCaptionType::ExistsInDatabase() since Netblog 2.0.b6
 * @param string $type A global caption's type.
 * @return boolean TRUE if found, FALSE if not.
 */
static public function cptg_exists( $type )
{
	$set = Netblog::options()->getCaptionGlobals();
	return isset($set[$type]);
}


/**
 * Remove a global caption.
 *
 * @deprecated Replaced with safer nbCaptionType::Remove() since Netblog 2.0.b6
 * @param string $type A global caption's type.
 * @return boolean TRUE on success, FALSE on failure OR not found.
 */
static public function cptg_rm( $type )
{
	$set = Netblog::options()->getCaptionGlobals();
	if( !isset($set[$type]) ) return true;
	
	unset($set[$type]);
	return Netblog::options()->setCaptionGlobals($set);
}


/**
 * Get the number code from a human-readable number format name.
 *
 * @deprecated Replaced with safer nbCaptionType::InterpretNumberName() since Netblog 2.0.b6
 * @param string $nicename A number format name, e.g. decimal, upper-alpha.
 * @return int The found number code; -1 if $nicename was invalid.
 */
static public function cptg_numberCode( $nicename )
{
	$a = array('decimal'=>1,'lower-alpha'=>2,
		'upper-alpha'=>3,'lower-roman'=>4,'upper-roman'=>5,
		'lower-greek'=>6);
	return isset($a[$nicename]) ? $a[$nicename] : -1;
}


/**
 * Get the human-readable number format name from a number code.
 *
 * @deprecated Replaced with safer nbCaptionType::InterpretNumberCode() since Netblog 2.0.b6
 * @param int $numberCode The number code for a caption.
 * @return string A human-readable number name.
 */
static public function cptg_numberName( $numberCode )
{
	$a = array('decimal'=>1,'lower-alpha'=>2,
		'upper-alpha'=>3,'lower-roman'=>4,'upper-roman'=>5,
		'lower-greek'=>6);
	return array_search( $numberCode, $a );
}


/**
 * Get the display code from a human-readable display format name.
 *
 * @deprecated Replaced with safer nbCaptionType::InterpretDisplayFormatName() since Netblog 2.0.b6
 * @param string $nicename A human-readable display format name.
 * @return int The display code; -1 if $nicename was invalid.
 */
static public function cptg_displayCode( $nicename )
{
	$d = array('inline'=>1,'left'=>2,'right'=>3);
	return isset($d[$nicename]) ? $d[$nicename] : -1;
}


/**
 * Get the human-readable display name from a display code for captions.
 *
 * @deprecated Replaced with safer nbCaptionType::InterpretDisplayCode() since Netblog 2.0.b6
 * @param int $displayCode A display code for captions.
 * @return string A human-readable display name.
 */
static public function cptg_displayName( $displayCode )
{
	$d = array('inline'=>1,'left'=>2,'right'=>3);
	return array_search( $displayCode, $d );
}


/**
 * Generate the Wordpress Option Name for Captions.
 *
 * @param string $type A caption type.
 * @return string A generated name for Wordpress Options.
 */
//static private function cptg_mkOptionName( $type )
//{
//	return nbdb::cptg_getPreName().'_'.$type;
//}


/**
 * Get the pre name for all global captions; used to identify (make unique) WP options
 *
 * @return string
 */
//static private function cptg_getPreName() { return 'netblog_gcaption'; }


/**
 * Parse an encoded option string into an array.
 *
 * @param string $option_string A valid option string.
 * @return array An array of attributes, with [attr name] => [attr value].
 */
//static private function cptg_parse( $option_string )
//{
//	$mapAct = array(true=>1,false=>2);
//		
//	$out = array();	
//	$t = explode('|',$option_string,2);
//	$code = str_split($t[0]);
//	$out['active'] = (bool) array_search( (int)($code[0]), $mapAct );
//	$out['display'] = self::cptg_displayName( (int)($code[1]) );
//	$out['number_format'] = self::cptg_numberName( (int)($code[2]) );
//	$out['print_format'] = $t[1];
//	return $out;
//}


/**
 * Rebuild all captions.
 * 
 * @deprecated Replaced with safer nbCaptionType() since Netblog 2.0
 * @return boolean TRUE on success, FALSE on failure.
 */
static public function cptg_rebuild()
{
	global $wpdb;
	$query = "SELECT p.ID FROM $wpdb->posts p
				WHERE (p.post_status = 'publish' || p.post_status = 'draft' || p.post_status = 'pending')
	";	
	$r = $wpdb->get_results($query);
	foreach( $r as $o )
		self::updatePostCaptions($o->ID);
	return true;
}




//----------------------------------------------------------------------------------
// FOOTPRINTS
//----------------------------------------------------------------------------------

/**
 * Request FootprintID from server and save it for given post id OR use local method for calculating footprint id
 *
 * @param int $postID Wordpress post/page id.
 * @return boolean TRUE on success, FALSE on failure.
 */
static public function footprt_create( $postID )
{
	if( !Netblog::options()->useFootprints() ) return false;
	if( ($meta=self::getPostInfo($postID)) == null ) {
		Netblog::logWarning("Creating a footprint stopped because of invalid WP post ID $postID");
		return false;
	}
	
	if( class_exists('footprintConnect') && Netblog::options()->useFootprintServer() ) {
		$footprint = new footprintConnect();
		
		if( self::footprt_hasFootprint($postID) && $footprint->get(self::footprt_getID($postID)) ) {
			self::log("FOOTPRINT ok for '$postID' with '".self::footprt_getID($postID)."' and $footprint->id ($footprint->error)");
			return false;
		}
		if( $footprint->find(get_permalink($postID)) ) {		
			self::log("FOOTPRINT restoring footprint for '$postID' with '$footprint->id' for '$footprint->uri'");				
			if( self::footprt_insert($postID,$footprint->id) ) {
				self::log("FOOTPRINT restored footprint for '$postID'");
				return true;
			} else return false;
		}
		
		if( !$footprint->create( get_permalink($meta['ID']), $meta['post_title'] ) ) {
			self::log("FOOTPRINT Failed to get new footprint from server ($footprint->errno: $footprint->error)");
			return false;
		}
		$pilot = new nbTestPilot();
		$pilot->footprintAddServerMode($footprint->id);
		$pilot->save();
		
		self::log("FOOTPRINT got new footprint from server for '$postID' with '$footprint->id'");
		return self::footprt_insert( $meta['ID'], $footprint->id );
	} else if( !self::footprt_hasFootprint($postID) ) {
		$post = &get_post($postID,OBJECT);
		$usr = get_userdata($post->post_author);
		$fid = md5($post->post_date.$post->post_title.$usr->nickname.time().get_bloginfo('url'));
		self::log("FOOTPRINT generated new footprint id for '$postID' with '$fid'");
		
		$pilot = new nbTestPilot();
		$pilot->footprintAddLocalMode($fid);
		$pilot->save();
		
		return self::footprt_insert($postID,$fid);
	}
	return false;
}


/**
 * Create footprints for all published posts/pages if not exists.
 *
 */
static public function footprt_createAll()
{		
	global $wpdb;
	$query = "	SELECT p.ID, p.post_title FROM `$wpdb->posts` p 
				WHERE p.post_status = 'publish' 
				AND (p.post_type = 'page' || p.post_type = 'post')
	";
	$r = $wpdb->get_results($query);
	if($r==null || !is_array($r) || sizeof($r)==0 )
		return false;

	if(!Netblog::options()->useFootprints()) {
		Netblog::log("Cannot repair footprints; feature disabled");
		return false;
	}

	$numRep = 0;
	foreach( $r as $o ) {
		self::log($o->ID);
		if( self::footprt_create($o->ID) )
			$numRep++;		
	}
			
	self::log("FOOTPRINT repaired $numRep out of ".sizeof($r)."");
	$pilot = new nbTestPilot();
	$pilot->footprintRepair(sizeof($r),$numRep);
	$pilot->save();
	return true;
}


/**
 * Update footprint server (uri and title only); server mode only!
 *
 * @param int $postID Wordpress post/page id.
 * @return bool TRUE if updated, FALSE otherwise.
 */
static public function footprt_update( $postID )
{
	if( ($meta=self::getPostInfo($postID)) == null ) return false;
	if( !Netblog::options()->useFootprints() || !Netblog::options()->useFootprintServer()
		|| !class_exists('footprintConnect')  ) 
		return false;
	
	$footprint = new footprintConnect();
	if( ($footprintID=self::footprt_getID($postID)) == null ) return false;
	
	return $footprint->update( $footprintID, get_permalink($meta['ID']), $meta['post_title'] );
}


/**
 * Get saved footprint id of given post/page id.
 *
 * @param int $postID Wordpress post/page id.
 * @return string FootprintID on success, NULL otherwise.
 */
static public function footprt_getID( $postID )
{
	global $wpdb;
	$footprt = $wpdb->prefix . Netblog::options()->getServerTableFootprints();
	$query = "SELECT * FROM `$footprt` WHERE id = '$postID'";
	$r = $wpdb->get_row($query);
	
	// CREATE IF REQUIRED
	if( $r==null && self::footprt_create($postID) )
		return self::footprt_getID($postID);
	
	return $r!=null ? $r->footprint : null; 
}


/**
 * Get post id by unique footprint; locally.
 *
 * @param string $footprintID
 * @return int|null Wordpress post/page id; NULL if not found locally.
 */
static public function footprt_getPostIDByFootprint( $footprintID )
{
	global $wpdb;
	$footprintID = addslashes($footprintID);
	
	$footprt = $wpdb->prefix . Netblog::options()->getServerTableFootprints();
	$query = "SELECT * FROM `$footprt` WHERE footprint = '$footprintID'";
	$r = $wpdb->get_row($query);
	return $r!=null ? $r->id : null;
}


/**
 * Retrieve meta data associated with given footprint from footprint server.
 *
 * @param int $footprintID FootprintID.
 * @return array|null An array with meta data, with {footprint_id|uri|title => values}; NULL on failure.
 */
static public function footprt_getMetaFromServer( $footprintID )
{
	if( !Netblog::options()->useFootprints() || !Netblog::options()->useFootprintServer()
		|| !class_exists('footprintConnect') ) 
		return false;
	
	$footprint = new footprintConnect();
	if( !$footprint->get( $footprintID ) ) {
		if( $footprint->status == 503 )
			self::$clearImportMem = false;
		return null;
	}
	self::log("RETRIEVED footprint meta data for '$footprintID'");
	
	$o = array();
	$o['footprint_id'] = $footprint->id;
	$o['uri'] = $footprint->uri;
	$o['title'] = $footprint->title;
	return $o;
}


/**
 * Checks if a given footprint id is saved locally.
 *
 * @param string $footprint FootprintID.
 * @param string $field Optional; which field to check for; Any of footprint | id.
 * @return boolean Whether given footprint id exists.
 */
static public function footprt_exists( $footprint, $field = 'footprint' )
{
	global $wpdb;
	$footprint = addslashes($footprint);
	if( $field != 'footprint' && $field != 'id' ) $field = 'footprint';
	
	$footprt = $wpdb->prefix . Netblog::options()->getServerTableFootprints();
	$query = "SELECT * FROM `$footprt` WHERE $field = '$footprint'";
	$r = $wpdb->get_row($query);
	return $r!=null;
}


/**
 * Checks if a given post/page has a footprint.
 *
 * @param int $postID Post/page id.
 * @return boolean TRUE if post has a footprint, FALSE otherwise.
 */
static public function footprt_hasFootprint( $postID )
{
	return self::footprt_exists($postID,'id');
}


/**
 * Insert footprint id in local database.
 *
 * @param int $postID Wordpress post/page id.
 * @param string $footprint Footprint id.
 * @return boolean Whether successfull.
 */
static private function footprt_insert( $postID, $footprint )
{
	global $wpdb;
	$footprint = addslashes(trim($footprint));
	if( strlen($footprint) == 0 ) return false;	
	$footprt = $wpdb->prefix . Netblog::options()->getServerTableFootprints();
	
	// SAVE TO DATABASE
	if( self::footprt_exists($footprint) ) {
		$query = "UPDATE `$footprt` SET id = '$postID' WHERE footprint = '$footprint'";
		//update_post_meta($nodeID,'_netblog_footprintid',$footprint);
	} else if( self::footprt_exists($postID,'id') ) {
		$query = "UPDATE `$footprt` SET footprint = '$footprint' WHERE id = '$postID'";		
	} else {
		self::log("FOOTPRINT new id '$footprint' put to database");		
		//add_post_meta($nodeID,'_netblog_footprintid',$footprint,true);
		$query = "INSERT INTO `$footprt` (id,footprint) VALUES('$postID','$footprint')";
	}
	
	
	// SAVE TO META KEYS FOR POSSIBLE EXPORT		
	$v = get_post_custom_values('_netblog_footprintid',$postID);
	$fid = $footprint;
	if(!is_array($v) || sizeof($v)!=1) {						
		self::log("FOOTPRINT create meta key for '$postID' with '$fid'");
		delete_post_meta($postID,'_netblog_footprintid');
		add_post_meta($postID,'_netblog_footprintid',$fid,true);
	} else if($v[0]!=$fid) {
		self::log("FOOTPRINT update meta key from '".$v[0]."' to '$fid' for '$postID'");
		update_post_meta($postID,'_netblog_footprintid',$fid);
	}

	$wpdb->query($query);	
	return $wpdb->rows_affected == 1;	
}


//----------------------------------------------------------------------------------
// GENERAL
//----------------------------------------------------------------------------------

/**
 * Get current server version
 *
 * @return string
 */
static public function getServerVersion() {
	global $wpdb;
	$r = $wpdb->db_version();
	if($r===false) $r = 'na';
	return $r;
}


/**
 * Get more information about a given Wordpress post/page.
 *
 * @param int $nodeID A Wordpress post/page id.
 * @param string $extended Any of minimal|full.
 * @return array An array of post information, with [field name] => [field value]; NULL on failure.
 */
static public function getPostInfo( $nodeID, $extended = 'minimal' )
{
	global $wpdb;
	$select = 'p.ID, post_title, user_nicename, post_date, post_status, post_type, post_mime_type';
	if( $extended == 'full' ) $select = '*';
	
	$query = "
	    SELECT $select
	    FROM $wpdb->posts p, $wpdb->users u
      	WHERE p.id = '$nodeID'
      	AND p.post_author = u.ID
	    AND (p.post_type = 'post' || p.post_type = 'page' || p.post_type = 'attachment' )
	    ORDER BY p.post_date DESC;
	";	
	    																							//UPDT_1.2 - page
	$adjs = $wpdb->get_results($query, ARRAY_A);
	if( $adjs == null ) return null;
	
	if( $adjs[0]['post_type'] == 'attachment' )
		$adjs[0]['post_type'] = $adjs[0]['post_mime_type'];
	unset($adjs[0]['post_mime_type']);
	
	if( $adjs[0]['post_status'] == 'inherit' )
		$adjs[0]['post_status'] = 'publish';
		
	return $adjs[0];		
}


/**
 * Get the type of a Wordpress post/page.
 *
 * @param int $nodeID A Wordpress post/page ID.
 * @return string The type of a Wordpress post/page; NULL on failure.
 */
static public function getPostType( $nodeID )
{
	$nd = self::getPostInfo($nodeID);
	return $nd!=null ? $nd['post_type'] : null;	
}


/**
 * Checks whether a given Wordpress post/page is of a certain type.
 *
 * @param int $postID A Wordpress post/page ID.
 * @param string $postType A Wordpress post type to check for.
 * @return boolean TRUE if a given post/page is of type $postType, FALSE otherwise.
 */
static public function isPostType( $postID, $postType )
{
	return self::getPostType($postID) == $postType;
}


/**
 * Check if given Wordpress post/page has a certain status.
 *
 * @param int $postID Wordpress post/page id.
 * @param string $postStatus Any of publish | draft | pending.
 * @return boolean TRUE if post has given status, FALSE if not.
 */
static public function isPostStatus( $postID, $postStatus )
{
	$nd = self::getPostInfo($postID);
	return $nd!=null ? $nd['post_status'] == $postStatus : false;
}




/**
 * Try to get a post id for given post title and attributes; must be a unique result.
 *
 * @param string $postTitle A post title.
 * @param string $user_niceName Optional; the user nicename.
 * @param string $permalink Optional; a permalink to post.
 * @param string $fingerPrint Optional; a certain, unique fingerprint of given post.
 * @return int POST ID on success, NULL on failure.
 */
static public function getPostID( $postTitle, $user_niceName = '', $permalink = '', $fingerPrint = '' )
{
	global $wpdb;
	$postTitle = addslashes($postTitle);
	$permalink = addslahes($permalink);
	$user_niceName = addslashes($user_niceName);
	
	// TRY DIRECT MATCH
	$query = "SELECT p.ID FROM $wpdb->posts p
				WHERE (p.post_type = 'post' || p.post_type = 'page')
				AND (p.post_status = 'publish' || p.post_status = 'draft' || p.post_status = 'pending')
				AND p.post_title = '$postTitle'
	";
	if( ($r=$wpdb->get_row($query)) != null && $wpdb->num_rows == 1)
		return $r->ID;
	
	// TRY USING PERMALINK
	$query = "SELECT p.ID FROM $wpdb->posts p
				WHERE p.guid = '$permalink'
				AND (p.post_type = 'post' || p.post_type = 'page')
				AND (p.post_status = 'publish' || p.post_status = 'draft' || p.post_status = 'pending')
	";
	if( ($r=$wpdb->get_row($query)) != null && $wpdb->num_rows == 1)
		return $r->ID;
	
	// TRY USING USERNICENAME
	$query = "SELECT p.ID FROM $wpdb->posts p, $wpdb->users u
				WHERE p.post_author = u.ID
				AND (p.post_type = 'post' || p.post_type = 'page')
				AND (p.post_status = 'publish' || p.post_status = 'draft' || p.post_status = 'pending')
				AND u.user_nicename = '$user_niceName'
				AND p.post_title = '$postTitle'	
	";
	if( ($r=$wpdb->get_row($query)) != null && $wpdb->num_rows == 1)
		return $r->ID;
		
	// TRY USING FINGER PRINT
	$query = "SELECT p.ID FROM $wpdb->posts p
				WHERE (p.post_type = 'post' || p.post_type = 'page')
				AND (p.post_status = 'publish' || p.post_status = 'draft' || p.post_status = 'pending')
				AND p.post_title = '$postTitle'
	";
	$r=$wpdb->get_results($query);
	foreach( $r as $o )
		if( $fingerPrint == self::getPostFingerPrint($o->ID) )
			return $o->ID;
	
	return null;
}


/**
 * Check if a given Wordpress post/page is new.
 *
 * @param int $postID Wordpress post/page id.
 * @return boolean TRUE if new, FALSE if not OR postID not found.
 */
static public function isPostNew( $postID )
{
	global $wpdb;

	$query = "	SELECT post_date, post_modified FROM $wpdb->posts p
				WHERE p.post_parent = '$postID'
				LIMIT 1	
	";
	return $wpdb->get_row($query) == null;
	
	
//	$query = "	SELECT post_date, post_modified FROM $wpdb->posts p
//				WHERE p.ID = '$postID'
//				AND p.post_date = p.post_modified
//				AND p.post_date_gmt = 0
//	";
//	$r = $wpdb->get_row($query);
//	return $r != null && ($i=strtotime($r->post_date)) !== false ? time()-$i < 30 : false;
}


/**
 * Encode a given string.
 *
 * @param string $string A string to encode.
 * @return string An encoded string.
 */
static public function encode( $string )
{
	return base64_encode($string);
}


/**
 * Decode a string.
 *
 * @param string $code An encoded string to decode.
 * @return string A decoded string.
 */
static public function decode( $code )
{
	return base64_decode($code);
}


/**
 * Update post captions in database. Only required if global caption types were changed.
 *
 * @param int $postID Post/page id.
 */
static public function updatePostCaptions( $postID )
{
	// PREPARE
	Netblog::$isSavePost = true;
	$_POST['post_ID'] = $postID;
	nbcpt::reset();

	// UPDATE CAPTION TABLE
	do_shortcode( get_page(stripslashes($postID))->post_content );
	
	// CLEAN CAPTION TABLE	- There might be unused captions in database.
	$captionsDB = nbdb::cpt_getByHost($postID);
	
	foreach( $captionsDB as $res )
		if( nbcpt::getCaptNumber($res['name'], $res['type']) == -1 )			// UNUSED CAPTION
			nbdb::cpt_rm($res['name'],$res['type']);

	Netblog::$isSavePost = false;
}


/**
 * Remove all options from database
 */
static public function removeOptions()
{
	global $wpdb;
	$wpdb->query("DELETE FROM `$wpdb->options` WHERE option_name LIKE 'netblog%'");	
}


/**
 * Remove database table
 * @param string $tableName
 */
static public function removeTable( $tableName )
{
	global $wpdb;
	if( $wpdb->get_var("SHOW TABLES LIKE '$tbl'") != $tbl )
		return true;
		
	$res = $wpdb->query("DROP TABLE `$tableName`");
	return $res === true || $res >= 0;	
}

/**
 * Log unformatted, new messages in core/log.txt;
 *
 * @param string $msg The message to be logged.
 */
static private function log( $msg )
{
	Netblog::log($msg);
}


/**
 * The import log/memory to be used in importFinalize() to ensure proper linkage within imported articles.
 *
 * @var array
 */
static private $tasks_import = array();


/**
 * Whether to clear import log/memory after importFinalize(); used to prevent clearance in case of temporary server unavailability.
 *
 * @var boolean
 */
static private $clearImportMem = false;

}

//var_dump( nbdb::rsc_isAdj(851,'http://en.wikipedia.org/wiki/Potterys',true) );

?>
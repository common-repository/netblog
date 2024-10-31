<?php
require_once 'nbClass.php';

class nbLinkIntern extends nbClass {
	
	/**
	 * Creates an instance of nbLinkIntern, an internal link between two wp posts
	 * 
	 * @param int $parent_post A wp post id
	 * @param int $child_post A wp post id
	 */
	protected function __construct( $parent_post, $child_post ) {
		$this->parent = $parent_post;
		$this->child = $child_post;
	}
	
	/**
	 * Creates a new link in the database between two wp posts
	 * @param int $parent_post The parent post id for the new link
	 * @param int $child_post The child post id for the new link
	 * @return nbLinkIntern Returns nbLinkIntern if the new link has been created in the database, null otherwise.
	 */
	static public function Create( $parent_post, $child_post ) {
		if( $t=self::Load($parent_post, $child_post) )
			return $t;
		global $wpdb;
		$net = $wpdb->prefix . Netblog::options()->getServerTableNet();
		$res = $wpdb->query("INSERT INTO `$net` (id,adj_id) VALUES ('$parent_post','$child_post')");
		return $res ? new nbLinkIntern($parent_post, $child_post) : null;
	}

	/**
	 * Loads all internal links for a given wp parent post.
	 * @param int $parent_post The wp parent post id for this link
	 * @param int $child_post The wp parent post id for this link
	 * @return array An array of nbLinkIntern if at least one link has been found, null otherwise.
	 */
	static public function Load( $parent_post, $child_post ) {
		global $wpdb;
		
		$net = $wpdb->prefix . Netblog::options()->getServerTableNet();
		$q = "SELECT id as parID, adj_id as childID FROM `$net` WHERE id = '$parent_post' AND adj_id = '$child_post'";
		if( ($r=$wpdb->get_row($q)) )
			return new nbLinkIntern($r->parID, $r->childID);
		return null;
	}
	
	/**
	 * Loads all internal links for a given wp parent post.
	 * @param int $parent_post The wp parent post id for which outgoing links should be loaded
	 * @return array An array of nbLinkIntern if at least one link has been found, null otherwise.
	 */
	static public function LoadByParent( $parent_post ) {
		$o = array();
		global $wpdb;
		
		$net = $wpdb->prefix . Netblog::options()->getServerTableNet();
		$q = "SELECT id as parID, adj_id as childID FROM `$net` WHERE id = '$parent_post'";
		$r = $wpdb->get_results($q);
		if(is_array($r))
			foreach($r as $rs)
				$o[] = new nbLinkIntern($rs->parID, $rs->childID);
		return sizeof($o)>0 ? $o : null;
	}
	
	/**
	 * Loads all internal links for a given wp child post.
	 * @param int $child_post The wp child post id for which incoming links should be loaded
	 * @return array An array of nbLinkIntern if at least one link has been found, null otherwise.
	 */
	static public function LoadByChild( $child_post ) {
		$o = array();
		global $wpdb;
		
		$net = $wpdb->prefix . Netblog::options()->getServerTableNet();
		$q = "SELECT id as parID, adj_id as childID FROM `$net` WHERE adj_id = '$child_post'";
		$r = $wpdb->get_results($q);
		if(is_array($r))
			foreach($r as $rs)
				$o[] = new nbLinkIntern($rs->parID, $rs->childID);
		return sizeof($o)>0 ? $o : null;
	}
	
	/**
	 * Loads all internal links in the database
	 * @return array Returns an array of nbLinkIntern if at least one link has been found, null otherwise. 
	 */
	static public function LoadAll() {
		$o = array();
		global $wpdb;
		
		$net = $wpdb->prefix . Netblog::options()->getServerTableNet();
		$q = "SELECT id as parID, adj_id as childID FROM `$net` ORDER BY id";
		$r = $wpdb->get_results($q);
		if(is_array($r))
			foreach($r as $rs)
				$o[] = new nbLinkIntern($rs->parID, $rs->childID);
		return sizeof($o)>0 ? $o : null;
	}
	
	/**
	 * Removes this internal link from database
	 * @return bool Returns true if this link has been removed from database, false otherwise
	 */
	public function Remove() {
		global $wpdb;
		$net = $wpdb->prefix . Netblog::options()->getServerTableNet();
		return (bool)$wpdb->query("DELETE FROM `$net` WHERE id = '$this->parent' AND adj_id = '$this->child'");
	}
	
	/**
	 * Gets the parent post id where this link is coming from
	 * @return int A wp post id
	 */
	public function GetParentID() {
		return $this->parent;
	}
	
	/**
	 * Gets the child post id where this link is directed to
	 * @return int A wp post id
	 */
	public function GetChildID() {
		return $this->child;
	}
	
	/**
	 * The parent wp post id for this link
	 * @var int
	 */
	private $parent = 0;
	
	/**
	 * The child wp post id for this link
	 * @var int
	 */
	private $child = 0;
	
}
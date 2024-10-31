<?php

class nbMel { 
	/**
	 * Add a query template to mel to make search common queries easier and faster. If a template with the same
	 * name already exists, it will be overwritten.
	 * 
	 * @param string $name The new template name; must be unique;
	 * @param mixed $value The new template value
	 */
	static function AddTemplate( $name, $value ) {
		if( strlen($name) == 0 ) return;
	
		$cur = Netblog::options()->getMelUserTpls();
		$cur[$name] = $val;
		Netblog::options()->setMelUserTpls($cur);
	}
	
	/**
	 * Remove a query template from mel
	 * @param string $name The template's unique name
	 */
	static function RemoveTemplate( $name ) {
		$cur = Netblog::options()->getMelUserTpls();
		if( isset($cur[$name]) ) {
			unset($cur[$name]);
			Netblog::options()->setMelUserTpls($cur);
		}	
	}
	
	/**
	 * Get a template's value from database
	 * @param string $name
	 * @return mixed Returns the templates value, false if template has not been found
	 */
	static function GetTemplate( $name ) {
		$cur = Netblog::options()->getMelUserTpls();
		return isset($cur[$name]) ? $cur[$name] : false; 
	}
	
	/**
	 * Get an array of all templates defined in the database
	 * @return array[template_name=>template_value]
	 */
	static function GetAllTemplates() {
		return $cur = Netblog::options()->getMelUserTpls();
	}
	
	/**
	 * Collect links from query submitted by post and print the out as standard xml format. After finishing, this method
	 * causes the script to die.
	 */
	static function TransactLinks() {
		global $wpdb;
		$q = trim($_POST['query']);	
		$o = array();
		if( $q == '' ) $q = 'tpl:new';
		
		// PARSE ARGS
		$limit = 0; $sortBy = 'uri_id'; $flag = null; $refs = 0; $tpl = null;
		$pars = explode(' ',$q);
		$paramFrg = array();
		foreach( $pars as $k=>$p ) {
			$num = 0; $p = strtolower($p);
			if( substr($p,0,6) == 'limit:' && is_numeric( $num=substr($p,6) ) && $num > 0 ) $limit = $num;
			else if( substr($p,0,5) == 'sort:' && strlen($p) > 5 ) $sortBy = substr($p,5);
			else if( substr($p,0,5) == 'flag:' && strlen($p) > 5 ) $flag = substr($p,5);
			else if( substr($p,0,4) == 'tpl:' && strlen($p) > 4 ) $tpl = substr($p,4);
			else {
				$log = 'AND';
				if( substr($pars[$k],0,1) == '!' ) {
					$log = 'NOT';
					$pars[$k] = substr($pars[$k],1);
				} else if( substr($pars[$k],0,1) == '|' ) {
					$log = 'OR';
					$pars[$k] = substr($pars[$k],1);
				}
				$paramFrg[] = array('query'=>$pars[$k],'logical'=>'AND');
			}
		}
		
		$template = false;
		
		// JUST ADDED
		if( $tpl == 'new' ) {
			$template = true;
			$limit = 20;
			$sortBy = "uri_id desc,$sortBy";
		}
		
		// MOST POPULAR
		if( $tpl == 'popular' ) {
			$template = true;
			$limit = 20;
			$sortBy = "refs desc,$sortBy";
		}
	
		// OFFLINE, TRASHED
		if( $tpl == 'offline' || $tpl == 'trash' || $tpl == 'lock' ) {
			$template = true;
			$flag = $tpl;
		}
		
		// USER TEMPLATE
		if( strlen($tpl) > 0 ) {
			$tplQuery = nbMel::GetTemplate($tpl);
			if( $tplQuery !== false && is_string($tplQuery) && strlen($tplQuery)>0 ) {
				$_POST['query'] = $tplQuery;
				self::TransactLinks();
				die();
			}
		}
		
		$xml = new SimpleXMLElement('<mel/>');
		$xlk = $xml->addChild('LINKS');
		
		$attachParentPosts = true;
	
		$lk = Array();
		if( sizeof($paramFrg) == 0 ) {
			if( $r=nbLinkExtern::FindUris( $sortBy, $limit, $flag, $refs) )
				foreach($r as $l) {
					$xk = $l->AsXML($xlk);
					if($attachParentPosts && $par=nbLinkExternCollection::LoadByUri($l->GetID())) 
						foreach($par as $col)
							nbPost::AsXML($col->GetParent(), $xk);
				}
		} else if( $r=nbLinkExternCollection::MatchUri( $paramFrg, explode(',',$sortBy), $limit, $flag, $refs ) ) {
			foreach($r as $col) {
				for($i=0; $i<$col->CountLinks(); $i++) {
					$l=$col->GetLink($i);
					$xk = $l->AsXML($xlk);
					if($attachParentPosts && $par=nbLinkExternCollection::LoadByUri($l->GetID())) 
						foreach($par as $col)
							nbPost::AsXML($col->GetParent(), $xk);
				}
			}
		}
			
		$pilot = new nbTestPilot();
		$pilot->melLoadItems(sizeof($r),$q);
		$pilot->save();
		
		echo ($xml->asXML());;
		die();
	}

	/**
	 * Change an external links via transaction, i.e. ajax
	 */
	static function TransactChangeLink() {
		$attachPostInfos = true;
		
		$flag = is_numeric($f=$_POST['flag'])? $f : nbLinkExtern::InterpretFlagIName($f);
		$uri = $_POST['uri'];
		$title = str_replace(array("\'",'\"'), array("'",'"'), $_POST['title']);		
		$pilot = new nbTestPilot();		
		$xml = new SimpleXMLElement('<mel/>');
		$xlks = $xml->addChild('LINKS');

		if( $lk=nbLinkExtern::LoadByID($_POST['uri_id']) ) {
			if($flag==nbLinkExtern::FLAG_CHECK_STATUS || $flag==nbLinkExtern::FLAG_UPDATE_TITLE) {
				$res = DataTransfer::RetrieveUrl($lk->GetUri());
				if($res['error'] && strlen($res['error'])>0)
					$flag = nbLinkExtern::FLAG_OFFLINE;
				else {
					if($flag==nbLinkExtern::FLAG_UPDATE_TITLE && !($title=nbUri::GrabTitle($res['content'])) ) {
						if( !($title=nbUri::FindTitleByWebsearch($uri)) ) {
							$title = $_POST['title'];
							$lk->SetFlag(nbLinkExtern::FLAG_OFFLINE);
						}
					}
					$flag = $lk->GetFlag();
				} 
			} else if($flag==nbLinkExtern::FLAG_ERASE) {
				$pilot->melItemRemove($uri);
				$pilot->save();
				die( $lk->Remove() ? 'true' : 'failed' );
			} else if($flag==nbLinkExtern::FLAG_UNLOCK || $flag==nbLinkExtern::FLAG_RESTORE)
				$flag=nbLinkExtern::FLAG_NONE;
			$lk->SetTitle($title);
			$lk->SetUri($uri);
			$lk->SetFlag($flag);
			if( $lk->HasChanged() && !$lk->Save() )
				die('error:Failed to save link "'.$_POST['uri'].'"');
			$xlk = $lk->AsXML($xlks);
			if($attachPostInfos)
				nbLinkExternCollection::AttachPostAsXML($lk, $xlk);
			if($flag==nbLinkExtern::FLAG_RESTORE)
				$pilot->melItemRestore($uri);
			
			$pilot->save();
			echo $xml->asXML();
		} else die('failed');
		die();
	}
}

add_action('wp_ajax_get_links', 'nbMel::TransactLinks');
add_action('wp_ajax_send_link', 'nbMel::TransactChangeLink');

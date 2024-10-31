<?php
require_once 'nbSubmitClient.php';

class nbTestPilot {
	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$this->pilot = Netblog::options()->getTestPilotStorage();
	}
	
	/**
	 * Save test pilot to database
	 *
	 */
	public function save() {
		Netblog::options()->setTestPilotStorage($this->pilot);
	}
	
	/**
	 * Set how many settings have been updated
	 *
	 * @param int $numChanges
	 */
	public function updateSettings( $numChanges = 1, $numChangesFailed = 0 ) {
		$this->onEvent('update_setting',array('total'=>$numChanges,'failed'=>$numChangesFailed) );
	}
	
	/**
	 * Set how many connected items must have been loaded for one given item
	 *
	 * @param int $postTitleLen
	 * @param int $numOutIntern
	 * @param int $numOutExtern
	 * @param int $numInIntern
	 * @param int $numInBlogsearch
	 * @param int $numInPings
	 */
	public function netvisLoadItem( $postTitleLen, $numOutIntern, $numOutExtern = 0, $numInIntern = 0, $numInBlogsearch = 0, $numInPings = 0 ) {
		$this->onEvent('netvis_loaditem', array('post_title_len'=>$postTitleLen,
												'num_outlinks_intern' => $numOutIntern,
												'num_outlinks_extern' => $numOutExtern,
												'num_inlinks_intern' => $numInIntern,
												'num_inlinks_blogsearch' => $numInBlogsearch,
												'num_inlinks_pingbacks' => $numInPings,
		 ) );
	}
	
	/**
	 * Export Module
	 *
	 * @param string $moduleName
	 * @param string $moduleCodename
	 * @param string $moduleVersion
	 * @param string $moduleURI
	 * @param bool $scheduled
	 */
	public function exportModule($moduleName, $moduleCodename, $moduleVersion, $moduleURI, $scheduled=false) {
		 $this->onEvent('export_module',array('module_name'=>$moduleName, 
		 									'module_codename'=>$moduleCodename, 
		 									'module_version'=>$moduleVersion, 
		 									'module_uri'=>$moduleURI,
		 									'scheduled'=>$scheduled));
	}
	public function exportModuleGetSettings($moduleName, $moduleCodename, $moduleVersion, $moduleURI) { 
			$this->onEvent('export_module_getsettings',array('module_name'=>$moduleName, 
		 									'module_codename'=>$moduleCodename, 
		 									'module_version'=>$moduleVersion, 
		 									'module_uri'=>$moduleURI));
	}
	
	public function importModule($moduleName, $moduleCodename, $moduleVersion, $moduleURI) {
		 $this->onEvent('import_module',array('module_name'=>$moduleName, 
		 									'module_codename'=>$moduleCodename, 
		 									'module_version'=>$moduleVersion, 
		 									'module_uri'=>$moduleURI));
	}
	public function importModuleGetSettings($moduleName, $moduleCodename, $moduleVersion, $moduleURI) { 
		$this->onEvent('import_module_getsettings',array('module_name'=>$moduleName, 
		 									'module_codename'=>$moduleCodename, 
		 									'module_version'=>$moduleVersion, 
		 									'module_uri'=>$moduleURI));
	}
	
	public function exportSchedulerChange($id='',$name='') { $this->onEvent('export_scheduler_change', array('name'=>$name,'id'=>$id) ); }
	public function exportSchedulerRemove($id='',$name='') { $this->onEvent('export_scheduler_remove', array('name'=>$name,'id'=>$id) ); }
	public function exportSchedulerAdd($id='',$name='') { $this->onEvent('export_scheduler_add', array('name'=>$name,'id'=>$id) ); }
	public function exportSchedulerLoad($id='',$name='') { $this->onEvent('export_scheduler_load', array('name'=>$name,'id'=>$id) ); }
	
	public function melLoadItems($numItems=1,$query='') { $this->onEvent('mel_load_items',array('num'=>$numItems,'query'=>$query)); }
	public function melTemplateAdd($query) { $this->onEvent('mel_template', array('query'=>$query,'action'=>'add')); }
	public function melTemplateRemove($query) { $this->onEvent('mel_template', array('query'=>$query,'action'=>'remove')); }
	public function melItemAdd($uri='') { $this->onEvent('mel_item', array('uri'=>$uri,'action'=>'add') ); }
	public function melItemRemove($uri='') { $this->onEvent('mel_item', array('uri'=>$uri,'action'=>'remove') ); }
	public function melItemTrash($uri='') { $this->onEvent('mel_item', array('uri'=>$uri,'action'=>'trash') ); }
	public function melItemLock($uri='') { $this->onEvent('mel_item', array('uri'=>$uri,'action'=>'lock') ); }
	public function melItemUnlock($uri='') { $this->onEvent('mel_item', array('uri'=>$uri,'action'=>'unlock') ); }
	public function melItemRestore($uri='') { $this->onEvent('mel_item', array('uri'=>$uri,'action'=>'restore') ); }
	public function melItemUpdate($uri='') { $this->onEvent('mel_item', array('uri'=>$uri,'action'=>'update') ); }
	public function melItemCheckStatus($uri='') { $this->onEvent('mel_item', array('uri'=>$uri,'action'=>'checkstatus') ); }
		
	public function mtbFurReadItemRemove($numItems=1) { $this->onEvent('mtb_further_reading_item', array('num_item'=>$numItems,'action'=>'remove') ); }
	public function mtbFurReadItemAdd($numItems=1) { $this->onEvent('mtb_further_reading_item', array('num_item'=>$numItems,'action'=>'add')); }
	public function mtbFurReadItemsLoad($numItems=1) { $this->onEvent('mtb_further_reading_item', array('num_item'=>$numItems,'action'=>'load')); }
	public function mtbFurReadSearch($numItems=1,$query='') { $this->onEvent('mtb_further_reading_search', array('num_item'=>$numItems,'query'=>$query) ); }
	
	public function gcaptionAdd($numItems=1, $name = '') { $this->onEvent('gcaption', array('num_items'=>$numItems,'action'=>'add','name'=>$name) ); }
	public function gcaptionRemove($numItems=1, $name = '') { $this->onEvent('gcaption', array('num_items'=>$numItems,'action'=>'remove','name'=>$name) ); }
	public function gcaptionChange($numItems=1, $name = '') { $this->onEvent('gcaption', array('num_items'=>$numItems,'action'=>'change','name'=>$name) ); }
	public function gcaptionRebuild($numItems=1) { $this->onEvent('gcaption', array('num_items'=>$numItems,'action'=>'rebuild') ); }
	
	public function citationCustomStyleAdd($filterCode) { $this->onEvent('citation_customstyle', array('filtercode'=>$filterCode,'action'=>'add') ); }
	public function citationCustomStyleRemove($filterCode) { $this->onEvent('citation_customstyle', array('filtercode'=>$filterCode,'action'=>'remove') ); }
	
	public function footprintRepair($numItemsQueued=1,$numItemsRepaired=0) { $this->onEvent('footprint_repair', array('num_items_queued'=>$numItemsQueued,'num_items_repaired'=>$numItemsRepaired) ); }
	public function footprintAddLocalMode($id) { $this->onEvent('footprint_add', array('mode'=>'local','id'=>$id) ); }
	public function footprintAddServerMode($id) { $this->onEvent('footprint_add', array('mode'=>'server','id'=>$id) ); }
	
	public function searchServiceLoad() { $this->onEvent('search_service_load',''); }
	
	/**
	 * Get next submission timestamp
	 *
	 * @return timestamp
	 */
	public function getNextSubmission() {
		if(!isset($this->pilot['next_submission'])) {
			$this->pilot['next_submission'] = (time() + $this->submitInterval);
			$this->save();
		}
		return $this->pilot['next_submission'];
	}
	
	/**
	 * Submit test pilot data
	 *
	 */
	public function submit() {
		if(!Netblog::options()->useTestPilot())
			return;
		
		$data = $this->pilot;
		$stats = $this->collectStatistics();
		foreach($stats as $k=>$v)
			$data['statistics'][$k] = $v;
		$events = $this->collectEvents();
		foreach($events as $k=>$v)
			$data['events'][$k] = $v;
			
		Netblog::log("Starting test pilot transmission...");
		$submit = new nbSubmitClient($this->serverUri,$data);
		if($submit->hasError()) {
			Netblog::logError("Test Pilot transmission failed (Error $submit->errno: $submit->error)");
		} else {
			Netblog::logSuccess("Transmitted test pilot data");
			unset($this->pilot['next_submission']);
			$this->getNextSubmission();
			$this->clearPilotData();
			$this->save();
		}
	}
	
	/**
	 * Collects stats about posts and incoming/outgoing links.
	 *
	 * @return array
	 */
	public function collectStatistics() {
		$stats = array();
		$posts = get_posts( array('post_type' => 'post','numberposts' => -1, 'post_status' => null) );
		if(is_array($posts) && sizeof($posts)>0) {
			$stats['post_title_length'] = array();
			$stats['post_comment_count'] = array();
			$stats['post_links_out_intern'] = array();
			$stats['post_links_out_extern'] = array();
			$stats['post_links_in_pingbacks'] = array();
			$stats['post_links_in_blogsearch'] = array();
			$stats['post_links_in_intern'] = array();
			$stats['post_links_in_extern'] = array();
			$stats['post_parent_num'] = 0;
			$stats['post_footprint_has_mean'] = 0;
			$stats['post_footprint_server_mean'] = 0;
			$stats['post_footprint_client_mean'] = 0;
			
			foreach($posts as $post) {
				$this->helperCalcDistribution($stats['post_title_length'],strlen($post->post_title));
				$this->helperCalcDistribution($stats['post_comment_count'],intval($post->comment_count,10));
				$this->helperCalcDistribution($stats['post_links_out_intern'],nbdb::rsc_count($post->ID));
				$this->helperCalcDistribution($stats['post_links_out_extern'],nbdb::rsc_count($post->ID,true));
				
				$this->helperCalcDistribution($stats['post_links_in_intern'],
					is_array($t=nbdb::rsc_getParents($post->ID)) ? sizeof($t) : 0);
				$this->helperCalcDistribution($stats['post_links_in_extern'],
					is_array($t=nbdb::rsc_getParents($post->ID,true)) ? sizeof($t) : 0);
				
				$comments = get_comments('post_id='.$post->ID);
				$pings = 0;
				foreach($comments as $c) {
					if($c->comment_type == 'pingback')
						$pings++;
				}
				$this->helperCalcDistribution($stats['post_links_in_pingbacks'],$pings);
				
				$blogsearch = 0;
				if( ($rss=nbsearch::getLinksByBlogsearch('link:'.$post->guid,50)) != null )
					$blogsearch += sizeof($rss);
				if( ($rss=nbsearch::getLinksByBlogsearch('link:'.get_permalink($post->guid),50)) != null )
					$blogsearch += sizeof($rss);
				$this->helperCalcDistribution($stats['post_links_in_blogsearch'],$blogsearch);
				
				$stats['post_parent_num'] += $post->post_parent > 0 ? 1 : 0;
				
				$this->helperCalcDistribution($stats['post_captions_count'],nbdb::cpt_countInHost($post->ID));
				
				if(nbdb::footprt_hasFootprint($post->ID)) {
					$stats['post_footprint_has_mean']++;
					if( is_array($t=nbdb::footprt_getMetaFromServer(nbdb::footprt_getID($post->ID))) )
						$stats['post_footprint_server_mean']++;
					else if($t!==false) $stats['post_footprint_client_mean']++;
				}				
			}
			$stats['post_title_length']['mean'] /= sizeof($posts);
			$stats['post_comment_count']['mean'] /= sizeof($posts);
			$stats['post_links_out_intern']['mean'] /= sizeof($posts);
			$stats['post_links_out_extern']['mean'] /= sizeof($posts);
			$stats['post_links_in_pingbacks']['mean'] /= sizeof($posts);
			$stats['post_links_in_blogsearch']['mean'] /= sizeof($posts);
			$stats['post_captions_count']['mean'] /= sizeof($posts);
			$stats['post_links_in_intern']['mean'] /= sizeof($posts);
			$stats['post_links_in_extern']['mean'] /= sizeof($posts);
			$stats['post_footprint_has_mean'] /= sizeof($posts);
			$stats['post_footprint_server_mean'] /= sizeof($posts);
			$stats['post_footprint_client_mean'] /= sizeof($posts);
			$stats['post_parent_num'] /= sizeof($posts);
			
			$stats['posts_num'] = sizeof($posts);
			
			$stats['netblog_client'] = Netblog::getClientVersion();			// for statistics and to improve support
			$stats['netblog_server'] = Netblog::getServerVersion();
			$stats['host_type'] = 'wordpress';
			$stats['host_version'] = get_bloginfo('version');				// helps to find bugs and to improve wp support
			$stats['host_language'] = get_bloginfo('language');
			$stats['useragent'] = $_SERVER['HTTP_USER_AGENT'];				// for better testing of new versions		
			$stats['php_version'] = phpversion();
			$stats['mysql_version'] = nbdb::getServerVersion();
			
			if(Netblog::options()->getPrivacyLevel()=='medium' || Netblog::options()->getPrivacyLevel()=='none') {
				$stats['host_charset'] = get_bloginfo('charset');
				
				$ct = current_theme_info();
				$stats['host_theme_name'] = $ct->name;
				$stats['host_theme_title'] = $ct->title;
				$stats['host_theme_version'] = $ct->version;
				$stats['host_theme_parent'] = $ct->parent_theme;
				$stats['host_theme_template'] = $ct->template;
				
				$pls = get_plugins();$i=0;
				foreach($pls as $pl) {
					$stats['host_plugins'][] = array(
						'name' => $pl['Name'],
						'version' => $pl['Version'],
						'network' => $pl['Network'],
						'uri' => $pl['PluginURI']
						);
					$i++;
				}		
				$stats['host_multisite'] = is_multisite();				
				$stats['php_extensions'] = implode('|',get_loaded_extensions());
			}
		}
		return $stats;
	}
	
	/**
	 * Calculate distribution
	 *
	 * @param array $array
	 * @param int $num
	 */
	private function helperCalcDistribution( &$array, $num ) {
		$array['max'] = isset($array['max']) ? max($array['max'],$num) : $num;
		$array['min'] = isset($array['min']) ? min($array['min'],$num) : $num;
		$array['mean'] = isset($array['mean']) ? $array['mean'] + $num : $num;
	}
	
	/**
	 * Collect events
	 *
	 * @return array
	 */
	public function collectEvents() {
		$events = array();
		
		global $wpdb;
		$q = "SELECT * FROM `".$wpdb->prefix . Netblog::options()->getServerTableTestPilot()."`";
		if( ($rows=$wpdb->get_results($q)) != null) {
			foreach($rows as $row) {
				$events[$row->key][] = unserialize($row->value);
			}
		}
		return $events;
	}
	
	/**
	 * Register an event
	 *
	 * @param string $key
	 * @param string|array $value
	 */
	private function onEvent($key,$value) {
		$val = '';
		if(is_string($value) || is_numeric($value)) {
			$val = serialize(array('time'=>time(),'value'=>$value));
		} else if(is_array($value)) {
			$value['time'] = time();
			$val = serialize($value);
		}
		
		global $wpdb;
		$q = "INSERT INTO `".$wpdb->prefix . Netblog::options()->getServerTableTestPilot()."` (`key`,`value`,`time`)
				VALUES ('".$key."','".addcslashes($val,'\'')."',".time().")";
		$wpdb->query($q);
	}
	
	/**
	 * Clear all pilot data from database.
	 *
	 */
	private function clearPilotData() {
		global $wpdb;
		$q = "DELETE FROM `".$wpdb->prefix . Netblog::options()->getServerTableTestPilot()."`";
		$wpdb->query($q);
	}
	
	private $pilot;
	private $serverUri = 'http://netblog2.benjaminsommer.com/testpilot/submit.php';
	private $submitInterval = 259200;		// 3 days
	private $submitFailInterval = 3600;		// 1 hour
	//private $serverUri = 'http://localhost/nbtestpilot/submit.php';
}
?>
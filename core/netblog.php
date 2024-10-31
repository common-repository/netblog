<?php
/**
 * Main class to manage the plugin (installation, initialisation)
 * 
 * @author Benjamin Sommer
 * @todo Add nbBibliographyGUI to Netblog panel to show the posts' citations on one page
 * @todo Add the possibility to number citations page or site-wide, and either combined numbering for all referenced types or seperate numbering
 *  
 */
class Netblog
{
		
	/**
	 * Install WP plugin Netblog
	 * @return bool TRUE on success, FALSE on failure
	 */
	static public function install()
	{
		$ver = self::$options->getClientVersion();
		if(!(self::$options->getServerVersion() < self::$serverVer || $ver < self::$clientVer))
			return true;
				
		require_once 'DataTransfer.php';
		$t = new DataTransfer();
		$action = is_string($ver) && $ver>"0" ? 'update' : 'install';
		$t->SubmitPost(self::$uri_claction, array('claction'=>$action,'clversion'=>self::$clientVer,'clname'=>'netblog'));
		
		self::log("INSTALLING Netblog (client: from '".self::$options->getClientVersion()."' to '".self::$clientVer."', server: from '".self::$options->getServerVersion()."' to '".self::$serverVer."')");
		if(function_exists('get_current_site')) {			
			$wpmu = get_current_site();			
			self::log("WP site: $wpmu->site_name ($wpmu->domain, path:$wpmu->path, id:$wpmu->id)");
		}
		$out = true; 
		
		global $wpdb;
		
		/* SETUP DATABASE TABLES */
		require_once(ABSPATH . 'wp-includes/pluggable.php'); // to prevent error message: Fatal error: Call to undefined function wp_get_current_user()
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
			/* CAPTIONS */
			if( self::$options->getServerTableVerCaptions() < ($v=self::$options->getServerCTBVCaptions()) ) {
				$tbl = $wpdb->prefix . self::$options->getServerTableCaptions();
				if($wpdb->get_var("SHOW TABLES LIKE '$tbl'") != $tbl || self::$options->getServerTableVerCaptions() > '0' ) {      
					$sql = "CREATE TABLE `" . $tbl . "` (
						  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
						  `name` VARCHAR(125) NOT NULL,
						  `type` VARCHAR(32) NOT NULL,
						  `num` VARCHAR(32) NOT NULL,
						  host BIGINT(20) NOT NULL,
						  host_order INT NOT NULL,
						  title VARCHAR(255),			  
						  local_order INT NOT NULL,
						  print VARCHAR(64) NOT NULL,
						  PRIMARY KEY  (id)
						);";      
					dbDelta($sql);
					if( self::$options->setServerTblVerCaptions($v) )
						self::log('Installed database captions table (version '.$v.')');
					else {
						self::log('Failed to install database captions table (version '.$v.')');
						self::$options->enableGlobalCaptions(false);
						$out = false;
					}
				} else {
					self::log('Failed to install database captions table (version '.$v.'). \''.$tbl.'\' is a foreign table.');
					self::$options->enableGlobalCaptions(false);
					$out = false;
				}
			}
					
			/* FOOTPRINTS */
			if( self::$options->getServerTableVerFootprints() < ($v=self::$options->getServerCTBVFootprints()) ) {
				$tbl = $wpdb->prefix . self::$options->getServerTableFootprints();
				if($wpdb->get_var("SHOW TABLES LIKE '$tbl'") != $tbl || self::$options->getServerTableVerFootprints() > '0' ) {      
					$sql = "CREATE TABLE `" . $tbl . "` (
						  id BIGINT(20) UNSIGNED NOT NULL,
						  footprint VARCHAR(45) NOT NULL
						);";      
					dbDelta($sql);
					if( self::$options->setServerTblVerFootprints($v) ) {
						self::log('Installed database footprint table (version '.$v.')');
					} else {
						self::log('Failed to install database footprint table (version '.$v.')');
						self::$options->enableFootprints(false);
						$out = false;
					}
				} else {
					self::log('Failed to install database footprint table (version '.$v.'). \''.$tbl.'\' is a foreign table.');
					self::$options->enableFootprints(false);
					$out = false;
				}
				nbdb::footprt_createAll();
			}
			
			/* NET - LOCAL RESOURCES - FURTHER READING, REFERENCED BY TO ARTICLES ON THIS SITE */
			if( self::$options->getServerTableVerNet() < ($v=self::$options->getServerCTBVNet()) ) {
				$tbl = $wpdb->prefix . self::$options->getServerTableNet();
				if($wpdb->get_var("SHOW TABLES LIKE '$tbl'") != $tbl || self::$options->getServerTableVerNet() > '0' ) {      
					$sql = "CREATE TABLE `" . $tbl . "` (
						  id BIGINT(20) UNSIGNED NOT NULL,
						  adj_id BIGINT(20) UNSIGNED NOT NULL,
						  UNIQUE KEY netblogAdj  (id,adj_id)
						);";    
					dbDelta($sql);
					if( self::$options->setServerTblVerNet($v) )
						self::log('Installed database net table (version '.$v.')');
					else {
						self::log('Failed to install database net table (version '.$v.')');
						self::$options->enableLocalNet(false);
						$out = false;
					}
				} else {
					self::log('Failed to install database net table (version '.$v.'). \''.$tbl.'\' is a foreign table.');
					self::$options->enableLocalNet(false);
					$out = false;
				}
			}
			
			/* EXT NET - LINKS TO EXTERN RESOURCES */
			if( self::$options->getServerTableVerExt() < ($v=self::$options->getServerCTBVExt()) ) {
				$tbl = $wpdb->prefix . self::$options->getServerTableExt();
				if($wpdb->get_var("SHOW TABLES LIKE '$tbl'") != $tbl || self::$options->getServerTableVerExt() > '0' ) {   
					// MAX MEDIUMINT: 16777215 (unsigned)   
					$sql = "CREATE TABLE `" . $tbl . "` (
						  uri_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
						  uri VARCHAR(512) NOT NULL,
						  uri_title VARCHAR(127) NOT NULL,
						  flag INT(10),
						  refs MEDIUMINT NOT NULL DEFAULT 1,
						  footprint VARCHAR(45),
						  PRIMARY KEY  (uri_id)
						);";    
					dbDelta($sql);
					if( self::$options->setServerTblVerExt($v) ) {
						self::log('Installed database external resources table (version '.$v.')');
					} else {
						self::log('Failed to install database external resources table (version '.$v.')');
						self::$options->enableExtNet(false);
						$out = false;
					}
				} else {
					self::log('Failed to install database external resources table (version '.$v.'). \''.$tbl.'\' is a foreign table.');
					self::$options->enableExtNet(false);
					$out = false;
				}
			}
			
			/* RELATIONSHIP LOCAL ARTICLES - EXTERNAL RESOURCES */
			if( self::$options->getServerTableVerRelExtnodes() < ($v=self::$options->getServerCTBVRelExtnodes()) ) {
				$tbl = $wpdb->prefix . self::$options->getServerTableRelExtnodes();
				if($wpdb->get_var("SHOW TABLES LIKE '$tbl'") != $tbl || self::$options->getServerTableVerRelExtnodes() > '0' ) {      
					$sql = "CREATE TABLE `" . $tbl . "` (
						  id BIGINT(20) UNSIGNED NOT NULL,
						  `uri_id` BIGINT(20) UNSIGNED NOT NULL,
						  UNIQUE INDEX `netblog_rel_extnode`  (id,uri_id)
						);";   
					dbDelta($sql);
					if( self::$options->setServerTblVerRelExtnodes($v) )
						self::log('Installed database rel int<->ext table (version '.$v.')');
					else {
						self::log('Failed to install database rel int<->ext table (version '.$v.')');
						self::$options->enableRelExtnodes(false);
						$out = false;
					}
				} else {
					self::log('Failed to install database rel int<->ext table (version '.$v.'). \''.$tbl.'\' is a foreign table.');
					self::$options->enableRelExtnodes(false);
					$out = false;
				}
			}
			
			/* TEST PILOT */
			if( self::$options->getServerTableVerTestPilot() < ($v=self::$options->getServerCTBVTestPilot()) ) {
				$tbl = $wpdb->prefix . self::$options->getServerTableTestPilot();
				if( $wpdb->get_var("SHOW TABLES LIKE '$tbl'") != $tbl || self::$options->getServerTableVerTestPilot() > '0' ) {      
					$sql = "CREATE TABLE `" . $tbl . "` (
						  `key` VARCHAR(100) NOT NULL,
						  `value` TEXT NOT NULL,
						  `time` INT UNSIGNED NOT NULL
						);";
					dbDelta($sql);
					if( self::$options->setServerTblVerTestPilot($v) ) {
						self::log("Installed database table for testpilot, version $v");
						self::$options->enableTestPilot();
					} else {
						self::log('Failed to install database testpilot table (version '.$v.')');
						self::$options->enableTestPilot(false);
						$out = false;
					}
				} else {
					self::log('Failed to install database testpilot table (version '.$v.')');
					self::$options->enableTestPilot(false);
					$out = false;
				}
			}
			
			/* BIBLIOGRAPHIC ITEM */
			if( self::$options->getServerTableVerBibitem() < ($v=self::$options->getServerCTBVBibItem())  ) {
				$tbl = $wpdb->prefix . self::$options->getServerTableBibitem();
				if( $wpdb->get_var("SHOW TABLES LIKE '$tbl'") != $tbl || self::$options->getServerTableVerBibitem() > '0' ) {      
					$sql = "CREATE TABLE `" . $tbl . "` (
						  `itemID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						  `fieldID` SMALLINT UNSIGNED NOT NULL,
						  `fieldValue` VARCHAR(255) NOT NULL,
						  `usage` INT UNSIGNED NOT NULL DEFAULT 1,
						  PRIMARY KEY  (itemID)
						);";
					dbDelta($sql);
					if( self::$options->setServerTblVerBibitem($v) ) {
						self::log("Installed database table for bibliographic item, version $v");
					} else {
						self::log('Failed to install database bibliographic item table (version '.$v.')');
						$out = false;
					}
				} else {
					self::log('Failed to install database bibliographic item table (version '.$v.')');
					$out = false;
				}
			}

			/* BIBLIOGRAPHIC REFERENCE */
			if( self::$options->getServerTableVerBibReference() < ($v=self::$options->getServerCTBVBibReference())  ) {
				$tbl = $wpdb->prefix . self::$options->getServerTableBibReference();
				if( $wpdb->get_var("SHOW TABLES LIKE '$tbl'") != $tbl || self::$options->getServerTableVerBibReference() > '0' ) {      
					$sql = "CREATE TABLE `" . $tbl . "` (
						  `refID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						  `typeID` SMALLINT UNSIGNED NOT NULL,
						  `style` VARCHAR(30) NOT NULL,
						  `name` VARCHAR(50) NOT NULL,							  
						  `userID` INT UNSIGNED NOT NULL,
						  `time` INT UNSIGNED NOT NULL,
						  `excerpt` TEXT,
						  `hide_inline` TINYINT DEFAULT 0,
						  `usage` INT UNSIGNED NOT NULL DEFAULT 1,
						  PRIMARY KEY  (refID)
						);";
					dbDelta($sql);
					if( self::$options->setServerTblVerBibReference($v) ) {
						self::log("Installed database table for bibliographic reference, version $v");
					} else {
						self::log('Failed to install database bibliographic reference table (version '.$v.')');
						$out = false;
					}
				} else {
					self::log('Failed to install database bibliographic reference table (version '.$v.')');
					$out = false;
				}
			}
			
			/* BIBLIOGRAPHIC REFERENCE RELATION TO BIB ITEMS */
			if( self::$options->getServerTableVerBibReferenceRel() < ($v=self::$options->getServerCTBVBibReferenceRel())  ) {
				$tbl = $wpdb->prefix . self::$options->getServerTableBibReferenceRel();
				if( $wpdb->get_var("SHOW TABLES LIKE '$tbl'") != $tbl || self::$options->getServerTableVerBibReferenceRel() > '0' ) {      
					$sql = "CREATE TABLE `" . $tbl . "` (
						  `refID` INT UNSIGNED NOT NULL,
						  `itemID` INT UNSIGNED NOT NULL
						);";
					dbDelta($sql);
					if( self::$options->setServerTblVerBibReferenceRel($v) ) {
						self::log("Installed database table for \"bibliographic reference-item relation\", version $v");
					} else {
						self::log('Failed to install database "bibliographic reference-item relation" table (version '.$v.')');
						$out = false;
					}
				} else {
					self::log('Failed to install database "bibliographic reference-item relation" table (version '.$v.')');
					$out = false;
				}
			}			

		/* REMOVE EED - NOT USED ANY MORE - BUT OPTIONAL */
		if(self::$options->getClientVersion() < "2.0") {
			self::$options->enableEED(false);
			self::$options->enableEEDAutoRebuild(false);
			nbdb::eed_remove();
		} else {
			self::log("No need to remove and disable EED");
		}		
		self::$options->enableFootprints();
		self::$options->setClientVersion(self::$clientVer);
		self::$options->setServerVersion(self::$serverVer);
			
		return $out;			
	}
	
	
	/**
	 * Activate WP plugin Netblog
	 * @return bool FALSE on failure
	 */
	static public function activate()
	{
		Netblog::log('Activated plugin');
		if( self::install() ) {
			self::log('Successfully activated Netblog '.self::$clientVer);
			return true;
		} else {
			self::log('Activation requires successful installation. Skipping process.');
			return false;
		}
	}
	
	/**
	 * Deactive WP plugin Netblog
	 *
	 */
	static public function deactivate()
	{
		Netblog::log('Deactivated plugin');
		remove_action('admin_menu', 'Netblog::initAdminPanelMenu');
		//self::$options->enableAll(false);
		//Netblog::log('Deactivated plugin');
		//self::log('Deactivated Netblog '.self::$clientVer);
		
		require_once 'DataTransfer.php';
		$t = new DataTransfer();
		//$t->SubmitPost(self::$uri_claction, array('claction'=>'deactivate','clversion'=>self::$clientVer,'clname'=>'netblog'));
	}
		
	
	/**
	 * Uninstall Netblog
	 * 
	 * @return bool TRUE on success, FALSE on failure or partial removal	 *
	 */
	static public function uninstall()
	{	
		self::log('Starting Netblog '.self::$clientVer.' Uninstall');
		
		self::deactivate();

		nbdb::rsc_rmExportData();
		self::log('Removed EED');

		$hasShortcodes = true;
		if( $hasShortcodes ) {
			self::logError('Automatically removing shortcodes in WP articles is highly insecure. Use secure Shortcode Removal Wizard before uninstall.');
			self::log('Stopping Uninstallation of Netblog '.self::$clientVer.'');
			self::log('Incomplete Uninstall of Netblog '.self::$clientVer);
			return false;
		}
		
		
		nbdb::removeTable(self::$options->getServerTableRelExtnodes());
		nbdb::removeTable(self::$options->getServerTableExt());
		nbdb::removeTable(self::$options->getServerTableCaptions());
		nbdb::removeTable(self::$options->getServerTableFootprints());
		nbdb::removeTable(self::$options->getServerTableNet());
		self::log('Removed Database Tables');
		
		self::$options->removeAll();
		self::log('Removed Options');
	
		self::log('Uninstalled Netblog '.self::$clientVer.' (complete)');
		
		require_once 'DataTransfer.php';
		$t = new DataTransfer();
		//$t->SubmitPost(self::$uri_claction, array('claction'=>'uninstall','clversion'=>self::$clientVer,'clname'=>'netblog'));
		
		return true;
	}
	
	
	/**
	 * Initialize/Register Callbacks for Netblog for each request
	 *
	 */
	static public function init()
	{
	
		if( self::$options == null )
			self::$options = new nboption();
		
		if( self::$options->getClientVersion() < self::$clientVer )					/* FUI - FIRST USE INSTALLATION */
			self::install();
          
		add_action('init', 				'Netblog::initPublic' );
		add_action('admin_init', 		'Netblog::initAdminPanel');
		add_action('admin_init', 		'nbMetaboxRefmaker::Register');
		add_action('admin_init', 		'nbMetaboxFurtherReading::Register');
		add_action('admin_init', 		'nbBibMetaboxGUI::register');
		add_action('admin_menu',		'Netblog::initAdminPanelMenu');

		add_action('save_post', 		'Netblog::onSavePost');
        add_action('deleted_post', 		'Netblog::onRemovePost');
		add_action('delete_attachment', 'Netblog::onRemoveAttachement');
		add_filter('the_content', 		'Netblog::filterPostContent');
		add_filter('the_editor_content','Netblog::filterPostContent');	
				
		wp_register_style('Netblog-plugin-css', WP_PLUGIN_URL . '/netblog/styles/style-admin.css');
		
		if(!self::$isStable) {
			wp_register_style('Netblog-plugin-netvis', WP_PLUGIN_URL . '/netblog/styles/netvis.css');
			wp_register_script('Netblog-plugin-netvis', WP_PLUGIN_URL . '/netblog/js/netvis.js');
		}
		
		wp_register_script('Netblog-plugin-js', WP_PLUGIN_URL . '/netblog/js/admin.js');
		wp_register_style('Netblog-plugin-css-autocomplete', WP_PLUGIN_URL . '/netblog/styles/autocomplete.css');
		wp_register_script('Netblog-plugin-js-autocomplete2', WP_PLUGIN_URL . '/netblog/js/autocomplete2.js');
		wp_register_style('Netblog-plugin-css-lister', WP_PLUGIN_URL . '/netblog/styles/lister.css');
		wp_register_script('Netblog-plugin-js-lister', WP_PLUGIN_URL . '/netblog/js/lister.js');
		wp_register_script('Netblog-plugin-js-md5', WP_PLUGIN_URL . '/netblog/js/md5.js');
		wp_register_script('Netblog-plugin-js-fade', WP_PLUGIN_URL . '/netblog/js/fade_hideShow.js');
		wp_register_script('Netblog-plugin-js-timeoutRefresh', WP_PLUGIN_URL . '/netblog/js/setTimeoutRefresh.js');
		wp_register_style('netblog-code-css', WP_PLUGIN_URL . '/netblog/styles/nbcode.css' );
	
		// CUSTOM FILTER REGISTER
		if( get_option('netblog_note_print') === 'true' || get_option('netblog_bib_print') === 'true' )
			add_filter('the_content', 'Netblog::filter_append2post',1000);
		
		// LOCALIZATION SUPPORT
		$plugin_dir = basename(dirname(__FILE__));
		load_plugin_textdomain( 'netblog', null, $plugin_dir.'/lang' );
		

	}
	
	
	/**
	 * Initialize Netblog for WP admin area and panel
	 *
	 */
	static public function initAdminPanel()
	{ 
		wp_enqueue_style('Netblog-plugin-css');
		wp_enqueue_style('Netblog-plugin-css-autocomplete');
		wp_enqueue_style('Netblog-plugin-css-lister');
		if(!self::$isStable)
			wp_enqueue_style('Netblog-plugin-netvis');
	
		wp_enqueue_script('Netblog-plugin-js');
		wp_enqueue_script('Netblog-plugin-js-autocomplete2');
		wp_enqueue_script('Netblog-plugin-js-lister');
		wp_enqueue_script('Netblog-plugin-js-md5');
		wp_enqueue_script('Netblog-plugin-js-fade');
		wp_enqueue_script('Netblog-plugin-js-timeoutRefresh');	
		if(!self::$isStable)
			wp_enqueue_script('Netblog-plugin-netvis');
		
		$pilot = new nbTestPilot();
		if($pilot->getNextSubmission() < time())
			$pilot->submit();
			
		$sched = new nbExportScheduler();
		$sched->runSchedules();
		
		self::install();							/* automatic install - ensures network activation */
	}
	
	
	/**
	 * Initialize Netblog menus in WP admin panel
	 *
	 */
	static public function initAdminPanelMenu()
	{
		if(!function_exists('current_user_can')) {
			Netblog::logError('WP function "current_user_can" is not defined!');
			return;
		}
		global $my_plugin_hook;
		$my_plugin_hook = add_menu_page( 'Netblog', 'Netblog', 'publish_posts', 'netblog-menu', 'nbMainGUI::printWnd' );	
			
		if( get_option('netblog_mel') == 'true' && current_user_can( self::options()->getMelPread() ) )
			add_submenu_page( 'netblog-menu', __('Manage External Links','netblog'), 'Links', 'publish_posts', 'netblog-main-mk', 'netblog_mel' );
			
		add_submenu_page( 'netblog-menu', 'Captions', 'Captions', 'publish_posts', 'netblog-main-captions', 'nbCaptionGUI::display' );
		
		if (class_exists('nbBibStylesGUI'))
			add_submenu_page( 'netblog-menu', 'Professional Styles', 'Pro Styles', 'manage_options', 'netblog-main-bibstyles', 'nbBibStylesGUI::display' );
		
		// FOR FUTURE VERSION
		//add_submenu_page( 'netblog-menu', 'Citations', 'Citations', 'publish_posts', 'netblog-main-captions', 'nbCaptionGUI::display' );
		//add_submenu_page( 'netblog-menu', 'Citation Styles', 'Citation Styles', 'publish_posts', 'netblog-main-captions', 'nbCaptionGUI::display' );
		
		nbOptionsGUI::$pagehook = add_submenu_page( 'netblog-menu', 'Settings', 'Settings', 'manage_options', 'netblog-settings', 'nbOptionsGUI::printWnd' );
		add_filter('contextual_help', 'nbOptionsGUI::printHelp', 10, 3);

		// DISABLED UNSTABLE FEATURES
		//add_submenu_page( 'netblog-menu', 'NetCit', 'Citations', 'publish_posts', 'netblog-main-netcit', 'nbNetCit::printWnd' );
		//if(!self::$isStable)
		//	add_submenu_page( 'netblog-menu', 'NetVis', 'Net Visualizer', 'publish_posts', 'netblog-main-netvis', 'nbNetVis::printWnd' );
		
		// commend out the next line if you need the export scheduler. note that you still can export, and import functionality is still missing, though.
		//add_submenu_page( 'netblog-menu', 'Export Scheduler', 'Export Scheduler', 'manage_options', 'netblog-export-scheduler', 'nbExportSchedulerGUI::printWnd' );
		
		// DISABLED OLD SETTINGS PAGE
		// commend out the next line if you need to manage global captions or custom citation styles.
		//add_submenu_page( 'netblog-menu', 'Settings (Old)', 'Settings (Depreciated)', 'manage_options', 'netblog-setting', 'netblog_settings' );		
		add_submenu_page( 'netblog-menu', 'Logging', 'Logging', 'manage_options', 'netblog-logging-manage', 'nbLoggingGUI::display' );
		
		if(defined('NBDEBUG') && constant('NBDEBUG'))
			add_filter('admin_footer_text', 'Netblog::customAdminFooter');
	}
	
	public static function customAdminFooter() {
		echo '<table style="display:inline-block; border:0px solid black; float:left"><tr><td style="vertical-align:top">';
			$time = microtime(true) - NBSTART;
			echo 'cpuTime: '.number_format($time*1000,4).'ms <br />';
			$mem = self::convert2Bytes(ini_get('memory_limit'));
			echo 'memUsage: '.self::formatBytes(memory_get_usage(),4).', '.round(memory_get_usage()/$mem*100,3).'%<br />';
			echo 'memPeak: '.self::formatBytes(memory_get_peak_usage(),4).', '.round(memory_get_peak_usage()/$mem*100,3).'%<br />';
		echo '</td><td style="padding-left:50px;vertical-align:top">';
			$included_files = get_included_files();
			echo 'Includes: '.sizeof($included_files).' files<br />';
		echo '</td></tr></table>';
	}


	
	
	/**
	 * Initialize Netblog for public requests
	 *
	 */
	static public function initPublic()
	{
		wp_enqueue_style('netblog-code-css');
	}
	
	
	/**
	 * Event handler on removing a WP post
	 *
	 * @param int $postid
	 */
	static public function onRemovePost( $postid )
	{
		nbdb::rsc_detachFromNode($postID);
		nbdb::cpt_rmByHost($postID, true);		
	}
	
	
	/**
	 * Event handler for removing attached resources from posts, pages
	 *
	 * @param int $resourceid
	 */
	static public function onRemoveAttachement( $resourceid )
	{
		nbdb::rsc_detachNode( $resourceid, false);
	}
	
	/**
	 * Event handler on saving a WP article
	 *
	 * @param int $postid
	 * @return int
	 */
	static public function onSavePost( $postid )
	{
		if (!current_user_can('publish_posts'))  {
			wp_die( __('You do not have sufficient permissions to access this page.','netblog') );
		}
	
	//	if ( !wp_verify_nonce( $_POST['netblog_noncename'], plugin_basename(__FILE__) )) {
	//		return $post_id;
	//	}
	
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return $post_id;
	
		if ( 'page' == $_POST['post_type'] ) {
			if ( !current_user_can( 'edit_page', $post_id ) )
				return $post_id;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}
		
		$id = $_POST['post_ID'];
	
		//
		// RESOURCES
		//	
			if( nbdb::isPostStatus($id, 'publish') ) {
				// CREATE FOOTPRINT
				if( !nbdb::footprt_hasFootprint($id) )
					nbdb::footprt_create($id);
					
				// SAVE POST -> UPDATE FOOTPRINT META
				else nbdb::footprt_update( $id ); 	
			}			
		
			// SAVE BY IMPORT
			if( defined('WP_LOAD_IMPORTERS') || isset($_GET['import']) || nbdb::isPostNew($id) )
				nbdb::rsc_importNode( $post_id );
			
			// SAVE EXPORT-DATA IN POST CONTENT (INVISIBLE)
			else if( get_option('netblog_export_build') == 'save_post') 
				nbdb::rsc_exportNode( $id, true );				
		
		// 
		// CAPTIONS
		//
		nbdb::updatePostCaptions($id);	
				
		return true;
	}
	
	
	/**
	 * Filter a post content to be displayed for public visitors
	 *
	 * @param string $content
	 * @return string
	 */
	static public function filterPostContent( $content )
	{
		return nbdb::rsc_stripExportTag($content);
	}
	
	/**
	 * Get version status string
	 * @return string
	 */
	static public function getVersionStatus() {
		$latest = self::getLatestVersion();
		$stable = self::isStable();
		if($latest > self::getClientVersion())
			return "<a href=\"http://netblog.benjaminsommer.com/download.php\" target=\"_blank\">r$latest available for download</a>";
		if($stable)
			return "latest and stable";
		return "latest beta";
	}
	
	/**
	 * Checks whether this release is stable or not
	 * @return bool
	 */
	static public function isStable() {
		return self::$isStable && strpos(self::$clientVer, 'b')===false;
	}
	
	/**
	 * Checks whether this release is the latest one
	 */
	static public function isLatest() {
		return self::getLatestVersion()==self::getClientVersion();
	}
	
	
	/**
	 * Get Netblog option handler. 
	 *
	 * @return nboption
	 */
	static public function options() { return self::$options != null ? self::$options : (self::$options=new nboption()); }
	
	/**
	 * Status whether current post is being saved
	 *
	 * @var bool
	 */
	static public $isSavePost = false;
	
	/**
	 * Netblog option handler
	 *
	 * @var nboption
	 */
	static private $options = null;

	
	//----------------------------------------------------------------------------------
	// CUSTOM FILTER FUNCTIONS
	//----------------------------------------------------------------------------------
	
	/**
	 * Content filter to append to WP articles
	 *
	 * @param string $content The parsed WP article
	 * @return string Parsed and appended WP artcle
	 */
	static public function filter_append2post( $content = '' )
	{
		if( get_option('netblog_note_print') === 'true' && !nbnote::postHasTableWPLoop() )
			$content .= nbnote::shortcode( array('print'=>'true') );
		if( get_option('netblog_bib_print') === 'true' && !nbcite::hasTableWPL() )
			$content .= nbcite::shortcode( array('print'=>'default') );
		return $content;
	}
	
	
	//----------------------------------------------------------------------------------
	// HELPER FUNCTIONS
	//----------------------------------------------------------------------------------
	/**
	 * Strip string to maximum length
	 *
	 * @param string $string
	 * @param int $maxSize
	 * @param string[optional] $appendOnExceed
	 * @return string
	 */
	static public function cstrip( $string, $maxSize, $appendOnExceed = null )
	{
		if( $maxSize > 0 && strlen($string) > $maxSize )
			$string = substr($string,0,$maxSize) . (is_string($appendOnExceed) ? $appendOnExceed : '');
		return $string;
	}
	
	
	/**
	 * Cast an object between classes.
	 *
	 * @param object $obj
	 * @param string $to_class
	 * @return object|false
	 */
	static public function castObj($obj, $to_class)
	{
	  if(class_exists($to_class)) {
	    $obj_in = serialize($obj);
	    $obj_out = 'O:' . strlen($to_class) . ':"' . $to_class . '":' . substr($obj_in, $obj_in[2] + 7);
	    return unserialize($obj_out);
	  } else return false;
	}
	
	
	/**
	 * Get an object classname
	 *
	 * @param object $obj
	 * @return string
	 */
	static public function getObjClassname($obj)
	{
		if( !is_Object($obj) ) return '[not-an-object]';
		
		$t = serialize($obj);
		$s = strpos($t,'"')+1;
		$e = strpos($t,'"',$s);
		return substr($t,$s,$e-$s);
	}
	
	
	/**
	 * Print data to a file.
	 *
	 * @param string $file
	 * @param mixed $var
	 */
	static function put2file( $file, $var )
	{
		ob_start();
		var_dump($var);
		$o = ob_get_contents();
		ob_end_clean();
		@file_put_contents( dirname(__FILE__).'/'.$file, $o );
	}
	
	
	/**
	 * Write string to log.
	 *
	 * @param string $var
	 * @version 2.12
	 */
	static function log($var) {
	       $path = self::getLogPath();
	        if (!file_exists($path)) {
		   @chmod(dirname($path), 0755);
		   @file_put_contents($path,'');
		}
		@file_put_contents( $path, date(DATE_RFC822).': '.$var.PHP_EOL, FILE_APPEND);
	}
	
	/**
	 * Get content of the log
	 * @return string
	 */
	static function getLog() { return @file_get_contents(self::getLogPath()); }
	
	/**
	 * Get log file size
	 * @return bytes
	 */
	static function getLogSize() { return @filesize(self::getLogPath()); }
	
	/**
	 * Clear the log
	 */
	static function clearLog() { 
		@file_put_contents(self::getLogPath(), ''); 
	}
	
	/**
	 * Retrive current path to log
	 */
	static public function getLogPath() { return dirname(__FILE__).'/log.txt'; }
	
	/**
	 * Protocol error to log
	 * @param string $msg
	 */
	static function logError( $msg ) { self::log("Error: $msg"); }
	
	/**
	 * Procol a warning message to log
	 * @param string $msg
	 */
	static function logWarning( $msg ) { self::log("Warning: $msg"); }
	
	/**
	 * Procol a success message to log
	 * @param string $msg
	 */
	static function logSuccess( $msg ) { self::log("Success: $msg"); }
	
	
	
	/**
	 * Format bytes
	 *
	 * @param int $b
	 * @param uint $prec
	 * @return string
	 */
	static function formatBytes( $b, $prec = NULL ) {
		$e = 'B';
		if($b > 1024) { $b/=1024; $e='KB'; }
		if($b > 1024) { $b/=1024; $e='MB'; }
		if($b > 1024) { $b/=1024; $e='GB'; }
		if($b > 1024) { $b/=1024; $e='TB'; }
		return round($b,$prec).$e;
	}
	
	static function convert2Bytes($b) {
		$c=substr($b, -1); $f=1;
		switch($c) {
			case 'G': $f*=1024;
			case 'M': $f*=1024;
			case 'K': $f*=1024;
		}
		return ((int)substr($b,0,-1))*$f;
	}
	
	
	/**
	 * Strip strings
	 *
	 * @param string $str
	 * @param int $size
	 * @param string $strCat
	 * @return string
	 */
	static function stripStr( $str, $size, $strCat = NULL ) {
		if(strlen($str)>$size && $size>0) {
			$str = substr($str,0,$size);
			if($strCat!=NULL)
				$str .= $strCat;
		}
		return $str;
	}

	
	/**
	 * Convert a string to an alpha-numeric string
	 *
	 * @param string $string
	 * @param bool $trim_whitespace
	 * @return string
	 */
	function str2alnum( $string, $trim_whitespace = true )
	{
		if( $trim_whitespace )
			return preg_replace("/[^a-zA-Z0-9\s]/", "", $string);
		else return preg_replace("/[^a-zA-Z0-9]/", "", $string);
	}
	
	
	/**
	 * Make HTML/PHP querystring
	 *
	 * @param string $add A string of keys to add, concatenated with &
	 * @param string $remove A string of keys to remove, concatenated with &
	 * @param string[optional] $delimiter Character or string being the delimiter of the resulting querystring 
	 * @return string
	 */
	static public function mkqs( $add = '', $remove = '', $delimiter = '&' )
	{
		$get = $_GET;
		$t = explode('&',$add);
		foreach( $t as $v ) {
			$s = explode('=',$v);
			$get[$s[0]] = $s[1];
		}
		
		$t = explode('&',$remove);
		foreach( $t as $v ) {
			$s = explode('=',$v);
			if( isset($get[$s[0]])) unset($get[$s[0]]);
		}
		
		$out = array();
		foreach( $get as $k=>$v )
			if($k!='')
			$out[] = "$k=$v";
		return implode($delimiter,$out);
	}
	
	/**
	 * Make HTML/PHP url
	 *
	 * @param string $qsadd
	 * @param string $qsremove
	 * @param string $delimiter
	 * @return string
	 */
	static public function mkurl( $qsadd = '', $qsremove = '', $delimiter = '&' ) 
	{
		return basename($_SERVER['SCRIPT_URL']) . '?' . self::mkqs($qsadd,$qsremove,$delimiter);
	}

	/**
	 * Get current server version
	 * 
	 * @return string
	 */
	static public function getServerVersion() { return self::$serverVer; }
	
	/**
	 * Get current client version
	 * 
	 * @return string
	 */
	static public function getClientVersion() { return self::$clientVer; } 

	/**
	 * Get latest version from Netblog server, because betas are not published via wordpress.org
	 *
	 * @param string $stable
	 * @return string
	 */
	static public function getLatestVersion( $stable=false ) {
		require_once 'DataTransfer.php';
		$result = DataTransfer::RetrieveUrl(self::$uriGetVersion.($stable?'?stable':'?latest'));
		if( $result['content']!=null && strlen($result['content'])>0 )
			return $result['content'];
		else return self::$clientVer;
	}
	
	/*
	 * PRIVATE DATA
	 */
	static private $serverVer = '2.22';
	static private $clientVer = '2.22';
	static public $isStable = true;
	static public $name = 'Netblog';
	static public $build = '586074';
	static public $buildDate = 'Sep. 27, 2012';
	static public $author = 'Benjamin Sommer';
	static public $mail = 'developer@benjaminsommer.com';
	static public $web = 'http://netblog.benjaminsommer.com/';
	static public $uriGetVersion = 'http://netblog.benjaminsommer.com/getversion.php';
	static public $uriDownload = 'http://netblog.benjaminsommer.com/download.php';
	static public $uri_claction = 'http://netblog.benjaminsommer.com/claction.php';
	static public $pluginHook = '';
}


?>
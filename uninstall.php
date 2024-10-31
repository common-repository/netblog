<?php

if ( !defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
}

/*---------------------------------------------------------------------------------------------------------
** LOAD CONFIGURATION
**---------------------------------------------------------------------------------------------------------
*/
include_once('core/config.php');

function netblog_rm() {
	global $wpdb;
	
	// REMOVE DATABASE TABLES
	$dbtbl = array();
	$dbtbl[] = $wpdb->prefix . constant('NETBLOG_DB_NET');
	$dbtbl[] = $wpdb->prefix . constant('NETBLOG_DB_EXT');
	$dbtbl[] = $wpdb->prefix . constant('NETBLOG_DB_REL_EXTNODE');
	
	foreach( $dbtbl as $tbl ) {
		if( $wpdb->get_var("SHOW TABLES LIKE '$tbl'") == $tbl )
			$wpdb->query("DROP TABLE `$tbl`");
	}
	// 	$wpdb->query("DROP TABLE IF EXISTS ".implode(',',$dbtbl) ); 		// ALTERNATIVE METHOD - SOMEWHAT FASTER
}



// REMOVE OPTIONS
$options = array('netblog_version','netblog_db_net_ver','netblog_db_ext_ver','netblog_db_rel_extnode_ver','netblog_db_caption_ver',
			'netblog_db_host_tree_ver','netblog_caption_gadd','netblog_cite_style','netblog_cite_style_override',
			'netblog_note_format','netblog_mel_read','netblog_mel_edit','netblog_mel_start_tpl','netblog_mel_tpl','netblog_mel');
foreach( $options as $o )
	delete_option($o);	
?>



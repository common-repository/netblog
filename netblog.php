<?php
/*
	Plugin Name: NetBlog
	Plugin URI: http://netblog.benjaminsommer.com
	Description: Connect posts, pages and external resources (websites, pdf, doc, data). Use Captions, Cross-references, Bibliographies and Footnotes (all with wizards and tiny tools). It uses Pingbacks and Blogsearch to dynamically manage your Weblog content. Export/Import user data and create export schedules.
	Author: Benjamin Sommer
	Version: 2.22
	Author URI: http://benjaminsommer.com
	License: CC GNU GPL 2.0 license
	Text Domain: netblog

 */
   		
//---------------------------------------------------------------------------------------------------------
// SETUP REQUIRED DEPENDENCIES AND FEATURES
//---------------------------------------------------------------------------------------------------------

//define('NBDEBUG', true);
//define('NBSTART', microtime(true));

// STAND-ALONE UTILITIES
require_once 'core/nbPost.php';
include_once 'core/footprintConnect.php';
require_once 'core/nbTestPilot.php';
require_once 'core/nbFeedbackSubmit.php';
require_once 'core/nbsearch.php';


// CORE CLASSES
require_once 'core/nboption.php';
require_once 'core/nbdb.php';							// DEPRECATED
require_once 'core/sshndl_ajax.php';					// MOSTLY DEPRECATED
require_once 'core/netblog.php';


// FEATURE CLASSES
require_once 'core/nbLinkExternCollection.php';
require_once 'core/nbLinkIntern.php';
require_once 'core/nbMetaboxFurtherReading.php';
require_once 'core/nbWidgets.php';

require_once 'core/nbBibMetaboxGUI.php';
require_once 'core/nbcstyle.php';
require_once 'core/nbcite.php' ;
require_once 'core/nbMetaboxRefmaker.php';
require_once 'core/nbFootnote.php';

require_once 'core/nbCaptionGUI.php';
require_once 'core/nbcpt.php';							// MOSTLY DEPRECATED; REMOVED AND REPLACED BY nbCaption IN FUTURE VERSIONS
require_once 'core/nbCaption.php';

require_once 'core/nbBibStylesGUI.php';

require_once 'core/nbMel.php';
require_once 'core/nbMelGUI.php';

require_once 'core/nbExportSchedulerGUI.php';

require_once 'core/settings.php';
require_once 'core/nbLoggingGUI.php';

// WINDOW CLASSES
//require_once 'core/nbImportBibTexGUI.php';
require_once 'core/nbMainGUI.php';
require_once 'core/nbOptionsGUI.php';

// BETA FEATURE CLASSES
require_once 'core/netcit.php';
require_once 'core/netvis.php';
require_once 'core/nbcode.php';
require_once 'core/syntaxHighlighter.php';


require_once 'core/NetblogInit.php';
register_activation_hook (__FILE__, array( 'NetblogInit', 'on_activate' ));
register_deactivation_hook( __FILE__, array( 'NetblogInit', 'on_deactivate' ) );
register_uninstall_hook( __FILE__, array( 'NetblogInit', 'on_uninstall' ) );

// INITIALIZE APPLICATION
Netblog::init();

?>
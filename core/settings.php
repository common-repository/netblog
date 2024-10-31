<?php

//----------------------------------------------------------------------------------
// SETTINGS PAGE
//----------------------------------------------------------------------------------


function netblog_popup()
{
	
	$name = 'name';
	
	
	?>

	<div class="netblog-container" id="nbpopup_sets" style="display:none">
	<div class="nbarea-popup-bg opac50"></div>
	<div class="nbarea-popup-bg2 opac65"></div>
		<div style="width:480px; height:300px;" id="nbpopup_sets_wnd" class="nbpopup noopac shadow4">
			<h2>Confirm Settings Update</h2>
			<div style="margin-bottom: 10px;">A couple of WP Netblog Settings have been modified. Do you wish to save them now?</div>
			
			<div id="nbpop_details" style="display:none">
			<div style="float: right; padding: 3px 2px; color: #666; font-size: 10px" id="nbpop_details_stats">5 changes</div>
			<div style="font-weight:bold; padding: 3px 2px; background-color: #FFF;">Details</div>
			<div style="height: 130px; overflow: auto; width: 100%;" id="nbpop_tbl_details">						
			</div>
			</div>				
			
			<div style="text-align:center; margin: 20px;">
				<input type="button" value="Save" style="margin: 0 10px; cursor: pointer" 
					onclick="nbset_save();" id="nbpop_btn_save" />
				<input type="button" value="Close Window" style="margin: 0 10px; cursor: pointer" onclick="nbpop_unload()" /> 
				<input type="button" value="Undo changes" style="margin: 0 10px; cursor: pointer" onclick="nbset_clear(); nbpop_unload()" /> 
			</div>
		</div>		 
	</div>
	
	
	<div class="netblog-container" id="nbpopup_export" style="display:none">
	<div class="nbarea-popup-bg opac50"></div>
	<div class="nbarea-popup-bg2 opac65"></div>
		<div style="width:600px; height:440px"	id="nbpopup_export_wnd" class="nbpopup noopac shadow4">
			<h2>Export Netblog Data</h2>
			
			<form method="post" id="nbarea-export-form" />
			
			<div class="col1">Format</div>			
			<div class="col2">
				<?php
				require_once 'nbexp.php';
				$e = nbexp::getInstalledPlugins();
				$error = false;
				
				if(sizeof($e)>0) {
					echo '<select name="plugin" onchange="getExpOptions(this.value,\'none\',\'nbarea-exp-options\',\'nbarea-exp-status\')"
						id="nbexport-module">
							<option>Choose</option>';
					foreach($e as $pl) {
						if($pl['status'] == 'ok')
							echo '<option value="'.$pl['codenm'].'"> '.$pl['name'].' </option>';
						else $error = true;
					}
					echo '</select>';
					if($error)
						echo ' <em>Some Modules could not be loaded!</em>';
				} else {
					echo '<em>No Export Modules Found!</em>';
				}
							
				?>
				</div>
			<div class="nofloat"></div>
			<div class="col1">Export Schedules</div>
			<div class="col2">
				<?php
				require_once 'nbExportScheduler.php';
				$sched = new nbExportScheduler();
				if($sched->numItems() == 0 ) {
					echo '<em>No Export Schedules!</em>';
				} else {
					echo '<select onchange="getExpOptions(\'none\',this.value,\'nbarea-exp-options\',\'nbarea-exp-status\')" name="export_schedule_id"><option>Choose</option>';
					for($i=0; $i<$sched->numItems(); $i++) {
						$item = $sched->getItem($i);
						echo '<option value="'.$item->getId().'">'.$item->name.'</option>';
					}
					echo '</select>';
				}
				?>
				</div>
			<div class="nofloat nl"></div>
			
			<div id="nbarea-exp-options" style="overflow: auto; height:250px;">
			</div>							
			
			
			<div id="nbarea-exp-status" style="margin-top:10px; margin-bottom:0px; height:15px"></div>
			
			<div style="text-align:center; margin: 20px;">
				<input type="button" value="Export Now" style="margin: 0 10px; cursor: pointer"	
						onclick="sendExpOptions('nbarea-export-form','nbarea-exp-status',this)" id="" />
				<input type="button" value="Cancel" style="margin: 0 10px; cursor: pointer" onclick="nbpop_unload()" />  
			</div>
			
			</form>				
		</div>		 
	</div>
	
	
	<div class="netblog-container" id="nbpopup_import" style="display:none">
	<div class="nbarea-popup-bg opac50"></div>
	<div class="nbarea-popup-bg2 opac65"></div>
		<div style="width:950px; height:400px;" id="nbpopup_import_wnd" class="nbpopup noopac shadow4">
			<h2>Import Netblog Data</h2>
			
			<form method="post" id="nbarea-import-form" />
			
			<div style="overflow: auto; height:270px;">
			
			<div class="col1">Specify Data</div>	
			<div class="nofloat noflt"></div>
					
			<div class="col1r">From Server</div>
			<div class="col2">
				<select name="import_file" id="import_file" style="width: 95%"
					 onchange="getImpOptions('nbarea-imp-options','nbarea-imp-status',document.getElementById('nbarea-import-btn'),'nbarea-import-form',true)">
				<?php				
					require_once 'nbExportScheduler.php';
					require_once 'timeSpan.php';
					
					echo '<optgroup label="Manual Backups">';
					$p_ = dirname(__FILE__).'/../data/';					
					$d = scandir($p_);	
					$opts[] = array();				
					foreach($d as $fn)
						if(is_file($file=$p_.$fn)) {
							$p = strrpos($fn,'.');			
							$span = new timeSpan($t=(time()-filectime($p_.$fn)));			
							$opts[$t] = '<option value="'.$file.'" style="padding-left:7px">'.$span->getFormatted(true,2).' ago --- '.Netblog::stripStr(substr($fn,0,$p),45,'..').substr($fn,$p).'</option>';
						} 
					ksort($opts);
					echo implode('',$opts);
					echo '</optgroup>';					
					echo '<optgroup label="Scheduled Backups" >';
					
					$p = nbExportScheduler::getBackupLocation();
					$d = scandir($p);					
					$sched = new nbExportScheduler();
					$undefined = '';
					foreach($d as $e) {
						if($e == '.' || $e == '..' || !is_dir($p.$e)) continue;
						$item = $sched->getItemById($e);
						$d2 = scandir($subdir="$p$e/");
						$opts = array();
						foreach($d2 as $e2) {
							if( !is_file($file="$subdir$e2") ) continue;
							$span = new timeSpan($tdif=(time()-filectime($file)));
							$opts[filectime($file)] = $tun='<option value="'.$file.'" style="padding-left:15px">'. ($item==null ? "$e " : ''). $span->getFormatted(true,3).' ago</option>';
							if($item==null)
								$undefined .= $tun;
						}
						if( $item!=null ) {
							krsort($opts);
							echo '<optgroup label="'.$item->name.'" style="padding-left:7px">'.implode('',$opts).'</optgroup>';
						}
					}
					if(strlen($undefined)>0)
						echo '<optgroup label="Undefined" style="padding-left:7px">'.$undefined.'</optgroup>';
						
					echo '</optgroup>';
				 ?>
				</select></div>
			<div class="nofloat nl"></div>
			<div id="nbarea-imp-options">
			</div>					
			
			</div>
			
			<div id="nbarea-imp-status" style="margin-top:10px; margin-bottom:0px; height:15px"></div>
			
			<div style="text-align:center; margin: 20px;">
			
				<input type="button" value="Start Import" style="margin: 0 10px; cursor: pointer; visibility:hidden" 
						onclick="sendImpOptions('nbarea-import-form','nbarea-imp-status',this)" id="nbarea-import-btn" />
				<input type="button" value="Update Import Settings" style="margin: 0 10px; cursor: pointer;"
						onclick="getImpOptions('nbarea-imp-options','nbarea-imp-status',document.getElementById('nbarea-import-btn'),'nbarea-import-form',false)" id="nbarea-import-btn-update" />
				 <input type="button" value="Cancel" style="margin: 0 10px; cursor: pointer" onclick="nbpop_unload()" />
			</div>
			<div class="nofloat nl"></div>
			</form>				
		</div>		 
	</div>

	
	<script type="text/javascript">
	<!--
	
	
	function getExpOptions( pluginName, schedule_id, id, statusId ) {
		var d = document.getElementById(id);
		
		if(typeof(d) != "object" || typeof(pluginName) != "string" || pluginName.length == 0)
			return;
		
		var o = document.getElementById(statusId);		
		o.innerHTML = "Loading Export Settings...";
		d.innerHTML = '';
		
		jQuery(document).ready(function($) {
			var data = {
				action: 'nbexport_options_retrieve',
				plugin: pluginName,
				export_schedule: schedule_id
			};
			jQuery.post(ajaxurl, data, function(xml) {
				var type = 'error';
				var message = 'no message';
				if(xml == '0' || xml.length == 0) {
					message = 'Service Unavailable';
				}
				jQuery(xml).find("status").each(function() {
					type = jQuery(this).attr('type');
					message = jQuery(this).attr('message');
					o.innerHTML = ""+type.toUpperCase()+": " + message;					
				});
				jQuery(xml).find("parameter").each(function() {
					var plugin = jQuery(this).attr('plugin');
					document.getElementById('nbexport-module').value = plugin;
				});
				jQuery(xml).find("build_settings").each(function() {
					d.innerHTML = jQuery(this).text();
				});											
			});
		});
	}
	
	function getImpOptions( optId, statusId, button, formId, clearForm ) {		
		var d = document.getElementById(optId);
		if(clearForm) d.innerHTML = '';
		
		var str = jQuery("#"+formId).serialize();

		if(typeof(d) != "object")
			return;
			
		var o = document.getElementById(statusId);		
		o.innerHTML = "Status: reading file";
		
		jQuery(document).ready(function($) {
			var data = {
				action: 'nbimport_options_retrieve',
				form_encoded: str
			};			
			jQuery.post(ajaxurl, data, function(xml) {
				jQuery(xml).find("parameter").each(function() {
					var status = jQuery(this).attr('status');
					o.innerHTML = "Status: " + status;
					var status_options = jQuery(this).attr('status_options');
					if(status_options=='complete'){
						button.style.visibility = "visible";	
						button.disabled = "";
					} else {
						button.style.visibility = "hidden";	
						button.disabled = "disabled";
					}			
				});
				jQuery(xml).find("html_options").each(function() {
					d.innerHTML = jQuery(this).text();					
				});
										
			});
		});
	}
	
	function sendExpOptions( formId, statusId, button ){		
		var str = jQuery("#"+formId).serialize();
		if(str.length==0) return;
		
		var o = document.getElementById(statusId);		
		o.innerHTML = "Status: Building Export File, please wait...";
		
		var buttonOld = button.value;
		button.value = "Exporting...";

		jQuery(document).ready(function($) {
			var data = {
				action: 'nbexport_options_send',
				form_encoded: str
			};
			jQuery.post(ajaxurl, data, function(xml) {
				jQuery(xml).find("parameter").each(function() {
					var status = jQuery(this).attr('status');
					if(status == 'OK') {
						o.innerHTML = "OK: " + jQuery(this).attr('message');
						setTimeout('nbpop_unload()',3000);						
					} else {
						o.innerHTML = "Status: " + status;
					}
				});
				jQuery(xml).find("data").each(function() {
					alert(jQuery(this).text());
					document.getElementById('nbxml').innerHTML = jQuery(this).text();
				});
				button.value = buttonOld;										
			});
		});
	}
	
	function sendImpOptions( formId, statusId, button ){	
		var str = jQuery("#"+formId).serialize();
		if(str.length==0) return;
		
		var o = document.getElementById(statusId);		
		o.innerHTML = "Status: Importing data, please wait...";

		var buttonOld = button.value;
		button.value = "Importing...";	
		jQuery(document).ready(function($) {
			var data = {
				action: 'nbimport_options_send',
				form_encoded: str
			};
			jQuery.post(ajaxurl, data, function(xml) {
				jQuery(xml).find("parameter").each(function() {
					var status = jQuery(this).attr('status');
					o.innerHTML = "Status: " + status;
				});					
				button.value = buttonOld;										
			});
		});
	}
	//-->
	</script>
	
	<?php
}




function netblog_settings()
{
	netblog_popup();
	
	$t = Netblog::options()->getGUISpeed();
	switch($t) {
		case 'medium': case 'mid':
			$nbareaJsSpeed = 150;
			break;
		case 'slow': case 'low':
			$nbareaJsSpeed = 250;
			break;
		case 'ultra_fast':
			$nbareaJsSpeed = 10;
			break;
		case 'instant':
			$nbareaJsSpeed = 0;
			break;
		default:
			$nbareaJsSpeed = 50;
	}
	
		
	echo '<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>';
	
	netblog_feedback_smilies();

	echo '<h2>Netblog '.__( 'Settings', 'netblog').'</h2>';
	
	if(($v=Netblog::getLatestVersion()) > Netblog::options()->getClientVersion() ) {
		echo '<br />';
		require_once 'infobox.php';
		$box = new infobox("There is a new version of Netblog $v available. Please <a href=\"".Netblog::$uriDownload."\">update now</a>.");
		$box->display();
	}
	?>
	
	<textarea style="width:100%; height:150px; display:none" id="nbset_hist_opts"></textarea>

	
	<br />
	<form action="<?php echo Netblog::mkurl(); ?>" method="post">
	<div class="netblog-area" id="nbset-box" style="display:none">
		<div class="nbarea-menu" id="nbarea-menu">	
			<div class="nbareabtn" onmouseover="toogleArea('nbset_area_general','gray',this)" id="nbarea-general">General</div>
			<div class="nbareaLn"></div>
			<div class="nbareabtn" onmouseover="toogleArea('nbset_area_mel','yellow',this)" id="nbarea-mel">MEL</div>
			<div class="nbareabtn" onmouseover="toogleArea('nbset_area_search','orange',this)" id="nbarea-search">Search</div>
			<div class="nbareabtn" onmouseover="toogleArea('nbset_area_note','vine',this)" id="nbarea-footnotes">Footnotes</div>
			<div class="nbareabtn" onmouseover="toogleArea('nbset_area_cite','blue',this)" id="nbarea-cites">Citations</div>
			<div class="nbareabtn" onmouseover="toogleArea('nbset_area_fig','green',this)" id="nbarea-fig">Captions</div>
			<div class="nbareaLn"></div>
			<div class="nbareabtn" onmouseover="toogleArea('nbset_area_export','navi',this)" id="nbarea-exp">Export - Import</div>
			<div class="nbareabtn" onmouseover="toogleArea('nbset_area_adv','brown',this)" id="nbarea-adv">Advanced</div>
			<div class="nbareaLn"></div>
			<div class="nbareabtn" onmouseover="toogleArea('nbset_area_about','black',this)" id="nbarea-about" >About</div>
			<div class="nbareaLn"></div>
			<div class="nbareaLn"></div>
			<div class="nbareabtn nbarea-bg-blackt" id="nbarea-btn-save" onclick="nbpop_load_set('nbpopup_sets')"></div>
			<div class="noflt"></div>			
		</div>		
		<div class="nbarea-body" id="nbarea-body">
			<div id="nbset_area_general" style="display:none"><?php echo netblog_settings_general(); ?></div>
			
			<div id="nbset_area_mel" style="display:none"><?php echo netblog_settings_mel(); ?></div>
			<div id="nbset_area_search" style="display:none"><?php echo netblog_settings_search(); ?></div>
			<div id="nbset_area_note" style="display:none"><?php echo netblog_settings_note(); ?></div>
			<div id="nbset_area_cite" style="display:none"><?php echo netblog_settings_cite(); ?></div>
			<div id="nbset_area_fig" style="display:none"><?php echo netblog_settings_fig(); ?></div>
			
			<div id="nbset_area_export" style="display:none"><?php echo netblog_settings_export(); ?></div>
			<div id="nbset_area_adv" style="display:none"><?php echo netblog_settings_adv(); ?></div>
			
			<div id="nbset_area_about" style="display:none"><?php echo netblog_settings_about(); ?></div>	
		</div>
		<div class="noflt"></div>
	</div>
	</form>

	
	<script type="text/javascript">
	<!--
	var area = null;
	var menu_btn = null;
	var areaBody = null;
	var areaMenu = null;
	var timeout = null;
	
	function toogleArea(id, color, menuBtn )
	{		
		var speed = <?php echo $nbareaJsSpeed; ?>;
		if(timeout == null) {
			timeout = setTimeout('toogleAreaDraw(\''+id+'\',\''+color+'\',\''+menuBtn.id+'\')',speed);
			return;
		} else {
			clearTimeout(timeout);
			timeout = setTimeout('toogleAreaDraw(\''+id+'\',\''+color+'\',\''+menuBtn.id+'\')',speed);
			return;
		}		
	}
	function toogleAreaDraw(id, color, menuBtnId)
	{
		// draw
		if(area != null)
			area.style.display = 'none';
		area = document.getElementById(id);
		area.style.display = 'block';
		
		var menuBtn = document.getElementById(menuBtnId);
		
		if(menu_btn != null)
			menu_btn.className = "nbareabtn";
		menuBtn.className = "nbareabtn nbarea-bg-" + color;
		menu_btn = menuBtn;

		if(areaBody == null) {
			areaBody = document.getElementById('nbarea-body');
			areaMenu = document.getElementById('nbarea-menu');
		}
		areaBody.className = "nbarea-body nbarea-" + color;
		if( area.offsetHeight < areaMenu.offsetHeight )
			area.style.height = areaMenu.offsetHeight + "px";
			
		timeout = null;
	}
	//-->
	</script>
		
		
	<script type="text/javascript">
	<!--
	var nbpop_wnd = document.getElementById('nbpopup');		
	var nbsetopts = document.getElementById('nbset_hist_opts');	
	var nbdelimMain = ";\n";
	var nbdelimSub = ", ";
	var nbdelimSub2 = "|||";
	var nbset_btn_save = document.getElementById('nbopts_btn_save');
	var nbsaved = true;
	
	var nbopts_preview = []; 
	
	function nbpop_render_details() {	
		
		var str = "";

		if(nbopts_preview!=null && nbopts_preview.length > 0) {					 
			for( var i = 0; i < nbopts_preview.length; i++ ) {
			 	str += '<div class="nbpop_tbl_row"><div class="nbpop_tbl_left">'
			 			+ nbopts_preview[i].keyword + '</div><div class="nbpop_tbl_right">'
			 			+ nbopts_preview[i].value + '</div><div class="noflt"></div></div>';
			}
			document.getElementById('nbpop_details_stats').innerHTML = nbopts_preview.length + " changes";
			document.getElementById('nbarea-btn-save').innerHTML = 'Save Changes ('+nbopts_preview.length+')';			
		} else {
			document.getElementById('nbarea-btn-save').innerHTML = '';
		}	
		
		document.getElementById('nbpop_tbl_details').innerHTML = str;
			
		return true;
	}
	
	function nbpop_load_set(id) {
		if(nbsaved)
			return;		
		var savebtn = document.getElementById('nbpop_btn_save');		
		savebtn.value = "Save";
		savebtn.disabled = false;			
		nbpop_render_details();		
		document.getElementById('nbpop_details').style.display = 'block';
		
		nbpop_load(id);
	}
	
	function nbset_mkopt( id, param, keyword_nice, value_nice ) 
	{
		if(typeof(keyword_nice) == 'undefined' || typeof(value_nice) == 'undefined')
			return;
		
		if( value_nice == null )
			value_nice = param;
		
		if( typeof(param) == 'boolean' ) {
			if(param) value_nice = 'Enabled';
			else if(!param)	value_nice = 'Disabled';
		} else if(isArray(param)) {
			var str = "";
			for(var i = 0; i < param.length; i++ ) {
				str += param[i];
				if( i < param.length-1 )
					str += nbdelimSub2;
			}
			param = str;
		}
		if(isArray(value_nice)) {
			var str = "";
			for(var i = 0; i < value_nice.length; i++ ) {
				str += value_nice[i];
				if( i < value_nice.length-1 )
					str += ", ";
			}
			value_nice = str;
		}
		
		for( var i = 0; i < nbopts_preview.length; i++ ) 
			if( nbopts_preview[i].id == id ) {
				if( nbopts_preview[i].param == param )
					return;
				nbopts_preview[i].param = param;
				nbopts_preview[i].keyword = keyword_nice;
				nbopts_preview[i].value = value_nice;
				nbset_mkopt_rebuild_datapack();
				return;
			}
			
		nbopts_preview.push( {'keyword':keyword_nice, 'value':value_nice, 'id':id, 'param':param} );		
		nbsetopts.innerHTML += id + nbdelimSub + param + nbdelimMain;
		
		document.getElementById('nbarea-btn-save').innerHTML = 'Save Changes ('+nbopts_preview.length+')';
		nbsaved = false; 
	} 
	
	
	function isArray(testObject) {   
	    return testObject && !(testObject.propertyIsEnumerable('length')) && typeof testObject === 'object' && typeof testObject.length === 'number';
	}
	
	function nbset_mkopt_rebuild_datapack() {
		var str = "";
		for( var i = 0; i < nbopts_preview.length; i++ )
			str += nbopts_preview[i].id + nbdelimSub + nbopts_preview[i].param + nbdelimMain;
		nbsetopts.innerHTML = str;
	}
	
	function nbset_save()
	{
		if(nbsaved)
			return true;
		if(nbopts_preview == null || nbopts_preview.length == 0 || nbsetopts == null )
			return false;
			
		var savebtn = document.getElementById('nbpop_btn_save');		
		savebtn.value = "Saving...";
		savebtn.disabled = true;
			
		jQuery(document).ready(function($) {
			var data = {
				action: 'nbopts_save',
				data: nbsetopts.innerHTML
			};
			jQuery.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  data: data,
			  async: false,
			  success: function(r) {
					savebtn.disabled = false;
					r = r.trim();
					if(r.length == 0 || r == "true" || r == "1" || r == "success") {
						nbsaved = true;
						nbset_clear();
						savebtn.value = "Saved!";
						setTimeout('nbpop_unload()',500);
					} else if( r.match(/fatal error/i)) {
						alert(r);
					} else {
						var nboptions = [];
						nbsetopts.innerHTML = r;
						var ids = r.split(';');
						for( var i = 0; i < ids.length; i++ ) {
							var tr = ids[i].trim();
							var t = tr.split(',');
							if(tr.length > 0 && t!=null && t.length > 1 && t[0].length > 0) {
								for(var j = 0; j<nbopts_preview.length; j++) {
									if(nbopts_preview[j].id == t[0]) {
										nboptions.push(nbopts_preview[j]);
									}
								}
							}
						}
						nbopts_preview = nboptions;
						nbpop_render_details();
						if(nbopts_preview.length > 0)
							savebtn.value = "Saved with Errors";
						else { 
							savebtn.value = "Settings Saved!";
							nbsaved = true;
						}
						setTimeout('document.getElementById(\'' + savebtn.id + '\').value = \'Save\';',5000);
					}		
			  }
			});

		});
		return nbsaved;
	}
	
	function nbset_clear() {
		nbsaved = true;
		nbsetopts.innerHTML = "";
		document.getElementById('nbarea-btn-save').innerHTML = "";
		nbopts_preview = [];
		nbpop_render_details();
	}
	
	function nbset_leave()	{
		if(!nbsaved) {		
			if( confirm("You have unsaved changes. Save them now?") ) {
				if( !nbset_save() )
					alert("Failed to save changes.");
			} else {
			
			}
		}
	}
	
	
	window.onbeforeunload = nbset_leave;
	window.onload = function() { show('nbset-box',450,500); }
	toogleArea('nbset_area_general','gray',document.getElementById('nbarea-general'));
	
	//-->
	</script>
	
	<?php
	//echo '<pre>';
	//$sched = new nbExportScheduler();
	//var_dump($sched);
	//$start = microtime();
	//$pilot = new nbTestPilot();
	//$pilot->collectStatistics();
	//var_dump($pilot->collectEvents());
	//$end = microtime();
	//echo 'time: '.($end-$start).' ms';
	//echo '</pre>';
	echo '</div>';
}

function netblog_settings_mkmenuclass( $button )
{
	if( isset($_GET['sub']) && $_GET['sub'] == $button )
		return 'lnk-active';
	else return 'lnk';
}

function netblog_settings_general()
{
	ob_start();
	
	$nbareaSpeed = array('instant'=>__('Instant','netblog'), 'fast'=>__('Fast','netblog'), 'medium'=>__('Medium','netblog'));
	$nbareaSpeedSlc = '';
	$_s = Netblog::options()->getGUISpeed();
	foreach($nbareaSpeed as $k=>$v) {
		$nbareaSpeedSlc .= '<option value="'.$k.'"';
		if($k==$_s) $nbareaSpeedSlc .= ' selected="selected"';
		$nbareaSpeedSlc .= ' onclick="nbset_mkopt(\'setGUISpeed\', \''.$k.'\', \'Netblog GUI Speed\', \''.$v.'\' )">'.$v.'</option>';
	}
	
	$nbareaAutocplSlc = '';
	$_a = Netblog::options()->getGUIAutocompleteMinLen();
	for($i = 1; $i<10; $i++) {		
		$nbareaAutocplSlc .= '<option value="'.$i.'"';
		if($i==$_a) $nbareaAutocplSlc .= ' selected="selected"';
		$nbareaAutocplSlc .= ' onclick="nbset_mkopt(\'setGUIAutocompleteMinLen\', \''.$i.'\', \'Netblog GUI Auto-Complete\', \''.$i.' Characters at least\' )">'.$i.'</option>';
	}
	
	?>


	
	<label class="col1"><?php _e('Widgets','netblog'); ?></label>
	<div class="col2"><label>&nbsp;</label></div>	
	<div>
		<div class="col1r"><?php _e('Further Reading','netblog') ?></div>
		<div class="col2">
			<input type="checkbox" value="true" id="nbarea-gen-widget-outnode" <?php checked(Netblog::options()->useWidgetOutnodes()) ?>
				onclick="nbset_mkopt('enableWidgetOutnodes', this.checked, 'Further Reading Widget', this.checked )" /> 
			<label for="nbarea-gen-widget-outnode" style="font-weight:normal"><?php _e('Enable and display links to other posts and websites.','netblog'); ?></label>
			</div>
		
		<div class="col1r"><?php _e('Referenced By','netblog') ?></div>
		<div class="col2">
			<input type="checkbox" value="true" id="nbarea-gen-widget-innode" <?php checked(Netblog::options()->useWidgetInnodes()) ?>
				onclick="nbset_mkopt('enableWidgetInnodes', this.checked, 'Referenced By Widget', this.checked )" /> 
			<label for="nbarea-gen-widget-innode" style="font-weight:normal"><?php _e('Enable and display incoming links from other posts and websites.','netblog'); ?></label>
			</div>	
	</div>
	
	
	<label class="col1"><?php _e('Wizards','netblog'); ?></label>
	<div class="col2"><label>&nbsp;</label></div>
	<div>
		<div class="col1r"><?php _e('Further Reading','netblog') ?></div>
		<div class="col2">
			<input type="checkbox" value="true" id="nbarea-gen-wizard-outnode" <?php checked(Netblog::options()->useWizardOutnodes()) ?>
				onclick="nbset_mkopt('enableWizardOutnodes', this.checked, 'Further Reading Wizard', this.checked )" /> 
			<label for="nbarea-gen-wizard-outnode" style="font-weight:normal"><?php _e('Enable and easily link to external resources.','netblog'); ?></label>
			</div>
		
		<div class="col1r"><?php _e('Reference Maker','netblog') ?></div>
		<div class="col2">
			<input type="checkbox" value="true" id="nbarea-gen-wizard-innode" <?php checked(Netblog::options()->useWizardRefmaker()) ?>
				onclick="nbset_mkopt('enableWizardRefmaker', this.checked, 'Reference Wizard', this.checked )" /> 
			<label for="nbarea-gen-wizard-innode" style="font-weight:normal"><?php _e('Enable, speed up and simplify creation of references.','netblog'); ?></label>
			</div>	
	</div>
	
	<div class="lb"></div>
	<label class="col1"><?php _e('Sidebar') ?></label>
	<div class="col2">
		<input type="checkbox" name="netblog_sidebar" value="true" id="netblog_sidebar_sidebar" <?php checked( Netblog::options()->useSidebar() ) ?> 
			onclick="nbset_mkopt('enableSidebar', this.checked, 'Use Sidebar', this.checked )" /> 
			<label for="netblog_sidebar_sidebar" style="font-weight:normal">
				<?php _e('Enable and display Widgets below articles') ?>				
			</label><br />
			<small><?php _e('Some user reported that this functionality did not work with their installation. Should you experience any problems, you might want to disable it and please do not hesitate to inform the developer.') ?></small>
		</div>
		
	
	<label class="col1"><?php _e('Netblog GUI','netblog'); ?></label>
	<div class="col2">&nbsp;</div>	
		<div class="col1r"><?php _e('Responsiveness','netblog') ?></div>
		<div class="col2"><select onchange="nbset_mkopt(\'setGUISpeed\', \''.$k.'\', \'Netblog GUI Speed\', \''.$v.'\' )"><?php echo $nbareaSpeedSlc ?></select></div>
	
		<div class="col1r"><?php _e('Auto-Complete','netblog') ?></div>
		<div class="col2"><select onchange="nbset_mkopt(\'setGUIAutocompleteMinLen\', \''.$i.'\', \'Netblog GUI Auto-Complete\', \''.$i.' Characters at least\' )"><?php echo $nbareaAutocplSlc ?></select> <?php _e('Characters minimum') ?></div>
			
	<!-- 
	<div class="lb"></div>	
	<label class="col1"><?php _e('Footprints','netblog'); ?></label>
	<div class="col2">
		<input type="checkbox" name="netblog_footprints" value="save_post" id="netblog_footprints-checkbox" <?php checked( Netblog::options()->useFootprints() ) ?> /> 
			<label for="netblog_footprints-checkbox" style="font-weight:normal"><?php _e('Activate Footprints','netblog'); ?></label><br />
			<small><?php _e('Note: This is still a Beta feature. If you deactivate Footprints, internal links will not be exported.', 'netblog') ?> <a href="netblog.benjaminsommer.com/footprints"><?php _e('Learn more about Footprints.', 'netblog') ?></a></small> 
		</div>
	-->
						
					
	<?php
	$o = ob_get_contents();
	ob_end_clean();	
	return $o;
}


function netblog_settings_mel()
{
	ob_start();	
		
	$roles = array( 	'activate_plugins'=> __('Administrator','netblog'), 
						'delete_pages'=> __('Editor','netblog'), 
						'publish_posts'=> __('Author','netblog'), 
						'edit_posts'=> __('Contributor','netblog'), 
						'read'=> __('Subscriber','netblog')
	);
	$mel_tpl = array( 	'new'=>__('Just Added','netblog'), 
						'popular'=>__('Most Popular','netblog'), 
						'offline'=>__('Offline','netblog'), 
						'trash'=>__('Trashed','netblog') 
	);
	$saveOffset = array( 0=>__('Immediate','netblog'), 
						5=> vsprintf(_n("%d second", "%d seconds", 5, 'netblog'), 5), 
						10=>vsprintf(_n("%d second", "%d seconds", 10, 'netblog'), 10), 
						20=> vsprintf(_n("%d second", "%d seconds", 20, 'netblog'), 20), 
						45=> vsprintf(_n("%d second", "%d seconds", 45, 'netblog'), 45),
						60=> vsprintf(_n("%d second", "%d seconds", 60, 'netblog'), 60),
						120=> vsprintf(_n("%d min", "%d mins", 120, 'netblog'), 120),
						240=> vsprintf(_n("%d min", "%d mins", 240, 'netblog'), 240)
	);
	
	if( isset($_GET['rmtpl']) ) {
		netblog_mel_tpl_rm($_GET['rmtpl']);
		unset($_GET['rmtpl']);
	}

	$mel_tpls_user = Netblog::options()->getMelUserTpls();
	$tplStartup = Netblog::options()->getMelTplStartup();
	
	$selectMelTplUser = '';
	if(is_array($mel_tpls_user))
	foreach($mel_tpls_user as $name=>$value)
		$selectMelTplUser .= '<option value="'.$name.'" '.selected($name==$tplStartup,true,false)."
			onclick=\"nbset_mkopt('setMelTplStartup', this.value, 'MEL Startup Template', '".(ucfirst($name))."' )\">".ucfirst($name).'</option>';

	?>
	

	<label class="col1">MEL - <?php _e('Manage Extern Links','netblog'); ?></label>
	<div class="col2"><input type="checkbox" value="true" <?php checked( Netblog::options()->useMel() ) ?> id="mel-enable"
			 onclick="nbset_mkopt('enableMel', this.checked, 'MEL', this.checked )" /> 
			<label for="mel-enable" style="font-weight:normal"><?php _e('Enabled','netblog'); ?></label></div>	

	<div class="col1r"><?php _e('Startup Template','netblog'); ?></div>
	<div class="col2"><select name="netblog_mel_start_tpl" onchange="nbset_mkopt('setMelTplStartup', this.value, 'MEL Startup Template', this.value )"><optgroup label="Built-in Templates"><?php 
				$tpl = Netblog::options()->getMelTplStartup();
				if( is_array($mel_tpl) )
				foreach( $mel_tpl as $k=>$nm )
					echo '<option value="'.$k.'" '.selected( $k == $tpl, true, false)."
		onclick=\"nbset_mkopt('setMelTplStartup', this.value, 'MEL Startup Template', '$nm' )\">".$nm.'</option>';
				?></optgroup>
			<optgroup label="Custom Templates"><?php echo $selectMelTplUser; ?></optgroup></select></div>
		
 	<div class="col1r"><?php _e('Custom Search Templates','netblog'); ?></div>
	<div class="col2">
		<input type="button" id="mel-add-tpl" value="<?php _e('Add Template','netblog'); ?>" class="fltrght"
			onclick="nbset_mkopt('__addMelTpl__' + document.getElementById('mel-tpl-name').value, 
						['name:'+document.getElementById('mel-tpl-name').value, 
							'query:'+document.getElementById('mel-tpl-val').value], 
						'Add MEL Template', 
						[document.getElementById('mel-tpl-name').value, document.getElementById('mel-tpl-val').value] )"
			style="display:<?php echo Netblog::options()->useMelTpl() ? 'block' : 'none' ?>"/>
			
		<input type="checkbox" value="true" id="mel-tpl-enable" <?php checked( Netblog::options()->useMelTpl()) ?> 
			onclick="revealPara('mel-para-custom-tpl',this); revealPara('mel-add-tpl',this); nbset_mkopt('enableMelTpl', this.checked, 'MEL Templates', this.checked )"/> 
			<label for="mel-tpl-enable" style="font-weight:normal"><?php _e('Enabled','netblog'); ?></label></div>
			
	<div id="mel-para-custom-tpl" style="display:<?php echo Netblog::options()->useMelTpl() ? 'block' : 'none' ?>">			
		
								
		<div class="col1r nbcol-emph"><?php _e('Screen Name') ?></div>
		<div class="col2">
				
				<input type="text" id="mel-tpl-name" value="" size="15" onkeydown="str2alphanumNW(this);input_strip(this,35)" title="<?php _e('Screen name','netblog'); ?>"
					style="width:300px;" />
				
					</div>
		
		<div class="col1r nbcol-emph"><?php _e('Query','netblog'); ?></div>
		<div class="col2"><input type="text" id="mel-tpl-val" value=""
					title="<?php printf( __('Your custom search query, e.g. %s','netblog'), 'wordpress tpl:new sort:id-desc,title limit:50 flag:offline' ); ?> "
					style="width:300px; "/></div>
			
		<div class="col1r nbcol-emph"><?php _e('Query Syntax','netblog'); ?></div>
		<div class="col2" style="color:#666">
					[name]<br />				
					sort:[id,title,uri,refs,flag]<br />
					limit:[integer]<br />
					flag:[online,offline,trash]<br />
					tpl:[new,popular,offline,trash,valid custom template name]</div>
		
		<div class="col1r nbcol-emph"><?php _e('Examples','netblog'); ?></div>
		<div class="col2" style="color:#666">
					wordpress sort:title,uri limit:20 flag:online<br />
					netblog tpl:popular</div>		
					
		<?php if( is_array($mel_tpls_user) && sizeof($mel_tpls_user) > 0 ) { ?>
		<div class="col1r nbcol-emph"><?php _e('Templates') ?></div>
		<div class="col2"><?php				
			foreach( $mel_tpls_user as $nm=>$val )
				echo '<a class="del" title="'.__('Remove this template','netblog').'" 
					onclick="'."nbset_mkopt('__rmMelTpl__$nm', ['name:$nm','query:$val'], 'Remove MEL Template', ['$nm','$val'] )".'"></a><div class="col1">'.$nm.'</div><div class="col2-sl">'.$val.'</div>';
			?></div>
		<?php } ?>
	</div>
		
	<label class="col1"><?php _e('Privileges','netblog'); ?></label>
	<div class="col2">&nbsp;</div>
		
 	<div class="col1r"><?php _e('Read/Access Privilege','netblog'); ?></div>
	<div class="col2"><select name="netblog_mel_read" onchange="nbset_mkopt('setMelPread', this.value, 'MEL Read Privilege', this.value )"><?php 
			$r = Netblog::options()->getMelPread();
			if(is_array($roles))
			foreach( $roles as $role=>$nm )
				echo '<option value="'.$role.'" '.selected( $role == $r, true, false)."
		onclick=\"nbset_mkopt('setMelPread', this.value, 'MEL Read Privilege', '$nm' )\">".$nm.'</option>';
				
		?></select></div>	
		
 	<div class="col1r"><?php _e('Edit Privilege','netblog'); ?></div>
	<div class="col2"><select name="netblog_mel_edit" onchange="nbset_mkopt('setMelPedit', this.value, 'MEL Write Privilege', this.value )"><?php 
			$r = Netblog::options()->getMelPedit();
			if(is_array($roles))
			foreach( $roles as $role=>$nm )
				echo '<option value="'.$role.'" '.selected( $role == $r, true, false)."
		onclick=\"nbset_mkopt('setMelPedit', this.value, 'MEL Write Privilege', '$nm' )\">".$nm.'</option>';
				
		?></select></div>		
		
	<div class="col1r"><?php _e('Auto-save after modification','netblog'); ?></div>
	<div class="col2"><select name="netblog_mel_save" onchange="nbset_mkopt('setMelSavetime', this.value, 'MEL Auto-save Interval', this.value )"><?php 
			$r = Netblog::options()->getMelSavetime();
			if(is_array($saveOffset))
			foreach( $saveOffset as $time=>$nm )
				echo '<option value="'.$time.'" '.selected( $time == $r, true, false)."
		onclick=\"nbset_mkopt('setMelSavetime', this.value, 'MEL Auto-save Interval', '$nm' )\">".$nm.'</option>';
				
		?></select></div>
	 
	 <script type="text/javascript">
	<!--
	/* PRINT HELP BOX */

	var optHelp = document.getElementById('nb-help-enabled');

	var lastHelpBox = null;
	
	function help( helpid, sourceObj )
	{
	if( !optHelp.checked ) return;
	
	if( lastHelpBox != null && lastHelpBox.id != helpid )
		hidePlus( lastHelpBox.id, 0, 200, true, true );
		
	var box = document.getElementById(helpid);
	if( box == null )
		return;
	
	showPlus( box.id, 400, 200, true, true );
	lastHelpBox = box;

	}	
		
	function revealPara( targetid, inObj )
	{
		var target = document.getElementById(targetid);
		if( target == null ) return;
		
		if( inObj.checked )
			showPlus(targetid,0,250,true,false);
		else hidePlus(targetid,0,250,true,false); 
	}	

	-->
	</script>	
	<?php
	$o = ob_get_contents();
	ob_end_clean();	
	return $o;
}

function netblog_settings_search()
{
	ob_start();	
	
	$search = new nbsearch();
	$search->loadDefinitions();
	
	$slctBlogsearch = '';
	if($search->getBlogsearchs()!=null)
	foreach($search->getBlogsearchs() as $s) {
		$slctBlogsearch .= '<option value="'.$s['id'].'">'.ucfirst($s['name']).'</option>';
	}
	
	$slctWebsearch = '';
	if($search->getWebsearchs()!=null)
	foreach($search->getWebsearchs() as $s) {
		$slctWebsearch .= '<option value="'.$s['id'].'">'.ucfirst($s['name']).'</option>';
	}
	
	?>
	 	
	<label class="col1"><?php _e('Blogsearch','netblog'); ?></label>	
	<div class="col2">
		<?php if(strlen($slctBlogsearch)>0) { ?>
			<select id="blogsearch-preset"><option value=""><?php _e('Predefined Provider','netblog'); ?></option>
			<?php echo $slctBlogsearch ?></select>
			<input type="button" name="blogsearch-tpl" value="<?php _e('Apply','netblog'); ?>" 
				onclick="loadSearchProvider(document.getElementById('blogsearch-preset').value,'blog','nbsearch-blog-status')" />&nbsp;
			<span class="netblog-box-info nodisplay" id="nbsearch-blog-status"></span>
		<?php } else _e('No predefined providers found','netblog'); ?>
	</div>
		
	 	<div class="col1r"><?php _e('Provider Name','netblog'); ?></div>
		<div class="col2">
			<input type="text" id="nbsearch-blog-name" size="40" value="<?php echo Netblog::options()->getBlogsearchProviderName(); ?>" 
				onkeyup="nbset_mkopt('setBlogsearchProviderName', this.value, 'Blogsearch Provider Name', this.value )" />
		</div>	
	 	<div class="col1r"><?php _e('Provider URL','netblog'); ?></div>
		<div class="col2">
			<input type="text" id="nbsearch-blog-urlprovider" size="40" value="<?php echo Netblog::options()->getBlogsearchProviderUri();  ?>"
				onkeyup="nbset_mkopt('setBlogsearchProviderUri', this.value, 'Blogsearch Provider URI', this.value )" />			
		</div>	
	 	<div class="col1r"><?php _e('URL','netblog'); ?></div>
		<div class="col2">
			<input type="text" id="nbsearch-blog-urlquery" value="<?php echo Netblog::options()->getBlogsearchUri(); ?>" style="width:100%"
			 onkeyup="nbset_mkopt('setBlogsearchUri', this.value, 'Blogsearch URI', this.value )" />
			<div class="helpbox" id="hblog-uri" style="display:none; position: relative">
				<div class="pad"><b><?php _e('Blogsearch URL','netblog'); ?></b> 
					<small><i><?php _e('must return a valid XML Feed','netblog'); ?></i></small><br />
				<div class="netblog-indent-hang-long"><?php printf(__('%s := will be replaced either with Global Max Results or with a lower number
					so that the total number of links displayed will not exceed the maximum
					number defined by the user in the widget section.','netblog'),'??count??') ?></div>
				<div class="netblog-indent-hang-long"><?php printf(__('%s := will be replaced with the query.','netblog'),'??query??'); ?></div> 				
				</div></div>
		</div>
	 	<div class="col1r"><?php _e('Global Max Results','netblog'); ?></div>
		<div class="col2">
			<input type="text" id="nbsearch-blog-maxresults" size="3" value="<?php echo Netblog::options()->getBlogsearchMaxResults(); ?>" 
				onkeyup="str2int(this); nbset_mkopt('setBlogsearchMaxResults', this.value, 'Blogsearch Max Results', this.value )"/>
		</div>			
		<div class="col1r"><?php _e('API Key','netblog'); ?></div>
		<div class="col2">
			<input type="text" id="nbsearch-blog-apikey" size="40"  value="<?php echo Netblog::options()->getBlogsearchAPIKey() ?>"
				onkeyup="nbset_mkopt('setBlogsearchAPIKey', this.value, 'Blogsearch API Key', this.value )" />
		</div>
	
	<div class="hr"></div>
	
	<div class="lb"></div>	
	<label class="col1"><?php _e('Websearch','netblog'); ?></label>
	<div class="col2">
		<?php if(strlen($slctWebsearch)>0) { ?>
			<select id="websearch-preset"><option value=""><?php _e('Predefined Provider','netblog'); ?></option>
				<?php echo $slctWebsearch ?></select>
			<input type="button" name="blogsearch-tpl" value="<?php _e('Apply','netblog'); ?>" 
				onclick="loadSearchProvider(document.getElementById('websearch-preset').value,'web','nbsearch-web-status')" />&nbsp;
			<span class="netblog-box-info nodisplay" id="nbsearch-web-status"></span>
		<?php } else _e('No predefined providers found','netblog'); ?>
	</div>
		
	 	<div class="col1r"><?php _e('Provider Name','netblog'); ?></div>
		<div class="col2">
			<input type="text" id="nbsearch-web-name" size="40" value="<?php echo Netblog::options()->getWebsearchProviderName() ?>"
				onkeyup="nbset_mkopt('setWebsearchProviderName', this.value, 'Websearch Provider Name', this.value )"  />
		</div>	
	 	<div class="col1r"><?php _e('Provider URL','netblog'); ?></div>
		<div class="col2">
			<input type="text" id="nbsearch-web-urlprovider" size="40" value="<?php echo Netblog::options()->getWebsearchProviderUri() ?>"
				onkeyup="nbset_mkopt('setWebsearchProviderUri', this.value, 'Websearch Provider URI', this.value )" />
		</div>	
	 	<div class="col1r"><?php _e('URL','netblog'); ?></div>
		<div class="col2">
			<input type="text" id="nbsearch-web-urlquery" value="<?php echo Netblog::options()->getWebsearchUri() ?>" style="width:100%"
				onkeyup="nbset_mkopt('setWebsearchUri', this.value, 'Websearch URI', this.value )" />
			<div class="helpbox" id="hbweb-uri" style="display:none; position: relative">
				<div class="pad"><b><?php _e('Websearch URL','netblog'); ?></b> 
					<small><i><?php _e('must return a valid XML Feed','netblog'); ?></i></small><br />
				<div class="netblog-indent-hang-long"><?php printf(__('%s := will be replaced either with Global Max Results or with a lower number
					so that the total number of links displayed will not exceed the maximum
					number defined by the user in the widget section.','netblog'),'??count??') ?></div>
				<div class="netblog-indent-hang-long"><?php printf(__('%s := will be replaced with the query.','netblog'),'??query??'); ?></div>
				</div></div>
		</div>
	 	<div class="col1r"><?php _e('Global Max Results','netblog'); ?></div>
		<div class="col2">
			<input type="text" id="nbsearch-web-maxresults" size="3" value="<?php echo Netblog::options()->getWebsearchMaxResults() ?>"
				onkeyup="str2int(this); nbset_mkopt('setWebsearchMaxResults', this.value, 'Websearch Max Results', this.value )" />
		</div>
		<div class="col1r"><?php _e('API Key','netblog'); ?></div>
		<div class="col2">
			<input type="text" id="nbsearch-web-apikey" size="40"  value="<?php echo Netblog::options()->getWebsearchAPIKey() ?>"
				onkeyup="nbset_mkopt('setWebsearchAPIKey', this.value, 'Websearch API Key', this.value )" />
		</div>
	
	<script type="text/javascript">
	<!--
	function loadSearchProvider( id, searchType, statusid ) {
		var stat = document.getElementById(statusid);
		stat.innerHTML = '<?php _e('Loading...','netblog'); ?>';
		stat.style.display = 'inline';

		jQuery(document).ready(function($) {
			var data = {
				action: 'nbsearch_get_provider',
				searchid: id
			};
			jQuery.post(ajaxurl, data, function(r) {
				r = r.trim();
				if(r.length>0) {
					var t = r.split(';');
					for(var i = 0; i < t.length; i++) {						
						var p = t[i].indexOf(':');
						if( p < 1 || p >= t[i].length-1) continue;
						
						var tk = t[i].slice(0,p);
						var tv = t[i].slice(p+1);
						
						if(tk == "name") {
							if( document.getElementById('nbsearch-'+searchType+'-name').value != tv ) {
								document.getElementById('nbsearch-'+searchType+'-name').value = tv;
								if(searchType=="web")
									nbset_mkopt('setWebsearchProviderName', tv, 'Websearch Provider Name', tv );
								else if(searchType=="blog")
									nbset_mkopt('setBlogsearchProviderName', tv, 'Blogsearch Provider Name', tv );
							}
						} else if(tk == "urlprovider") {
							if( document.getElementById('nbsearch-'+searchType+'-urlprovider').value != tv ) {
								document.getElementById('nbsearch-'+searchType+'-urlprovider').value = tv;
								if(searchType=="web")
									nbset_mkopt('setWebsearchProviderUri', tv, 'Websearch Provider URI', tv );
								else if(searchType=="blog")
									nbset_mkopt('setBlogsearchProviderUri', tv, 'Blogsearch Provider URI', tv );
							}								
						} else if(tk == "urlquery") {
							if( document.getElementById('nbsearch-'+searchType+'-urlquery').value != tv ) {
								document.getElementById('nbsearch-'+searchType+'-urlquery').value = tv;
								if(searchType=="web")
									nbset_mkopt('setWebsearchUri', tv, 'Websearch URI', tv );
								else if(searchType=="blog")
									nbset_mkopt('setBlogsearchUri', tv, 'Blogsearch URI', tv );
							}
						} else if(tk == "maxresults") {
							if( document.getElementById('nbsearch-'+searchType+'-maxresults').value != tv ) {
								document.getElementById('nbsearch-'+searchType+'-maxresults').value = tv;
								if(searchType=="web")
									nbset_mkopt('setWebsearchMaxResults', tv, 'Websearch Max Results', tv );
								else if(searchType=="blog")
									nbset_mkopt('setBlogsearchMaxResults', tv, 'Blogsearch Max Results', tv );
							}
						} else if(tk == "apikey") {
							if( document.getElementById('nbsearch-'+searchType+'-apikey').value != tv ) {
								document.getElementById('nbsearch-'+searchType+'-apikey').value = tv;
								if(searchType=="web")
									nbset_mkopt('setWebsearchAPIKey', tv, 'Websearch API Key', tv );
								else if(searchType=="blog")
									nbset_mkopt('setBlogsearchAPIKey', tv, 'Blogsearch API Key', tv );
							}
						}
					}					
				}
				stat.innerHTML = '';
				stat.style.display = 'none';
				//alert(r);		
			});
		});
	}	
	//-->
	</script>
		
	<?php
	$o = ob_get_contents();
	ob_end_clean();	
	return $o;
}

function netblog_settings_note()
{
	ob_start();
		
	$formats = array( 'decimal'=>1, 
					  'lower-alpha' => 'a', 'lower-greek'=>'a', 'lower-roman'=>1,
					  'upper-alpha'=> 'A', 'upper-roman' =>1 );
	
	foreach( $formats as $k=>$v ) {
		$t = array();
		$i = 1;
		$t[0] = nbcpt::increment($i,$k,0);
		$t[1] = nbcpt::increment($i++,$k);
		$t[2] = nbcpt::increment($i++,$k);
		$t[3] = nbcpt::increment($i++,$k);
		$formats[$k] = implode(', ',$t) . ', ...';
	}
	
	// MK SELECT OPTIONS
	$optionNoteFormat = '';
	$f = Netblog::options()->getNoteFormat();
	foreach( $formats as $k=>$v )
		$optionNoteFormat .= '<option value="'.$k.'" '.selected( $k==$f, true, false).'>'.$v.'</option>';
	

	$optionFigPrint = Netblog::options()->useNoteAutoprint();
		
	$slct_hrule = ''; $_hrule = Netblog::options()->getFootnoteHorizontalRule();
	foreach(nbnote::GetHorzRulesOpts() as $key=>$name) {
		$selected = $key==$_hrule? ' selected="selected" ' : '';
		$slct_hrule .= '<option value="'.$key.'" '.$selected.'>'.$name.'</option>';
	}
	
	?>
	<label class="col1"><?php _e('Tag Name','netblog'); ?></label>
	<div class="col2">
		<input type="text" value="<?php echo Netblog::options()->getNoteShortcode(); ?>" 
			name="netblog_ss_footnote" disabled="disabled" /></div>
	
	<label class="col1"><?php _e('General','netblog'); ?></label>
	<div>&nbsp;</div>	
		<div class="col1r"><?php _e('Format','netblog'); ?></div>
		<div class="col2">
			<select name="netblog_note_format" 
				onchange="nbset_mkopt('setNoteFormat', this.value, 'Footnote Number Format', this.value )">
				<?php echo $optionNoteFormat ?></select>
			</div>
		<div class="col1r"><?php _e('Location','netblog'); ?></div>
		<div class="col2">
			<select name="netblog_note_location">
				<option value="bottom"><?php _e('Bottom of page','netblog'); ?></option>
				<option value="inline" disabled="disabled"><?php _e('Inline','netblog'); ?></option>
			</select>
			</div>

	<div class="lb"></div>		
	<label class="col1"><?php _e('Table of Footnotes','netblog'); ?></label>
	<div class="col2">
		<input type="checkbox" value="true" id="nb-note-fig-print" <?php echo checked($optionFigPrint) ?>
			onclick="nbset_mkopt('enableNoteAutoprint', this.checked, 'Append Table of Footnotes', this.checked )"  />
		<label for="nb-note-fig-print" style="font-weight:normal"><?php _e('Auto-append Table of Footnotes to your WP articles.','netblog'); ?></label>
		</div>		
	<div class="col1r"><?php _e('CSS Formatting','netblog'); ?></div>	
	<div class="col2"><input type="text" name="netblog_footnote_formatting" value="<?php echo Netblog::options()->getFootnoteCssFormatting(); ?>" id="nb-footnote-formatting"
			onblur="nbset_mkopt('setFootnoteCssFormatting', this.value, 'Footnote CSS Formatting', this.value )" style="width:100%" /></div>	
	<div class="col1r"><?php _e('Horizontal Rule','netblog'); ?></div>	
	<div class="col2"><select name="netblog_footnotes_hrule" onchange="nbset_mkopt('setFootnoteHorizontalRule', this.value, 'Footnotes Horizontal Rule', this.value )"><?php echo $slct_hrule ?></select></div>		
			
	<?php
	$o = ob_get_contents();
	ob_end_clean();	
	return $o;
}



function netblog_settings_cite()
{
	ob_start();
	nbcite::init();
	
	$styledft = Netblog::options()->getCiteStyle();
	
	$cstyles = nbcstyle::getStyles();
	$cstylesPrint = $cstyles;
	if( $cstylesPrint == '' ) $cstylesPrint = __('You haven\'t created any custom citation styles, yet. ','netblog');
	
	// MK SELECT FILTER TYPE
	$selectFilterType = '';
	$t = nbcstyle::getDftTypes();
	if(is_array($t))
	foreach($t as $k=>$v)		
		$selectFilterType .= "<option value=\"$k\">$v</option>";
		

	// MK SELECT CUSTOM STYLE
	$optioncStyle = '';
	$t = strlen($cstyles) > 0 ? explode(', ',$cstyles) : array();
	if(is_array($t))
	foreach( $t as $v )
		if( strlen($v) > 0 )
		$optioncStyle .= "<option value=\"$v\" ".selected( $v == $styledft, true, false ).">$v</option>";

	
	// MK SELECT DEFAULT STYLES
	$optionDftStyle = '';
	$t = nbcstyle::getDftStyles();
	if(is_array($t))
	foreach( $t as $v )
		$optionDftStyle .= "<option value=\"$v\" ".selected( $v == $styledft, true, false ).">$v</option>";
		

	// MK SELECT BIB MAX COUNT
	$optionBibMaxCount = '';
	$bibmax = nbcite::getBibsPerPost();	
	for($i=1;$i<=10;$i++)
		$optionBibMaxCount .= "<option value=\"$i\" ".selected($i==$bibmax,true,false).">$i</option>";

		
	// MK JS HEL - VALID ATTS
	$atts = nbcstyle::getDftAtts();
	$attsPtr = '';
	if(is_array($atts)) {
		ksort($atts); 
		foreach( $atts as $att=>$dft ) {
			if( substr($att,0,strlen('print')) == 'print' ) continue;
			if( $dft != '' ) $dft = " [$dft]";
			$attsPtr .= '<div class="col" style="text-transform: uppercase"><a onclick="style_cfilter_append(\''.$att.' \')" style="cursor:pointer;color:#39F">'.$att.'</a>'.$dft.'</div>';
		}
	}
	
	$cfilterFormat = '';	
	$t = nbcstyle::getFormatHTMLCodesNamed();
	foreach( nbcstyle::getFormatHTMLCodes() as $code=>$tag ) {
		$nice = $t[$code];
		if(strlen($nice) == 0 ) continue;
		$cfilterFormat .= '<a onclick="style_cfilter_append(\''.$code.'\')" style="cursor:pointer; color:#39F;  padding: 5px;">'.$tag[0].$nice.$tag[1].'</a>';
	}
	
	$headlineStyles = ''; $cur_headtag = Netblog::options()->getBibHeadlineHtmlTag();
	for($i=1; $i<9; $i++) {
		$selected = $cur_headtag=='h'.$i ? ' selected="selected" ' : '';
		$headlineStyles .= '<option value="h'.$i.'"'.$selected.'>Heading '.$i.'</option>';
	}
	
	$formats_ = array( 'decimal'=>1, 
					  'lower-alpha' => 'a', 'lower-greek'=>'a', 'lower-roman'=>1,
					  'upper-alpha'=> 'A', 'upper-roman' =>1 );
	$formats = array('literal'=>'Literal/Strict Inline Citation');
	foreach( $formats_ as $k=>$v ) {
		$t = array();
		$i = 1;
		$t[0] = nbcpt::increment($i,$k,0);
		$t[1] = nbcpt::increment($i++,$k);
		$t[2] = nbcpt::increment($i++,$k);
		$t[3] = nbcpt::increment($i++,$k);
		$formats[$k] = implode(', ',$t) . ', ...';
	}
	
	// MK SELECT OPTIONS
	$optionCiteFormat = '';
	$f = Netblog::options()->getCiteFormatOutput();
	foreach( $formats as $k=>$v )
		$optionCiteFormat .= '<option value="'.$k.'" '.selected( $k==$f, true, false).'>'.$v.'</option>';
	
	?>
	<label class="col1"><?php _e('Tag Name','netblog'); ?></label>
	<div class="col2"><input type="text" value="<?php echo Netblog::options()->getCiteShortcode() ?>" name="netblog_ss_citation" disabled="disabled" /></div>

	<label class="col1"><?php _e('Default Style','netblog'); ?></label>
	<div class="col2">
		<select name="netblog_cite_style" onchange="nbset_mkopt('setCiteStyle', this.value, 'Default Style', this.value )">
			<optgroup label="<?php _e('Built-in Styles','netblog'); ?>"><?php echo $optionDftStyle ?></optgroup>
			<optgroup label="<?php _e('Custom Styles','netblog'); ?>"><?php echo $optioncStyle ?></optgroup></select>
		<a href="http://netblog.benjaminsommer.com/citation"><?php _e('Advanced Details (Netblog Server)','netblog') ?></a>
		</div>
	<div class="col1r"><?php _e('Headline','netblog'); ?></div>	
	<div class="col2"><input type="text" name="netblog_cite_style_headline" value="<?php echo nbcite::getHeadline() ?>" id="nb-cite-style-headline"
			onblur="nbset_mkopt('setBibHeadline', this.value, 'Bibliography Headline', this.value )" /></div>
	<div class="col1r"><?php _e('Headline Style Level','netblog'); ?></div>	
	<div class="col2"><select name="netblog_cite_style_headlinestyle" onchange="nbset_mkopt('setBibHeadlineHtmlTag', this.value, 'Bibliography Headline Level', this.value )">
				<?php echo $headlineStyles ?></select></div>
					
	<div class="col1r"><?php _e('Global Style','netblog'); ?></div>	
	<div class="col2"><input type="checkbox" name="netblog_cite_style_override" value="true" id="nb-cite-style-override" <?php echo checked(Netblog::options()->getCiteStyleOverride()) ?>
			onclick="nbset_mkopt('enableCiteStyleOverride',this.checked, 'Global Citation Style', this.checked )" />
		<label for="nb-cite-style-override" style="font-weight:normal"><?php _e('Enable and override article\'s citation style','netblog'); ?></label></div>
	

	<div class="lb"></div>
	<label class="col1"><?php _e('Table of Bibliography','netblog'); ?></label>
	<div class="col2">
		<select name="netblog_bib_maxnum" onchange="nbset_mkopt('setBibMaxNum', this.value, 'Bibliographies per article', this.value )"><?php echo $optionBibMaxCount ?></select> 
			<?php _e('Bibliographies per article, at most.','netblog') ?>
		</div>
	<div class="col1r"><?php _e('Auto-append','netblog'); ?></div>	
	<div class="col2"><input type="checkbox" name="netblog_bib_print" value="true" id="nb-cite-bib-print" <?php echo checked(Netblog::options()->getBibAutoprint()) ?>
			onclick="nbset_mkopt('enableBibAutoprint',this.checked, 'Append Bibliography', 'Auto append to articles' )" />
		<label for="nb-cite-bib-print" style="font-weight:normal"><?php _e('Enable and automatically append Table of Bibliography for each article.','netblog'); ?></label>
		</div>
	<div class="col1r"><?php _e('Include IDs','netblog'); ?></div>	
	<div class="col2"><input type="checkbox" name="netblog_bib_ids" value="true" id="nb-cite-bib-ssids" <?php echo checked(Netblog::options()->getBibShortCodeInclIds()) ?>
			onclick="nbset_mkopt('enableBibShortCodeInclIds',this.checked, 'Shortcode Reference IDs', 'Include Reference IDs in shortcodes' )" />
		<label for="nb-cite-bib-ssids" style="font-weight:normal"><?php _e('Include database field ids in generated reference shortcodes.','netblog'); ?></label>
		</div>
	<div class="col1r"><?php _e('CSS Formatting','netblog'); ?></div>	
	<div class="col2"><input type="text" name="netblog_bib_formatting" value="<?php echo Netblog::options()->getBibCssFormatting() ?>" id="nb-bib-formatting"
			onblur="nbset_mkopt('setBibCssFormatting', this.value, 'Bibliography CSS Formatting', this.value )" style="width:100%" /></div>
		
	<div class="lb"></div>
	<label class="col1"><?php _e('Inline Citations','netblog'); ?></label>
	<div class="col2">&nbsp;</div>
	<div class="col1r"><?php _e('Output Format','netblog'); ?></div>	
	<div class="col2"><select name="netblog_cite_outputformat" onchange="nbset_mkopt('setCiteFormatOutput', this.value, 'Inline Citation Output Format', this.value )"><?php echo $optionCiteFormat ?></select>
		</div>
	<div class="col1r"><?php _e('CSS Formatting','netblog'); ?></div>	
	<div class="col2"><input type="text" name="netblog_cite_cssformat" value="<?php echo Netblog::options()->getCiteFormatStyle() ?>" id="nb-cite-cssformat"
			onblur="nbset_mkopt('setCiteFormatStyle', this.value, 'Inline Citation CSS Formatting', this.value )" style="width:100%" /></div>
	<div class="col1r"><?php _e('Custom Output Format','netblog'); ?></div>	
	<div class="col2"><input type="text" name="netblog_cite_custformat" value="<?php echo Netblog::options()->getCiteFormatCustomOutput() ?>" id="nb-cite-custformat"
			onblur="nbset_mkopt('setCiteFormatCustomOutput', this.value, 'Inline Citation Custom Output Format', this.value )" style="width:50%" /> <i>Note: <?php echo htmlspecialchars('"<output>" is required here')?></i></div>
		
		
			
	<div class="lb"></div>
	<label class="col1"><?php _e('Custom Styles','netblog'); ?></label>
	<div class="col2"><?php echo $cstylesPrint; ?></div>
	
	<div class="col1r"><?php _e('Create Citation Style','netblog'); ?></div>	
	<div class="col2">
		<input type="button" value="<?php _e('Create','netblog'); ?>" name="addfilter" class="fltrght" style="cursor:pointer" title="<?php _e('Click to add custom filter to save list (left)','netblog'); ?>"
			onclick="nbcaption_create()" />
		<select onchange="nb_copyval2val(this,'custom_style')" <?php echo strlen($optioncStyle)==0 ? 'disabled="disabled"' : '' ?>>
			<option value=""><?php _e('Style Name','netblog'); ?></option><?php echo $optioncStyle ?></select>
		<select onchange="nb_copyval2val(this,'custom_filtertype')"><option value=""><?php _e('Filter Type','netblog'); ?></option><?php echo $selectFilterType ?></select>
		</div>
	
	<div class="col1r"><?php _e('Style Name','netblog'); ?></div>	
	<div class="col2">
		 <input type="text" name="custom_style" id="custom_style"  onkeyup="str2alphanum(this);netblog_count_chars(this,'cfilter-style-chars')"
			onchange="netblog_count_chars(this,'cfilter-style-chars')"
			onkeydown="netblog_count_chars(this,'cfilter-style-chars')"
			onfocus="helpbox('helpbox',this);netblog_count_chars(this,'cfilter-style-chars');" onblur="helpbox('helpbox',null)"
			style="width: 100%" /></div>
				
	<div class="col1r"><?php _e('Type','netblog'); ?></div>	
	<div class="col2">
		<input type="text" name="custom_filtertype" id="custom_filtertype" onkeyup="str2alphanum(this);netblog_count_chars(this,'cfilter-type-chars')" onfocus="helpbox('helpbox2',this);netblog_count_chars(this,'cfilter-type-chars');" onblur="helpbox('helpbox2',null)"
			style="width: 100%" /></div>
			
	<div class="col1r"><?php _e('Filter','netblog'); ?></div>
	<div class="col2">
		<input type="text" name="custom_filter" id="custom_filter" 
			onkeyup="netblog_cite_preview(this.value,'nbarea-cite-preview')"
			style="width: 100%" onfocus="helpbox('helpbox3',this)" onblur="helpbox('helpbox3',null)" /></div>
	
	<div class="col1r nbcol-emph"><?php _e('Preview','netblog'); ?></div>
	<div class="col2" id="nbarea-cite-preview"><?php _e('Not available','netblog') ?></div>
	
	<div class="col1r nbcol-emph"><?php _e('Parameter','netblog'); ?></div>
	<div class="col2" id="nbarea-cite-param" style="max-height: 200px; overflow: auto;"><?php echo $attsPtr ?></div>
	
	<div class="col1r nbcol-emph"><?php _e('Formating','netblog'); ?></div>
	<div class="col2" id="nbarea-cite-format"><?php echo $cfilterFormat ?></div>
	

	<script type="text/javascript">
	<!--
	function nbcaption_create() {
		if( document.getElementById('custom_style').value == "" ) {
			alert('<?php _e('Missing Style Name','netblog') ?>');
			return;
		} else if( document.getElementById('custom_filtertype').value == "" ) {
			alert('<?php _e('Missing Style Type','netblog') ?>');
			return;
		} else if( document.getElementById('custom_filter').value == "" ) {
			alert('<?php _e('Missing Filter','netblog') ?>');
			return;
		}
		nbset_mkopt('__addBibFilter__' + document.getElementById('custom_style').value + '_' + document.getElementById('custom_filtertype').value,
						['style:'+document.getElementById('custom_style').value, 
							'type:'+document.getElementById('custom_filtertype').value, 
							'filter:'+document.getElementById('custom_filter').value], 
						'Create Citation Style',
						[document.getElementById('custom_style').value, document.getElementById('custom_filtertype').value, '<br />' + document.getElementById('nbarea-cite-preview').innerHTML] );
	}	
	//-->
	</script>
		
			
	<?php
	
	// LOAD CUSTOM FILTER
	$list = array();
	$cstyles = nbcstyle::getStyles(ARRAY_A);
	
	if(is_array($cstyles))
	foreach($cstyles as $style ) {
		$filters = nbcstyle::getFilter($style,true);
		if( !is_array($filters) )
			continue;		
		echo '<div class="col1r">'.$style.'</div>
			<div class="col2">';
		foreach( $filters as $type=>$cmd ) {
			$cmd = nbcstyle::previewFilter($cmd);
			echo "<a class=\"del\" onclick=\"nbset_mkopt('__rmBibFilter__$style$type',
						['style:$style', 'type:$type'], 'Remove Citation Style', ['$style', '$type'])\"
					 \"></a><div class=\"col1\"> $type</div><div class=\"col2-sl\">$cmd</div>";
		}
		echo '</div>';
	}


	
	?>
	<script type="text/javascript">
	<!--
	var protectedStyles = '<?php echo implode(', ', nbcstyle::getDftStyles() ); ?>';
	var validAtts = '';
	var maxChars = 20;
	var minChars = 3;
	
	var optHelp = document.getElementById('nb-help-enabled');
	var oCFilter = document.getElementById('custom_filter');
	
	/* APPLY SELECT VALUE TO HTML-ID-VALUE */
	function nb_copyval2val( objold, idnew )
	{
		document.getElementById(idnew).value = objold.value;
	} 
	
	/* PRINT HELP BOX */
	function helpbox( id, refObj )
	{
//		if( !optHelp.checked ) return;
//		var box = document.getElementById(id);
//		if( refObj == null || typeof(refObj) == 'undefined' ) {
//			//hide( box.id, 200, 200);
//			hidePlus( box.id, 200, 500, true, true);
//			return;
//		}
//			
//		var refid = refObj.id;		
//		var help = '';
//		
//		if( refid == 'custom_style' ) {			
//			help += '<b><?php printf(__('Allowed characters','netblog'),''); ?></b>: a-z, A-Z, 0-9<br />';
//			help += '<?php printf(__('Max characters: %s of %s','netblog'),'<span id="cfilter-style-chars">0</span>',''); ?> ' + maxChars + '<br />';
//			help += '<?php printf(__('Protected Names: %s','netblog'),''); ?> ' + protectedStyles + '<br />';
//		} else if( refid == 'custom_filtertype' ) {
//			help += '<b><?php printf(__('Allowed characters','netblog'),''); ?></b>: a-z, A-Z, 0-9<br />';
//			help += '<?php printf(__('Max characters: %s of %s','netblog'),'<span id="cfilter-type-chars">0</span>',''); ?> ' + maxChars + '<br />';
//			help += '<?php _e('Use of an existing filter type for your custom style will overwrite the filter.','netblog'); ?>';
//		} else if( refid == 'custom_filter' ) {
//			help += '<b><?php _e('Preview','netblog'); ?></b>: <div id="netblog_cite_preview" style="display:inline"><?php _e('no preview available','netblog'); ?></div><br /><br />';
//			help += '<div class="title"><?php _e('Valid Keywords','netblog'); ?></div><?php echo $attsPtr ?>';
//		}
//		
//		if( help != '' ) {
//			box.innerHTML = '<div class="pad"><div class="title"><?php _e('Help Tool','netblog'); ?></div>' + help + '<div class="netblog-clear"></div></div>';
//			//show( box.id, 0, 200);			
//			showPlus( box.id, 200, 200, true, true);
//		} 
	}
	
	function style_cfilter_append( word )
	{
		if( oCFilter!=null ) {
			oCFilter.value = oCFilter.value + word;
			netblog_cite_preview(oCFilter.value,'nbarea-cite-preview');
			oCFilter.focus();
		}
	}
	
	
	function netblog_cite_preview( filter, id )
	{	
		jQuery(document).ready(function($) {
			var data = {
				action: 'cite_filter_preview',
				filter: filter
			};
			jQuery.post(ajaxurl, data, function(r) {
				r = r.trim();
				if( r == '' || r == 'false' )
					r = '-----';
					
				var prev = document.getElementById(id);
				if( prev != null ) {
					prev.innerHTML = r + "&nbsp;";
				};				
			});
		});	
	}
	
	//-->
	</script> 
	
	<?php
	
	$o = ob_get_contents();
	ob_end_clean();	
	return $o;
}


function netblog_settings_fig()
{
	ob_start();
	
	$roles = array( 	'activate_plugins'=>__('Administrator','netblog'), 
						'delete_pages'=>__('Editor','netblog'), 
						'publish_posts'=>__('Author','netblog'), 
						'edit_posts'=>__('Contributor','netblog')
	);
	
	$gadd = Netblog::options()->getCaptionPrivGadd();

	$formats = array( 'decimal'=>1, 
					  'lower-alpha' => 'a', 'lower-greek'=>'a', 'lower-roman'=>1,
					  'upper-alpha'=> 'A', 'upper-roman' =>1 );
	
	$display = array( 	'inline'=>__('Inline','netblog'), 
						'left'=>__('Left','netblog'),
						'right'=>__('Right','netblog')
	);
	
	foreach( $formats as $k=>$v ) {
		$t = array();
		$i = 1;
		$t[0] = nbcpt::increment($i,$k,0);
		$t[1] = nbcpt::increment($i++,$k);
		$t[2] = nbcpt::increment($i++,$k);
		$t[3] = nbcpt::increment($i++,$k);
		$formats[$k] = implode(', ',$t) . ',...';
	}

	$useGlobalCaptionNumbers = Netblog::options()->useGlobalCaptions();
	
	
	?>
	<label class="col1"><?php _e('Tag Name','netblog'); ?></label>
	<div class="col2"><input type="text" value="<?php echo Netblog::options()->getCaptionShortcode() ?>" name="netblog_ss_caption" disabled="disabled" /></div>
	
	<label class="col1" title="<?php _e('Captions are automatically added when first declared in a post/page and if this privilege is met.','netblog'); ?>">
		<?php _e('Who may add global types?','netblog'); ?></label>	
	<div class="col2"><select name="netblog_caption_gadd" 
			>
		<?php
		if(is_array($roles))
		foreach( $roles as $k=>$v )
			echo '<option value="'.$k.'" '.selected($k==$gadd,true,false).' 
			onclick="nbset_mkopt(\'setCaptionPrivGadd\', this.value, \'Privilege for new global Captions\', \''.$v.'\' )">'.$v.'</option>';
		?>
	</select></div>
		
	<?php
	
	
	$types = nbdb::cptg_getTypes(true);
	if( is_array($types) && sizeof($types) > 0 ) {
		echo '<label class="col1">'.__('Installed Captions','netblog').'</label><div class="col2">&nbsp;</div>';
		foreach( $types as $type=>$para ) {			
			echo '<div class="col1r"><span onclick="nbarea_edit_caption(\''.$type.'\')" style="cursor:pointer">'.ucfirst($type).'</span><br />
				<span style="color:#AAA">'.ucfirst($para['display']);
				if($para['isactive'])
					echo ', Active';
			echo '</span></div>';
			echo '<div class="col2">';
			echo $formats[$para['numberFormat']];
			echo '<br /><span style="color:#555">'.$para['printFormat'].'</span>';
			echo '</div>';
		}
	}
	
	?>
	
	<div class="col1"><label ><?php _e('Create & Change','netblog'); ?></label></div>
	<div class="col2">&nbsp;</div>
	
	<div class="col1r"><?php _e('Caption Name') ?></div>
	<div class="col2">	
		<input type="text" id="nb-caption-type-new" value="" title="<?php _e('Type in a caption name','netblog'); ?>"
			onkeyup="nbarea_type_caption()" />&nbsp;
		<span id="nbarea-fig-new-status" class="netblog-box-info nodisplay"></span></div>	
	<div id="nb-caption-new" style="display:none">
	 	<div class="col1r"><?php _e('Numbering Format','netblog'); ?></div>
		<div class="col2"><select id="nb-caption-new-numformat">
			<?php 
				foreach( $formats as $k=>$v) 
					echo '<option value="'.$k.'">'.$v.'</option>';
			 ?></select></div>
			 
	 	<div class="col1r"><?php _e('Display Style','netblog'); ?></div>
		<div class="col2"><select id="nb-caption-new-display">
			<?php 
				if(is_array($display))
				foreach( $display as $k=>$v) 
					echo '<option value="'.$k.'">'.$v.'</option>';
			 ?></select></div>	
		
		<div class="col1r"><?php _e('Override Articles','netblog'); ?></div>
		<div class="col2"><input type="checkbox" value="true" id="nb-caption-new-active"> <?php _e('Enable','netblog'); ?></div>
			 	 	 
	 	<div class="col1r"><?php _e('Print format','netblog'); ?></div>
		<input type="button" onclick="nbset_mkopt('__createCaption__' + document.getElementById('nb-caption-type-new').value, 
									['name:'+document.getElementById('nb-caption-type-new').value,  
									'numbering:'+document.getElementById('nb-caption-new-numformat').value,
									'display:'+document.getElementById('nb-caption-new-display').value, 
									'format:'+document.getElementById('nb-caption-new-format').value,
									'active:'+document.getElementById('nb-caption-new-active').checked] 
								, 'Set Global Caption', 
									[document.getElementById('nb-caption-type-new').value,  
									document.getElementById('nb-caption-new-numformat').value,
									document.getElementById('nb-caption-new-display').value, 
									document.getElementById('nb-caption-new-format').value,
									document.getElementById('nb-caption-new-active').checked] )"
			 value="<?php _e('Create Caption','netblog'); ?>" class="fltrght" id="nbarea-fig-uptmk-btn" />
		<div class="col2"><input type="text" id="nb-caption-new-format" value="($number)" /></div>
	</div>
	
	
	<?php
	$cpts = nbdb::cpt_getAll(OBJECT_K);
	$slctCptFilterType = '';
	if( is_array($types) && sizeof($types) > 0 )
	foreach($types as $type=>$para) {
		$slctCptFilterType .= '<option value="type::'.$type.'">'.Netblog::cstrip(ucfirst($type),50,'...').'</option>';
	}
	
	$slctCptFilterPost = '';
	$cptFilterPosts = array();
	if($cpts!=null && is_array($cpts))
	foreach($cpts as $k=>$para) {
		if(!isset($cptFilterPosts[$para->host]))
			$cptFilterPosts[$para->host] = Netblog::cstrip(get_the_title($para->host),50,'...');
	}
	foreach($cptFilterPosts as $postId=>$postNm)
		$slctCptFilterPost .= '<option value="post::'.$postId.'">'.ucfirst($postNm).'</option>';
	?>
	
	
	<label class="col1" title=""><?php _e('Advanced','netblog'); ?></label>
	<div class="col2"><input type="button" id="nb-caption-rebuild" 
			onclick="nbset_mkopt('__rebuildCaptions', true, 'Rebuild Captions', true )"
			value="<?php _e('Rebuild Caption Numbers','netblog'); ?>" /></div>
	<div class="col1r"><?php _e('Global Captions') ?></div>
	<div class="col2">
		<input type="checkbox" id="nb-caption-useglobal" name="netblog_caption_useGlobal" value="true" <?php checked($useGlobalCaptionNumbers) ?> 
			 onclick="nbset_mkopt('enableGlobalCaptions', this.checked, 'Global Caption Numbers', this.checked )" />
			<span onclick="document.getElementById('nb-caption-useglobal').click();" style="cursor:pointer"><?php _e('Ensure consistent and unique numbers for this WP site.','netblog'); ?></span></div>
		
		
	<script type="text/javascript">
	<!--
	var minCharType = 3;
	var figparaVisible = false;
	var timerId = null;
	
	function nbarea_edit_caption( caption ) {
		document.getElementById('nb-caption-type-new').value = caption;
		nbarea_type_caption_tout();
	}
	
	function nbarea_type_caption() {	
		document.getElementById('nbarea-fig-new-status').innerHTML = '<?php _e('checking...','netblog') ?>';	
		document.getElementById('nbarea-fig-new-status').style.display = 'inline';
		
		if(timerId!=null)
			clearTimeout(timeId);
		timeId = setTimeout('nbarea_type_caption_tout()',1000);	
	}
	
	function nbarea_type_caption_tout() {
		var o = document.getElementById('nb-caption-type-new');
		str2alphanum(o);
		input_strip(o,32);
		nbarea_checkFigName(o,'nb-caption-new','nbarea-fig-new-status');			
	}
	
	function nbarea_checkFigName( inObj, paramFieldId, statusFieldId ){
		var status = document.getElementById(statusFieldId);		
		
		jQuery(document).ready(function($) {
			var data = {
				action: 'netblogfig_exists',
				caption_name: inObj.value
			};
			jQuery.post(ajaxurl, data, function(r) {
				r = r.trim();
				if( r == '1' || r == 'true' || r == 'TRUE' ){
					var target = document.getElementById(paramFieldId);
					if( target == null ) return;
				
					if( target.style.display == '' ) 
						target.style.display = 'none';
					
					if( inObj.value.length <= minCharType-1 && target.style.display != 'none' ) {
						hidePlus(paramFieldId,0,250,true,false);
						figparaVisible = false;
					}
					else if( inObj.value.length >= minCharType && target.style.display == 'none' ) {
						showPlus(paramFieldId,0,250,true,false);
						figparaVisible = true;						
					}
					document.getElementById('nbarea-fig-uptmk-btn').value = "<?php _e('Create Caption','netblog') ?>";
					status.innerHTML = '';
					status.style.display = 'none';
				} else {
					status.innerHTML = '<?php _e('Caption will be overwritten','netblog') ?>';
					status.style.display = 'inline';
					if(!figparaVisible) {
						showPlus(paramFieldId,0,250,true,false);						
					}
					loadCaptionParam(inObj.value);
					document.getElementById('nbarea-fig-uptmk-btn').value = "<?php _e('Update Caption','netblog') ?>";
				}
						
			});
		});
	}
	
	function loadCaptionParam( captionName ) {
		jQuery(document).ready(function($) {
			var data = {
				action: 'nbcpt_param_retrieve',
				caption_name: captionName
			};
			jQuery.post(ajaxurl, data, function(r) {
				r = r.trim();
				if(r.length>0) {
					var t = r.split(';');
					for(var i = 0; i < t.length; i++) {						
						var p = t[i].indexOf(':');
						if( p < 1 || p >= t[i].length-1) continue;
						
						var tk = t[i].slice(0,p);
						var tv = t[i].slice(p+1);
						
						if(tk == "name") 
							document.getElementById('nb-caption-type-new').value = tv;										
						else if(tk == "numbering")
							document.getElementById('nb-caption-new-numformat').value = tv;										
						else if(tk == "display")
							document.getElementById('nb-caption-new-display').value = tv;										
						else if(tk == "printFormat")
							document.getElementById('nb-caption-new-format').value = tv;
						else if(tk == "active") {
							//alert(tv);
							document.getElementById('nb-caption-new-active').checked = (tv == 'true' || tv == '1' || tv == 'TRUE');
						}
					}
				}
				
				//alert(r);		
			});
		});
	} 
	
	function revealOnInput( inObj, targetid )
	{	
		var target = document.getElementById(targetid);
		if( target == null ) return;
	
		if( target.style.display == '' ) 
			target.style.display = 'none';
		
		if( inObj.value.length <= minCharType-1 && target.style.display != 'none' )
			hidePlus(targetid,0,250,true,false);
		else if( inObj.value.length >= minCharType && target.style.display == 'none' )
			showPlus(targetid,0,250,true,false);
	}
	
	function revealPara( targetid, inObj )
	{
		var target = document.getElementById(targetid);
		if( target == null ) return;
		
		if( inObj.checked )
			showPlus(targetid,0,250,true,false);
		else hidePlus(targetid,0,250,true,false); 
	}
		
	//-->
	</script>
				
	<?php
	$o = ob_get_contents();
	ob_end_clean();	
	return $o;	
	
}

function netblog_settings_export()
{
	ob_start();
	
	
	require_once 'infobox.php';
	$box = new infobox('<strong>Caution</strong>: Export and import of netblog data is still experimential. Also note that Import of data has not been fully implemented, yet. In the meantime,
				export Netblog\'s data and your WordPress Database in the old fashioned way by backuping your complet database.');
	$box->display();
	
	?>
	<br />
	<label class="col1"><?php _e('Export','netblog'); ?></label>
	<div class="col2"><input type="button" value="<?php _e('Export Netblog User Data','netblog'); ?>" onclick="nbpop_load('nbpopup_export')" /></div>
		
	<label class="col1"><?php _e('Import','netblog'); ?></label>
	<div class="col2"><input type="button" value="<?php _e('Import Netblog User Data','netblog'); ?>" onclick="nbpop_load('nbpopup_import')" /></div>
	<div class="lb"></div>	
	
	<label class="col1"><?php _e('Embedded Export Data','netblog'); ?></label>
	<div class="col2"><input type="checkbox" value="true" <?php checked( Netblog::options()->useEED() ) ?> id="eed-enable"
			 onclick="nbset_mkopt('enableEED', this.checked, 'EED Feature (optional)', this.checked )" /> 
			<label for="eed-enable" style="font-weight:normal"><?php _e('Enable this feature and store Netblog Footprints in WP articles (optional)','netblog'); ?></label><br />
		<input type="checkbox" name="netblog_export_build" value="true" id="netblog_export_build-checkbox" <?php checked( Netblog::options()->getExportBuildon() == 'save_post', true) ?>
				onclick="nbset_mkopt('enableEEDAutoRebuild', this.checked, 'EED Auto Rebuild', this.checked )" /> 
			<label for="netblog_export_build-checkbox" style="font-weight:normal"><?php _e('Update these data everytime you update WP articles to stay up to date','netblog'); ?></label></div>
	<div class="col1r"><?php _e('Maintenance') ?></div>
	<div class="col2">		
		<input type="button" value="<?php _e('Rebuild all embedded Data','netblog'); ?>" onclick="nbset_mkopt('__eedRebuild', true, 'EED Rebuild', true )" /><br />
		<input type="button" value="<?php _e('Remove all embedded Data','netblog'); ?>" onclick="nbset_mkopt('__eedRemove', true, 'EED Remove', true )" />
	</div>			
	<div class="lb"></div>	
	
	<script type="text/javascript">
	<!--
	var minCharType = 3;
	function revealOnInput( inObj, targetid )
	{
		var target = document.getElementById(targetid);
		if( target == null ) return;
	
		if( target.style.display == '' ) 
			target.style.display = 'none';
		
		if( inObj.value.length <= minCharType-1 && target.style.display != 'none' )
			hidePlus(targetid,0,250,true,false);
		else if( inObj.value.length >= minCharType && target.style.display == 'none' )
			showPlus(targetid,0,250,true,false);
	}
	
	function revealPara( targetid, inObj )
	{
		var target = document.getElementById(targetid);
		if( target == null ) return;
		
		if( inObj.checked )
			showPlus(targetid,0,250,true,false);
		else hidePlus(targetid,0,250,true,false); 
	}	
	//-->
	</script>		

	<?php
	$o = ob_get_contents();
	ob_end_clean();	
	return $o;
}

function netblog_settings_adv()
{
	ob_start();
	
	$pilot = new nbTestPilot();
	
	$privLevels = array('ultra'=>__('Disable communication by Netblog','netblog'),
						'high'=>__('Submit: version numbers (wordpress, php, mysql, netblog), WordPress language','netblog'),
						'medium'=>__('Submit: installed plugins (name, uri, version), active theme (name, title, version), php extensions, version numbers','netblog'),
						'none'=>__('Submit: WP site information (URL, name, description), installed plugins, active theme, php extensions, version numbers','netblog'));	
	?>	
	<label class="col1"><?php _e('Privacy Level','netblog'); ?></label>
	<div class="col2">
	
	<?php 
	$l = Netblog::options()->getPrivacyLevel();
		foreach($privLevels as $nm=>$label) {
			echo '<div style="float:left"><input type="radio" name="nbprivacy_level" value="'.$nm.'" '.checked($nm,$l,false).' 
				onclick="nbset_mkopt(\'setPrivacyLevel\', this.value, \'Privacy Level\', \''.strtoupper($nm).'\' )" /> '.
			strtoupper($nm).'</div><div style="margin-left: 100px;">'.$label.'</div><div style="height:5px">&nbsp;</div>';
		}
	?>
		</div>
		
	<div class="lb"></div>
	
	
	<label class="col1"><?php _e('Test Pilots & Development','netblog'); ?></label>
	<div class="col2">
		<input type="checkbox" value="true" id="nbtestpilot-enable" onclick="nbset_mkopt('enableTestPilot', this.checked, 'Test Pilot & Development',  this.checked )"
			<?php checked(Netblog::options()->useTestPilot()); ?> /> 
		<label for="nbtestpilot-enable" style="font-weight:normal"><?php _e('Help improve this software by sending annonymous usage data to the official Netblog server. No personally identifiable information will be send!','netblog'); ?></label></div>		
	
	<div class="col1r"><?php _e('Next Scheduled Transmission') ?></div>
	<div class="col2"><?php
		$span = new timeSpan(abs($pilot->getNextSubmission()-time())); 
		if($pilot->getNextSubmission()-time() > 0)
			echo 'In '.$span->getFormatted(true,3);
		else echo $span->getFormatted(true,3).' ago';
	?></div>	
	<div class="lb"></div>
	
	
	<label class="col1"><?php _e('Footprints','netblog'); ?></label>
	<div class="col2">
		<input type="checkbox" value="true" id="nbfootprint-enable" onclick="nbset_mkopt('enableFootprints', this.checked, 'Use Footprints',  this.checked )"
			<?php checked(Netblog::options()->useFootprints()); ?>  />
			<label for="nbfootprint-enable" style="font-weight:normal"> <?php _e('Enable Footprints (strongly recommended for export)','netblog'); ?></label><br />
		<input type="checkbox" value="true" id="nbfootprint-enable-server" onclick="nbset_mkopt('enableFootprintServerUse', this.checked, 'Use Footprint Server', this.checked )"
			<?php disabled($_SERVER['HTTP_HOST'],'localhost') ?>
			<?php checked(Netblog::options()->useFootprintServer()); ?>  />
			<label for="nbfootprint-enable-server" style="font-weight:normal"> <?php _e('Use Footprint Server','netblog');
				if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'localhost' )
					echo ' <br /><small>'.__('Localhost servers are excluded from using this feature!').'</small>';
			?></label>
		</div>		
	
	<div class="col1r"><?php _e('Maintenance','netblog'); ?></div>
	<div class="col2">
		<input type="button" value="<?php _e('Repair Footprints') ?>" onclick="footprint_repair()" />
			<span id="nbarea-footprints-repair-status" class="netblog-box-info nodisplay"></span>
		</div>
	
	<div class="nofloat noflt"></div>
	
	<script type="text/javascript">
	<!--
	function footprint_repair() {
		var status = document.getElementById('nbarea-footprints-repair-status');		
		if( confirm('Do you really want to repair the local footprint database? \n\nIf unsure, read the documentation or tutorials!') ) {
			status.innerHTML = 'repairing footprints...';
			status.className = 'netblog-box-info';
			
			jQuery(document).ready(function($) {
				var data = {
					action: 'nbfootprint_repair'
				};
				jQuery.post(ajaxurl, data, function(r) {
					r = r.trim();
					if(r=='ok') {
						status.className = 'netblog-box-info nodisplay';
					} else {
						if( r=='0' || r==0)
							r = 'Service Unavailable!';
						status.innerHTML = r;
					}
				});
			});
		}
	}
	//-->
	</script>
	
	<?php	
	$o = ob_get_contents();
	ob_end_clean();	
	return $o;
}

function netblog_settings_about()
{
	ob_start();
	
	?>
	
	<div class="netblog_logo" style="float: left"></div>
	<div style="margin-left: 250px;">
		<h2><?php echo Netblog::$name.' '.Netblog::getClientVersion() ?> </h2>
		<p>By <?php echo Netblog::$author; ?></p>
		<p>News, Tutorials, Modules available at <a href="<?php echo Netblog::$web ?>">Official Netblog Homepage</a></p>
		<p>Build: <?php echo Netblog::$build ?> (client: <?php echo Netblog::getClientVersion() ?>, server: <?php echo Netblog::getServerVersion() ?>, release date: <?php echo Netblog::$buildDate ?>) </p>
		<p>Contact: <a href="http://netblog.benjaminsommer.com/contact.php">Development Team</a></p>
		<p>Feedback and Report Bugs: Please use the built-in feedback system (see the two smilies)</p>
		<p><small>Logo &copy; By Benjamin Sommer. High resolution available.</small></p>
	</div>
	<div class="nofloat"></div>
	<?php
	$o = ob_get_contents();
	ob_end_clean();	
	return $o;
}

?>
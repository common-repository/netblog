<?php
//----------------------------------------------------------------------------------
// NETBLOG-PAGE
//----------------------------------------------------------------------------------

class nbMelGUI {
	static function Display() {
		echo '<div class="wrap">
			<div id="icon-link-manager" class="icon32"><br /></div>';
		netblog_feedback_smilies();
		echo '<h2>Netblog MEL - '.__('Manage External Links','netblog').'</h2><br />';
	
		if(($v=Netblog::getLatestVersion()) > Netblog::options()->getClientVersion() ) {
			require_once 'infobox.php';
			$box = new infobox("There is a new version of Netblog $v available. Please <a href=\"".Netblog::$uriDownload."\">update now</a>.");
			$box->display();
		}	
		
		$externs = nbLinkExternCollection::LoadAll();
		if($externs==null || count($externs)==0) {
			echo '<div class="netblog-container"><div class="infobox">';
			echo 'You do not have any external links, yet. But this is no problem.<br /><br />';
			echo '<em>Adding External Links</em>';
			echo '<div style="margin-left:20px">Go to the <i>Edit Post</i> page and in the metabox "Further Reading" at the top, type in URL or keywords (like in Google Web Search). If links are found, you will see an autocomplete box - just click on one link. Thats all!</div>';
			echo '</div></div>';
			return;
		}
				
		$mel_tpl = array( 	'new'=>__('Just Added','netblog'), 
							'popular'=>__('Most Popular','netblog'), 
							'offline'=>__('Offline','netblog'), 
							'trash'=>__('Trashed','netblog') 
		);
		
		$mel_tpl_startup = 'tpl:'.($mel_tpl_startupName=Netblog::options()->getMelTplStartup());
		$mel_tpls_user = nbMel::GetAllTemplates();
		
		$mel_logbox_time = 500;
		switch(Netblog::options()->getGUISpeed()) {
			case 'medium': case 'mid':
				$mel_logbox_time = 500;
				break;
			case 'slow': case 'low':
				$mel_logbox_time = 1500;
				break;
			case 'ultra_fast': case 'fast':
				$mel_logbox_time = 250;
				break;
			case 'instant':
				$mel_logbox_time = 10;
				break;
			default:
				$mel_logbox_time = 500;
		}
		
		?>
		<div class="netblog-container" id="nb-popup" style="display:none">
		</div>
		
		<div class="netblog-area" id="netblog-mel-box" style="display:none">
			<div id="nbarea-mel-progbar" class="nbarea-mel-progbar"></div>
			<div class="nbarea-menu-top" style="z-index:96;position:relative">	
				<div class="nbarea-menu-search">
					<input type="text" value="<?php _e('Quicksearch','netblog') ?>" onfocus="nbmel_search_focus(this)" onblur="nbmel_search_blur(this)" onkeyup="searchInput('quickfind','netblog_retrieve(\'' + this.value + '\',\'<?php echo _e('Quicksearch','netblog')?>\')');" class="disabled" />
				</div>
				
				<a class="icon32 ico-new" onclick="netblog_retrieve('tpl:new','<?php _e('Just Added','netblog') ?>')" title="<?php _e('Just Added','netblog') ?>" ></a>
				<a class="icon32 ico-popular" onclick="netblog_retrieve('tpl:popular','<?php _e('Most Popular','netblog') ?>')" title="<?php _e('Most Popular','netblog') ?>" ></a>
				<a class="icon32 ico-offline" onclick="netblog_retrieve('tpl:offline','<?php _e('Offline','netblog') ?>')" title="<?php _e('Offline','netblog') ?>" ></a>
				<a class="icon32 ico-trashed" onclick="netblog_retrieve('tpl:trash','<?php _e('Trashed','netblog') ?>')" title="<?php _e('Trashed','netblog') ?>" ></a>
				<?php if( Netblog::options()->useMelTpl() && sizeof($mel_tpls_user) > 0 ) { ?>
					<a class="icon32 ico-user" id="nb-mel-custom" onmouseover="nb_toogle_custommenu()" onmouseout="hidePlus('nb-mel-custom-sub',400,50,true,false)"></a>
					<div id="nb-mel-custom-sub" class="netblog-menu-sub" onmouseover="stopshPlus('nb-mel-custom-sub')" onmouseout="hidePlus('nb-mel-custom-sub',200,50,true,false)">
					<?php $i=0; foreach( $mel_tpls_user as $nm=>$val ) { ?>
						<div onclick="netblog_retrieve('<?php echo $val ?>','<?php echo ucfirst($nm) ?>')">
							<a class="icon32 ico-user-search noflt" id="nb-mel-custom-sub-<?php echo ++$i ?>"></a>
							<a class="lnk" onmouseover="document.getElementById('nb-mel-custom-sub-<?php echo $i ?>').className = 'icon32 ico-user-search noflt icon32Hover'"
								 onmouseout="document.getElementById('nb-mel-custom-sub-<?php echo $i ?>').className = 'icon32 ico-user-search noflt'"><?php echo ucfirst($nm); ?></a>
							 <div class="netblog-clear"></div></div>
					<?php } ?>
					</div>
				<?php } ?>
				
				<?php if( current_user_can( Netblog::options()->getMelPedit() ) ) { ?>
				<a class="icon32 ico-slash"></a>
				
				<a class="icon32 ico-restore" onclick="linkSet.restoreElements()" title="<?php _e('Restore','netblog') ?>" ></a>
				<a class="icon32 ico-trash" onclick="linkSet.removeElements()" title="<?php _e('Trash','netblog') ?>" ></a>
				<a class="icon32 ico-erase" onclick="linkSet.removePermanent()" title="<?php _e('Erase','netblog') ?>" ></a>
				<a class="icon32 ico-slash"></a>
				
				<a class="icon32 ico-check" onclick="linkSet.checkOnlineStatusOfElements()" title="<?php _e('Check Status','netblog') ?>" ></a>
				<a class="icon32 ico-updt" onclick="linkSet.updateTitleOfElements()" title="<?php _e('Update','netblog') ?>" ></a>
				<a class="icon32 ico-lock" onclick="linkSet.lockElements()" title="<?php _e('Lock','netblog') ?>" ></a>
				<a class="icon32 ico-unlock" onclick="linkSet.unlockElements()" title="<?php _e('Unlock','netblog') ?>" ></a>
				<?php } ?>
				<div class="noflt"></div>		
			</div>
			<div class="nbarea-body"  style="height:400px; margin: 5px; overflow: auto">
				<div id="nbarea-body"></div>
				<div class="noflt"></div>
				
			</div>		
			
			<div class="nbarea-footer">
				<div class="fltrght">
					<label class="legend offline"><span id="nbmel-count-offline">0</span> <?php _e('Offline','netblog') ?></label>
					<label class="legend trash"><span id="nbmel-count-trash">0</span> <?php _e('Trashed','netblog') ?></label>
					<label class="legend lock"><span id="nbmel-count-lock">0</span> <?php _e('Locked','netblog') ?></label>
					<label class="legend mod"><span id="nbmel-count-mod">0</span> <?php _e('Modified','netblog') ?></label>
					<label class="legend dft"><span id="nbmel-count-dft">0</span> <?php _e('Other','netblog') ?></label>
					</div>
				<div id="nbmel-dsp-status" onclick="nbmel_toogleLogBox()"></div>
			</div>	
			
			<div class="nbarea-log-par" id="nbarea-log-par"><div class="nbarea-log" id="nbmel-log-box"></div></div>	
		</div>
	 	
		
		<div id="terminal"></div>
		<script type="text/javascript">
		<!--
			var delim_main = '<?php echo Netblog::options()->getAjaxDelimiterMain(); ?>';
			var delim_sub = '<?php echo Netblog::options()->getAjaxDelimiterSub() ?>';
			var scopeElemID = 'netblog-elem-';
			var classModified = 'mod';
			var classTrashed = 'trash';
			var classOffline = 'offline';
			var linkSet = new netblog_links();
			linkSet.setFlushInterval( <?php echo Netblog::options()->getMelSavetime()*1000 ?> );
			linkSet.init( 'linkSet', scopeElemID, classModified, classTrashed, classOffline, 'nbarea-mel-progbar', 'nbarea-mel-progbar-err' );
			linkSet.setLogElement('nbmel-log-box');
			
			var editable = <?php echo current_user_can( Netblog::options()->getMelPedit() ) ? 'true' : 'false' ?>;
	
			/* DISPLAY FOOTER - LEGEND */
			showPlus( 'nbmel-footer', 3000, 1500, true, false );
			
			/* ALIGN CUSTOM-SUB-MENU */	
			var lastSubMenu = null;	
			function nb_toogle(id)
			{
				if( lastSubMenu != null )
					hidePlus(lastSubMenu.id,0,200,true,false);
					
				var o = document.getElementById(id);
				if( o == null ) return;
				if( o.style.display == 'none' ) {
					showPlus(id,0,100,true,false);
					lastSubMenu = o;
				} else hidePlus(id,0,200,true,false);
			}
			function nb_toogle_custommenu() { nb_toogle('nb-mel-custom-sub'); }
			function nb_submenu_init(idmain, idsub )
			{
				var m = document.getElementById(idmain);
				var s = document.getElementById(idsub);
				if( m != null && s != null ) {
					s.style.display = 'none';
					s.style.position = 'absolute';
					s.style.top = (m.offsetTop + m.offsetHeight) + 'px';
					s.style.left = m.offsetLeft + 'px';
				}			
			}
			
			
			
			var meldsp_offline = document.getElementById('nbmel-count-offline');
			var meldsp_trash = document.getElementById('nbmel-count-trash');
			var meldsp_lock = document.getElementById('nbmel-count-lock');
			var meldsp_mod = document.getElementById('nbmel-count-mod');
			var meldsp_dft = document.getElementById('nbmel-count-dft');
			
			/* FORMAT LINK FUNC */
			function netblog_linkF( id, uri, title, info, flag, ref )
			{	
				var className = 'dft';
				if( flag == 1 ) {
					className = 'offline';
	//				meldsp_offline.innerHTML = linkSet.countOffline;
				} else if( flag == 3 || flag == 2 ) { 
					className = 'trash';
	//				meldsp_trash.innerHTML = parseInt(meldsp_trash.innerHTML) + 1; 
				} else if( flag == 99 ) {
					className = 'lock';				
	//				meldsp_lock.innerHTML = parseInt(meldsp_lock.innerHTML) + 1; 
				} else {
	//				meldsp_dft.innerHTML = parseInt(meldsp_dft.innerHTML) + 1;
				}
				var e = '';		
				if( editable && flag != 99 ) 	
					e = '<div id="'+scopeElemID + id +'" class="'+className+'"><div class="title"><div onclick="netblog_linkE(this,\''+id+'\')">' + title + '</div></div>'+
						'<div class="uri"><div onclick="netblog_linkE(this,\''+id+'\')">' + uri + '</div></div>'+
						'<div class="info"><a href="'+uri+'" title="Visit Link" target="_blank"><img src="<?php echo WP_PLUGIN_URL . '/netblog/images/external-link-ltr-icon.png'; ?>" /></a> ' 
							+ info + ' | In '+ref+' Post'+(ref!=1?'s':'')+'</div></div>';
				else 
					e = '<div id="'+scopeElemID + id +'" class="'+className+'"><div class="title"><div>' + title + '</div></div>'+
						'<div class="uri"><div>' + uri + '</div></div>'+
						'<div class="info"><a onclick="nbpopup_load(\''+title+'\',\''+uri+'\')" style="cursor:pointer"><img src="<?php echo WP_PLUGIN_URL . '/netblog/images/external-link-ltr-icon.png'; ?>" /></a> ' 
							+ info + ' | In '+ref+' Post'+(ref!=1?'s':'')+'</div></div>';			
				
				var blockID = scopeElemID + '-block-' + id;			

				if( linkSet.countErrors()==1 ) {
					nbmel_showLogBox();
				}
				
				var obj;
				if( (obj=document.getElementById(blockID)) != null ) {
					obj.innerHTML = e;				
					return '';					
				} else {
					//show( ''+blockID+'', 200, 250 );
					//showPlus( 'netblog-body', 200, 250, true, false );
					return '<div class="elem" id="'+blockID+'" style="display:block">' + e + '</div>';
				}
			}
			
			/* MK LINK EDIT FUNC */
			function netblog_linkE( obj, uri_id )
			{
				if( !editable ) return;
				var inputID = 'netblog-link-edit-'+uri_id;
				var val = obj.innerHTML.replace('"',"''");
				var inner = '<input type="text" value="' + val + '" class="' + obj.parentNode.className + '" onblur="netblog_linkS(this,\''+uri_id+'\')" id="'+inputID+'" onkeydown="nbmel_edit(this)" />';
				obj.parentNode.innerHTML = inner;
				document.getElementById(inputID).select();
			}
			
			/* MK LINK SAVE */
			function netblog_linkS( obj, uri_id )
			{
				if( !editable ) return;
				var type = obj.className;
				var val = obj.value;
				
				obj.parentNode.innerHTML = '<div onclick="netblog_linkE(this,\''+uri_id+'\')">'+val+'</div>';	
				
				if( type == 'title' )
					linkSet.setTitle( uri_id, val );
				else if( type == 'uri' )
					linkSet.setUri( uri_id, val );
					
				meldsp_mod.innerHTML = linkSet.countModified;
			}
			
			function nbmel_edit( obj )
			{
//				if( event.keyCode == 13 )	// enter
//					obj.blur();
//				else if( event.keyCode == 9 ) {	// tab - select next
//				}
			}
			
			function netblog_retrieve(query,title)
			{	
				if( !linkSet.isBusy() ) {
					
					//hidePlus( 'netblog-body', 0, 250, true, false );
					linkSet.clear();
					linkSet.updateStatusBar( 'Loading...' );
					linkSet.setGroupName(title);
					nbmel_hideLogBox();
					//showPlus( 'netblog-body', 250, 250, true, false );
					//document.getElementById('netblog-body').innerHTML = '<div class="empty" id="netblog-body-nothing"><?php _e('search ...','netblog') ?></div>';	
					//show('netblog-body-nothing',0,50);
					var link_set = linkSet;
					jQuery(document).ready(function($) {
						var data = {
							action: 'get_links',
							query: query
						};
						jQuery.post(ajaxurl, data, function(xml) {
							jQuery(xml).find("LINK").each(function() {
								var infos = '';
								// add is formatted like this: id, uri, title, info, flag, ref
								linkSet.add( jQuery(this).attr('id'),
											 jQuery(this).attr('uri'),
											 jQuery(this).attr('title'),
											 infos,
											 jQuery(this).attr('flag_code'),
											 jQuery(this).attr('references') );
							});
							linkSet.showAll();
							linkSet.updateStatusBar( 'results' );
						});
					});
				}
			}	
			
			function netblog_clear_stats() {
				/* onEvent */
			}
			
			function nbmelUpdateCountFlags() {
				meldsp_offline.innerHTML = linkSet.countOffline;
				meldsp_trash.innerHTML = linkSet.countTrashed;
				meldsp_lock.innerHTML = linkSet.countLocked;
				meldsp_dft.innerHTML = linkSet.countDefault;
				meldsp_mod.innerHTML = linkSet.countModified;
			}
			
			/* REGISTER HANDLER */
			linkSet.registerCBflush2server( netblog_ajax_send_link,this, '','','','','', '' );
			linkSet.registerCBflushed( linkSet.flushed, linkSet, '','','','', '' );
			linkSet.registerCBlinkF( netblog_linkF, this, '', '', '', '', '', '' );
			linkSet.registerPrint2Obj( 'nbarea-body' );
			linkSet.registerStatusObj( 'nbmel-dsp-status' );	
			linkSet.registerCBonEvent( netblog_clear_stats );
			linkSet.registerCBupdateCountFlags( nbmelUpdateCountFlags, this );
			netblog_retrieve('<?php echo $mel_tpl_startup ?>','<?php echo ucfirst($mel_tpl_startupName) ?>');
				
			/* POPUP INIT */
			nbpopup_init('nb-popup');	
	
			var logboxvis = false;
			function nbmel_toogleLogBox() {
				var t = <?php echo $mel_logbox_time ?>;
				if(logboxvis) {
					hidePlus('nbarea-log-par',0.25*t, t,true,true);
					logboxvis = false;
				} else {
					document.getElementById('nbarea-log-par').style.height = 160+"px";
					showPlus('nbarea-log-par',0.25*t, t,true,true);
					logboxvis = true;
				}
			}

			function nbmel_hideLogBox() {
				var t = <?php echo $mel_logbox_time ?>;
				if(logboxvis) {
					hidePlus('nbarea-log-par',0.25*t, t,true,true);
					logboxvis = false;
				}
			}

			function nbmel_showLogBox() {
				var t = <?php echo $mel_logbox_time ?>;
				if(!logboxvis) {
					document.getElementById('nbarea-log-par').style.height = 160+"px";
					showPlus('nbarea-log-par',0.25*t, t,true,true);
					logboxvis = true;
				}
			}
			
			
			function nbmel_search_focus( obj ) {
				if(obj.value == '<?php _e('Quicksearch','netblog') ?>') {
					obj.value=''; 
					obj.className=''
				}
			}
			function nbmel_search_blur( obj ) {
				if(obj.value == '') {
					obj.value = '<?php _e('Quicksearch','netblog') ?>'; 
					obj.className = 'disabled'
				}
			}
			
			window.onload = function() { 
				var d = document.getElementById('netblog-mel-box');
				d.style.visibility = 'hidden';
				d.style.display = 'block';			
				nb_submenu_init( 'nb-mel-custom', 'nb-mel-custom-sub' );
				nb_submenu_init( 'nb-mel-adv', 'nb-mel-adv-sub' );
				d.style.visibility = 'visible';
				d.style.display = 'none';
				show('netblog-mel-box',150,500);
			 }
		//-->
		</script>	
			
		<?php
		
		echo '</div>';		
	}
}

/**
 * Render the mel interface
 * @deprecated This method has been replaced with nbMelGUI::Display() since Netblog 2.0.b6
 */
function netblog_mel()
{
	nbMelGUI::Display();
}

/**
 * Use to add a template to query
 * @deprecated This method has been replaced with nbMel::AddTemplate() since Netblog 2.0.b6
 * @param string $name
 * @param string $val
 */
function netblog_mel_tpl_add( $name, $val )
{
	if( strlen($name) == 0 ) return;
	
	$cur = Netblog::options()->getMelUserTpls();
	$cur[$name] = $val;
	Netblog::options()->setMelUserTpls($cur);
}

/**
 * Removes a query template from mel
 * @deprecated This method has been replaced with nbMel::RemoveTemplate() since Netblog 2.0.b6
 * @param string $name
 */
function netblog_mel_tpl_rm( $name )
{
	$cur = Netblog::options()->getMelUserTpls();
	if( isset($cur[$name]) ) {
		unset($cur[$name]);
		Netblog::options()->setMelUserTpls($cur);
	}	
}

/**
 * Gets a templates value
 * @deprecated This method has been replaced with nbMel::GetTemplate() since Netblog 2.0.b6
 * @param string $name
 * @return mixed
 */
function netblog_mel_tpl_get( $name )
{
	$cur = Netblog::options()->getMelUserTpls();
	return isset($cur[$name]) ? $cur[$name] : false; 
}

/**
 * Get an array of all templates
 * @deprecated This method has been replaced with nbMel::GetAllTemplates() since Netblog 2.0.b6
 * @return array
 */
function netblog_mel_tpls()
{
	return $cur = Netblog::options()->getMelUserTpls();
}
function netblog_mel_tpl_scope()
{
	//return 'netblog_meltpl_';
}
?>
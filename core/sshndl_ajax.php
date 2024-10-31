<?php
/*
 * REQUIRES DEFINITION OF
 * 	- NETBLOG FUNCTIONS OF COMMON DB-QUERIES
 */


//----------------------------------------------------------------------------------
// SERVER-SIDE AJAX-HANDLER
//----------------------------------------------------------------------------------

add_action('wp_ajax_cite_filter_preview', 'netblog_ajax_cite_filterPreview');
function netblog_ajax_cite_filterPreview()
{
	$filter = nbcstyle::parseCMD($_POST['filter']);
	$prev = nbcstyle::previewFilter($filter);
	if($prev !== false)
		echo $prev;
	die();
}


add_action('wp_ajax_get_citation_rsctypes','get_citation_rsctypes');
function get_citation_rsctypes()
{
	$style = $_POST['citationstyle']; 
	$type = ''; $classname = '';
	if( nbcstyle::is_style($style,$type,$classname) ) {
		//if(!file_exists("$classname.php")) die();
		
		require_once 'nbcs_apa.php';
		require_once "$classname.php";
		
		$nbcs = new nbcs_apa();
		
		$nbcs = Netblog::castObj($nbcs,$classname);
		if($nbcs===false) die();
		
		if($classname == 'nbcs_custom')
			$nbcs->setStyle($style);
		
		$typesNamed = nbcstyle::getDftTypes();
		$o = array();
		
		$types = $nbcs->reqAtts(null);
		if(is_array($types) )
			foreach($types as $type=>$fields) {
				$t = array();
				$t['name'] = $type;
				$t['nicename'] = isset($typesNamed[$type]) ? $typesNamed[$type] : ucfirst($type);
				$t['required'] = '1';
				$o[] = $t;
				unset($typesNamed[$type]); 
			}
		foreach($typesNamed as $type=>$name) {
			$t = array();
			$t['name'] = $type;
			$t['nicename'] = $name;
			$t['required'] = '0';
			$o[] = $t;
		}
		echo netblog_ajax_parse_nodeInfos($o);
	};
	die();
}


add_action('wp_ajax_get_citation_fields','get_citation_fields');
function get_citation_fields() {
	$style = $_POST['citationstyle'];
	$type = $_POST['citationtype'];
	$t = ''; $classname = '';
	
	if( nbcstyle::is_style($style,$t,$classname) ) {
		require_once 'nbcs_apa.php';
		require_once "$classname.php";
		
		$nbcs = new nbcs_apa();
		
		$nbcs = Netblog::castObj($nbcs,$classname);
		if($nbcs===false) die();

		if($classname == 'nbcs_custom')
			$nbcs->setStyle($style);
			
		$atts = $nbcs->getAttributes($type);
		if( $atts===null ) die();
		
		$attsNamed = nbcstyle::getDftAttsNamed();
		
		$fields = array();
		foreach($atts as $field=>$opts) {
			if( strlen($field) == 0 ) continue;
			
			$f = array();
			$f['name'] = $field;
			$f['nicename'] = isset($attsNamed[$field]) ? $attsNamed[$field] : ucfirst($field);
			$t = ''; $req = '1';
			if(is_string($opts))
				if( strpos($opts,'-optional')!==false || strpos($opts,'optional')!==false )
					{ $t = __('Optional','netblog'); $req = '0'; }
			$f['info'] = $t;
			$f['required'] = $req;
			$f['covered'] = '1';
			$fields[] = $f;
			unset($attsNamed[$field]);
		}
		foreach($attsNamed as $field=>$name) {
			$f = array();
			$f['name'] = $field;
			$f['nicename'] = $name;
			$f['info'] = '';
			$f['required'] = '0';
			$f['covered'] = '0';
			$fields[] = $f;
		}
		echo netblog_ajax_parse_nodeInfos($fields);
	}
	die();
}


add_action('wp_ajax_netblogfig_exists','netblogfig_exists');
function netblogfig_exists() {
	$name = $_POST['caption_name'];
	
	if( nbdb::cpt_existsName($name) || nbdb::cptg_exists($name) )
		echo "FALSE";
	else echo "TRUE";
	
	die();
}

add_action('wp_ajax_nbopts_save','nbopts_save');
function nbopts_save() {
	$data = $_POST['data'];
	
	if(!is_string($data) || strlen($data) == 0 )
		echo 'false';
	$data = urldecode($data);
	$data = htmlspecialchars_decode($data);		
	$options = Netblog::options();
	$dt = explode(';',$data);
	
	$pilot = new nbTestPilot();
	
	$num = sizeof($dt);
	foreach($dt as $k=>$cmd) {
		$t = explode(',',$cmd);
		if(sizeof($t)==2 && sizeof($c=trim($t[0]))>0 && sizeof($c2=trim($t[1]))>0) {			
			if(substr($c,0,2) !== '__') {
				if($options->$c($c2))
					unset($dt[$k]);
			} else {
				$p = strpos($c,'_',2);
				if(!$p) $p = strlen($c);
				$act = substr($c,2,$p-2);
				$actSub = '';
				if($p+2<strlen($c) && substr($c,$p,2)==='__'){
					$actSub = substr($c,$p+2);
				}
				
				$para = explode('|||',$c2);
				foreach($para as $park=>$parv) {
					if(($_p=strpos($parv,':'))!==false) {
						$para[substr($parv,0,$_p)] = substr($parv,$_p+1);
						unset($para[$park]);
					}
				}
				
				switch($act) {
					case 'addMelTpl':
						if(!isset($para['name']) || !isset($para['query']))
							continue;
						if( $options->addMelUserTpl($para['name'],$para['query']) )
							unset($dt[$k]);
						$pilot->melTemplateAdd($para['query']);
						break;
					case 'rmMelTpl':
						if(!isset($para['name'])) continue;
						$tpls = $options->getMelUserTpls();
						$pilot->melTemplateRemove($tpls[$para['name']]);
						if( $options->rmMelUserTpl($para['name']) )
							unset($dt[$k]);
						
						break;
						
					case 'addBibFilter':
						if(!isset($para['style']) || !isset($para['type']) || !isset($para['filter']))
							continue;
						if( nbcstyle::addFilter($para['style'],$para['type'],$para['filter']) )
							unset($dt[$k]);
						$pilot->citationCustomStyleAdd($para['filter']);
						break;
					case 'rmBibFilter':
						if(!isset($para['style']) || !isset($para['type']))
							continue;
						
						$pilot->citationCustomStyleRemove( nbcstyle::getFilterCMD($para['style'],$para['type'],false) );
						
						if( nbcstyle::rmFilter($para['style'],$para['type']) )
							unset($dt[$k]);						
						break;
						

					case 'createCaption':
						if(!isset($para['name']) || !isset($para['numbering']) || !isset($para['display']) || !isset($para['format']))
							continue;
						$a = isset($para['active']) ? $para['active']=='1' || $para['active']=='true' : true;
						if(nbdb::cptg_exists($para['name']))
							$pilot->gcaptionChange(1,$para['name']);
						else $pilot->gcaptionAdd(1,$para['name']);
						
						nbdb::cptg_set($para['name'],$para['numbering'],$para['format'],$para['display'],$a);
						unset($dt[$k]);						
						break;
						
					case 'rebuildCaptions':
						if($c2=='true' && nbdb::cptg_rebuild()) {							
							unset($dt[$k]);
						}
						$pilot->gcaptionRebuild();
						break;
						
					case 'eedRebuild':
						nbdb::eed_rebuild();
						unset($dt[$k]);						
						break;
						
					case 'eedRemove':
						nbdb::eed_remove();
						unset($dt[$k]);
						break;
						
					default:
						echo 'Fatal Error: unknown command "'.$act.'"';
						Netblog::log('Fatal Error: unknown command "'.$act.'"');
						die();
				}
			}
		}
	}	
	$pilot->updateSettings($num-1,sizeof($dt)-1);
	$pilot->save();
	
	$data = trim(implode(';',$dt));
	if($data=='') echo 'true';
	else echo $data;
	die();
}


add_action('wp_ajax_nbcpt_param_retrieve','nbcpt_param_retrieve');
function nbcpt_param_retrieve() {
	$cpt = $_POST['caption_name'];
	
	if(!nbdb::cptg_exists($cpt)) {
		return 'false';
		die();
	}
	
	$p = nbdb::cptg_get($cpt);
	echo 'name:'.$cpt.';';
	echo 'numbering:'.$p['numberFormat'].';';
	echo 'display:'.$p['display'].';';
	echo 'printFormat:'.$p['printFormat'].';';
	echo 'active:'.($p['isactive'] ? 'true' : 'false');
	
	die();
}


add_action('wp_ajax_nbexport_options_retrieve','nbexport_options_retrieve');
function nbexport_options_retrieve() {
	$pl = $_POST['plugin'];
	$schedId = $_POST['export_schedule'];
	$post = array();
	
	$xml = new SimpleXMLElement('<nbexport_root/>');
	$xstatus = $xml->addChild('status');	
	$xpara = $xml->addChild('parameter');
	
	if(strlen($schedId)>0 && $schedId!='none') {		
		require_once 'nbExportScheduler.php';
		$sched = new nbExportScheduler();
		if( ($item=$sched->getItemById($schedId)) != null ) {
			parse_str(html_entity_decode($item->settings),$post);
			if(isset($post['plugin']))
				$pl = $post['plugin'];
			else {
				$xstatus->addAttribute('type','error');
				$xstatus->addAttribute('message','Trying to apply settings from export schedule failed (illegal stored settings)');
				echo $xml->asXML();
				die();
			}
		} else {
			$xstatus->addAttribute('type','error');
			$xstatus->addAttribute('message','Trying to apply settings from export schedule failed (illegal schedule id)');
			echo $xml->asXML();
			die();
		}		
	}
	
	if(strlen($pl)>0 && $pl!='none') {
		require_once 'nbexp.php';
		$p = nbexp::loadPlugin($pl);
		if($p==NULL) {
			$xstatus->addAttribute('type','error');
			$xstatus->addAttribute('message','Failed to load export module "'.$pl.'"');
			echo $xml->asXML();
			die();
		}
			
		$pilot = new nbTestPilot();
		$pilot->exportModuleGetSettings($p->getName(),$pl,$p->getBuildNo(),$p->getAuthorURI() );
		$pilot->save();
			
		$op = $p->getExportOptions();
		if(!is_array($op) || sizeof($op) == 0) {
			$xstatus->addAttribute('type','error');
			$xstatus->addAttribute('message','Export module "'.$pl.'" does not have any options. Cannot build settings menu.');
			echo $xml->asXML(); 
			die();
		}
	} else {
		$xstatus->addAttribute('type','error');
		$xstatus->addAttribute('message','Missing export module identifier.');
		echo $xml->asXML();
		die();
	}
	
	$xpara->addAttribute('plugin',$pl);
	$xpara->addAttribute('export_schedule_id',$schedId);
	
	$name = array( 'Export Name' => array(''=>'text|name:export_name'),
				'Automation' => array( ''=>array('checkbox|name:automation|value:true|label:'.(sizeof($post)==0 ? 'Save these settings for scheduled export' : 'Update Settings for Export Schedule') ) )
			 );
	$form = nbeximport_build_htmlform_options($name,$post);	
	$form .= nbeximport_build_htmlform_options($op,$post);
	
	$xml->addChild('build_settings',$form);
	$xstatus->addAttribute('type','ok');
	$xstatus->addAttribute('message','Loaded export settings');
	echo $xml->asXML();
	die();
}

function nbeximport_build_htmlform_options( $options, $post = array() ) {
	$o = '';
	foreach($options as $g1=>$v1) {
		if(!is_string($g1) || strlen($g1)==0 ) continue;
		if(is_string($v1) && strlen($v1)==0 ) continue;
		
		$o .= '<div class="col1">'.ucfirst($g1).'</div>';
		if(is_string($v1)) $v1 = array(''=>$v1);
		foreach($v1 as $g2=>$v2) {
			if(strlen($g2)>0) {
				$o .= '<div class="nofloat noflt"></div>';
				$o .= '<div class="col1r">'.ucfirst($g2).'</div>';
			}
			if(is_string($v2)) $v2 = array($v2);
			foreach($v2 as $set) {
				$t = explode('|',$set);				
				foreach($t as $tk=>$tv) {
					if(($sp=strpos($tv,':')) !== false) {
						$t[substr($tv,0,$sp)] = substr($tv,$sp+1);
						unset($t[$tk]);
					}
				}
				if(!isset($t['type'])) {
					if(isset($t[0])) {
						$t['type'] = $t[0];
						unset($t[0]);
					} else continue;
				}
				$f = '';
				switch($t['type']) {
					case 'checkbox': case 'radio':
						if(!isset($t['name'])) continue;
						$id = 'nbexp-'.$pl.$g1.$g2.$t['type'].$t['name'];
						$f = '<input type="'.$t['type'].'" name="'.$t['name'].'" ';
						if(isset($post[$t['name']]))
							$f .= ' checked="checked" ';
						else if(array_search('checked',$t)!==false)
							$f .= ' checked="checked" ';
						$f .= ' value="'.(isset($t['value']) ? $t['value'] : 'true').'" ';
						$f .=' id="'.$id.'"/> ';
						if(isset($t['label']))
							$f .= '<label for="'.$id.'">'.$t['label'].'</label> ';
						break;
						
					case 'select':
						if(!isset($t['name']) || !isset($t['options'])) continue;
						$f = '<select name="'.$t['name'].'"';						
						if(isset($t['multirows']) && intval($t['multirows'],10) > 0)
							$f .= ' multiple="multiple" size="'.intval($t['multirows'],10).'" style="height:'.(intval($t['multirows'],10)*25).'px"';
						$f .= ' >';
						$slc = explode('@',$t['options']);
						if(sizeof($slc)==0) continue;
						foreach($slc as $slco) {
							$f .= '<option';							
							if(($slcop=strpos($slco,'>'))!==false) {
								$slcz = substr($slco,0,$slcop);
								if(isset($post[$t['name']])) {
									if($post[$t['name']] == $slcz)
										$f .= " selected=\"selected\" ";
								} else if(($_p1=strpos($slcz,'{'))!==false && ($_p2=strpos($slcz,'}'))!==false
									&& $_p1 < $_p2) {
									$_t = explode(',',substr($slcz,$_p1+1,$_p2-$_p1-1));									
									foreach($_t as $_v) {
										if($_v == "selected") 
											$f .= " $_v=\"$_v\" ";									
									}
									$slcz = substr($slcz,0,$_p1);
								}
								$f .= ' value="'.$slcz.'" ';
								$slco = substr($slco,$slcop+1);
							}
							$f .= '> '.$slco.' </option>';
						}
							
						$f .= '</select>';
						break;
						
					case 'text': case 'hidden': case 'password':
						if(!isset($t['name'])) continue;
						$f = '<input type="'.$t['type'].'" name="'.$t['name'].'" ';
						if(isset($post[$t['name']]))
							$f .= ' value="'.$post[$t['name']].'" ';						
						else if(isset($t['value']))
							$f .= ' value="'.$t['value'].'" ';
						$f .= '/>';
						break;
						
					case 'legend':
						if(!isset($t['display'])) continue;
						$f = $t['display'];
						break;
						
						
					default: continue;
				}
				
				$o .= '<div class="col2">'.$f.'</div>';
			}
		}
		$o .= '<div class="nofloat noflt nl"></div>';
	}
		
	return $o;
}

add_action('wp_ajax_nbimport_options_retrieve','nbimport_options_retrieve');
function nbimport_options_retrieve() {
	$formStr = $_POST['form_encoded'];	
	parse_str(html_entity_decode($formStr),$form);	
	$file = $form['import_file'];
	
	$xml = new SimpleXMLElement('<nbimport_root/>');
	$xpara = $xml->addChild('parameter');

	if(!file_exists(($file))) { 
		$xpara->addAttribute('status','File not found');
		print $xml->asXML();
		die(); 
	}	
	$data = file_get_contents($file);	
	
	require_once 'nbexp.php';
	$pls = nbexp::getInstalledPlugins(false);
	foreach($pls as $pl) {
		$p = nbexp::loadPlugin($pl);
		if($p==NULL) continue;
		
		$pilot = new nbTestPilot();
		$pilot->importModuleGetSettings($p->getName(), $pl, $p->getBuildNo(), $p->getAuthorURI() );
		$pilot->save();
	
		$isComplete = false;
		try {
			$op = $p->getImportOptions($data,$form,$isComplete);
		} catch( Exception $e) {
			$xpara->addAttribute('status',$e->getMessage());
			print $xml->asXML();
			die();
		}
		$xpara->addAttribute('status_options', $isComplete ? 'complete' : 'incomplete');
		
		if(!is_array($op) || sizeof($op)==0)
			continue;
		else {
			$stat = array( 'Stats' => array( '' => array('hidden|name:plugin|value:'.$pl),
											'Data Format'=>'legend|display:'.$p->getName(),
											'File Size'=>'legend|display:'.Netblog::formatBytes(filesize($file),3),
											'Last Saved'=>'legend|display:'.date('r',filemtime($fp))),
											 );
			$opts = nbeximport_build_htmlform_options($stat);
			$opts .= nbeximport_build_htmlform_options($op);
			$xpara->addAttribute('status','OK');
			$xml->addChild('html_options',$opts);
			print $xml->asXML();
			die();
		}
	}
	
	$xpara->addAttribute('status','Unknown file type');
	print $xml->asXML();
	die();
}


add_action('wp_ajax_nbexport_options_send','nbexport_options_send');
function nbexport_options_send() {
	$formStr = $_POST['form_encoded'];	
	parse_str(html_entity_decode($formStr),$form);
	
	$xml = new SimpleXMLElement('<nbexport_root/>');
	$xpara = $xml->addChild('parameter');
	 
	$script = str_replace('\\','/',dirname($_SERVER['SCRIPT_URI']));
	$file = str_replace('\\','/',dirname(__FILE__));
	$path = 'false';
	
	$t = explode('/',$script);
	$sl = 'wp-content';
	
	$t2 = explode('/',$file);
	$k = array_search($sl,$t2);
	
	if(!isset($form['export_name']) || strlen($form['export_name']) < 3 ) {
		$xpara->addAttribute('status','Illegal Export Name. Must be at least 3 characters.');
		echo $xml->asXML();
		die();
	}
	
	if($k!==false) {
		unset($t[sizeof($t)-1]);
		$path = implode('/',$t);
		for($i=$k;$i<sizeof($t2)-1;$i++)
			$path .= '/'.$t2[$i];
		$path .= ($pathrel='/data/'.$form['export_name'].'.'.time().'.'.$form['plugin'].'.bac');
	}	
	$xpara->addAttribute('path_data', $path);

	require_once 'nbexp.php';
	$p = nbexp::loadPlugin($form['plugin']);
	if($p==NULL) {
		$xpara->addAttribute('status','Unable to load export module');
		print $xml->asXML();
		die();
	}
		
	$pilot = new nbTestPilot();
	$pilot->exportModule($p->getName(), $form['plugin'], $p->getBuildNo(), $p->getAuthorURI(), false);
	$pilot->save();
		
	try {
		$data = $p->exportData($form, $form['export_name'], dirname(__FILE__).'/..'.$pathrel);		
		$xpara->addAttribute('status','OK');
		$xpara->addAttribute('message','Exported Data to "'.$path.'"');
	} catch( Exception $e ) {
		$xpara->addAttribute('status',$e->getMessage());
	}	
	print $xml->asXML();	
	die();
}

add_action('wp_ajax_nbimport_options_send','nbimport_options_send');
function nbimport_options_send() {
	$formStr = $_POST['form_encoded'];	
	parse_str(html_entity_decode($formStr),$form);
	
	$xml = new SimpleXMLElement('<nbexport_root/>');
	$xpara = $xml->addChild('parameter');
	
	require_once 'nbexp.php';
	$p = nbexp::loadPlugin($form['plugin']);
	if($p==NULL) {
		$xpara->addAttribute('status','Unable to load import module');
		print $xml->asXML();
		die();
	}
		
	$pilot = new nbTestPilot();
	$pilot->importModule($p->getName(), $form['plugin'], $p->getBuildNo(), $p->getAuthorURI() );
	$pilot->save();
	
	if(!file_exists(($fp=$form['import_file']))) {
		$xpara->addAttribute('status','Unable to open file');
		print $xml->asXML();
		die();
	}
	
	try {		
		$stat = $p->importData( @file_get_contents($fp),$form);		
		$xpara->addAttribute('status','OK');
	} catch( Exception $e ) {
		$xpara->addAttribute('status',$e->getMessage());
	}	
	print $xml->asXML();
	die();
}

add_action('wp_ajax_nbfootprint_repair','nbfootprint_repair');
function nbfootprint_repair() {
	if( nbdb::footprt_createAll() )
		echo 'ok';
	else echo __('Repairing Footprints Failed!','netblog');
	die();
}

add_action('wp_ajax_nbexpsched_checktime','nbexpsched_checktime');
function nbexpsched_checktime() {
	$timeStr = $_POST['time_string'];
	$type = $_POST['time_type'];
	
	$timezone = ini_get('date.timezone');
	date_default_timezone_set($timezone);
	
	if($type == 'date') {
		if($timeStr == '0' || $timeStr == '' || $timeStr == 'never' || $timeStr == '-')
			echo __('Never','netblog');
		else if( ($time=strtotime($timeStr))===false || $time==-1 )
			echo 'false';
		else echo date('r',$time);
	} else if($type == 'interval') {
		if(substr($timeStr,0,1)!='+') 
			$timeStr = "+$timeStr";
		if( ($time=strtotime($timeStr))===false || $time==-1 )
			echo 'false';
		
		require_once 'timeSpan.php';	
		$span = new timeSpan($time-time());
		echo $span->getFormatted('true',3);
	} else {
		echo 'false';
	}	
	die();
}

add_action('wp_ajax_nbexpsched_getitem','nbexpsched_getitem');
function nbexpsched_getitem() {
	$id = $_POST['item_id'];

	
	$sched = new nbExportScheduler();
	if( ($item=$sched->getItemById($id))==null ) {
		echo 'false';
		die();
	}
	
				
	$pilot = new nbTestPilot();
	$pilot->exportSchedulerLoad("$id:$item->name");
	$pilot->save();
	
	$xml = new SimpleXMLElement('<nbExportScheduler/>');
	$item->asXML($xml);
	echo $xml->asXML();
	die();
}

add_action('wp_ajax_nbexpsched_saveitem','nbexpsched_saveitem');
function nbexpsched_saveitem() {
	$id = $_POST['item_id'];

	
	$sched = new nbExportScheduler();
	if( ($item=$sched->getItemById($id))==null ) {
		echo 'false';
		die();
	}
		
	$pilot = new nbTestPilot();
	$pilot->exportSchedulerChange("$id:$item->name");
	$pilot->save();
	
	$xml = new SimpleXMLElement('<nbExportScheduler/>');
	$item->name = $_POST['item_name'];
	$item->scheduleType = $_POST['scheduleType'];
	$item->scheduleTime = $_POST['scheduleTime'];
	$item->scheduleTimeStart = $_POST['scheduleTimeStart'];
	$item->scheduleTimeEnd = $_POST['scheduleTimeEnd'];
	$item->verifySchedule();
	$item->calculateSchedule();	
	$item->asXML($xml);
	$sched->save();
	echo $xml->asXML();
	die();
}

add_action('wp_ajax_nbexpsched_removeitem','nbexpsched_removeitem');
function nbexpsched_removeitem() {
	$id = $_POST['item_id'];
	
	$pilot = new nbTestPilot();
	$pilot->exportSchedulerRemove($id);
	$pilot->save();
	
	$sched = new nbExportScheduler();
	if($sched->removeItemById($id)) {
		echo 'true';
		$sched->save();
	} else echo 'false';
	die();
}

add_action('wp_ajax_nbsearch_get_provider','nbsearch_get_provider');
function nbsearch_get_provider() {
	if(strlen($id=$_POST['searchid'])==0) die(0);
	
	$s = new nbsearch();
	$s->loadDefinitions();
	if( ($d=$s->getById($id)) == null ) die(0);
	
	echo 'name:'.$d['name'].';';
	echo 'id:'.$d['id'].';';
	echo 'urlprovider:'.$d['urlprovider'].';';
	echo 'urlquery:'.$d['urlquery'].';';
	echo 'maxresults:'.$d['maxresults'].';';
	echo 'apikey:'.$d['apikey'];
	
	die();
}

add_action('wp_ajax_netblog_crosssite_forward','netblog_urlforward');
function netblog_urlforward() {
	$url = $_POST['urlforward'];
	$post = $_POST;
	unset($post['urlforward']);
	unset($post['action']);
	
	require_once 'DataTransfer.php';
	$tr = new DataTransfer();
	$res = $tr->SubmitPost($url, $post);	
	echo $res['content'];
	die();
}

add_action('wp_ajax_netblog_getlatest_links','netblog_getlatest_links');
function netblog_getlatest_links() {	
	$lks = nbLinkExternCollection::LoadLatest(5);
	
	$xml = new SimpleXMLElement('<root/>');
	foreach($lks as $col) {
		for($i=0;$i<$col->CountLinks();$i++) {
			$col->GetLink($i)->AsXML($xml);
		}
	}
	echo $xml->asXML();
	die();
}

add_action('wp_ajax_netblog_link_getmetatags','netblog_link_getmetatags');
function netblog_link_getmetatags() {
	$tags = get_meta_tags($_POST['url']);
	if (!isset($tags['description'])) {		
		$urldata = DataTransfer::RetrieveUrl($_POST['url']);
		$regex_pattern = "/<p(.*)>(.*)<\/p>/";
		preg_match_all($regex_pattern,$urldata['content'],$matches);
		$t = array();
		foreach ($matches as $m1)
			foreach ($m1 as $m2)
				$t[] = $m2;
		$tags['description'] = substr(strip_tags(implode('... ', $t)), 0, 255).' ....';
	}
	
	$xml = new SimpleXMLElement('<root/>');
	$xl = $xml->addChild('LINK');
	$xl->addAttribute('url', $_POST['url']);	
	foreach($tags as $k=>$v)
		$xl->addChild($k,$v);
	die($xml->asXML());
}

add_action('wp_ajax_netblog_references_latest','netblog_references_latest');
function netblog_references_latest() {
	$refs = nbBibliographyReference::getLatest(5);
	$xml = new SimpleXMLElement('<root/>');
	$xl = $xml->addChild('References');
	foreach($refs as $o)
		$o->asXML($xl);
	die($xml->asXML());
}



/**
 * Parse an array into a string for ajax-transmission.
 *
 * @param array[][] $nodes
 * @return string
 */
function netblog_ajax_parse_nodeInfos( $nodes )
{
	for( $i=0; $i<sizeof($nodes); $i++ )
		$nodes[$i] = implode( Netblog::options()->getAjaxDelimiterSub(), $nodes[$i] );
	return implode( Netblog::options()->getAjaxDelimiterMain(), $nodes );
}

function netblog_post_save( $post_id )
{
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !wp_verify_nonce( $_POST['netblog_noncename'], plugin_basename(__FILE__) )) {
	  return $post_id;
	}
	
	// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
	// to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
	  return $post_id;
	 
	// Check permissions
	if ( 'page' == $_POST['post_type'] ) {
	  if ( !current_user_can( 'edit_page', $post_id ) )
	    return $post_id;
	} else {
	  if ( !current_user_can( 'edit_post', $post_id ) )
	    return $post_id;
	}
	
	// saving work
}
?>
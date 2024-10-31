<?php
require_once 'nbExportScheduler.php';
require_once 'timeSpan.php';

/**
 * Graphical user interface for Export Scheduler
 *
 * @since Netblog 2.0
 */
class nbExportSchedulerGUI {

	static function printWnd() {
		echo '<div class="wrap">
			<div id="icon-options-general" class="icon32"><br /></div>';
		netblog_feedback_smilies();
		echo '<h2>Netblog Export Scheduler</h2><br />';
	
		$timezone = ini_get('date.timezone');
		date_default_timezone_set($timezone);
		
		$sched = new nbExportScheduler();
		?> 
		
		<div class="netblog-container" id="nb-popup" style="display:none">
		</div>
		
		<?php
		if($sched->numItems() == 0) {
			echo '<div class="netblog-container"><div class="infobox">';
			echo 'You do not have any export schedules, yet.<br /><br />';
			echo '<em>Creating Export Schedules</em>';
			echo '<div style="margin-left:20px">Go to Settings | Export. Make sure to check the automation option, if the desired export module supports it.</div>';
			echo '</div></div>';
		} else {
		?>
		
		<div class="netblog-area" style="width: 1100px; display:none" id="netblog-expsched-box">		
			<ul class="netblog-expsched-item">
				<li class="break"></li>
			</ul>
			<?php			
			for($i=0; $i<$sched->numItems(); $i++ ) {
				$item = $sched->getItem($i);
				$id = $item->getId();
				$span = new timeSpan($t=($item->getNextSchedule() - time()));
				$next = $span->getFormatted(true,3);
				if($item->getNextSchedule()==0)
					$next = __('Never Run','netblog');
				else if($t>0)
					$next = __('In','netblog').' '.$next;
				else 
					$next .= ' '.__('ago','netblog');
				
				echo '<ul class="netblog-expsched-item" id="nbexpsched-'.$id.'">';
					echo '<li class="name">'.$item->name.'</li>';
					echo '<li class="nextSched">'.$next.'</li>';
					echo '<li class="schedOptions">';
					if($item->scheduleTime == 0) {
						echo __('No Scheduling','netblog');
					} else if($item->scheduleType == 'once') {
						echo 'Once at <span class="small">'.date('r', $item->scheduleTime ).'</span>';
					} else {
						$every = new timeSpan($item->scheduleTime);
						echo 'Every <span class="small">'.$every->getFormatted(true,4).'</span><br />';
						echo 'From <span class="small">'.date('r', $item->scheduleTimeStart ).'</span><br />';
						echo 'To <span class="small">'.date('r', $item->scheduleTimeEnd ).'</span>';
					}
					if( sizeof($history=$item->getHistory()) > 0 ) {
						rsort($history);
						echo '<span id="nbexpsched-'.$id.'-history" style="display:none; overflow: auto; max-height: 300px;"><br />Past Schedules';
						$hi = 0;
						foreach($history as $time) {
							if($hi<3) {
								$span = new timeSpan(time()-$time);
								$timePrint = $span->getFormatted(true,3).' '.__('ago','netblog');
							} else $timePrint = date('r',$time);
							echo '<br /><span class="small indent">'.$timePrint.'</span>';
							$hi++;
						}
						echo '</span>';
					}
					echo '</li>';
					echo '<li class="actions">
								<a onclick="editItem(\''.$id.'\')">Edit</a>
								<a onclick="removeItem(\''.$id.'\')">Remove</a>
								'.(sizeof($history)>0 ? '<a onclick="nbtoogle(\'nbexpsched-'.$id.'-history\')">History</a></li>' : '');
					echo '<li class="break hline"></li>';
				echo '</ul>';
			}
			?>
		</div>
		<?php } ?>
		<script type="text/javascript">
		<!--
		function nbtoogle(id) {
			var o = document.getElementById(id);
			if(o.style.display == 'none')
				show(id,50,500);
			else hide(id,50,250);
		}
		function setSchedType( type, id ) {
			if(type == 'once') {
				document.getElementById('nbexpsched-'+id+'-opt-once').style.display = 'block';
				document.getElementById('nbexpsched-'+id+'-opt-periodic').style.display = 'none';
			} else {
				document.getElementById('nbexpsched-'+id+'-opt-once').style.display = 'none';
				document.getElementById('nbexpsched-'+id+'-opt-periodic').style.display = 'block';
			}
		}
		
		function checkTimeString( string, type, previewId ) {
			var preview = document.getElementById(previewId);
			preview.innerHTML = 'checking...';
			jQuery(document).ready(function($) {
				var data = {
					action: 'nbexpsched_checktime',
					time_string: string,
					time_type: type
				};
				jQuery.post(ajaxurl, data, function(r) {
					r = r.trim();
					if(r=='0' || r.length == 0)
						preview.innerHTML = 'Service Unavailable';
					else if(r=='false') 
						preview.innerHTML = 'Illegal Time Format';
					else
						preview.innerHTML = r;									
				});
			});
		}	
		
		function editItem( elemNum ) {
			var elem = document.getElementById('nbexpsched-'+elemNum);
			fadeOpacity('nbexpsched-'+elemNum,100,20,350,24);
			jQuery(document).ready(function($) {
				var data = {
					action: 'nbexpsched_getitem',
					item_id: elemNum
				};
				jQuery.post(ajaxurl, data, function(xml) {
					xml = xml.trim();
					if(xml=='0' || xml.length == 0)
						alert('Service Unavailable');
					else if(xml=='false') 
						alert('Bad Request');
					else {
						var found = false;
						jQuery(xml).find("item").each(function() {
							var id = jQuery(this).attr('id');
							var type = jQuery(this).attr('scheduleType');
							if(id==elemNum) {
								var out = '<li class="name"><input type="text" value="'+ jQuery(this).attr('name') +'" class="text" id="nbexpsched-'+id+'-name" /></li>';
								out += '<li class="nextSched" id="nbexpsched-'+id+'-nextsched">'+ jQuery(this).attr('scheduleNextNice') +'</li>';
								out += '<li class="schedOptions">';
								out += '<select onchange="setSchedType(this.value,\''+id+'\')" id="nbexpsched-'+id+'-type">';
								out += '<option value="once" '+ (type!='every' ? 'selected="selected"' : '') +'>Once</option>';
								out += '<option value="periodic" '+ (type=='every' ? 'selected="selected"' : '') +'>Periodic</option>';
								out += '</select><br />';
								out += '<div id="nbexpsched-'+id+'-options">';
								
								out += '<div id="nbexpsched-'+id+'-opt-once" '+(type!='once' ? 'style="display:none"' : '')+'>';
								out += 'Date: <input type="text" value="'+ (type=='once' ? jQuery(this).attr('scheduleTimeNice') : 'never') +'" class="text" onblur="checkTimeString(this.value,\'date\',\'nbexpsched-'+id+'-date\')" />';
								out += ' <span class="small" id="nbexpsched-'+id+'-date">'+ jQuery(this).attr('scheduleTimeNice') +'</span>';
								out += '</div>';
							
								out += '<div id="nbexpsched-'+id+'-opt-periodic" '+(type!='every' ? 'style="display:none"' : '')+'><table>';
								out += '<tr><td>Start</td><td><input type="text" value="'+ (type=='every' ? jQuery(this).attr('scheduleTimeStartNice') : 'today') +'" class="text" onblur="checkTimeString(this.value,\'date\',\'nbexpsched-'+id+'-start\')" />';
									out += ' <span class="small" id="nbexpsched-'+id+'-start">'+ (type=='every' ? jQuery(this).attr('scheduleTimeStartNice') : 'today') +'</span></td></tr>';
								out += '<tr><td>End</td><td><input type="text" value="'+ (type=='every' ? jQuery(this).attr('scheduleTimeEndNice') : '+1 year') +'" class="text" onblur="checkTimeString(this.value,\'date\',\'nbexpsched-'+id+'-end\')" />';
									out += ' <span class="small" id="nbexpsched-'+id+'-end">'+ (type=='every' ? jQuery(this).attr('scheduleTimeEndNice') : '+1 year') +'</span></td></tr>';
								out += '<tr><td>Interval</td><td><input type="text" value="'+ (type=='every' ? jQuery(this).attr('scheduleTimeNice') : '1 week') +'" class="text" onblur="checkTimeString(this.value,\'interval\',\'nbexpsched-'+id+'-interval\')" />';
									out += ' <span class="small" id="nbexpsched-'+id+'-interval">'+ (type=='every' ? jQuery(this).attr('scheduleTimeNice') : '1 week') +'</span></td></tr>';
								out += '</table></div>';
								
								out += '</div></li>';
								out += '<li class="actions"><a onclick="saveItem(\''+id+'\')">Save</a></li>';
								out += '<li class="break hline"></li>';
								elem.innerHTML = out;
								fadeOpacity('nbexpsched-'+elemNum,20,100,250,24);
								found = true;
							}							
						});	
						if(!found)
							alert(xml);
					}									
				});
			});
		}	
		
		function saveItem(id) {
			var elem = document.getElementById('nbexpsched-'+id);
			var type = document.getElementById('nbexpsched-'+id+'-type').value;
			fadeOpacity('nbexpsched-'+id,100,20,350,24);
			jQuery(document).ready(function($) {
				var data = {
					action: 'nbexpsched_saveitem',
					item_id: id,
					item_name: document.getElementById('nbexpsched-'+id+'-name').value,
					scheduleType: type,
					scheduleTime: document.getElementById(type=='once' ? 'nbexpsched-'+id+'-date' : 'nbexpsched-'+id+'-interval').innerHTML,
					scheduleTimeStart: document.getElementById('nbexpsched-'+id+'-start').innerHTML,
					scheduleTimeEnd: document.getElementById('nbexpsched-'+id+'-end').innerHTML
				};
				jQuery.post(ajaxurl, data, function(xml) {
					xml = xml.trim();					
					if(xml=='0' || xml.length == 0)
						alert('Service Unavailable');
					else if(xml=='false') 
						alert('Unable to Save Schedule');
					else {
						var save = false;
						jQuery(xml).find("item").each(function() {
							var idreturn = jQuery(this).attr('id');
							var type = jQuery(this).attr('scheduleType');
							if(id==idreturn) {
								var out = '';
								out += '<li class="name">'+ jQuery(this).attr('name') +'</li>';
								out += '<li class="nextSched">'+ jQuery(this).attr('scheduleNextNice') +'</li>';
								out += '<li class="schedOptions">';
								if( jQuery(this).attr('scheduleTime') == '0' )
									out += 'No Scheduling';
								else if(type == 'once') {
									out += 'Once at <span class="small">'+ jQuery(this).attr('scheduleTimeNice')+'</span>';
								} else {
									out += 'Every <span class="small">'+ jQuery(this).attr('scheduleTimeNice') +'</span><br />';
									out += 'From <span class="small">'+ jQuery(this).attr('scheduleTimeStartNice') +'</span><br />';
									out += 'To <span class="small">'+ jQuery(this).attr('scheduleTimeEndNice') +'</span><br />';
								}
								out += '</li>';
								out += '<li class="actions"> <a onclick="editItem(\''+id+'\')">Edit</a> <a onclick="removeItem(\''+id+'\')">Remove</a>';
								out += '</li><li class="break hline"></li>';
								elem.innerHTML = out;
								fadeOpacity('nbexpsched-'+id,20,100,250,24);
								save = true;
							}
						});
						if(!save)
							alert(xml);	
					}									
				});
			});
		}
		
		function removeItem(id) {
			if( confirm('Removing an export schedule can NOT BE UNDONE!\n\nDo you really want to proceed?') ) {
				var elem = document.getElementById('nbexpsched-'+id);
				jQuery(document).ready(function($) {
					var data = {
						action: 'nbexpsched_removeitem',
						item_id: id
					};
					jQuery.post(ajaxurl, data, function(r) {
						r = r.trim();
						if(r=='0' || r.length == 0)
							alert('Service Unavailable');
						else if(r=='false') 
							alert('Failed to remove schedule');
						else
							elem.style.display = 'none';
					});
				});
			}
		}
		
		window.onload = function() { show('netblog-expsched-box',150,500); }
		//-->
		</script>
		</div>		
		<?php
	}
}
?>
<?php

class nbLoggingGUI {
	static function display() {
		echo '<div class="wrap">
			<div id="icon-options-general" class="icon32"><br /></div>';		
		echo '<h2>Netblog Logs</h2><br />';		
		
		//echo '<a onclick="nbpop_fetch(\''.nbImportBibTexGUI::$ID.'\')">Load Window: bibtex</a>';
		
		$timezone = ini_get('date.timezone');
		date_default_timezone_set($timezone);
		
		if(isset($_GET['nblogging_action']) && $_GET['nblogging_action'] == 'clear') {
			Netblog::clearLog();
			unset($_GET['nblogging_action']);
		}
		
		$formatter = array('error:'=>'color:red', 'warning:'=>'color:orange', 'success:'=>'color:green');
		$formatterCounts = array('error:'=>0, 'warning:'=>0, 'success:'=>0);
		
		$log = Netblog::getLog();
		$logPrint = nl2br( $log );
		$logArr = explode('<br />',$logPrint);
		foreach($logArr as $k=>&$v) {
			if(strlen($v=trim($v))==0) {
				unset($logArr[$k]);
				continue;
			}
			foreach($formatter as $word=>$style)
				if(stripos($v,$word)!==false) {
					$v = "<span style=\"$style\">$v</span>";
					$formatterCounts[$word]++;
					break;
				}
		}
								
		$lines = sizeof($logArr);
		//$logPrint = implode('<br />',array_reverse($logArr));
		$logPrint = implode('<br />',$logArr);
		
		if($logPrint == '')
			$logPrint = '<i>Log is empty</i>';
		
		echo '<div class="netblog-area" style="">';
			echo "<div style=\"padding: 5px 10px; max-height: 600px; overflow: auto\" id=\"netblog-logging-print\">$logPrint</div>";
			echo "<div class=\"nbarea-statusbar\">";
				echo '<div style="float:right">'.$formatterCounts['error:'].' Errors, '.$formatterCounts['warning:'].' Warnings, '.$formatterCounts['success:'].' Success</div>';
				echo '<div>Log Size: '.Netblog::formatBytes( Netblog::getLogSize(), 3).', '.$lines.' Lines</div>';
			echo "</div>";
		echo '</div>';
		
		?>
		<script type="text/javascript">
		<!-- 
		var objDiv = document.getElementById("netblog-logging-print");
		objDiv.scrollTop = objDiv.scrollHeight;
nbpop_load('bibtex');		
		//-->
		</script>
		<?php 
		echo '<h3>Maintenance</h3>';
		echo '<a href="?'.http_build_query( array_merge($_GET,array('nblogging_action'=>'clear'))).'">Clear Log</a>';
		
		echo '</div>';
	}	
}

?>
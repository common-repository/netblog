<?php
require_once 'nbCaption.php';

/**
 * Class for managing the user interface in Netblog panel's Captions
 * 
 * @todo Add a caption filter to list only those captions of a given type.
 * @todo Add an integrated editing functionality to fastly change caption numbers or caption text, without reloading the page (ajax)
 * 
 */
class nbCaptionGUI { 
	static function display() {
		echo '<div class="wrap">
			<div id="icon-options-general" class="icon32"><br /></div>';		
		echo '<h2>Captions (DEPRECIATED)</h2><br />';
		echo '<p><strong>Important</strong>: This feature (caption) along with caption shortcodes will be removed in future versions. Please remove all `nbcaption` shortcodes in your posts.</p>';
		
		$cpts = nbCaption::LoadAll();
		$lastHost = 0;
		$bdHosts = array();
		$cpTypes = array();
		
		if(!cpts || !is_array($cpts)) {
			require_once 'infobox.php';
			$box = new infobox('You don\'t have any captions, yet.');
			$box->Display();
			return;
		}
		
		$colors = self::computeRGBs(sizeof($cpts)*1, 10, 20, 40);
		
		//echo '<pre>';
		//var_dump($colors);
		//echo '</pre>';
		
		foreach($colors as $color) {
			//echo '<div style="border-left: 5px solid rgb('.implode(',',$color).')">&nbsp;</div>';
		}
		
		foreach($cpts as $cpt) {
			$d = '';
			
			$title = strlen($cpt->GetTitle())>0 ? $cpt->GetTitle() : '<span style="color:gray; font-style:italic">No Title</span>';
			
			$color = array();
			if(!isset($cpTypes[$name=$cpt->GetType()->GetName()])) {
				$color = array_pop($colors);
				$color = array_pop($colors);
				$cpTypes[$name] = array('type'=>$cpt->GetType(), 'color'=>$color);
			} else
				$color = $cpTypes[$name]['color'];
			
			$colorStr = !empty($color) ? implode(',',$color) : '';
			$d = "<div style=\"border-left: 0px solid rgb($colorStr); padding-left: 5px\" onmouseover=\"nbCaptionGUIStatus('Caption Type: ".ucfirst($name)."')\" onmouseout=\"nbCaptionGUIStatus('')\">";
			$d .= $cpt->GetNumber()." $title</div>";
			
			
			//$d .= '</div>';
			
			$bdHosts[$cpt->GetHost()] .= $d;
		}
		
		$body = '';
		foreach($bdHosts as $id=>$h) {
			$body .= "<div style=\"font-weight:bold\">".get_the_title($id)."</div>";
			$body .= "<div style=\"margin: 0 0 10px 15px; padding-left:15px; border-left:2px solid #DDD\">$h</div>";
		}
		
		echo '<div class="netblog-area" style="cursor:default">';
			echo "<div style=\"padding: 5px 10px; max-height: 600px; overflow: auto\" id=\"netblog-logging-print\">$body</div>";
			echo "<div class=\"nbarea-statusbar\">";
				echo '<div style="float:right" id="nbCaptionGUIStatus"></div>';
				echo '<div>'.sizeof($cpts).' Captions</div>';
			echo "</div>";
		echo '</div>';
		
		echo '<h3>Legend</h3>';
		foreach($cpTypes as $tp) {
			$colorStr = !empty($color) ? implode(',',$color) : '';
			echo '<div style="float: left; border-left: 5px solid rgb('.$colorStr.'); padding: 0 5px; margin: 10px 20px;">';
			echo '<b>'.ucfirst($tp['type']->GetName()) .'</b><br />'. $tp['type']->GetNumberFormatNicename() .', '.$tp['type']->GetDisplayFormatNicename().'<br />';
			echo htmlspecialchars($tp['type']->GetPrintFormat());
			echo '</div>';
		}
		
		
		?>
		<script type="text/javascript">
		<!--	
		function nbCaptionGUIStatus( string ) {
			document.getElementById('nbCaptionGUIStatus').innerHTML = string;
		}
		//-->
		</script>
		<?php 
		
		echo '</div>';
	}
	
	static function randColorComp( $colorArray, $deltaColor = 1) {
		
	}
	
	static function randRGB() {
		return array('r'=>rand(0,255),'g'=>rand(0,255),'b'=>rand(0,255));
	}
	
	static function computeRGBs( $count, $deltaRed = 10, $deltaGreen = 10, $deltaBlue = 10, $offsetRed = 0, $offsetGreen = 0, $offsetBlue = 0 ) {
		$c = array(); 
		$r = $offsetRed; $g = $offsetGreen; $b = $offsetBlue;
		for($i=0; $i<$count; $i++) {
			$c[] = array('r'=>$r%255, 'g'=>($g*1)%255, 'b'=>($b*1)%255 );
			$r+=$deltaRed; $g+=$deltaGreen; $b+=$deltaBlue;
		}
		return $c;
	}
}
?>
<?php
require_once 'nbcs.php';

class NbBibStylesGUI {
	
	public static function display() {
		$a = $_GET['action'];
		
		if ($a == 'delete') {
			if (nbcs::deleteStyle($_GET['style']))
				echo '<div class="nb-error"><p>Style '.$_GET['style'].' has been removed</p></div>';
			else echo '<div class="nb-error"><p>Failed to remove style '.$_GET['style'].'</p></div>';
		}
		
		if ($a == 'new')
			self::addStyle();
		else if ($a == 'edit') 
			self::editStyle();
		else 
			self::listStyles();
	}
	
	public static function listStyles() {
		echo '<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>';
		netblog_feedback_smilies();
		echo '<h2>My Professional Bibliography Styles <a href="?'.http_build_query(array_merge($_GET,array('action'=>'new'))).'" class="add-new-h2">Add New</a></h2><br />';
		
		$mystyles = nbcs::getCustomStyles();
		if (!empty($mystyles)) {
			foreach ($mystyles as $s)
				echo "<strong>$s</strong> | <a href=\"?".http_build_query(array_merge($_GET,array('action'=>'edit','style'=>$s)))."\">Edit</a> ".
						"<a href=\"?".http_build_query(array_merge($_GET,array('action'=>'delete','style'=>$s)))."\">Delete</a><br />";
		} else {
			echo "<h3>You don't have custom styles</h3>";
		}
		
		echo '</div>';		
	}
	
	public static function addStyle() {
						
		$def = isset($_POST['styledef']) ? stripcslashes($_POST['styledef']) : '';
		$name = isset($_POST['stylename']) ? ereg_replace("[^A-Za-z0-9]", "", $_POST['stylename']) : '';		
		if (isset($_POST['basestyle']))
			$def = file_get_contents(dirname(__FILE__).'/nbcs_'.$_POST['basestyle'].'.php');
		
		if (isset($_POST['create'])) {
			if (nbcs::isValidStylename($name)) {
				if (!empty($def)) {
					nbcs::createStyle($name, $def);
					echo '<div class="nb-error"><p>Style created</p></div>';
				} else echo '<div class="nb-error"><p>No style definition given</p></div>';
			} else echo '<div class="nb-error"><p>No valid style name given</p></div>';			
		}
		
		echo '<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>';
		netblog_feedback_smilies();
		echo '<h2>New Bibliography Style</h2><br />';
		
		$styles = nbcs::getDefaultStyles();
		$mystyles = nbcs::getCustomStyles();
		
		echo '<form method="post">';
		echo 'Installed Bibliography Styles: <select name="basestyle">';
		foreach (array_merge($styles, $mystyles) as $s)
			echo '<option value="'.$s.'">'.strtoupper($s).'</option>';
		echo '</select>'.
			'<input type="submit" value="Use as Base Template" class="button-primary" />'.
			'</form>';
		
		echo '<hr />';
		echo '<form method="post">';
		echo '<h3>Unique Style Name</h3><input type="text" name="stylename" value="'.$name.'" /> (required, only alphanumeric characters allowed)<br />';
		echo '<h3>Style Definition</h3><textarea name="styledef" style="width:100%; height:400px">'.$def.'</textarea><br />';
		echo '<input type="submit" value="Create Style" class="button-primary" name="create" />'.
			'</form><br />';
		
		echo '<h2>How-To</h2>
			<h3>1. Load Base Style</h3>
			<p>Choose an existing style from above and load it. You can choose previously made custom styles too.</p>
			<h3>2. Define a unique name</h3>
			<p>Choose a name in alpha-numeric characters which briefly describes your new style. This name will be used in `nbcite` shortcodes to generate tables of bibliography. E.g. `mystyle`.</p>
			<h3>3. Define a valid class name</h3>
			<p>Look for a line like `class nbcs_apa extends nbcs {` and change it for example into `class nbcs_mystyle extends nbcs {`</p>
			<h3>4. Format Citations</h3>
			<p>Within the function `public function printBiblio( $headline = null, $sections = false )`, the foreach statement loops through all previously made citations. In this loop you
				define how a given citation is formatted. After the statement `extract($atts);` you can define some preprocessors. The `$type` variable reflects the given media type, e.g. 
				webpage, book, booksection etc. Within the `switch($type)` statement, you may use conditions for more complex formatting. Note that by default, the variable `$t` is used 
				to store the temporary result of the formatting process - after the switch statement, this result is stored in an array `$o[$type][$id] = $t;` (this array needs to be 
				defined like this!). At the end of the function printBiblio, always use this 
				statement `'.htmlentities('return $this->bprint( $headline, $o, $sections, \'\', \'\', \'<div style="height:50px"></div>\');').'`.</p>
			<h3>5. Define required attributes</h3>
			<p>With the function `public function reqAtts( $type = null )`, you have to define the required attributes for each valid media type. If $type is null, the method has to return
				an array with MEDIA_TYPE => REQUIRED_ATTRIBUTES. REQUIRED_ATTRIBUTES has to be a comma separated string of required attributes (with the $). In case an attribute is optional,
				postfix the attribute name with `|optional`, e.g. `title,author,pages|optional,year|optional`.</p>
		';
		
		echo '<h2>Valid Media Types ($type)</h2><table>';		
		foreach ($types = nbcstyle::getDftTypes() as $name=>$title)
			echo "<tr><td>$name</td><td>$title</td></tr>";
		echo '</table>';
		
		echo '<h2>Valid Citation Attributes</h2><table>';
		foreach ($types = nbcstyle::getDftAttsNamed() as $name=>$title)
			echo "<tr><td>$$name</td><td>$title</td></tr>";
			echo '</table>';
		
		echo '</div>';
	}
	
	public static function editStyle() {
		$name = $_GET['style'];
		$def = isset($_POST['styledef']) ? stripslashes($_POST['styledef']) : file_get_contents(dirname(__FILE__).'/nbcs_'.$name.'.php');;
		
		if (isset($_POST['update'])) {
			if (!empty($def)) {
				nbcs::createStyle($name, $def);
				echo '<div class="nb-error"><p>Style updated</p></div>';
			} else echo '<div class="nb-error"><p>No style definition given</p></div>';
		}
		
		echo '<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>';
		netblog_feedback_smilies();
		echo '<h2>Edit Style: '.$name.'</h2><br />';
		
		echo '<form method="post">';
		echo '<h3>Style Definition</h3><textarea name="styledef" style="width:100%; height:400px">'.$def.'</textarea><br />';
		echo '<input type="submit" value="Update Style" class="button-primary" name="update" />'.
				'</form><br /><br />';
		
		echo '<h2>How-To</h2>
		<h3>1. Load Base Style</h3>
		<p>Choose an existing style from above and load it. You can choose previously made custom styles too.</p>
		<h3>2. Define a unique name</h3>
		<p>Choose a name in alpha-numeric characters which briefly describes your new style. This name will be used in `nbcite` shortcodes to generate tables of bibliography. E.g. `mystyle`.</p>
		<h3>3. Define a valid class name</h3>
		<p>Look for a line like `class nbcs_apa extends nbcs {` and change it for example into `class nbcs_mystyle extends nbcs {`</p>
		<h3>4. Format Citations</h3>
		<p>Within the function `public function printBiblio( $headline = null, $sections = false )`, the foreach statement loops through all previously made citations. In this loop you
		define how a given citation is formatted. After the statement `extract($atts);` you can define some preprocessors. The `$type` variable reflects the given media type, e.g.
		webpage, book, booksection etc. Within the `switch($type)` statement, you may use conditions for more complex formatting. Note that by default, the variable `$t` is used
		to store the temporary result of the formatting process - after the switch statement, this result is stored in an array `$o[$type][$id] = $t;` (this array needs to be
		defined like this!). At the end of the function printBiblio, always use this
		statement `'.htmlentities('return $this->bprint( $headline, $o, $sections, \'\', \'\', \'<div style="height:50px"></div>\');').'`.</p>
		<h3>5. Define required attributes</h3>
		<p>With the function `public function reqAtts( $type = null )`, you have to define the required attributes for each valid media type. If $type is null, the method has to return
		an array with MEDIA_TYPE => REQUIRED_ATTRIBUTES. REQUIRED_ATTRIBUTES has to be a comma separated string of required attributes (with the $). In case an attribute is optional,
		postfix the attribute name with `|optional`, e.g. `title,author,pages|optional,year|optional`.</p>
		';
		
		echo '<h2>Valid Media Types ($type)</h2><table>';
		foreach ($types = nbcstyle::getDftTypes() as $name=>$title)
			echo "<tr><td>$name</td><td>$title</td></tr>";
			echo '</table>';
		
			echo '<h2>Valid Citation Attributes</h2><table>';
			foreach ($types = nbcstyle::getDftAttsNamed() as $name=>$title)
			echo "<tr><td>$$name</td><td>$title</td></tr>";
			echo '</table>';
		
		echo '</div>';
	}
}
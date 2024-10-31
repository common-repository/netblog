<?php

class nbMetaboxRefmaker {

	/**
	 * Register this metabox reference maker to appear on post and pages (while editing them)
	 */
	public static function Register() {
		if( function_exists('add_meta_box') && Netblog::options()->useWizardRefmaker() ) {
			$types = get_post_types(array('public'=>true));
				foreach ($types as $t)
					add_meta_box('netblog_refmaker', __('Reference Maker','netblog'), 'nbMetaboxRefmaker::Display', $t, 'side');
		}
	}
	
	public static function Display() {		
		?>
		<p>
		<?php _e('Wizard','netblog') ?>: <select onchange="mkbodyS(this)" onkeyup="mkbodyS(this)" id="nb-menu">
			<optgroup label="<?php _e('Create','netblog') ?>">
				<option value="mk-note"><?php _e('Footnote','netblog') ?></option>
				<option value="mk-caption"><?php _e('Caption','netblog') ?></option>
			</optgroup>
			<optgroup label="<?php _e('Cross-reference','netblog') ?>">
				<option value="ref-caption"><?php _e('Caption','netblog') ?></option>
			</optgroup>
			<optgroup label="<?php _e('Tables','netblog') ?>">
				<option value="lst-note"><?php _e('Footnotes','netblog') ?></option>
				<option value="mk-biblio"><?php _e('Bibliography','netblog') ?></option>
			</optgroup>
		</select>
		</p>
		<hr style="border: 1px solid #DDD" />
		<div id="nb-mk-note" style="display:none">
			<p>
				<input type="checkbox" name="print" value="true" onclick="note_toogle('nb-mk-note-content','nb-mk-note-content-input')" id="nb-mk-note-print" /> 
				<label for="nb-mk-note-print"><?php _e('Print Footnotes','netblog') ?></label>
			</p>
			<p id="nb-mk-note-content">
				<label for="nb-mk-note-content-input"><b><?php _e('Your Footnote','netblog') ?></b></label>
				<textarea rows="3" style="width:100%" name="netblog-content" id="nb-mk-note-content-input"></textarea>
			</p>
			<p>
				<label for="nb-mk-note-go"><b><?php _e('Copy &amp; Paste','netblog') ?></b></label>
				<textarea rows="3" style="width:100%" onfocus="mktag('nb-mk-note',this)" name="nbnote" id="nb-mk-note-go"></textarea>
			</p>		
		</div>		
		<?php

		$formats_lut = nbCaptionType::GetNumberLUT();
		$formats = array();
		
		foreach( $formats_lut as $num_format=>$const ) {
			$t = array();
			for($i=1; $i<5; $i++)
				$t[] = nbCaptionNumber::Increment($i,$num_format,0);
			$formats[$num_format] = implode(', ',$t) . '...';
		}
		
		$selectTypes = '';
		if($types=nbCaptionType::LoadAll())
			foreach($types as $type)
				$selectTypes .= '<option value="'.$type->GetName().'">'.ucfirst($type->GetName()).'</option>';
		
		$selectTypesDftRaw = nbCaptionType::GetDefaultCaptionTypes();
		$selectTypesDft = '';
		foreach($selectTypesDftRaw as $n)
			$selectTypesDft .= '<option value="'.$n.'">'.ucfirst($n).'</option>';

		$slctDisplayFormat = '';
		if($display_lut=nbCaptionType::GetDisplayLUT())
			foreach($display_lut as $dis_format=>$const) 
				$slctDisplayFormat .= '<option value="'.$dis_format.'">'.ucfirst($dis_format).'</option>';
				
		?>
		<div id="nb-mk-caption" style="display:none">
			<p>
			<label for="nb-mk-caption-type"><b><?php _e('Type','netblog') ?></b> <small>(<?php _e('required','netblog') ?>) 
					<a href="http://netblog.benjaminsommer.com/captions"><?php _e('Learn more about captions.','netblog') ?></a></small></label><br />
			<select onchange="chooseCaptionType(this.value,'nb-mk-caption-type')">
				<option value=""><?php _e('Choose','netblog'); ?></option>
				<optgroup label="<?php _e('Used caption types','netblog') ?>"><?php echo $selectTypes ?></optgroup>
				<optgroup label="<?php _e('Default caption types','netblog') ?>"><?php echo $selectTypesDft ?></optgroup>
				<optgroup label="<?php _e('Create','netblog') ?>"><option value="new"><?php _e('New Type','netblog') ?></option></optgroup>
				
			</select>
			<input type="text" name="type" style="width:100%; display:none" onblur="clean(this); str2alphanumNW_US(this); input_strip(this,32);"
				 id="nb-mk-caption-type" />
			</p>
			<p>
			<label for="nb-mk-caption-name"><b><?php _e('Name','netblog') ?></b> <small>(<?php _e('unique','netblog') ?>; <?php _e('required','netblog') ?>)</small>
				<span style="color:red" id="nb-mk-caption-name-infobox"><?php _e('Too Short','netblog') ?></span>
				</label><br />
			<input type="text" name="name" style="width:100%" onblur="clean(this); str2alphanumNW_US(this); input_strip(this,125); checkCaptionUniqueness(this.value,'nb-mk-caption-name-infobox')" 
				id="nb-mk-caption-name" />
			</p>
			<p>
			<label for="nb-mk-caption-title"><?php _e('Title','netblog') ?> <small>(<?php _e('optional','netblog') ?>, <?php _e('recommended','netblog') ?>)</small></label><br />
			<input type="text" name="title" style="width:100%" onblur="clean(this);str2alphanum(this);input_strip(this,125);" id="nb-mk-caption-title" />
			</p>		
			<p>
			<label for="nb-mk-caption-format"><?php _e('Format','netblog') ?> <small>(<?php _e('optional','netblog') ?>)</small></label><br /><select name="format" id="nb-mk-caption-format" style="width:100%">
			<?php foreach( $formats as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';  ?>
			</select>
			</p>
			<p>
			<label><?php _e('Display','netblog') ?> <small>(<?php _e('the first of each type per post counts','netblog') ?>)</small></label><br />
			<select style="width:100%" name="display">
				<option value=""><?php _e('Default','netblog') ?></option>
				<?php echo $slctDisplayFormat ?></select>
			</p>
			<p>
			<label for="nb-mk-caption-print"><?php _e('Print','netblog') ?> <small>(<?php printf(__('e.g. %s','netblog'),htmlspecialchars('<strong><type> <number></strong>: <title> [<name>]')) ?>)</small></label><br />
			<input type="text" name="print" style="width:100%" value="" onblur="clean(this)" id="nb-mk-caption-print" />
			</p>		
			<p>
			<label for="nb-mk-caption-go"><b><?php _e('Copy &amp; Paste','netblog') ?></b></label>
			<textarea rows="3" style="width:100%" onfocus="mktag('nb-mk-caption',this)" name="<?php echo Netblog::options()->getCaptionShortcode() ?>" id="nb-mk-caption-go"></textarea>
			</p>		
		</div>	
			
		<?php
		// CITATION
		
		$cstyles = nbcstyle::getStyles();
		$cstylesPrint = $cstyles;
		if( $cstylesPrint == '' ) $cstylesPrint = __('none','netblog');
		
		// MK SELECT FILTER TYPE
		$selectFilterType = '';
		$t = nbcstyle::getDftTypes();
		foreach($t as $k=>$v)
			$selectFilterType .= "<option value=\"$k\">$v</option>";
			
	
		// MK SELECT CUSTOM STYLE
		$optioncStyle = '';
		$t = explode(', ',$cstyles);
		foreach( $t as $v )
			$optioncStyle .= "<option value=\"$v\">$v</option>";
		
		// MK SELECT DEFAULT STYLES
		$optionDftStyle = '';
		$t = nbcstyle::getDftStyles();
		foreach( $t as $k=>$v )
			$optionDftStyle .= "<option value=\"$k\">$v</option>";
		
		?>
		
		<div id="nb-mk-biblio" style="display:none" >
			<?php if(Netblog::options()->getBibAutoprint()) echo '<p class="netblog-mtb-info">Current Setting: Auto append Table of Bibliographies</p>'; ?>
			<p>
			<label><b><?php _e('Citation Style','netblog') ?></b></label><br />
			<select style="width:100%" id="nb-mk-biblio-print" name="print"><optgroup label="Built-in Styles">
				<option value="default"><?php _e('Default','netblog') ?></option><?php echo $optionDftStyle ?></optgroup>
					<optgroup label="<?php _e('Custom Styles','netblog') ?>"><?php echo $optioncStyle ?></optgroup></select>
			</p>
			<p>
			<label for="nb-mk-biblio-headline"><?php _e('Headline','netblog') ?> <small>(<?php _e('default: References','netblog') ?>)</small></label><br />
			<input type="text" name="print_headline" style="width:100%" onblur="clean(this)" id="nb-mk-biblio-headline" />
			</p>		
			
			<p>
			<label for="nb-mk-biblio-go"><b><?php _e('Copy &amp; Paste','netblog') ?></b></label>
			<textarea rows="3" style="width:100%" onfocus="mktag('nb-mk-biblio',this)" name="<?php echo Netblog::options()->getCiteShortcode() ?>" id="nb-mk-biblio-go"></textarea>
			</p>		
		</div>	
		
		
		<div id="nb-lst-note" style="display:none" >
			<p>
			<label for="nb-lst-note-go"><b><?php _e('Copy &amp; Paste','netblog') ?></b></label>
			<textarea rows="3" style="width:100%" id="nb-lst-note-go">[<?php echo Netblog::options()->getNoteShortcode() ?> print="true"]</textarea>
			</p>	
		</div>
		
		
		<?php
		// GET ALL CAPTIONS IN DATABASE
		$captions = nbCaption::LoadAll();
		$captionsGroup = array();
		if(is_array($captions) )
		foreach( $captions as $cpt ) {
			$type = $cpt->GetType();
			$captionsGroup[ $type->GetName() ] .= '<option value="'.$type->GetName().'-------'.$cpt->GetName().'">'.$cpt->GetNumber().' '.$cpt->GetName().'</option>';
		}
		ksort($captionsGroup);
		?>
		<div id="nb-ref-caption" style="display:none" >
			<?php 
			if( sizeof($captions) == 0 ) 
				echo '<p class="netblog-mtb-info">'.__('You dont\'t have any captions to cite.','netblog').'<p>';
			else {
			?>
			<p>
			<label><b>Global Captions</b></label><br />
			<select onchange="mkrefcaption( this.value, '-------','nb-ref-caption-type','nb-ref-caption-ref')" id="nb-ref-caption-select">
				<?php
				foreach( $captionsGroup as $type=>$ct ) {
					echo '<optgroup label="'.ucfirst($type).'">'.$ct.'</optgroup>';
				}
				?>
			</select>
			</p>
			
			<input type="hidden" name="type" value="" id="nb-ref-caption-type" />
			<input type="hidden" name="ref" value="" id="nb-ref-caption-ref" />
			
			<p>
			<label for="nb-ref-caption-go"><b><?php _e('Copy &amp; Paste','netblog') ?></b></label>
			<textarea rows="3" style="width:100%" onfocus="mktag('nb-ref-caption',this)" name="<?php echo Netblog::options()->getCaptionShortcode() ?>" id="nb-ref-caption-go"></textarea>
			</p>	
			<?php } ?>		
		</div>
		
		
		<div class="netblog-clear"></div>
	
		
		<script type="text/javascript">
		<!--
		
		/* AJAX HANDLER */
			var delim_main = '<?php echo Netblog::options()->getAjaxDelimiterMain() ?>';
			var delim_sub = '<?php echo Netblog::options()->getAjaxDelimiterSub() ?>';
			var onload_opacity = 50;
		
		var bodyLast = null;

		function mkbodyS( menuobj )
		{
			var body = document.getElementById( 'nb-' + menuobj.value );
			if( body == null ) return;
			
			if( bodyLast != null ) bodyLast.style.display = 'none';
			body.style.display = 'block';
			bodyLast = body;
		}
	
	
		function mktagByid( parseid, print2id ) {
			mktag( parseid, document.getElementById(print2id) );
		}
		function mktag( formid, print2obj ) {
			var formobj = jQuery('#'+formid);
			var outobj = jQuery(print2obj);
			var tagname = outobj.attr('name');	
			var tag = '[' + tagname + ' ';
			var content = '';
			
			jQuery('#'+formid+' input[type="text"], '+
				   '#'+formid+' input[type="radio"]:checked, '+
				   '#'+formid+' input[type="checkbox"]:checked, '+
				   '#'+formid+' select, #'+formid+' textarea').each(function() {
				if (jQuery(this).attr('name') && jQuery(this).attr('name') != tagname && jQuery(this).val().length > 0) {
					var attr = jQuery(this).attr('name').replace('netblog-','');
					if (attr == 'content')
						content = jQuery(this).val();
					else
						tag += attr  + '="' + jQuery(this).val() + '" ';
				}
			});		
			
			if( content.length > 0 )
				tag += ']' + content + '[/' + tagname + ']';
			else
				tag += '/]';
					
			outobj.text(tag);
			print2obj.select();
		}
		
		
		function note_toogle( boxid, inputid )
		{
			var box = document.getElementById(boxid);
			if( box.style.display == 'block' || box.style.display == '' ) {
				box.style.display = 'none';
				document.getElementById(inputid).value = '';
			} else box.style.display = 'block';
			
		}
		function mkrefcaption( val, sep, typeid, refid )
		{
			var t = val.split(sep);
			document.getElementById(typeid).value = t[0];
			document.getElementById(refid).value = t[1];		
		}
		
		function clean(obj)
		{
			if( obj.value != 'undefined' && obj.value.length > 0 )
			{
				obj.value = obj.value.replace("\"", '' );
				obj.value = obj.value.trim();
			}
		}
		
		function chooseCaptionType(name,inputTextId)
		{
			var o = document.getElementById(inputTextId);
			if( o == null || name.Length == 0 ) return;
			
			if( name == "new" ) {
				o.style.display = 'block';
				o.value = '';
			} else {
				o.value = name;
				o.style.display = 'none';
			}
		}
	
		
		function nb_cite_reveal( id, fromobj )
		{
			var obj = document.getElementById(id);
			if( obj == null ) return;
			
			var d = obj.style.display;
			if( d == 'none' || d == '' ) {			
				showPlus(id,0,1000,true,true);
				fromobj.innerHTML = 'Less';
			} else {
				hidePlus(id,0,500,true,false);
				fromobj.innerHTML = 'More';
			}
		}
		
		var captionNameUniquenessLast = '';
		
		function checkCaptionUniqueness( name, noteBoxId ) {
			if( captionNameUniquenessLast == name ) return;
			captionNameUniquenessLast = name;
			
			var o = document.getElementById(noteBoxId);
			if( o == null ) return;
			
			if( name.length == 0 ) { o.innerHTML = "<?php _e('Too short','netblog') ?>"; return; }
			
			o.innerHTML = '<?php _e('checking...','netblog') ?>';
			
			jQuery(document).ready(function($) {
				var data = {
					action: 'netblogfig_exists',
					caption_name: name
				};
				jQuery.post(ajaxurl, data, function(response) {
					if( response == "TRUE" )
						o.innerHTML = '';
					else o.innerHTML = "<?php _e('Not available!','netblog') ?>";
				});
			});			
			
		}
		
	
		function getResourceTypes( name_citationstyle ) {
			var slct = document.getElementById('mtbrm_rsctypes');
			var fields_default = document.getElementById('nb-mk-cite-attsdft');
			var fields_optional = document.getElementById('nb-mk-cite-attsadv');
			
			if( onload_opacity < 100 && onload_opacity > 0 ) {
				setOpacity('mtbrm_rowtypes', onload_opacity);
				setOpacity('mtbrm_rowatts', onload_opacity);
			}
			
			
			fields_default.innerHTML = '';
			fields_optional.innerHTML = '';
			slct.innerHTML = '';
			
			jQuery(document).ready(function($) {
				var data = {
					action: 'get_citation_rsctypes',
					citationstyle: name_citationstyle
				};
				jQuery.post(ajaxurl, data, function(response) {
					var ar = interpretStr( response, delim_main, delim_sub );				
					var fldReq = ' ', fldOpt = ' ';
	
					var i;
					for( i=0; i<ar.length; i++ )
						if( ar[i].length > 1 ) {
							var name = ar[i][0];
							var nicename = ar[i][1];
							var required = ar[i][2];
							if( required == '1' ) 
								fldReq += '<option value="'+name+'">'+nicename+'</option>';
							else fldOpt += '<option value="'+name+'">'+nicename+'</option>';
						}
					var t = '<optgroup label="<?php echo __('Available Resource Types','netblog') ?>">'+fldReq
									+'</optgroup><optgroup label="<?php echo __('Optional Resource Types','netblog') ?>">'+fldOpt+'</optgroup>';
					t = '<select style="width:100%" name="type" ' +
				 		'onchange="getCitStyleAtts(document.getElementById(\'mtbrm_rscstyles\').value, this.value)" >'+t+'</select>';
					slct.innerHTML = t;
							
					if( onload_opacity < 100 )
						setOpacity('mtbrm_rowtypes', 100);

					var rscstyles = document.getElementById('mtbrm_rscstyles');
					var rsctypes = document.getElementById('mtbrm_rsctypes');
					if(rscstyles && rsctypes)
						getCitStyleAtts(rscstyles.value, rsctypes.value);			
				});
			});			
		}
		
		
		
		function getCitStyleAtts( name_style, name_type ) {
			var fields_default = document.getElementById('nb-mk-cite-attsdft');
			var fields_optional = document.getElementById('nb-mk-cite-attsadv');
			
			if( onload_opacity < 100 && onload_opacity > 0 )
				setOpacity('mtbrm_rowatts', onload_opacity);
			
			save_fields('nb-mk-cite');
			
			jQuery(document).ready(function($) {
				var data = {
					action: 'get_citation_fields',
					citationstyle: name_style,
					citationtype: name_type
				};
				jQuery.post(ajaxurl, data, function(response) {
					var ar = interpretStr( response, delim_main, delim_sub );
					var fldReq = '', fldOpt = '';
					var i;
					for( i=0; i<ar.length; i++ )
						if( ar[i].length > 1 ) {
							var name = ar[i][0];
							var nicename = ar[i][1];
							var info = ar[i][2];
							var required = ar[i][3];
							var covered = ar[i][4];
					
							if( name.length == 0 ) continue;
					
							if( info.length > 0 ) info = ' <small>(' + info + ')</small>';
							if( required == '1' ) nicename = '<b>' + nicename + '</b>';
							if( covered == '0' ) { info = '<i>'+info+'</i>'; nicename = '<i>'+nicename+'</i>'; }
							
							var f = '<p><label for="nb-mk-cite-'+name+'">' + nicename + info + '</label><br />' +
									'<input type="text" name="'+name+'" value="" style="width:100%" onblur="clean(this)" ' +
									'id="nb-mk-cite-'+name+'" /></p>';
								
							if( covered == '1' ) 
								fldReq += f;
							else fldOpt += f;
						}
						
					fields_default.innerHTML = fldReq;
					fields_optional.innerHTML = fldOpt;	
					load_fields('nb-mk-cite');
					
					if( onload_opacity < 100 )
						setOpacity('mtbrm_rowatts', 100);
				});
			});			
		}
		
		var fields_saved = [];	
		function save_fields( parseid )
		{
			var obj = document.getElementById(parseid);
			
			var i;
			var input;
			input = obj.getElementsByTagName('input');
			for( i=0; i<input.length; i++ ) {
				if( input.item(i).name == 'content' )
					continue;
				else if( input.item(i).type == 'checkbox' && !input.item(i).checked )
					continue;
				else if( input.item(i).value.length > 0 && input.item(i).name.length > 0 )
					fields_saved.push( {'name':input.item(i).name, 'value':input.item(i).value} );
			}
		}
		
		function load_fields( parseid )
		{
			var obj = document.getElementById(parseid);
			
			var i;
			var input;
			input = obj.getElementsByTagName('input');
			for( i=0; i<input.length; i++ ) {
				if( input.item(i).name == 'content' )
					continue;
				else if( input.item(i).type == 'checkbox' && !input.item(i).checked )
					continue;
				else if( input.item(i).name.length > 0 ) {
					var ii;
					for( ii=0; ii<fields_saved.length; ii++ ) {
						if( fields_saved[ii].name == input.item(i).name ) {
							input.item(i).value = fields_saved[ii].value; 
							break;
						}
					}		
				}			
			}
		}

		var rscstyles = document.getElementById('mtbrm_rscstyles');
		if(rscstyles)
			getResourceTypes(rscstyles.value);		
				
		if( document.getElementById('nb-ref-caption-select') != null )
		mkrefcaption( document.getElementById('nb-ref-caption-select').value, '-------','nb-ref-caption-type','nb-ref-caption-ref');
		
		if( document.getElementById('nb-menu') != null )
			mkbodyS( document.getElementById('nb-menu') );
		
		//-->
		</script>
		
		
		<?php
	}
}
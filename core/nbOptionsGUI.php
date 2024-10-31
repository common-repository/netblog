<?php

class nbOptionsGUI {
	
	private static function mkclasstab($tab) {
		return isset($_GET['tab'])&&$_GET['tab']==$tab ? 'nav-tab-active' : '';
	}
	
	private static function getTab() {
		return isset($_GET['tab'])?$_GET['tab']:$_GET['tab']='general';
	}
	
	public static function printWnd() {
		$tab = self::getTab();
		$formdata = http_build_query($_GET);
		
		echo '<div class="wrap">
			<div id="icon-options-general" class="icon32"><br /></div>';
		netblog_feedback_smilies();
		echo '<h2>Netblog Settings</h2><br />';		
		
		$tabs['general']['name'] = 'General';
		$tabs['links']['name'] = 'Links';
		$tabs['bibliography']['name'] = 'Bibliography';
		//$tabs['search']['name'] = 'Search';
		//$tabs['export']['name'] = 'Export/Import';
		$tabs['advanced']['name'] = 'Advanced';
			
		self::readpost();
		$opt = self::mkContent();
		?>
				
		<!-- Netblog Settings Menu -->
		<h3 class="nav-tab-wrapper">
			<?php
				foreach($tabs as $k=>$o) 
					echo '<a href="?'.http_build_query(array_merge($_GET,array('tab'=>$k))).'" class="nav-tab '.self::mkclasstab($k).'">'.$o['name'].'</a>'; 
			?>
		</h3>
		
		<!-- Netblog Settings Content -->
		<form method="post" action="" id="netblog-settings-form">
			<input type="hidden" id="_wpnonce" name="_wpnonce" value="cdcbc8717e" />
			<input type="hidden" name="_wp_http_referer" value="/wordpress/wp-admin/network/site-info.php?id=1" />
			<input type="hidden" name="id" value="1" />
			
			<table class="form-table">	
			<?php 
				foreach($opt[$tab] as $kh=>$h) {
					echo '<tr><th><h3 style="margin:0px;">'.$h['title'].'</h3></th><td>'.$h['content'].'</td></tr>';
					foreach($h as $k=>$e) {
						if(!is_numeric($k)) continue;
						echo '<tr><th scope="row" style="text-align:right">'.$e['title'].'</th><td>'.$e['content'].'</td></tr>';
					}
				}
			?>
			<!-- 		
				<tr class="form-field form-required">
					<th scope="row">Domain</th>
					<td><code>http://localhost</code></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row">Path</th>
					<td><code>/wordpress/</code></td>
				</tr>
				<tr class="form-field">
					<th scope="row">Registered</th>
					<td><input name="blog[registered]" type="text" id="blog_registered" value="2011-09-06 08:58:18" /></td>
				</tr>
				<tr class="form-fields">
					<th scope="row">Last Updated</th>
					<td><input name="blog[last_updated]" type="text" id="blog_last_updated" value="2011-11-10 10:21:53" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row">Attributes</th>
					<td>
						<label><input type="checkbox" name="blog[public]" value="1"  checked='checked' /> Public</label><br/>
						<label><input type="checkbox" name="blog[mature]" value="1"  /> Mature</label><br/>
					</td>
				</tr> -->
			</table>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"  /></p>
		</form>
		
		
		<script>
		jQuery(document).ready(function() {			
			jQuery('input[type="checkbox"]').each(function() {
				var name = jQuery(this).attr('name');
				jQuery(this).attr('name',"checkbox["+name+"]");
				jQuery(this).attr('id',name);
				jQuery('<input type="hidden" name="'+name+'" value="'+jQuery(this).is(':checked')+'" id="'+name+'-hidden" />').insertAfter(this);
				jQuery(this).click(function() {
					jQuery("#"+jQuery(this).attr('id')+"-hidden").val(jQuery(this).is(':checked'));
				});
			});
		});
		</script>
		
		<?php 
		
		echo '</div>';
	}
	
	private static function mkContent() {
		$tab = self::getTab();
		$opt = array();
		
		switch($tab) {
		case 'general':
			$opt['widgets']['title'] = 'Widgets';
			$opt['widgets']['help'] = 'There are two widgets in Netblog, "Further Reading" and "Referenced by". With each one, you can manage how to display the internal and external links of your WordPress article on your public homepage for your visitor. You can put sevaral instances of them in the sidebar, and each may display different links.';
			$opt['widgets'][0]['title'] = 'Further Reading';
			$opt['widgets'][0]['content'] = '<label><input type="checkbox" name="enableWidgetOutnodes" value="true" '.checked(Netblog::options()->useWidgetOutnodes(),true,false).' /> Enable and display links to other posts and websites.</label>';
			$opt['widgets'][1]['title'] = 'Referenced By';
			$opt['widgets'][1]['content'] = '<label><input type="checkbox" name="enableWidgetInnodes" value="true" '.checked(Netblog::options()->useWidgetInnodes(),true,false).' /> Enable and display incoming links from other posts and websites.</label>';		
			$opt['wizards']['title'] = 'Wizards';
			$opt['wizards']['help'] = 'These tools help you to easily and safely work with Netblog features, like creating bibliographic references, or adding external links to the post being edited.';
			$opt['wizards'][0]['title'] = 'Further Reading';
			$opt['wizards'][0]['content'] = '<label><input type="checkbox" name="enableWizardOutnodes" value="true" '.checked(Netblog::options()->useWizardOutnodes(),true,false).' /> Enable and easily link to external resources.</label>';
			$opt['wizards'][1]['title'] = 'Reference Maker';
			$opt['wizards'][1]['content'] = '<label><input type="checkbox" name="enableWizardRefmaker" value="true" '.checked(Netblog::options()->useWizardRefmaker(),true,false).' /> Enable, speed up and simplify creation of references.</label>';		
			$opt['sidebar']['title'] = 'Sidebar';
			$opt['sidebar']['help'] = 'With this additional sidebar positioned below each post/page, Widgets can be placed directly after your articles.<br />Enable this option if you want to display outgoing or incoming links after your posts.<br />Note: Some user reported that this functionality did not work with their installation. Should you experience any problems, you might want to disable it and please do not hesitate to inform the developer.';
			$opt['sidebar']['content'] = '<label><input type="checkbox" name="enableSidebar" value="true" '.checked(Netblog::options()->useSidebar(),true,false).' /> Enable and display Widgets below articles.</label>';
			$opt['gui']['title'] = 'User-Interface';
			$opt['gui'][0]['title'] = 'Responsiveness';
			$opt['gui'][0]['content'] = '';
			$opt['gui'][1]['title'] = 'Autocomplete';
			$opt['gui'][1]['content'] = '';
			
			$nbareaSpeed = array('instant'=>__('Instant','netblog'), 'fast'=>__('Fast','netblog'), 'medium'=>__('Medium','netblog'));
			$_s = Netblog::options()->getGUISpeed(); $t='';
			foreach($nbareaSpeed as $k=>$v) {
				$t.= '<option value="'.$k.'"' .selected($k,$_s,false).'>'.$v.'</option>';
			}
			$opt['gui'][0]['content'] = '<select name="setGUISpeed">'.$t.'</select>';
			
			$_s = Netblog::options()->getGUIAutocompleteMinLen(); $t='';
			for($i = 1; $i<10; $i++) {		
				$t.='<option value="'.$i.'"'.selected($i,$_s,false).'>'.$i.'</option>';
			}
			$opt['gui'][1]['content'] = '<select name="setGUIAutocompleteMinLen">'.$t.'</select> Characters, at least.';
			break;
			
		case 'links':
			$opt['mel']['title'] = 'Manage External Links';
			$opt['mel']['help'] = 'In the rapidly changing WWW, resources like Webpages and PDF-files, just to name a few, may be transfered to different servers, may become out of date. Webpage titles may change, and the names for external links become outdated. When using the widget "Further Reading", WordPress articles can be linked to internal and external resources, thus they should be maintained permanently! <br />Maintain links to external resources in an ever changing WWW. By using MEL, you can list and search for external links from all posts and perform batch operations on them, like editing title and url, lock, trash, restore, permanently delete. Additionally, periodically check their online status to prevent dead links, or dynamically update their title to stay up to date.';
			$opt['mel']['content'] = '<label><input type="checkbox" name="enableMel" value="true" '.checked(Netblog::options()->useMel(),true,false).' /> Enable</label>';
			$opt['mel'][0]['title'] = 'Startup Template';
			$opt['mel'][0]['content'] = '';
			$opt['mel'][1]['title'] = 'Custom Search Templates';
			$opt['mel'][1]['content'] = '<label><input type="checkbox" name="enableMelTpl" value="true" '.checked(Netblog::options()->useMelTpl(),true,false).' /> Enable</label>';

			$opt['blogsearch']['title'] = 'Blogsearch';
			$opt['blogsearch']['help'] = 'Use blogsearch to automatically list incoming links from other blogs. This is used by the widget "Referenced By".<br />In case you accidentially modified these values and cannot restore them properly, see <a href="http://netblog.benjaminsommer.com/modules/nbsearchdefs.txt">Default Search Definitions</a>.';
			$opt['blogsearch'][0]['title'] = 'Provider Name';
			$opt['blogsearch'][0]['content'] = '<input type="text" name="setBlogsearchProviderName" value="'.Netblog::options()->getBlogsearchProviderName().'" class="regular-text" />';
			$opt['blogsearch'][1]['title'] = 'Provider URL';
			$opt['blogsearch'][1]['content'] = '<input type="text" name="setBlogsearchProviderUri" value="'.Netblog::options()->getBlogsearchProviderUri().'" class="regular-text" />';
			$opt['blogsearch'][2]['title'] = 'Service URL';
			$opt['blogsearch'][2]['content'] = '<input type="text" name="setBlogsearchUri" value="'.Netblog::options()->getBlogsearchUri().'" class="regular-text" />';
			$opt['blogsearch'][3]['title'] = 'Maximum Results';
			$opt['blogsearch'][3]['content'] = '<input type="text" name="setBlogsearchMaxResults" value="'.Netblog::options()->getBlogsearchMaxResults().'" class="regular-text" />';
			$opt['blogsearch'][4]['title'] = 'API Key';
			$opt['blogsearch'][4]['content'] = '<input type="text" name="setBlogsearchAPIKey" value="'.Netblog::options()->getBlogsearchAPIKey().'" class="regular-text" />';
			
			$opt['chmod']['title'] = 'Privileges';
			$opt['chmod']['help'] = 'Increase security of your production environment by choosing who can see and edit your links.<br />The autosave option is used by the panel "Links" (also known as MEL), which is the time interval between your last edit and the automatically triggered save operation.';
			$opt['chmod'][0]['title'] = 'Read/Access Privilege';
			$opt['chmod'][0]['content'] = '';
			$opt['chmod'][1]['title'] = 'Edit Privilege';
			$opt['chmod'][1]['content'] = '';
			$opt['chmod'][2]['title'] = 'Auto save after modification';
			$opt['chmod'][2]['content'] = '';
			
			
			$mel_tpl = array( 	'new'=>__('Just Added','netblog'), 
						'popular'=>__('Most Popular','netblog'), 
						'offline'=>__('Offline','netblog'), 
						'trash'=>__('Trashed','netblog') 
			);
			$f = Netblog::options()->getMelTplStartup(); $t='';
			$mel_tpls_user = Netblog::options()->getMelUserTpls();
			if(is_array($mel_tpl)) {
				$t.='<optgroup label="Default Templates">';
				foreach( $mel_tpl as $k=>$nm )
					$t.='<option value="'.$k.'" '.selected($k==$f,true,false).'>'.$nm.'</option>';
				$t.='</optgroup>';
			}
			if(is_array($mel_tpls_user)) {
				$t.='<optgroup label="User Templates">';
				foreach($mel_tpls_user as $name=>$value)
					$t.='<option value="'.$name.'" '.selected($name==$f,true,false).'>'.$name.'</option>';
				$t.='</optgroup>';
			}
			$opt['mel'][0]['content'] = '<select name="setMelTplStartup">'.$t.'</select>';
			
			$roles = array( 	'activate_plugins'=> __('Administrator','netblog'), 
						'delete_pages'=> __('Editor','netblog'), 
						'publish_posts'=> __('Author','netblog'), 
						'edit_posts'=> __('Contributor','netblog'), 
						'read'=> __('Subscriber','netblog')
			);
			$f = Netblog::options()->getMelPread(); $t='';
			foreach($roles as $k=>$v)
				$t.='<option value="'.$k.'" '.selected($k==$f,true,false).'>'.$v.'</option>';
			$opt['chmod'][0]['content'] = '<select name="setMelPread">'.$t.'</select>';
			
			$f = Netblog::options()->getMelPedit(); $t='';
			foreach($roles as $k=>$v)
				$t.='<option value="'.$k.'" '.selected($k==$f,true,false).'>'.$v.'</option>';
			$opt['chmod'][1]['content'] = '<select name="setMelPedit">'.$t.'</select>';
			
			$saveOffset = array( 0=>__('Immediate','netblog'), 
						5=> vsprintf(_n("%d second", "%d seconds", 5, 'netblog'), 5), 
						10=>vsprintf(_n("%d second", "%d seconds", 10, 'netblog'), 10), 
						20=> vsprintf(_n("%d second", "%d seconds", 20, 'netblog'), 20), 
						45=> vsprintf(_n("%d second", "%d seconds", 45, 'netblog'), 45),
						60=> vsprintf(_n("%d second", "%d seconds", 60, 'netblog'), 60),
						120=> vsprintf(_n("%d min", "%d seconds", 120, 'netblog'), 120),
						240=> vsprintf(_n("%d min", "%d seconds", 240, 'netblog'), 240)
			);
			$f = Netblog::options()->getMelSavetime(); $t='';
			foreach($saveOffset as $k=>$v)
				$t.='<option value="'.$k.'" '.selected($k==$f,true,false).'>'.$v.'</option>';
			$opt['chmod'][2]['content'] = '<select name="setMelSavetime">'.$t.'</select>';
			
			break;
			
		case 'bibliography':
			$opt['cites']['title'] = 'Citations';
			$opt['cites']['help'] = 'Netblog comes with a variety of widely used academic citation styles, such as APA, MLA, Chicago, Turabian and Harvard Citation Style. You can switch between these styles '.
									'anytime, meaning that if you have written an inline citation with sufficient attributes, you are free to choose the citation style when printing out the Table of Bibliography. '.
									'Especially in larger Weblogs with a lot of editors, authors and contributers, the administrator can even force all articles to use a specific citation style, no matter what '.
									'each author might choose. This option is necessary to ensure an appropriate and consistent layout within one site. Moreover, it can be switched or modified later, without '.
									'editing each article manually!<br />The built-in citation styles are commonly used for <strong>different scientific disciplines.</strong> They are as follows:'.
									'<ul><li><strong>APA</strong>: psychology, education, and other social sciences.</li>'.					
									'<li><strong>MLA</strong>: literature, arts, and humanities.</li>'.
									'<li><strong>Chicago</strong>: used with all subjects in the "real world" by books, magazines, newspapers, and other non-scholarly publications.</li>'.
									'<li><strong>Turabian</strong>: designed for college students to use with all subjects.</li>'.
									'<li><strong>Harvard</strong>: especially in the physical, natural and social sciences;   widely accepted in academic publications.</li></ul>'.
									'<em>Recommended option</em>: "Use Global Style" enabled<br />'.
									'<a href="http://netblog1.benjaminsommer.com/tutorial/citation_styles.html">More About Citation Styles</a>';
			$opt['cites'][0]['title'] = 'Default Style';
			$opt['cites'][0]['content'] = '';
			$opt['cites'][1]['title'] = 'Use as Global Style';
			$opt['cites'][1]['content'] = '<label><input type="checkbox" name="enableCiteStyleOverride" value="true" '.checked(Netblog::options()->getCiteStyleOverride(),true,false).' /> Enable</label>';
			
			$opt['citesinl']['title'] = 'Inline Citations';
			$opt['citesinl']['help'] = 'Define how inline citations, e.i. citations displayed in your text, should be rendered on your post. First, choose the output format, either a literal or a numbering format. Note that '.
										'the literal format is determined by the chosen citation style and the citation attributes.<br />'.
										'Use the "Custom Output Format" to add extra information or characters to your final output, which is influenced by the option "Output Format". You can write any valid HTML code here, as long as the string "<output>" is found, which will be replaced with the rendered inline citation output - otherwise nothing will be displayed.';
			$opt['citesinl'][0]['title'] = 'Output Format';
			$opt['citesinl'][0]['content'] = '';
			$opt['citesinl'][1]['title'] = 'CSS Formatting';
			$opt['citesinl'][1]['content'] = '<input type="text" name="setCiteFormatStyle" value="'.Netblog::options()->getCiteFormatStyle().'" class="regular-text" />';
			$opt['citesinl'][2]['title'] = 'Custom Output Format';
			$opt['citesinl'][2]['content'] = '<input type="text" name="setCiteFormatCustomOutput" value="'.Netblog::options()->getCiteFormatCustomOutput().'" class="regular-text" />';
			
			$opt['capts']['title'] = 'Captions';
			$opt['capts']['help'] = 'Label your pictures, headlines, equations etc. in your posts to cross-reference them within posts. Captions being found while processing posts can be saved to the database to enable cross-references across posts - during this step caption types are stored in the database as well. Provided the user has enough privileges, caption types like "Table" can be edited (numbering format etc.)';
			$opt['capts'][0]['title'] = 'Global Captions Edit Privilege';
			$opt['capts'][0]['content'] = '';
			$opt['capts'][1]['title'] = 'Use Global Captions';
			$opt['capts'][1]['content'] = '<label><input type="checkbox" name="enableGlobalCaptions" value="true" '.checked(Netblog::options()->useGlobalCaptions(),true,false).' /> Enable</label>';
			
			$opt['notes']['title'] = 'Footnotes';
			$opt['notes']['help'] = 'Notes are most often used as an alternative to long explanatory notes that can be distracting to readers. Most literary style guidelines (including the Modern Language Association and the American Psychological Association) recommend limited use of foot and endnotes. However, publishers often encourage note references in lieu of parenthetical references. Aside from use as a bibliographic element, notes are used for additional information or explanatory notes that might be too digressive for the main text.<br />'.
									'In particular, footnotes are the normal form of citation in historical journals. This is due, firstly, to the fact that the most important references are often to archive sources or interviews which do not readily fit standard formats, and secondly, to the fact that historians expect to be see the exact nature of the evidence which is being used at each stage.<br />'.
									'The footnote numbers directly appear in your text and in the table of footnotes. Numbers are incremented by one step.';
			$opt['notes'][0]['title'] = 'Numbering Style';
			$opt['notes'][0]['content'] = '';	
				
			$opt['notestable']['title'] = 'Table of Footnotes';
			$opt['notestable']['help'] = 'Previously made footnotes in posts are only printed out within the "Table of Footnotes". These options lets you customize this table in a way that suits your theme best. See the wizard "Reference Maker" to learn more about how to create the correct shortcode that displays this table.';
			$opt['notestable']['content'] = '<label><input type="checkbox" name="enableNoteAutoprint" value="true" '.checked(Netblog::options()->useNoteAutoprint(),true,false).' /> Enable and display links to other posts and websites.</label>';
			$opt['notestable'][0]['title'] = 'CSS Formatting';
			$opt['notestable'][0]['content'] = '<input type="text" name="setFootnoteCssFormatting" value="'.Netblog::options()->getFootnoteCssFormatting().'" class="regular-text" />';
			$opt['notestable'][1]['title'] = 'Horizontal Rule';
			$opt['notestable'][1]['content'] = '';	
			
			$opt['bibtable']['title'] = 'Table of Bibliography';
			$opt['bibtable']['help'] = 'Being quite similar to the Table of Footnotes, choose the styles and display options that are best for your theme.<br /><em>Recommended options</em>: "Auto Append" enabled, 3 or less bibliographies per posts.';
			$opt['bibtable']['content'] = 'Bibliographies per article, at most.';
			$opt['bibtable'][0]['title'] = 'Default Headline';
			$opt['bibtable'][0]['content'] = '<input type="text" name="setBibHeadline" value="'.nbcite::getHeadline().'" class="regular-text" />';
			$opt['bibtable'][1]['title'] = 'Default Heading Level';
			$opt['bibtable'][1]['content'] = '';
			$opt['bibtable'][2]['title'] = 'Auto Append';
			$opt['bibtable'][2]['content'] = '<label><input type="checkbox" name="enableBibAutoprint" value="true" '.checked(Netblog::options()->getBibAutoprint(),true,false).' /> Enable and automatically append Table of Bibliography for each article.</label>';
			$opt['bibtable'][3]['title'] = 'CSS Formatting';
			$opt['bibtable'][3]['content'] = '<input type="text" name="setBibCssFormatting" value="'.Netblog::options()->getBibCssFormatting().'" class="regular-text" />';	
			
			$f = Netblog::options()->getCiteStyle(); $t='';
			$styles = nbcstyle::getDftStyles();
			if(is_array($styles)) {
				$t.='<optgroup label="Default Styles">';
				foreach($styles as $k=>$v)
					$t.='<option value="'.$k.'" '.selected($k==$f,true,false).'>'.$v.'</option>';
				$t.='</optgroup>';
			}
			$styles = nbcstyle::getStyles(ARRAY_A);
			if(is_array($styles)) {
				$t.='<optgroup label="Default Styles">';
				foreach($styles as $k=>$v)
					$t.='<option value="'.$k.'" '.selected($k==$f,true,false).'>'.$v.'</option>';
				$t.='</optgroup>';
			}
			$opt['cites'][0]['content'] = '<select name="setCiteStyle">'.$t.'</select>';
			
			$formats = nbCaptionType::GetNumberLUT();
			foreach( $formats as $k=>$v ) {
				$t = array();
				for($i=0;$i<4;$i++)
					$t[$i] = nbcpt::increment($i,$k);
				$formats[$k] = implode(', ',$t) . ', ...';
			}
			$f = Netblog::options()->getCiteFormatOutput(); $t='';
			foreach( $formats as $k=>$v )
				$t.='<option value="'.$k.'" '.selected($k==$f,true,false).'>'.$v.'</option>';
			$opt['citesinl'][0]['content'] = '<select name="setCiteFormatOutput">'.
											'<option value="literal">Literal/Strict Inline Citation</option>'.$t.'</select>';
			
			$roles = array( 'activate_plugins'=>__('Administrator','netblog'), 
						'delete_pages'=>__('Editor','netblog'), 
						'publish_posts'=>__('Author','netblog'), 
						'edit_posts'=>__('Contributor','netblog')
			);
			$f = Netblog::options()->getCaptionPrivGadd(); $t='';
			foreach($roles as $k=>$v)
				$t.='<option value="'.$k.'" '.selected($k==$f,true,false).'>'.$v.'</option>';
			$opt['capts'][0]['content'] = '<select name="setCaptionPrivGadd">'.$t.'</select>';
			
			$f = Netblog::options()->getNoteFormat(); $t='';
			foreach( $formats as $k=>$v )
				$t.='<option value="'.$k.'" '.selected($k==$f,true,false).'>'.$v.'</option>';
			$opt['notes'][0]['content'] = '<select name="setNoteFormat">'.$t.'</select>';
			
			$f = Netblog::options()->getFootnoteHorizontalRule(); $t='';
			$rules = nbnote::GetHorzRulesOpts();
			foreach( $rules as $k=>$v )
				$t.='<option value="'.$k.'" '.selected($k==$f,true,false).'>'.$v.'</option>';
			$opt['notestable'][1]['content'] = '<select name="setFootnoteHorizontalRule">'.$t.'</select>';
			
			$f = nbcite::getBibsPerPost(); $t='';
			for($i=0;$i<=10;$i++)
				$t.='<option value="'.$i.'" '.selected($i==$f,true,false).'>'.$i.'</option>';
			$opt['bibtable']['content'] = '<select name="setBibMaxNum">'.$t.'</select> '.$opt['bibtable']['content'];
			
			$f = Netblog::options()->getBibHeadlineHtmlTag(); $t='';
			for($i=1; $i<9; $i++)
				$t.='<option value="h'.$i.'" '.selected($i==$f,true,false).'>Heading '.$i.'</option>';
			$opt['bibtable'][1]['content'] = '<select name="setBibHeadlineHtmlTag">'.$t.'</select>';
			
			break;
			
		case 'advanced':			
			$opt['security']['title'] = 'Security';
			$opt['security']['help'] = 'Increase security of your production environment by choosing appropriate options. A higher "Privacy Level" means that less information will be transfered to external servers, like the official Netblog Test Pilot and Footprint Server (note that these two are secure premium servers and no private information will be collected or transfered without your consent).<br />';			
			$opt['security'][0]['title'] = 'Privacy Level';
			$opt['security'][0]['content'] = '';
			
			$opt['feedback']['title'] = 'Support Development';
			$opt['feedback']['help'] = 'Netblog is free. So you don\'t have to pay (donations are accepted though).<br />But you are given the opportunity to give something back, because development already consumed a lot of time and resources. In the end, you are getting a much better software.';
			//$opt['feedback'][0]['title'] = 'Integrated Feedbacks';
			//$opt['feedback'][0]['content'] = '<label><input type="checkbox" name="" value="true" '.checked(false,true,false).' /> Enable</label>';
			$opt['feedback'][1]['title'] = 'Collect &amp; Submit Usage Data';
			$opt['feedback'][1]['content'] = '<label><input type="checkbox" name="enableTestPilot" value="true" '.checked(Netblog::options()->useTestPilot(),true,false).' /> Enable</label>';

			$privacy = array('ultra'=>__('Communication Disabled','netblog'),
						'high'=>__('Transfer: version numbers (wordpress, php, mysql, netblog), WordPress language','netblog'),
						'medium'=>__('Transfer: list of installed plugins (name, uri, version), active theme (name, title, version), php extensions, version numbers','netblog'),
						'none'=>__('Transfer: WP site information (URL, name, description), installed plugins, active theme, php extensions, version numbers','netblog'));	
			$f = Netblog::options()->getPrivacyLevel(); $t=''; $t2 = '';
			foreach( $privacy as $k=>$v ) {
				$t.='<option value="'.$k.'" '.selected($k==$f,true,false).'>'.ucfirst($k).'</option>';
				$t2.="<li><em>".ucfirst($k)."</em>: $v</li>";
			}
			$opt['security'][0]['content'] = '<select name="setPrivacyLevel">'.$t.'</select>';
			$opt['security']['help'] .= "<strong>Privacy Levels</strong><ul>$t2</ul>";	
			break;
		}
		
		
		return array($tab=>$opt);
	}
	
	private static function readpost() {
		//var_dump($_POST);
		
		$obj = Netblog::options();
		foreach($_POST as $k=>$v) {
			if($k[0]=='_') continue;
			//echo "<br />calling: nboptions::$k($v) <br />";
			if(@call_user_func(array($obj,$k),$v)!==null);
				unset($_POST[$k]);			
		}
		
		if(isset($_POST['_tasks'])) {
			foreach($_POST['_tasks'] as $k=>$v)
				switch($k) {
					case 'repair_footprints':
						require_once 'infobox.php';
						if( nbdb::footprt_createAll() )
							$box = new infobox("Footprints <strong>repaired</strong>.");
						else $box = new infobox("<strong>Failed</strong> to repair footprints!");
						$box->display();
						break;
				}
				
		}
	}
	
	public static function printHelp($contextual_help, $screen_id, $screen) {
		if ($screen_id == self::$pagehook) {
			$opt = self::mkContent();
			$tab = self::getTab();
			$t='<table><tr><td style="min-width:100px; padding-right:20px"></td></tr>'; $hasHelp = false;
			foreach($opt[$tab] as $kh=>$h) {
				if(isset($h['help'])) {
					if($hasHelp)
						$t.='<tr><td style="height:10px"></td></tr>';
					$hasHelp = true;
				} else continue;
				$t.='<tr><td style="vertical-align:top"><strong>'.$h['title'].'</strong></td><td>'.$h['help'].'</td></tr>';
				foreach($h as $k=>$e) {
					if(!is_numeric($k) || !isset($e['help'])) continue;
					$hasHelp = true;
					$t.='<tr><td scope="row" style="vertical-align:top;">'.$e['title'].'</td><td>'.$e['help'].'</td></tr>';
				}
			}
			$t.='</table>';
			if($hasHelp)
				$contextual_help = $t;
		}
		return $contextual_help;
	}
	
	public static $pagehook = '';	
}
?>
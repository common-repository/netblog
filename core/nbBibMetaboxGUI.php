<?php
require_once 'nbcs.php';
require_once 'nbBibliographyReference.php';

class nbBibMetaboxGUI {
	
	static public function register() {
		$types = get_post_types(array('public'=>true));
		foreach ($types as $t)
			add_meta_box('netblog_bibmtb_gui', 'Bibliography', array(new nbBibMetaboxGUI,'display'), $t, 'advanced','high');
	}
	
	/**
	 * print out the GUI for metabox bibliography
	 *
	 */
	public function display() {
		
		
		
                echo '<table>';
                
                echo '<tr><td></td></tr>';
                
                echo '</table>';
                
		echo '<form id="nbbib-reference-data" autocomplete="off" >';
		echo '<strong>Search Reference Names</strong>';
		echo '<span id="nbbib-search-parent">
			<input type="text" style="width:50%;font-size:14px;padding:5px;" name="nbbib-search" 
                        id="nbbib-search" autocomplete="off"/></span>';
		echo '<hr style="border:1px solid #BBB" />';
                
                echo '<div id="nbbib-cite-main" style="display:none">';
                    $slctStyles = '<select name="'.self::$prefix.'citation_style" onchange="nbGetStyleTypes(this.value)" id="nbbib-citation-style"><optgroup label="Built-in Styles">';
                    $defaultStyle = Netblog::options()->getCiteStyle();
                    $dft = nbcstyle::getDftStyles();
                    foreach($dft as $code=>$nice)
                            $slctStyles .= "<option value=\"$code\" ".(strtolower($code)==strtolower($defaultStyle) || $nice==$defaultStyle ? 'selected="selected"':'').">$nice".(strtolower($code)==strtolower($defaultStyle) || $nice==$defaultStyle ? ' (Default)':'')."</option>";
                    $slctStyles .= "</optgroup>";

                    $cust = nbcstyle::getStyles(ARRAY_N);
                    if(is_array($cust) && sizeof($cust)>0) {
                            $slctStyles .= "<optgroup label=\"Custom Styles\">";
                            foreach($cust as $nm)
                                    $slctStyles .= "<option value=\"$nm\" ".(strtolower($code)==strtolower($defaultStyle) || $nice==$defaultStyle ? 'selected="selected"':'').">$nm".(strtolower($code)==strtolower($defaultStyle) || $nice==$defaultStyle ? ' (Default)':'')."</option>";
                            $slctStyles .= "</optgroup>";
                    }
                    $slctStyles .= '</select>';

                    $slctTypes = '<span id="nbbib-citation-types-parent"></span>';

                    echo '<div style="float:right">'.'<a onclick="nbResetFields()" style="cursor:pointer; font-weight:bold;">Reset Fields</a>'.
                            '</div>';

                    echo '<h4>1. Choose Citation Style '.($slctStyles.$slctTypes).' <span class="nbinput-ajaxloader" id="nbbib-manual-ico-ajaxloader"></span></h4>';

                    echo '<h4>2. Fill in Fields</h4>';

                    echo '<table id="nbbib-reqs"></table>';

                    echo '<div style="margin-left: 10px;" class="nbbib-metabox-fields">';
                    echo '<div style="float:left;width:130px;text-align:right;">Required</div>';
                    echo '<div style="margin-left: 140px;top:-10px;position:relative;" id="nbbib-fields-required"></div>';
                    echo '<div style="height:10px"></div>';
                    echo '<div style="float:left;width:130px;text-align:right;">Optional</div>';                
                    echo '<div style="margin-left: 140px;top:-10px;position:relative;" id="nbbib-fields-optional"></div>';
                    echo '<div style="height:10px"></div>';
                    echo '<div style="float:left;width:130px;text-align:right;">Uncovered<br /><br /><a id="nbbib-field-other-toogle">Show/Hide</a></div>';                
                    echo '<div style="margin-left: 140px;top:-10px;position:relative;display:none" id="nbbib-fields-other"></div>';
                    echo '<div style="float:none;clear:both"></div>';
                    echo '</div>';

                    echo '<h4>3. Define Reference Name ';
                        echo '<span id="nbbib-reference_name-parent"><input type="text" value="" name="nbbib-reference_name" id="nbbib-reference_name" /></span>';
                    echo '</h4>';

                    echo '<h4 style="float:left;width:130px;">4. Advanced Options</h4>';
                    echo '<div id="nbbib-advanced-options" style="padding: 0px 10px 5px 5px; margin-left: 140px;">';
                    echo '<p>Excerpt<br /><textarea name="nbbib-excerpt" id="nbbib-excerpt" style="width:90%;height:70px;"></textarea></p>';
                    echo '<p><input type="checkbox" name="nbbib-hideInlineCitation" id="nbbib-hideinlinecitation" value="true"> <label for="nbbib-hideinlinecitation">Hide Inline Citation</label></p>';
                    echo '</div>';

                    echo '<h4>Preview <a onclick="nbGetPreview()" style="cursor:pointer">Create</a>&nbsp;&nbsp;<span class="nbinput-ajaxloader" id="nbbib-preview-ico-ajaxloader"></span>
                                    </h4>';
                    echo '<p id="nbbib-preview-inline" style="border:1px solid #CCC; border-radius:5px 5px; padding: 5px; background: #FFF"
                                    title="Inline Preview">Inline Preview</p>';
                    echo '<p id="nbbib-preview-list-element" style="border:1px solid #CCC; border-radius:5px 5px; padding: 5px; background: #FFF"
                                    title="Bibliographic List Preview">Bibliographic List Preview</p>';

                    echo '<h4>Generated Code <a onclick="nbSaveReference()" style="cursor:pointer">Save this Reference</a>&nbsp;&nbsp;<span class="nbinput-ajaxloader" id="nbbib-save-ico-ajaxloader"></span></h4>';
                    echo '<textarea style="width:100%;height:25px; overflow:hidden" id="nbbib-shortcode-short" title="Short Format"></textarea>';
                    echo '<textarea style="width:100%;height:50px; overflow:hidden" id="nbbib-shortcode-long" title="Expanded Format (Compatibility Mode)"></textarea>';
                echo '</div>';
		echo '</form>';
		?> 
		<script type="text/javascript">
		<!--		
		var nbAsync = true;
		var fields = new Array();

                
                
                jQuery('#nbbib-field-other-toogle').click(function() {
                    jQuery('#nbbib-fields-other').toggle();
                });

		function nbResetDiv(id,inner) {
			var o = document.getElementById(id);
			if(o)
				o.innerHTML = inner;
		}
		function nbResetInput(id) {
			var o = document.getElementById(id);
			if(o) {
				o.value = '';
				o.focus();
				o.blur();
			}
		}
		
		function nbResetFields() {
			nbResetInput('nbbib-reference_name');
			nbResetInput('nbbib-excerpt');
			nbResetInput('nbbib-shortcode-short');
			nbResetInput('nbbib-shortcode-long');
			nbResetDiv('nbbib-preview-inline','Inline Preview');
			nbResetDiv('nbbib-preview-list-element','Bibliographic List Preview');

			for(var i=0; i<fields.length; i++)
				nbResetInput(fields[i]);				
			
			document.getElementById('nbbib-hideinlinecitation').checked = false;
		}
		
		function nbGetStyleTypes(styleName) {
			var ico = document.getElementById('nbbib-manual-ico-ajaxloader');
			ico.style.visibility = 'visible';
			jQuery(document).ready(function($) {
				var data = {
					action: 'nbbib_get_style_types',
					style_name: styleName
				};
				
				jQuery.ajax({
					type: 'POST',
				  	url: ajaxurl,
				  	data: data,
				  	async: nbAsync,
				  	success: function(xml) {
						if(xml == '0' || xml.trim().length == 0) {
							alert('Service Unavailable');
							return false;
						}
						jQuery(xml).find("status").each(function() {
							var type = jQuery(this).attr('type');
							if(type=='error') {
								alert('Error: ' + jQuery(this).attr('message') );
								return false;
							}				
						});
						var sup = '';
						var opt = '';
						
						jQuery(xml).find("styleType").each(function() {
							var creq = jQuery(this).attr('flag');
							if(creq == 'supported')
								sup += '<option value="'+ jQuery(this).attr('codename') +'">'+ jQuery(this).attr('nicename') +'</option>';
							else if(creq == 'unsupported')
								opt += '<option value="'+ jQuery(this).attr('codename') +'">'+ jQuery(this).attr('nicename') +'</option>';
						});
						if(sup.length > 0 || opt.length>0) {						
							var slct = document.getElementById('nbbib-citation-types-parent');
							slct.innerHTML = '<select name="<?php echo self::$prefix; ?>citation_type" onchange="nbGetBibFields()" id="nbbib-citation-types"><optgroup label="Supported Types">'+ sup +'</optgroup>'+
											'<optgroup label="Other Types">'+ opt +'</optgroup></select>';
							nbGetBibFields();
							ico.style.visibility = 'hidden';
						}												
					}
				});
			});
			
		}	
		
		function nbGetBibFields() {
			document.getElementById('nbbib-manual-ico-ajaxloader').style.visibility = 'visible';
			var typeObj = document.getElementById('nbbib-citation-types');
			if(typeObj==null)
				return;
				
			var style = document.getElementById('nbbib-citation-style').value;
			var type = document.getElementById('nbbib-citation-types').value;
			
			jQuery(document).ready(function($) {
				var data = {
					action: 'nbbib_get_style_fields',
					style_name: style,
					style_type: type
				};
				jQuery.ajax({
					type: 'POST',
				  	url: ajaxurl,
				  	data: data,
				  	async: nbAsync,
				  	success: function(xml) {
						if(xml == '0' || xml.trim().length == 0) {
							alert('Service Unavailable');
							return false;
						}
						jQuery(xml).find("status").each(function() {
							var type = jQuery(this).attr('type');
							if(type=='error') {
								alert('Error: ' + jQuery(this).attr('message') );
								return false;
							}				
						});
						
						var req = document.getElementById('nbbib-fields-required');
						var opt = document.getElementById('nbbib-fields-optional');
						var oth = document.getElementById('nbbib-fields-other');
                                                var tblreqs = document.getElementById('nbbib-reqs');
						req.innerHTML = '';
						opt.innerHTML = '';
						oth.innerHTML = '';
                                                tblreqs.innerHTML = '';
						fields = new Array();
						
						var params = new Array();
						jQuery(xml).find("styleField").each(function() {
							var flag = jQuery(this).attr('flag');
							var id =  'nbbib-field-'+jQuery(this).attr('codename');
							var elem = '<span id="'+id+'-parent">'+
										'<input type="text" name="<?php echo self::$prefix ?>'+ jQuery(this).attr('codename') +'" value="" id="'+id+'" '+
										' title="'+jQuery(this).attr('nicename')+'" autocomplete="off"/></span>';
							
							if(flag == 'required')
								req.innerHTML +=  elem;
							else if(flag == 'optional')
								opt.innerHTML += elem;
							else if(flag == 'unsupported')
								oth.innerHTML += elem;	
							
//                                                        // for testing
//                                                        tblreqs.innerHTML += '<tr><td>'+jQuery(this).attr('nicename')+'</td>'+
//                                                                             '<td>'+elem+'</td></tr>';
                                                        
							params.push( [id,id+'-parent',jQuery(this).attr('nicename'),jQuery(this).attr('codename')] );			
						});
						for(var i=0; i<params.length; i++) {
							setupACBox(params[i][0],params[i][1],params[i][2],params[i][3]);
						}
						document.getElementById('nbbib-manual-ico-ajaxloader').style.visibility = 'hidden';
                                                
                                                
                                                
                                                
                                                jQuery('#nbbib-cite-main').slideDown('slow');
                                                
                                                
					}					
				});
			});			
		}		
		
		function setupACBox(inputID,parentID,defaultName,codeName) {
			fields.push(inputID);			
			var box = new nbAutocomplete();
			box.init(inputID,parentID,defaultName);
			box.name = codeName;
			box.recoverPreviousSession();
			box.registerCBLoader(this,nbACBoxLoader,null);
			var group = new nbAutocompleteGroup();
			group.name = 'default';
			group.title = defaultName;
			box.addGroup(group);
		}
		
		function nbACBoxLoader( boxObj) {
			var nitem = new nbAutocompleteItem();
			var value = boxObj.inputObj.value;
			
			jQuery(document).ready(function($) {
				var data = {
					action: 'nbbib_get_field_autocomplete',
					field_codename: boxObj.name,
					input_value: value 
				};
				jQuery.ajax({
					type: 'POST',
				  	url: ajaxurl,
				  	data: data,
				  	async: nbAsync,
				  	success: function(xml) {	
						if(xml == '0' || xml.trim().length == 0) {
							alert('Service Unavailable');
							boxObj.render();
							return false;
						}
						jQuery(xml).find("status").each(function() {
							var type = jQuery(this).attr('type');
							if(type=='error') {
								alert('Error: ' + jQuery(this).attr('message') );
								boxObj.render();
								return false;
							}				
						});					
						boxObj.clearGroups();
						jQuery(xml).find("Autocomplete").each(function() {									
							jQuery(this).find("Group").each(function() {
								var group = new nbAutocompleteGroup();
								group.name = jQuery(this).attr('name');
								group.value = jQuery(this).attr('name');
								group.title = jQuery(this).attr('title');
								jQuery(this).find("Item").each(function() {
									var item = new nbAutocompleteItem();
									item.value = jQuery(this).attr('fieldValue');
									item.title = jQuery(this).attr('fieldTitle');
									item.id = item.value;
									group.addItem(item);
								});
								if( !boxObj.addGroup(group) )
									alert('Cannot add group "'+group.title+'"');
							});		
						});
						boxObj.render();	
					}				
				});
			});					
		}	
			
		function nbGetPreview() {		
			var str = jQuery("form").serialize();
			var ico = document.getElementById('nbbib-preview-ico-ajaxloader');
			ico.style.visibility = 'visible';
			jQuery(document).ready(function($) {
				var data = {
					action: 'nbbib_get_preview',
					form: str
				};
				jQuery.ajax({
					type: 'POST',
				  	url: ajaxurl,
				  	data: data,
				  	async: nbAsync,
				  	success: function(xml) {
						if(xml == '0' || xml.trim().length == 0) {
							alert('Service Unavailable');
							ico.style.visibility = 'hidden';
							return false;
						}
						jQuery(xml).find("status").each(function() {
							var type = jQuery(this).attr('type');
							if(type=='error') {
								alert('Error: ' + jQuery(this).attr('message') );
								ico.style.visibility = 'hidden';
								return false;
							}				
						});
						jQuery(xml).find("Reference").each(function() {
							jQuery(this).find("Render").each(function() {
								var inline = jQuery(this).attr('inline');
								var prev_inline = document.getElementById('nbbib-preview-inline');
								prev_inline.innerHTML = jQuery(this).attr('inline');
								if(prev_inline.innerHTML == '')
									prev_inline.innerHTML = 'No Inline Citation to be displayed';
								document.getElementById('nbbib-preview-list-element').innerHTML = jQuery(this).attr('listElement');
							});
						});
						ico.style.visibility = 'hidden';
					}			
				});
			});
		}
		
		function nbSaveReference() {
			var str = jQuery("form").serialize();
			var ico = document.getElementById('nbbib-save-ico-ajaxloader');
			ico.style.visibility = 'visible';
			jQuery(document).ready(function($) {
				var data = {
					action: 'nbbib_save_reference',
					form: str
				};
				jQuery.ajax({
					type: 'POST',
				  	url: ajaxurl,
				  	data: data,
				  	async: nbAsync,
				  	success: function(xml) {
						if(xml == '0' || xml.trim().length == 0) {
							alert('Service Unavailable');
							ico.style.visibility = 'hidden';
							return false;
						}
						jQuery(xml).find("status").each(function() {
							var type = jQuery(this).attr('type');
							if(type=='error') {
								alert('Error: ' + jQuery(this).attr('message') );
								ico.style.visibility = 'hidden';
								return false;
							}				
						});
						jQuery(xml).find("Reference").each(function() {
							jQuery(this).find("Shortcode").each(function() {
								document.getElementById('nbbib-shortcode-long').innerHTML = jQuery(this).attr('extendedFormat');
								document.getElementById('nbbib-shortcode-short').innerHTML = jQuery(this).attr('shortFormat');
							});
						});
						ico.style.visibility = 'hidden';
					}				
				});
			});
		}
		
		function nbAutoLoadBibData(item) {
			var ico = document.getElementById('nbbib-search-ico-ajaxloader');
			ico.style.visibility = 'visible';
			var data = {
				action: 'nbbib_load_bibdata',
				item_value: item.value
			};
			jQuery.post(ajaxurl, data, function(xml) {
				if(xml == '0' || xml.trim().length == 0) {
					alert('Service Unavailable');
					ico.style.visibility = 'hidden';
					return false;
				}
				jQuery(xml).find("status").each(function() {
					var type = jQuery(this).attr('type');
					if(type=='error') {
						alert('Error: ' + jQuery(this).attr('message') );
						ico.style.visibility = 'hidden';
						return false;
					}				
				});
				nbAsync = false;

				jQuery(xml).find("Reference").each(function() {			
					document.getElementById('nbbib-citation-style').value = jQuery(this).attr('styleName');
					nbGetStyleTypes(jQuery(this).attr('styleName'));
					document.getElementById('nbbib-citation-types').value = jQuery(this).attr('typeName');
					nbGetBibFields();
					
					var obj = document.getElementById('nbbib-reference_name');
					obj.value = jQuery(this).attr('name');
					obj.focus();
					obj.blur();

					var excerpt = document.getElementById('nbbib-excerpt');
					if(excerpt)
						excerpt.value = jQuery(this).attr('excerptUnformatted');
					var hidecit = document.getElementById('nbbib-hideinlinecitation');
					if(hidecit)
						hidecit.checked = jQuery(this).attr('hideInlineCitation')=='true'?'checked':'';
					
					jQuery(this).find("Item").each(function() {
						var id =  'nbbib-field-'+jQuery(this).attr('fieldName');
						var obj = document.getElementById(id);
						if(obj!=null) {
							obj.value = jQuery(this).attr('fieldValue');
							obj.focus();
							//obj.keyup();
							obj.blur();
						}
					});
					var prev_inline = document.getElementById('nbbib-preview-inline');
					prev_inline.innerHTML = jQuery(this).attr('renderInline');
					if(prev_inline.innerHTML == '')
						prev_inline.innerHTML = 'No Inline Citation to be displayed';
					
					document.getElementById('nbbib-preview-list-element').innerHTML = jQuery(this).attr('renderListElement');
					document.getElementById('nbbib-shortcode-short').innerHTML = jQuery(this).attr('renderShortcodeShort');
					document.getElementById('nbbib-shortcode-long').innerHTML = jQuery(this).attr('renderShortcodeExtended');
				});
				nbAsync = true;
				ico.style.visibility = 'hidden';					
			});	
		}
			
		function nbForceSelect(id) {
			var obj = document.getElementById(id);
			if( window.addEventListener ) {
				obj.addEventListener('click', function() {document.getElementById(id).select();},false);
				obj.addEventListener('keyup', function() {document.getElementById(id).select();},false);
				obj.addEventListener('focus', function() {document.getElementById(id).select();},false);
			} else if( window.attachEvent ) { // LISTNER - IE5+
				obj.attachEvent('onclick', function() {document.getElementById(id).select();},false);
				obj.attachEvent('onkeyup', function() {document.getElementById(id).select();},false);
				obj.attachEvent('onfocus', function() {document.getElementById(id).select();},false);
			}
		}

		var box = new nbAutocomplete();
		box.init('nbbib-search','nbbib-search-parent','Enter Your Query');
		box.name = 'bibliographic_search';
		box.recoverPreviousSession();
		box.registerCBLoader(this,nbACBoxLoader,null);
		box.registerCBClickedItem(this,nbAutoLoadBibData,null);

		jQuery(document).ready( function() {
			setupACBox('nbbib-reference_name','nbbib-reference_name-parent','Reference Name','reference_name');
			
			nbForceSelect('nbbib-shortcode-short');
			nbForceSelect('nbbib-shortcode-long');	

			nbGetStyleTypes(document.getElementById('nbbib-citation-style').value);	
		});
		
		//-->
		</script>
		<?php
	}
	
	/**
	 * Get supported and other types by given style name with ajax.
	 *
	 */
	static public function getStyleTypes() {
		$style = $_POST['style_name'];
		
		$xml = new SimpleXMLElement('<nbBibliography/>');
		$xstat = $xml->addChild('status');
		
		if( ($mod=nbcs::loadModule($style))==null ) {
			$xstat->addAttribute('type','error');
			$xstat->addAttribute('message','Cannot Load Bibliographic Module');
			echo $xml->asXML();
			die();
		}
		$xtypes = $xml->addChild('styleTypes');
		
		$typesNamed = nbcstyle::getDftTypes();		
		$types = $mod->reqAtts(null);

		if(is_array($types) )
			foreach($types as $type=>$fields) {
				$xtype = $xtypes->addChild('styleType');
				$xtype->addAttribute('codename',$type);
				$xtype->addAttribute('nicename',isset($typesNamed[$type]) ? $typesNamed[$type] : ucfirst($type));
				$xtype->addAttribute('flag','supported');
				unset($typesNamed[$type]); 
			}
		foreach($typesNamed as $type=>$name) {
				$xtype = $xtypes->addChild('styleType');
				$xtype->addAttribute('codename',$type);
				$xtype->addAttribute('nicename',$name);
				$xtype->addAttribute('flag','unsupported');
		}
		
		echo $xml->asXML();
		die();
	}
	
	/**
	 * Get required and optional style fields with ajax.
	 *
	 */
	static public function getStyleFields() {
		$style = $_POST['style_name'];
		$type = $_POST['style_type'];
		$attsExclude = array('refID','refName');
		
		$xml = new SimpleXMLElement('<nbBibliography/>');
		$xstat = $xml->addChild('status');	
		if( $type==null ) {
			$xstat->addAttribute('type','error');
			$xstat->addAttribute('message','Cannot Load Bibliographic Module: No Citation Type given');
			echo $xml->asXML();
			die();
		}	
		if( ($mod=nbcs::loadModule($style))==null ) {
			$xstat->addAttribute('type','error');
			$xstat->addAttribute('message','Cannot Load Bibliographic Module');
			echo $xml->asXML();
			die();
		}
		
		$atts = $mod->getAttributes($type);
		if( $atts===null ) {
			$xstat->addAttribute('type','error');
			$xstat->addAttribute('message','No Fields found');
			echo $xml->asXML();
			die();
		}
		$xfields = $xml->addChild('styleFields');
		$attsNamed = nbcstyle::getDftAttsNamed();
		
		foreach($atts as $field=>$opts) {
			if( strlen($field) == 0 ) continue;			
			$xfield = $xfields->addChild('styleField');
			$xfield->addAttribute('codename',$field);
			$xfield->addAttribute('nicename',isset($attsNamed[$field]) ? $attsNamed[$field] : ucfirst($field));
			$xfield->addAttribute('flag', (strpos($opts,'-optional')!==false || strpos($opts,'optional')!==false ? 'optional' : 'required') );
			unset($attsNamed[$field]);
		}
		foreach($attsNamed as $field=>$name) {	
			if(array_search($field,$attsExclude)!==false)
				continue;	
			$xfield = $xfields->addChild('styleField');
			$xfield->addAttribute('codename',$field);
			$xfield->addAttribute('nicename',$name);
			$xfield->addAttribute('flag', 'unsupported' );
		}
		
		echo $xml->asXML();
		die();
	}
	
	/**
	 * Get autocomplete suggestions for a bibliographic field 
	 *
	 */
	static public function getFieldAC() {
		$field = $_POST['field_codename'];
		$value = $_POST['input_value'];
		
		if($field=='bibliographic_search')
			return self::nbBibliographicSearch($value);
			
		$xml = new SimpleXMLElement('<nbBibliography/>');
		$xac = $xml->addChild('Autocomplete');
		
		$xgroup = $xac->addChild('Group');
		$xgroup->addAttribute('name','local_database_'.$field);
		$xgroup->addAttribute('title',ucfirst($field).' at Local Database');
		
		switch($field) {
			case 'author': 
							/* like "author1;author2;author surname,author lastname;... */
						   	/* the last author will get autocomplete options */
				$val_ = explode(';',$value);
				$val = $val_[sizeof($val_)-1];
				unset($val_[sizeof($val_)-1]);
				$prefix = implode(';',$val_);
				$prefix .= strlen($prefix)>0 ? ';' : '';
				 
				$oItem = new nbBibliographyItem("%$val%",$field);				
				$items = $oItem->getMatches();
				foreach($items as $item) {
					$xitem = $xgroup->addChild('Item');
					$xitem->addAttribute('fieldID',$item->fieldID);
					$xitem->addAttribute('fieldValue',$prefix.$item->fieldValue);
					$xitem->addAttribute('fieldTitle',ucfirst($item->fieldValue));
					$xitem->addAttribute('usage',$item->usage);
					$xitem->addAttribute('id',$item->getID());
				}
				break;
			case 'reference_name':
				$oRef = new nbBibliographyReference();
				$oRef->name = "%$value%";
				$refs = $oRef->getMatches();
				foreach($refs as $ref) {
					$xitem = $xgroup->addChild('Item');
					$xitem->addAttribute('fieldID',$ref->typeID);
					$xitem->addAttribute('fieldValue',$ref->name);
					$xitem->addAttribute('fieldTitle',$ref->name);
					$xitem->addAttribute('usage',$ref->usage);
					$xitem->addAttribute('id',$ref->getID());
				}
				break;
			default:
				$oItem = new nbBibliographyItem("%$value%",$field);
				$items = $oItem->getMatches();
				foreach($items as $item)
					$item->asXML($xgroup);
		}
		
		echo $xml->asXML();
		die();
	}
	
	/**
	 * Search the bibliographic database for the given query
	 * @param string $query
	 */
	static public function nbBibliographicSearch($query) {
		$xml = new SimpleXMLElement('<nbBibliography/>');
		$xac = $xml->addChild('Autocomplete');
		
		$xgroup = $xac->addChild('Group');
		$xgroup->addAttribute('name','local_database_'.$field);
		$xgroup->addAttribute('title','Local Database');
		
		$oRef = new nbBibliographyReference();
		$oRef->name = "%$query%";
		$refs = $oRef->getMatches();
		foreach($refs as $ref) {
			$xitem = $xgroup->addChild('Item');
			$xitem->addAttribute('fieldID',$ref->typeID);
			$xitem->addAttribute('fieldValue',$ref->name);
			$ref->render();
			$title = strlen($ref->renderResultListElement)>0 ? 
				"<b>$ref->name</b><br />$ref->renderResultListElement" : $ref->name;
			$xitem->addAttribute('fieldTitle',$title);
			$xitem->addAttribute('usage',$ref->usage);
			$xitem->addAttribute('id',$ref->getID());
		}
		
		$xgroup = $xac->addChild('Group');
		$xgroup->addAttribute('name','internet_search_'.$field);
		$xgroup->addAttribute('title','Internet Search');		
		
		echo $xml->asXML();
		die();
	}
	
	/**
	 * Loads a bibliographic data from database or emulates it via retrieving information from the internet
	 *
	 */
	static public function loadBibData() {
		$itemValue = $_POST['item_value'];
		
		$xml = new SimpleXMLElement('<nbBibliography/>');
		$xstat = $xml->addChild('status');
		
		$ref = new nbBibliographyReference();		
		$ref->name = $itemValue;
		if($ref->load()) {
			$ref->asXML($xml);
		} else {
			$xstat->addAttribute('type','error');
			$xstat->addAttribute('message',$ref->hasError() ? $ref->error :'Cannot load bibliographic reference');
			echo $xml->asXML();
			die();
		}
		
		echo $xml->asXML();
		die();
	}
	
	/**
	 * Get the preview of a reference, inline and list style
	 *
	 */
	static public function getPreview() {
		$form = self::filterFormKeys();
		$error = '';
		
		$xml = new SimpleXMLElement('<nbBibliography/>');
		$xstat = $xml->addChild('status');
		
		if(!isset($form['citation_style']))
			$error = 'Missing Citation Style';
		else if(!isset($form['citation_type']))
			$error = 'Missing Citation Resource Type';
			
		if($error!='') {
			$xstat->addAttribute('type','error');
			$xstat->addAttribute('message',$error);
			echo $xml->asXML();
			die();
		}
		
		$form['styleName'] = $form['citation_style'];
		$form['typeName'] = $form['citation_type'];
		$form['printFormatInline'] = $form['print_custom'];
		
		$ref = new nbBibliographyReference();
		$ref->parseArray($form);
		$ref->render();
				
		$xref = $xml->addChild('Reference');
		$xrender = $xref->addChild('Render');
		$xrender->addAttribute('inline',$ref->renderResultInline);
		$xrender->addAttribute('listElement',$ref->renderResultListElement);
		
		echo $xml->asXML();
		die();
	}
	
	/**
	 * Saves/updates a reference to database and generates its short and extended shortcodes
	 *
	 */
	static public function saveReference() {
		$form = self::filterFormKeys();
		$error = '';
		
		$xml = new SimpleXMLElement('<nbBibliography/>');
		$xstat = $xml->addChild('status');
		
		if(!isset($form['citation_style']))
			$error = 'Missing Citation Style';
		else if(!isset($form['citation_type']))
			$error = 'Missing Citation Resource Type';
			
		if($error!='') {
			$xstat->addAttribute('type','error');
			$xstat->addAttribute('message',$error);
			echo $xml->asXML();
			die();
		}
		
		$form['styleName'] = $form['citation_style'];
		$form['typeName'] = $form['citation_type'];
		$form['refName'] = $form['reference_name'];
		$form['printFormatInline'] = isset($form['print_custom']) ? $form['print_custom'] : '';
		
		$ref = new nbBibliographyReference();
		$ref->parseArray($form);
		if(!$ref->save()) {
			$xstat->addAttribute('type','error');
			$xstat->addAttribute('message', $ref->hasError() ? $ref->error : 'Failed to Save Reference to Database');
		} else
			$ref->renderShortcode();
		
		$xref = $xml->addChild('Reference');
		$xcode = $xref->addChild('Shortcode');
		$xcode->addAttribute('shortFormat',$ref->renderResultShortcodeShort);
		$xcode->addAttribute('extendedFormat',$ref->renderResultShortcodeExtended);
		
		echo $xml->asXML();
		die();
	}
	
	/**
	 * Filters those form elemens matching the defined prefix.
	 *
	 * @return array
	 */
	static private function filterFormKeys() {
		parse_str(html_entity_decode($_POST['form']),$post);
		$atts = nbcstyle::getDftAttsNamed();
		$form = array();
		foreach($post as $k=>$v) {
			if(($p=strpos($k,self::$prefix))===0 && 
				(!(isset($atts[$k2=substr($k,strlen(self::$prefix))]) && $atts[$k2]==stripslashes($v))))
			//if(substr($k,0,strlen(self::$prefix)) == self::$prefix)
				$form[$k2] = $v;
		}
		return $form;
	}
	
	private $useIntelliSearch = true;
	static private $prefix = 'nbbib-';
}

add_action('wp_ajax_nbbib_get_style_types', 'nbBibMetaboxGUI::getStyleTypes');
add_action('wp_ajax_nbbib_get_style_fields', 'nbBibMetaboxGUI::getStyleFields');
add_action('wp_ajax_nbbib_get_field_autocomplete', 'nbBibMetaboxGUI::getFieldAC');
add_action('wp_ajax_nbbib_get_preview', 'nbBibMetaboxGUI::getPreview');
add_action('wp_ajax_nbbib_save_reference', 'nbBibMetaboxGUI::saveReference');
add_action('wp_ajax_nbbib_load_bibdata', 'nbBibMetaboxGUI::loadBibData');
?>
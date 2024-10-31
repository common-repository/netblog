<?php

class nbMetaboxFurtherReading {
	/**
	 * Render the metabox 
	 */
	static function Display() {
		global $post;
		$postID = $post->ID;
		$postinf = get_post($postID);
			
		// VERIFY
		echo '<input type="hidden" name="netblog_noncename" id="netblog_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';
		
		// METABOX CONTENT
		?>
		<div class="netblog-container" id="nb-popup" style="display:none"></div>
		
		<div class="netblog-post-container" style="width:100%;">
			       
	        <span id="nbtest-autocomplete-parent2" style="z-index:9999999; width:100%">
	        	<input type="text" id="nbtest-autocomplete2" style="width:95%;font-size:14px;padding:5px;" autocomplete="off"/></span>
	        <div class="helpS"><?php _e('Type a name to search for posts, pages and other resources in the internet.','netblog'); ?></div>
	                	
	        <div class="hr"></div>
	        <div class="lister" id="netblog-post-container-lister"></div>
	        
	        <script language="javascript">
	        	var list = new netblog_lister();
	        
	        /* AUTOCOMPLETE BOX */
				function nbACBoxLoader( boxObj ) {
					jQuery(document).ready(function($) {
						var data = {
							action: 'netblog_furead_getautocomplete',
							query: boxObj.inputObj.value,
							parent_post: nodeID
						};
						boxObj.clearGroupItems();
						jQuery.post(ajaxurl, data, function(xml) {
							jQuery(xml).find("ITEM").each(function() {
								var elem = new nbAutocompleteItem();
								elem.id = jQuery(this).attr('id');
								elem.url = jQuery(this).attr('url');
								elem.type = jQuery(this).attr('resouce_type');
								elem.urltitle = jQuery(this).attr('title');
								if(elem.type == 'intern') {
									elem.post_id = jQuery(this).attr('id');
									elem.post_type = jQuery(this).attr('post_type');
									elem.post_mime_type = jQuery(this).attr('post_mime_type');
									elem.author = jQuery(this).attr('author');
									elem.post_date = jQuery(this).attr('post_date');
									elem.post_status = jQuery(this).attr('post_status');
									elem.title = '<span style="line-height:20px"><b>'+jQuery(this).attr('title')+'</b>&nbsp;<small>By '+jQuery(this).attr('author')+', '+jQuery(this).attr('post_date')+', '+jQuery(this).attr('post_status')+', '+jQuery(this).attr('post_type')+'</small></span>';
									boxObj.getGroupByName('intern').addItem(elem);
								} else {
									elem.title = '<span style="line-height:20px"><b>'+jQuery(this).attr('title')+'</b>&nbsp;<small>'+jQuery(this).attr('url')+'</small></span>';
									boxObj.getGroupByName('extern').addItem(elem);
								}							
							});
							boxObj.render();
						});
					});
				}
				function nbACBoxItemClicked( item ) {
					addAdjNode(item);
				}
	
	        	var box2 = new nbAutocomplete();
	        	box2.init('nbtest-autocomplete2','nbtest-autocomplete-parent2','Search Resources');
	        	
	        	var groupin = new nbAutocompleteGroup();
	        	groupin.name = 'intern';
	        	groupin.title = "Internal Links";
	        	box2.addGroup(groupin);
	        	
	        	var groupex = new nbAutocompleteGroup();
	        	groupex.name = 'extern';
	        	groupex.title = "External Links";
	        	box2.addGroup(groupex);
	
	        	box2.registerCBLoader(this,nbACBoxLoader,null);
	    		box2.registerCBClickedItem(this,nbACBoxItemClicked,null);
		
			/* LISTER */
				if( typeof(netblog_lister) == 'function' ) { 
					list.init('netblog-post-container-lister','list');
					list.setSortImmediate(false);
					list.setAppendMode(2);
					list.setSubmenuMode(2);
					list.setFormatMode(3);
					list.setOnRemoveWaitFeedback(true);
					
					/* CUSTOM ROW FORMATER */
					function formater( elem, remove )
					{
						if( elem.type == 'extern' ) {
							var uriF = elem.permalink;
						
							// REMOVE CHAR / FROM END OF URI - nicer
							if( uriF.substring( uriF.length - 1 ) == '/' || uriF.substring( uriF.length - 1 ) == '\\' )
								uriF = uriF.substring(0, uriF.length - 1 );
													
							// NICE APPEARANCE FOR PROTOCOL										
							var protos = ['http://www.','http://','ftp://','https://'];
							var i;
							protos: for( i=0; i<protos.length; i++ ) {
								var t = uriF.match( new RegExp( protos[i], "i") );
								if( t  != null )
								for( s=0; s<t.length; s++ ) {
									uriF = uriF.replace( t[s], '<em>' + t[s] + '</em>' );
									uriF = uriF.replace( '</em>', '</em><b>' );
									uriF += '</b>';
									break protos;
								}
							}	
												
							//return '<a onclick="nbpopup_load(\''+ elem.title +'\',\''+ elem.permalink +'\')" class="right" ><?php echo _x('visit','url','netblog') ?></a>'+uriF+'&nbsp;&nbsp;<em class="title">'+ elem.title +'</em>';
							return uriF+'&nbsp;&nbsp;<em class="title">'+ elem.title +'</em>';
						} else {
							// ICON
							var iconClass = '';
							switch( elem.type ) {
								case 'post': case 'page': iconClass = 'post'; break;
								case 'application/pdf': case 'pdf': iconClass = 'pdf'; break;
							}
											
							// PRINT						
							return '<div class="iconS '+ iconClass +'"></div><strong>'+ elem.title +'</strong>&nbsp;&nbsp;<em>&mdash; <?php _e('Author','netblog') ?>:'+ elem.author +'</em><br/>'+
								'<dfn> <?php _e('Published on','netblog') ?> '+ elem.date +' <?php _e('as','netblog') ?> '+ elem.type +'</dfn>';
						}
					}	
					function formatterRightMenu( elem ) {
						if( elem.type == 'extern' ) {
							//return '<a onclick="nbpopup_load(\''+ elem.title +'\',\''+ elem.permalink +'\')" class="right" ><?php echo _x('visit','url','netblog') ?></a>';
							return '<a href="'+ elem.permalink +'" target="_blank" class="right" ><?php echo _x('visit','url','netblog') ?></a>';
						} else {
							//return '<a onclick="nbpopup_load(\''+ elem.title +'\',\''+ elem.permalink +'\')" class="right" ><?php echo _x('read','post','netblog') ?></a><a href="'+ elem.editlink +'" class="right" ><?php echo _x('edit', 'post','netblog') ?></a>';
							return '<a href="'+ elem.permalink +'" target="_blank" class="right" ><?php echo _x('read','post','netblog') ?></a><a href="'+ elem.editlink +'" class="right" ><?php echo _x('edit', 'post','netblog') ?></a>';
						}
					}
					list.registerCBFormatElem( this, formater, null, '' );
					list.registerCBFormatRMenu( this, formatterRightMenu, null );
						
				} else 	document.getElementById('netblog-post-container-lister').innerHTML += '<?php _e('Unable to load List Handler!','netblog') ?>';
				
			/* AJAX HANDLER */
				var delim_main = '<?php echo Netblog::options()->getAjaxDelimiterMain(); ?>';
				var delim_sub = '<?php echo Netblog::options()->getAjaxDelimiterSub(); ?>';
				var nodeID = <?php echo $postID ?>;	
				
				function getChildNodes() {
					jQuery(document).ready(function($) {
						var data = {
							action: 'netblog_furead_getchildren',
							nodeID: nodeID
						};
						jQuery.post(ajaxurl, data, function(xml) {
							jQuery(xml).find("POST").each(function() {
								var elem = new netblogListItem();
								elem.id = jQuery(this).attr('id');
								elem.title = jQuery(this).attr('title');
								elem.type = jQuery(this).attr('type');
								elem.permalink = jQuery(this).attr('permalink');
								elem.removable = jQuery(this).attr('removable') == 'true' ? true : false;
								elem.author = jQuery(this).attr('author');
								elem.date = jQuery(this).attr('date');
								elem.status = jQuery(this).attr('status');
								elem.editlink = jQuery(this).attr('editlink');
								list.add(elem);
							});
							jQuery(xml).find("LINK").each(function() {
								var elem = new netblogListItem();
								elem.id = jQuery(this).attr('id');
								elem.title = jQuery(this).attr('title');
								elem.type = jQuery(this).attr('type');
								elem.permalink = jQuery(this).attr('permalink');
								elem.removable = jQuery(this).attr('removable') == 'true' ? true : false;
								list.add(elem);
							});
						});
					});			
				}
				
				var infoboxVisible = false;
					
				function addAdjNode( elem )
				{
					if( elem.type == 'extern' ) {
						jQuery(document).ready(function($) {
							var data = {
								action: 'netblog_furead_addextern',
								parent_post: nodeID,
								uri: elem.url,
								uri_title: elem.urltitle
							};
							jQuery.post(ajaxurl, data, function(xml) {
								if(xml == 'failed') 
									alert('Failed to Add Link');	
								else if(xml.length > 0) {
									jQuery(xml).find("LINK").each(function() {
										var elem = new netblogListItem();
										elem.id = jQuery(this).attr('id');
										elem.title = jQuery(this).attr('title');
										elem.type = jQuery(this).attr('type');
										elem.permalink = jQuery(this).attr('permalink');
										elem.removable = jQuery(this).attr('removable') == 'true' ? true : false;
										list.addBottom( elem );
									});	
								} else {
									alert('Service Unavailable');
								}					
							});
						});
					} else {				
						jQuery(document).ready(function($) {
							var data = {
								action: 'netblog_furead_addintern',
								parent_post: nodeID,
								child_post: elem.post_id
							};						
							jQuery.post(ajaxurl, data, function(xml) {
								if(xml == 'failed') 
									alert('Failed to Add Link');	
								else if(xml.length > 0) {	
									jQuery(xml).find("POST").each(function() {
										var elem = new netblogListItem();
										elem.id = jQuery(this).attr('id');
										elem.title = jQuery(this).attr('title');
										elem.type = jQuery(this).attr('type');
										elem.permalink = jQuery(this).attr('permalink');
										elem.removable = jQuery(this).attr('removable') == 'true' ? true : false;
										elem.author = jQuery(this).attr('author');
										elem.date = jQuery(this).attr('date');
										elem.status = jQuery(this).attr('status');
										elem.editlink = jQuery(this).attr('editlink');
										list.addTop( elem );
									});	
								} else {
									alert('Service Unavailable');
								}										
							});
						});
					}
					
				}
				
				function removeAdjNode( elem )
				{
					var childNode = elem.id;
					var linkType = 'intern';
					if( elem.type == 'extern' ) {
						childNode = elem.permalink;
						linkType = 'extern';
					}
					jQuery(document).ready(function($) {
						var data = {
							action: 'netblog_furead_removelink',
							parent_post: nodeID,
							link_value: childNode,
							link_type: linkType
						};
						jQuery.post(ajaxurl, data, function(r) {
							r = r.trim();						
							if( r == 'removed' ) {
								list.removeOK(true);
								return true;							
							} else if( list.findRowID(childNode) != '' ) {
								alert('<?php _e('Cannot remove link','netblog') ?>');
								list.removeOK(false);
								return false;
							}
						});
					});
				}
				function stringCompare( str1, str2 )
				{
					if( str1 < str2 ) return -1;
					if( str1 > str2 ) return 1;
					return 0;
				}
								
			/* SEND LISTER-REMOVED -> SERVER */
				list.registerCBOnRemoveElem( this, removeAdjNode, '', '' );	
				jQuery(document).ready(function($) {				
					getChildNodes();
				});	
				
			/* POPUP INIT */
				nbpopup_issave(true);
				nbpopup_init('nb-popup');						
			</script>	
		</div>
		<?php		
	}
	
	/**
	 * Register this metabox in wordpress admin post edit page
	 */
	static function Register() {
		if( function_exists('add_meta_box') && Netblog::options()->useWizardOutnodes() ) {
			$types = get_post_types(array('public'=>true));
				foreach ($types as $t)
					add_meta_box('netblog_fureadmtb_gui', 'Further Reading', 'nbMetaboxFurtherReading::Display', $t, 'advanced','high');
		}
	}
	
	/**
	 * Load outgoing resources (internal and external links) for a given wp post for xml transmission
	 */
	static function TransactGetChildren() {
		global $wpdb;
		$parent_post = $_POST['nodeID'];
		
		$xml = new SimpleXMLElement('<mel/>');
		$xitems = $xml->addChild('ITEMS');
		
		if( $chld=nbLinkIntern::LoadByParent($parent_post) )
			foreach($chld as $rel)
				nbPost::AsXMLExtended($rel->GetChildID(), $xitems);
			
		if( $col=nbLinkExternCollection::LoadByParent($parent_post) ) {
			for($i=0; $i<$col->CountLinks(); $i++)
				$col->GetLink($i)->AsXML($xitems);
		}
		
		$pilot = new nbTestPilot();
		$pilot->mtbFurReadItemsLoad($num);
		$pilot->save();
		
		echo $xml->asXML();
		die();
	}
	
	/**
	 * Add an external links to a wp post via xml transmission
	 */
	static function TransactAddExternLink() {
		global $wpdb;
		$parent_post = $_POST['parent_post'];
		$uri = $_POST['uri'];
		$uri_title = $_POST['uri_title'];
		
		$pilot = new nbTestPilot();
		$pilot->mtbFurReadItemAdd();
		$pilot->save();
				
		$xml = new SimpleXMLElement('<mel/>');
		$xitems = $xml->addChild('ITEMS');

		if( ($lk=nbLinkExtern::Create($uri, $uri_title)) && !($rel=nbLinkExternCollection::LoadByRelation($parent_post, $lk->GetID())) ) {
			$rel = nbLinkExternCollection::LoadByParent($parent_post);		 
			$rel->Add($lk);
			$lk->AsXML($xitems);
			die($xml->asXML());
		}
		die('failed');
	}
	
	/**
	 * Add a new internal link to a wp post via xml transmission
	 */
	static function TransactAddInternLink() {
		$parent_post = $_POST['parent_post'];
		$child_post = $_POST['child_post'];
		
		$pilot = new nbTestPilot();
		$pilot->mtbFurReadItemAdd();
		$pilot->save();
		
		$xml = new SimpleXMLElement('<mel/>');
		$xitems = $xml->addChild('ITEMS');	
		
		if( $lk=nbLinkIntern::Create($parent_post, $child_post) ) {
			nbPost::AsXMLExtended($lk->GetChildID(), $xitems);
			die($xml->asXML());
		}
		die('failed');
	}
	
	/**
	 * Remove a link (intern or extern) from a wp post via xml transmission
	 */
	static function TransactRemoveLink() {
		global $wpdb;
		$parent_post = $_POST['parent_post'];
		$link_value = $_POST['link_value'];
		$link_type = $_POST['link_type'];
		
		$pilot = new nbTestPilot();
		$pilot->mtbFurReadItemRemove();
		$pilot->save();
		
		if($link_type=='intern') {
			if( ($lk=nbLinkIntern::Load($parent_post, $link_value)) && ($lk->Remove()) )
				die('removed');
			else die('failed');
		} else if($link_type=='extern') {
			if( ($lk=nbLinkExtern::LoadByUri($link_value)) && ($rel=nbLinkExternCollection::LoadByRelation($parent_post, $lk->GetID()))
					&& $rel->Remove(0) )
				die('removed');
			else die('failed');
		} else die('failed');
	}
	
	/**
	 * Get an autocomplete list for a matching query
	 */
	static function TransactGetAutocompleteItems() {
		$parent_post = $_POST['parent_post'];
		$query_ = addslashes($_POST['query']);
		$maxIntern = 10;
		
		$xml = new SimpleXMLElement('<nbResources/>');
		$xitems = $xml->addChild('ITEMS');
		
		global $wpdb;
		global $post;
		$net = $wpdb->prefix . Netblog::options()->getServerTableNet();
		$num = 0;
		
		//
		// INTERN NET - LOCAL POSTS AND PAGES
		// There is no better way to fetch the database then using direct sql commands
		$queryNoChld = "
			SELECT post_type, post_title, p.ID, post_content, user_nicename, post_date, post_status
		    FROM $wpdb->posts p, $wpdb->users u
	     	WHERE p.post_title like '%$query_%'
	      	AND p.ID != $parent_post
		    AND (p.post_type = 'post' || p.post_type = 'page')
		    AND p.post_status = 'publish'
	      	AND p.post_author = u.ID
		    ORDER BY p.post_date DESC
		    LIMIT $maxIntern;
		";																						//UPDT_1.2 - page
		$queryChld = "
			SELECT post_type, post_title, p.ID, post_content, user_nicename, post_date, post_status
			FROM $wpdb->posts p, $wpdb->users u
			WHERE (p.post_type = 'post' || p.post_type = 'page')
			AND p.post_status = 'publish'
			AND p.post_title LIKE '%$query_%'
			AND p.ID != '$parent_post'
			AND p.post_author = u.ID
			LIMIT $maxIntern;
		";
		
		$query = ($lks=nbLinkIntern::LoadByParent($parent_post)) ? $queryChld : $queryNoChld;

		$numI = 0;
		if( $links=$wpdb->get_results($query) )
			foreach($links as $link) {
				if( nbLinkIntern::Load($parent_post, $link->ID) )
					continue;
				$item = $xitems->addChild('ITEM');
				$item->addAttribute('resouce_type', 'intern');
				$item->addAttribute('post_type', $link->post_type);
				$item->addAttribute('id',  $link->ID);
				$item->addAttribute('url', get_permalink($link->ID));
				$item->addAttribute('title', $link->post_title);
				$item->addAttribute('author', $link->user_nicename);
				$item->addAttribute('post_date', $link->post_date);
				$item->addAttribute('post_status', $link->post_status);
				$item->addAttribute('excerpt', substr( strip_tags($link->post_content), 0, 200).'...');
				$num++;
			}		
		
		//
		// MEDIA - LOCAL ATTACHMENTS
		//
		$query = "	SELECT post_mime_type, post_title, p.ID, post_content, user_nicename, post_date, post_status
					FROM $wpdb->posts p, $wpdb->users u
					WHERE p.post_type = 'attachment'
					AND p.post_title LIKE '%$query_%'
					AND p.post_mime_type LIKE '%pdf%'
					AND p.post_author = u.ID;
		";
		
		if( $links=$wpdb->get_results($query) )
			foreach($links as $link) {
				if( nbLinkIntern::Load($parent_post, $link->ID) )
					continue;
				$item = $xitems->addChild('ITEM');
				$item->addAttribute('resouce_type', 'intern');
				$item->addAttribute('post_type', 'media');
				$item->addAttribute('post_mime_type', $link->post_type);
				$item->addAttribute('id',  $link->ID);
				$item->addAttribute('url', get_permalink($link->ID));
				$item->addAttribute('title', $link->post_type);
				$item->addAttribute('author', $link->user_nicename);
				$item->addAttribute('post_date', $link->post_date);
				$item->addAttribute('post_status', $link->post_status);
				$num++;
			}		
			
		//
		// EXTERN NET
		//
                if(filter_var($query_, FILTER_VALIDATE_URL)!==false)  {
                        require_once 'DataTransfer.php';
                        require_once 'nbUri.php';
                        $data = DataTransfer::RetrieveUrl($query_);
                        if(!DataTransfer::hasError($data)) {
                                $title = nbUri::GrabTitle($data['content']);				
                                $item = $xitems->addChild('ITEM');
                                $item->addAttribute('resouce_type', 'extern');
                                $item->addAttribute('id', $query_);
                                $item->addAttribute('url', $query_);
                                $item->addAttribute('title', !empty($title)?$title:'No Title');
                                $num++;
                        }
                }
		
		$pilot = new nbTestPilot();
		$pilot->mtbFurReadSearch($num,$query_);
		$pilot->save();
		
		die($xml->asXML());	
	}
	
}



add_action('wp_ajax_netblog_furead_getchildren', 'nbMetaboxFurtherReading::TransactGetChildren');
add_action('wp_ajax_netblog_furead_addextern', 'nbMetaboxFurtherReading::TransactAddExternLink');
add_action('wp_ajax_netblog_furead_addintern', 'nbMetaboxFurtherReading::TransactAddInternLink');
add_action('wp_ajax_netblog_furead_removelink', 'nbMetaboxFurtherReading::TransactRemoveLink');
add_action('wp_ajax_netblog_furead_getautocomplete', 'nbMetaboxFurtherReading::TransactGetAutocompleteItems');
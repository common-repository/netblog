<?php

class nbMainGUI {
	
	public static function printWnd() {
		?>
		<div class="wrap about-wrap">
			<?php netblog_feedback_smilies(); ?>
		<h1>Netblog <?php echo Netblog::getClientVersion() ?></h1>
        <div class="about-text">
        	Thank you for updating to the latest version! Netblog <?php echo Netblog::getClientVersion() ?> is already making your website better, 
        	faster, and more attractive, just like you!
        	<div class="nb-error" style="font-size:0.7em; line-height:1.5em; padding: 1em;">Management of Bibliographies and 
        		Footnotes will be removed soon. Please use the plugin <a href="http://academicpress.benjaminsommer.com/">AcademicPress</a> instead, as it supports many more features. In the meantime, I suggest to use it in parallel. Upgrade guides and wizards are coming soon.</div>
        </div>
               
        <h2 class="nav-tab-wrapper" id="netblog-nav">
			<a class="nav-tab nav-tab-active" name="whatsnew" style="cursor:pointer"> What’s New </a>
			<a class="nav-tab" name="latestnews" style="cursor:pointer"> Latest News </a>
			<a class="nav-tab" name="tips" style="cursor:pointer"> Tips & Tricks </a>
			<a class="nav-tab" name="compare" style="cursor:pointer"> Feature Comparison </a>
			<a class="nav-tab" name="forum" style="cursor:pointer"> Forum </a>
			<a class="nav-tab" name="credits" style="cursor:pointer"> Credits </a>
		</h2> 
		<div class="changelog point-releases" style="display:nones" id="whatsnew">
			<h3>Maintenance and Security Release</h3>
			<div id="netblog-whatsnew"><p>Loading...</p></div>
		</div>
		<div class="changelog point-releases" style="display:none" id="latestnews">
			<div id="netblog-news"><p>Loading...</p></div>
		</div>
		<div class="changelog point-releases" style="display:none" id="tips">
			<div id="netblog-tips"><p>Loading...</p></div>
		</div>
		<div class="changelog point-releases" style="display:none" id="compare">
			<p> <small>Netblog 2 will be branched into 2 separate plugins, namely Netblog 3 for managing links and AcademicPress 1 for bibliography and mathematics.<br />
				I believe that transparency and open discussion is an important part of successful software development. The following table can be seen as a
				guideline for added/changed/removed functionality (in general).<br />
				I strongly encourage you to participate in the discussion to get the most out of the plugin. For example, I am probably removing some features, like captions.
				In principle: the more you show your interest and discuss features, the more I am going to spend time for developing a free software.
				</small>
			</p>
			<table width="100%" cellspacing="0" cellpadding="4"><colgroup><col width="85*" /> <col width="85*" /> <col width="85*" /> </colgroup>
				<tbody>
				<tr valign="TOP">
				<td width="33%"><h2>Features</h2></td>
				<td width="33%"><h2>Netblog 2</h2></td>
				<td width="33%"><h2>Netblog 3</h2></td>
				</tr>
				<tr valign="TOP">
				<td width="33%"><strong>External and Internal Links</strong></td>
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Add internal and external links</td>
				<td width="33%">Yes</td>
				<td width="33%">Yes (no distinction anymore)</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Check online availability</td>
				<td width="33%">Yes (from local database only)</td>
				<td width="33%">Yes (by parsing the website)</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Automatically update link title</td>
				<td width="33%">Yes</td>
				<td width="33%">No</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Explicitly define link title</td>
				<td width="33%">No</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Build link hierarchy</td>
				<td width="33%">No</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Embed in WP posts</td>
				<td width="33%">No</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Use separate database tables</td>
				<td width="33%">Yes</td>
				<td width="33%">No</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Included in WP Backups</td>
				<td width="33%">No</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Public free blogsearch</td>
				<td width="33%">Yes</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Websearch</td>
				<td width="33%">No</td>
				<td width="33%">Yes (with private Google API)</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				<td width="33%"><h2>AcademicPress 1</h2></td>
				</tr>
				<tr valign="TOP">
				<td width="33%"><strong>Bibliography</strong></td>
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Captions</td>
				<td width="33%">Yes</td>
				<td width="33%">No</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Footnotes</td>
				<td width="33%">Yes</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Automatic table of footnotes</td>
				<td width="33%">Yes</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Global preferences for footnotes</td>
				<td width="33%">Yes</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Global preferences for citations</td>
				<td width="33%">Yes</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Automatic formatting of citations</td>
				<td width="33%">Yes</td>
				<td width="33%">Yes, Improved</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Easy custom citation styles</td>
				<td width="33%">Partial</td>
				<td width="33%">Not Yet</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Automatic table of references</td>
				<td width="33%">Yes</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Build custom table of references by using advanced query languages</td>
				<td width="33%">No</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Embed code in WP Posts</td>
				<td width="33%">Yes (database still needed)</td>
				<td width="33%">Yes (without additional DB tables)</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">BibTex and EndNote parser</td>
				<td width="33%">No</td>
				<td width="33%">In Development</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Public Bibliography Databases</td>
				<td width="33%">No</td>
				<td width="33%">Not Yet</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				</tr>
				<tr valign="TOP">
				<td width="33%"><strong>Mathematics</strong></td>
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">MathML and Classic Math Rendering</td>
				<td width="33%">No</td>
				<td width="33%">Not Yet</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">LaTex Math Preprocessors</td>
				<td width="33%">No</td>
				<td width="33%">Not Yet</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				</tr>
				<tr valign="TOP">
				<td width="33%"><strong>API and Software Quality</strong><sup>1</sup></td>
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Compliant to Zends PHP Coding Standard</td>
				<td width="33%">No</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Available as WP Plugin</td>
				<td width="33%">Yes</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Available as standalone package</td>
				<td width="33%">No</td>
				<td width="33%">In Development<sup>2</sup></td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Minimal server resource usage</td>
				<td width="33%">Partial</td>
				<td width="33%">Full</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Exposed core functionality to child plugins</td>
				<td width="33%">Partial</td>
				<td width="33%">Full</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Modularized Plugin</td>
				<td width="33%">No</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				</tr>
				<tr valign="TOP">
				<td width="33%"><strong>Security</strong><sup>1</sup></td>
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Access privileges</td>
				<td width="33%">Yes</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Edit privileges</td>
				<td width="33%">Yes</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Per-User privileges</td>
				<td width="33%">No</td>
				<td width="33%">Maybe</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Secure Account Connection (to bibliographic servers, web search engines)</td>
				<td width="33%">No</td>
				<td width="33%">Yes</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				</tr>
				<tr valign="TOP">
				<td width="33%"><strong>User Interface and Wizards</strong></td>
				<td width="33%">&nbsp;</td>
				<td width="33%">&nbsp;</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Internationalization</td>
				<td width="33%">Partial</td>
				<td width="33%">Full<sup>1</sup></td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Integrated Help Contents</td>
				<td width="33%">No</td>
				<td width="33%">Yes<sup>1</sup></td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Further Reading Metabox</td>
				<td width="33%">Yes</td>
				<td width="33%">Merged with Table of Bibliography</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">MEL</td>
				<td width="33%">Yes</td>
				<td width="33%">No</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Reference Maker Metabox</td>
				<td width="33%">Yes</td>
				<td width="33%">No</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Bibliography Metabox</td>
				<td width="33%">Yes</td>
				<td width="33%">No</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Realtime Math Metabox</td>
				<td width="33%">No</td>
				<td width="33%">Not Yet</td>
				</tr>
				<tr valign="TOP">
				<td width="33%">Simulate in Real-Time</td>
				<td width="33%">No</td>
				<td width="33%">Yes, using AcademicPress VirtualBox</td>
				</tr>
				</tbody>
				</table>
				<p>	<small><sup>1</sup> For both Netblog3 and AcademicPress1</small><br />
					<small><sup>2</sup> Standalone package uses jQuery, Json and Server Backend</small></p>
			
		</div>
		<div class="changelog point-releases" style="display:none" id="forum">
			<div>
				<iframe src="http://forum.benjaminsommer.com/" style="width:100%; height:600px"></iframe>
			</div>
		</div>
		<div class="changelog point-releases" style="display:none" id="credits">
			<div id="netblog-credits">
				<p>Build Revision: <?php echo Netblog::$build ?><br />
					Release: <?php echo Netblog::getClientVersion().' ('.Netblog::$buildDate.')' ?><br />
					Version Status: <?php echo Netblog::getVersionStatus(); ?><br />
					Author: <?php echo Netblog::$author ?></p>
				<p>
					<a href="http://netblog.benjaminsommer.com/features.php" target="_blank">All Features</a><br />
					<a href="http://netblog.benjaminsommer.com/tutorial.php" target="_blank">Latest Tutorials</a><br />
					<a href="http://netblog.benjaminsommer.com/documentation/index.html" target="_blank">Documentation</a><br />
					<a href="http://netblog.benjaminsommer.com/download.php" target="_blank">Beta Versions</a><br />
					<a href="http://netblog.benjaminsommer.com/contact.php" target="_blank">Report a Bug</a>
				</p>
			</div>
		</div>
		
		
		<div class="changelog">
			<h3>Recent References</h3>
			<div class="feature-section images-stagger-right">
				<div id="netblog-recentrefs"><p>Loading...</p></div>
			</div>
		</div>  
		
		<div class="changelog">
			<h3>Recent Links</h3>
			<div class="feature-section images-stagger-right">
				<div id="netblog-recentlinks"><p>Loading...</p></div>
			</div>
		</div>
		
		<script>
		netblog_version = '<?php echo Netblog::getClientVersion() ?>';
		dtformat = 'F j, Y';
		jQuery(document).ready(function($) {

			jQuery('#netblog-nav a.nav-tab').each(function() {
				var link = jQuery(this);
				link.click(function() {
					jQuery('div.point-releases').hide();
					jQuery('#netblog-nav a.nav-tab-active').removeClass('nav-tab-active');
					
					link.addClass('nav-tab-active');
					jQuery('#'+link.attr('name')).show();
				});
			});
			
			// latest news
			var data = {
				action: 'netblog_crosssite_forward',
				urlforward: 'http://netblog.benjaminsommer.com/rssnews.php',
				version: netblog_version,
				dateformat: dtformat
			};						
			jQuery.post(ajaxurl, data, function(xml) {
				var changes = document.getElementById('netblog-news');
				var items_max = 5;
				var items_count = 0;
				changes.innerHTML = '';
				var b=0;
				jQuery(xml).find("news").each(function() {
					if(items_count<items_max) {
					var cl = b==0?"alternate":"";
					var date = jQuery(this).attr('dateformat');					
					changes.innerHTML += "<p><strong>"+jQuery(this).find('title').text()+"</strong> <small>"+date+"</small><br />"+jQuery(this).find('text').text()+"</p>";
					b=(b+1)%2;
					items_count++;
					}
				});				
				if(changes.innerHTML.length==0)
					changes.innerHTML = "<p>Cannot retrieve latest news. Sorry for that.</p>";									
			});

			// tips and tricks
			var data = {
				action: 'netblog_crosssite_forward',
				urlforward: 'http://netblog.benjaminsommer.com/rsstips.php',
				version: netblog_version
			};						
			jQuery.post(ajaxurl, data, function(xml) {
				var changes = document.getElementById('netblog-tips');
				var items_max = 5;
				var items_count = 0;
				changes.innerHTML = '';
				var b=0;
				jQuery(xml).find("tips").each(function() {
					if(items_count<items_max) {
					var cl = b==0?"alternate":"";						
					changes.innerHTML += "<p><strong>"+jQuery(this).find('title').text()+"</strong><br />"+jQuery(this).find('text').text()+"</p>";
					b=(b+1)%2;
					items_count++;
					}
				});				
				if(changes.innerHTML.length==0)
					changes.innerHTML = "<p>Cannot retrieve tips and tricks. Sorry for that.</p>";									
			});
			
			// whats new
			var data = {
				action: 'netblog_crosssite_forward',
				urlforward: 'http://netblog.benjaminsommer.com/whatsnew.php',
				version: netblog_version,
				v: netblog_version
			};						
			jQuery.post(ajaxurl, data, function(xml) {
				var changes = document.getElementById('netblog-whatsnew');
				if(xml=='failed'||xml=='0') 
					changes.innerHTML = "<p><em>Cannot retrieve changes. Sorry for that.</em></p>";
				else if(xml.length > 0) {
					jQuery(xml).find("CHANGES").each(function() {
						if(jQuery(this).attr('version')==netblog_version) {
							changes.innerHTML = '';
							var b=0;
							jQuery(this).find("ITEM").each(function() {
								var cl = b==0?"alternate":"";									
								changes.innerHTML += "<p><strong>"+jQuery(this).attr('title')+"</strong><br />"+jQuery(this).attr('desc')+"</p>";
								b=(b+1)%2;
							});
						} else alert("version not found");						
					});	
				}
				if(changes.innerHTML.length==0)
					changes.innerHTML = "<p><em>Cannot retrieve changes. Sorry for that.</em></p>";							
			});

			// get recent links
			var data = {
				action: 'netblog_getlatest_links'
			};						
			jQuery.post(ajaxurl, data, function(xml) {
				var body = document.getElementById('netblog-recentlinks');
				if(xml=='failed'||xml=='0') 
					body.innerHTML = "<p>You haven't created any links, yet.</p><p>To add external or internal links, go to `Edit Post` and add a URL by using the metabox `Further Reading`.<p>";
				else if(xml.length > 0) {	
					body.innerHTML = '';
					var b=0;
					var item_count = 0;
					jQuery(xml).find("LINK").each(function() {
						var cl = b==0?"alternate":"";									
						var id = "netblog-link-"+item_count;
						body.innerHTML += "<p><strong>"+jQuery(this).attr('title')+"</strong> "+
										"<a href=\""+jQuery(this).attr('permalink')+"\" target=\"_blank\">"+jQuery(this).attr('permalink')+"</a>"+
										"<div id=\""+id+"-description\"></div><div id=\""+id+"-keywords\"></div></p>";
						var data = {
							action: 'netblog_link_getmetatags',
							url: jQuery(this).attr('permalink')
						};						
						jQuery.post(ajaxurl, data, function(xml) {
							jQuery('#'+id+'-description').text(jQuery(xml).find('description').text());
							jQuery('#'+id+'-keywords').text(jQuery(xml).find('keywords').text());
						});						
						b=(b+1)%2;	
						item_count++;							
					});	
				}
				if(body.innerHTML.length==0)
					body.innerHTML = "<p>You haven't created any links, yet.</p><p>To add external or internal links, go to `Edit Post` and add a URL by using the metabox `Further Reading`.<p>";							
			});

			// get recent references
			var data = {
				action: 'netblog_references_latest'
			};						
			jQuery.post(ajaxurl, data, function(xml) {
				var body = document.getElementById('netblog-recentrefs');
				if(xml=='failed'||xml=='0') 
					body.innerHTML = "<p>You haven't created any references, yet.</p><p>To add references to your posts, go to `Edit Post` and use the metabox `References`. If you can't find it, you need to enable it in Netblog Settings.</p>";
				else if(xml.length > 0) {	
					body.innerHTML = '';
					var b=0;
					jQuery(xml).find("Reference").each(function() {
						var cl = b==0?"alternate":"";
						body.innerHTML += "<p>"+jQuery(this).attr('renderListElement')+"<br />"+jQuery(this).attr('name')+"</p>";						
						b=(b+1)%2;							
					});	
				}
				if(body.innerHTML.length==0)
					body.innerHTML = "<p>You haven't created any references, yet.</p><p>To add references to your posts, go to `Edit Post` and use the metabox `References`. If you can't find it, you need to enable it in Netblog Settings.</p>";
			});
			
			
		});
		</script>
		<?php 
		
		//echo '<pre>';
		//var_dump(nbBibliographyReference::getLatest());
		//echo '</pre>';
		
		echo '</div>';
	}
	
}
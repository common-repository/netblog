<?php

class nbNetVis {
	
	static function printWnd() {
		
		echo '<div class="wrap">
			<div id="icon-link-manager" class="icon32"><br /></div>';
		netblog_feedback_smilies();
		echo '<h2>Netblog NetVis BETA</h2><br />';
		
		if(($v=Netblog::getLatestVersion()) > Netblog::options()->getClientVersion() ) {
			echo '<br />';
			require_once 'infobox.php';
			$box = new infobox("There is a new version of Netblog $v available. Please <a href=\"".Netblog::$uriDownload."\">update now</a>.");
			$box->display();
		}
		?> 
		
		<div class="netblog-container" id="nb-popup" style="display:none">
		</div>
		
		<?php

		if(!isset($_GET['pid'])) {
			echo '<div class="netblog-container"><div class="infobox">';
			echo 'You do not have selected a WP article!<br /><br />';
			echo '<em>How to visualize WP Articles?</em>';
			echo '<div style="margin-left:20px">Go to the <i>edit post page</i> and in the metabox "Further Reading" at the top, click on the link "View in NetVis".</div>';
			echo '</div></div>';
		} else {		
		?>
		
		<div class="netblog-area">
			<a class="menu" onclick="resetPosition()">Reset Position</a> |
			<a class="menu" onclick="centerPosition()">Center</a> |
			<a class="menu" onclick="toogleAutoCenter()">Toogle Auto-Center</a> | 
			<a class="menu">Post View</a>
		<div class="netblog-netvis" id="netblog-netvis">
			
		<div style="position:absolute; background: #666" id="box"></div>
		<div class="netblog-netvis-area"  id="netblog-netvis-area">
			<div class="box-left" id="netblog-netvis-item-left">
				<div class="box-yellow box-color">
					<div class="title">From Intern Post</div>
					<ul>
						<li>from intern post asdf asdf asdf asdf adsfasdf 1</li>
						<li>from intern post 2</li>
						<li>from intern post 3</li>
					</ul>
				</div>
				<div class="box-blue box-color">
					<div class="title">From Pingback</div>
					<ul>
						<li>from pingback 1</li>
						<li>from pingback 2</li>
						<li>from pingback 3</li>
					</ul>
				</div>
				<div class="box-blue box-color">
					<div class="title">From Blogsearch</div>
					<ul>
						<li>from blogsearch 1</li>
						<li>from blogsearch 2</li>
						<li>from blogsearch 3</li>
					</ul>
				</div>
			</div>			
			<div class="box-mid" id="netblog-netvis-item-mid">
				<div class="menu-left" id="netblog-netvis-item-menu-left">
					<a href="">Edit</a></div>
				<div class="menu-right" id="netblog-netvis-item-menu-right">
					<a href="">View</a></div>
				<div class="nofloat"></div>
				
				<div class="bg-arrow-left"></div>
				<div class="bg-arrow-right"></div>
				<div class="thumbnail" id="netblog-netvis-item-thumb" ></div>
				
				<div class="txt-title" id="netblog-netvis-item-title" >Interactive mesh creation of trees and plants in Autodesk Maya 2011</div>
				<div class="txt-desc"  id="netblog-netvis-item-desc">The new version 1.5 of the Wordpress plugin Netblog uses a totally unique feature, which prevents dead links in the 'Further Reading' section between export and import of Wordpress articles, even for an unlimited time period between these two events.</div>
				<div class="txt-details" id="netblog-netvis-item-details">
					<table>
						<tbody>
							<tr><td class="key">Author</td>
								<td class="val">cg</td></tr>
							<tr><td class="key">Categories</td>
								<td class="val">Uncategorized</td></tr>
							<tr><td class="key">Tags</td>
								<td class="val">footprint, netblog, plugin</td></tr>
							<tr><td class="key">Comments</td>
								<td class="val">0</td></tr>
							<tr><td class="key">Date</td>
								<td class="val">2010/11/29, Published</td></tr>
							<tr><td class="key">Citations</td>
								<td class="val">5</td></tr>
							<tr><td class="key">Footnotes</td>
								<td class="val">3</td></tr>
							<tr><td class="key">Captions</td>
								<td class="val">3 Chapters, 1 Equation, 3 Tables</td></tr>
							<tr><td class="key">Attachements</td>
								<td class="val">3 Images, 1 PDF</td></tr>
						</tbody>
					</table>
    
					</div>
			</div>
			<div class="box-right" id="netblog-netvis-item-right">
				<div class="box-yellow box-color">
					<div class="title">To Intern Post</div>
					<ul>
						<li>intern link to post 1</li>
						<li>intern link to post 2</li>
						<li>intern link to post 3</li>
					</ul>				
				</div>
				<div class="box-orange box-color">
					<div class="title">To Intern Page</div>
					<ul>
						<li>intern link to page 1</li>
						<li>intern link to page 2</li>
					</ul>				
				</div>
				<div class="box-blue box-color">
					<div class="title">To Extern Resource</div>
					<ul>
						<li>extern link 1</li>
						<li>extern link 2</li>
						<li>extern link 3</li>
					</ul>
				</div>
			</div>
			<div class="nofloat"></div>
		</div>
		</div>
		</div>
		
		<div style="display:none">
		Mouse Move: Delta <input type="text" id="mm-delta" />, Accum <input type="text" id="mm-accum" /><br />
		Pivot: Offset <input type="text" id="pv-off" />, Style Offset: <input type="text" id="pv-stl" /><br />
		Reference: Offset <input type="text" id="rf-off" /><br />
		</div>
		
		<script type="text/javascript">
		<!--
		var Netblog_netvis = new netblogNetVis();
		Netblog_netvis.name = 'Netblog_netvis';
		Netblog_netvis.nameCxt = this;
		Netblog_netvis.netvisObj = document.getElementById('netblog-netvis');
		Netblog_netvis.netvisAreaObj = document.getElementById('netblog-netvis-area');
		Netblog_netvis.itemLeftObj = document.getElementById('netblog-netvis-item-left');
		Netblog_netvis.itemRightObj = document.getElementById('netblog-netvis-item-right');
		Netblog_netvis.itemMidObj = document.getElementById('netblog-netvis-item-mid');
		Netblog_netvis.itemMenuLeftObj = document.getElementById('netblog-netvis-item-menu-left');
		Netblog_netvis.itemMenuRightObj = document.getElementById('netblog-netvis-item-menu-right');
		Netblog_netvis.itemThumbObj = document.getElementById('netblog-netvis-item-thumb');
		Netblog_netvis.itemTitleObj = document.getElementById('netblog-netvis-item-title');
		Netblog_netvis.itemDescObj = document.getElementById('netblog-netvis-item-desc');
		Netblog_netvis.itemDetailsObj = document.getElementById('netblog-netvis-item-details');
		Netblog_netvis.currentPost = '<?php echo isset($_GET['pid']) && is_numeric($_GET['pid']) ? $_GET['pid'] : 23 ?>';
		Netblog_netvis.delimMain = '<?php echo Netblog::options()->getAjaxDelimiterMain(); ?>';
		Netblog_netvis.delimSub = '<?php echo Netblog::options()->getAjaxDelimiterSub(); ?>';

		Netblog_netvis.init();
		
		function resetPosition() {
			Netblog_netvis.resetPosition();
		}
		function centerPosition() {
			Netblog_netvis.centerReference();
		}
		function toogleAutoCenter() {
			Netblog_netvis.autoCenter = !Netblog_netvis.autoCenter; 
		}
		//-->
		</script>
		
		<?php
		}
		echo '</div>';
	}
	
	/**
	 * Load NetVis item (AJAX) 
	 *
	 */
	static function ajaxLoadItem() {
		$pilot = new nbTestPilot();		
		$nOutIntern = $nOutExtern = $nInIntern = $nInBlogsearch = $nInPings = 0;
		
		$pid = $_POST['postID'];		
		$post = &get_post($pid,OBJECT);		
		
		$xml = new SimpleXMLElement('<netvis/>');
		$p = $xml->addChild('Post');
		$p->addAttribute('pid',$pid );
		$p->addAttribute('date', $post->post_date );
		$p->addAttribute('modified', $post->post_modified );
		$p->addAttribute('post_status', $post->post_status );
		$p->addAttribute('post_type', $post->post_type );
		$p->addAttribute('comment_status', $post->comment_status );
		$p->addAttribute('protected', strlen($post->post_password)>0 ? 'true' : 'false');
		$p->addAttribute('url', $post->guid );
		$p->addAttribute('editlink', get_edit_post_link($pid) );
		$p->addAttribute('comments', $post->comment_count );		
		$p->addAttribute('title', $post->post_title );
		
		$desc = $post->post_excerpt;
		if(strlen($desc)==0) $desc = Netblog::stripStr(strip_tags($post->post_content),128,'...');			
		$p->addAttribute('description', $desc );
		
		
		// AUTHORS
		$auths = $p->addChild('Authors');
		$usr = get_userdata($post->post_author);
		$u = $auths->addChild('Author');
		$u->addAttribute('uid', $usr->ID);
		$u->addAttribute('nickname', $usr->nickname);
		$u->addAttribute('url', $usr->user_url);
		
		
		// OUTGOING - PINGED
		$pinged = $p->addChild('OutPinged');
		$t = $pinged->addChild('url', 'http://www.pinged1.com' );
		$t->addAttribute('title', 'Pinged1.com');
		$t = $pinged->addChild('url', 'http://www.pinged2.com' );
		$t->addAttribute('title', 'Pinged2.com');
		$t = $pinged->addChild('url', 'http://www.pinged3.com' );
		$t->addAttribute('title', 'Pinged3.com');
		
		
		// OUTGOING - INTERNAL POSTS/PAGES
		$lp = nbdb::rsc_getAdjs($pid,false,OBJECT);
		if(is_array($lp) && sizeof($lp)>0) {
			$xpo = $p->addChild('OutPosts');
			$xpg = $p->addChild('OutPages');
			foreach($lp as $rsc) {
				if(!is_object($rsc)) continue;
				$t3 = &get_post($rsc->ID,OBJECT);	
				if($t3->post_type == 'post')
					$t = $xpo->addChild('OutPost');
				else if($t3->post_type == 'page')
					$t = $xpg->addChild('OutPage');
				else continue;
								
				$t->addAttribute('title', $t3->post_title);
				$t->addAttribute('url', $t3->guid);
				$t->addAttribute('post_status', $t3->post_status);
				$t->addAttribute('post_id', $t3->ID);
				$nOutIntern++;
			}
		}
		
		// OUTGOING - EXTERNAL LINKS
		$lp = nbdb::rsc_getAdjs($pid,true,OBJECT);
		if(is_array($lp) && sizeof($lp)>0) {
			$xpr = $p->addChild('OutLinks');
			foreach($lp as $rsc) {
				$t = $xpr->addChild('OutLink');
				$t->addAttribute('id', $rsc->uri_id);
				$t->addAttribute('uri', $rsc->uri );
				$t->addAttribute('title', $rsc->uri_title );
				$t->addAttribute('footprint', $rsc->footprint );
				$t->addAttribute('refs', $rsc->refs );
				$nOutExtern++;
			}
		}
		
		// INCOMING - INTERNAL POSTS/PAGES
		$lp = nbdb::rsc_getNodesByAdj($pid,-1,null,OBJECT);
		if(is_array($lp) && sizeof($lp)>0) {
			$xpo = $p->addChild('InPosts');
			$xpg = $p->addChild('InPages');
			foreach($lp as $rsc) {
				if(!is_object($rsc)) continue;
				$t3 = &get_post($rsc->ID,OBJECT);	
				if($t3->post_type == 'post')
					$t = $xpo->addChild('InPost');
				else if($t3->post_type == 'page')
					$t = $xpg->addChild('InPage');
				else continue;
								
				$t->addAttribute('title', $t3->post_title);
				$t->addAttribute('url', $t3->guid);
				$t->addAttribute('post_status', $t3->post_status);
				$t->addAttribute('post_id', $t3->ID);
				$nInIntern++;
			}
		}
		
		// INCOMING - PINGBACKS
		$lp = get_comments('post_id='.$pid);
		$xpp = $p->addChild('InPingbacks');
		foreach($lp as $rsc) {
			if($rsc->comment_type == 'pingback') {
				$t = $xpp->addChild('InPingback');
				$t->addAttribute('title', $rsc->comment_author);
				$t->addAttribute('url', $rsc->comment_author_url);
				$nInPings++;
			}			
		}
		
		// INCOMING - BLOGSEARCH
		$q = 'link:'.$post->guid;
		$rss = nbsearch::getLinksByBlogsearch($q);
		$xpb = $p->addChild('InBlogsearchs');
		if($rss!=null && is_array($rss) && sizeof($rss)>0)			
			foreach($rss as $rsc) {
				$t = $xpb->addChild('InBlogsearch');
				$t->addAttribute('uri', $rsc->get_permalink() );
				$t->addAttribute('title', $rsc->get_title() );
				$nInBlogsearch++;
			}
		$q = 'link:'.get_permalink($pid);
		$rss = nbsearch::getLinksByBlogsearch($q);
		if($rss!=null && is_array($rss) && sizeof($rss)>0)
			foreach($rss as $rsc) {
				$t = $xpb->addChild('InBlogsearch');
				$t->addAttribute('uri', $rsc->get_permalink() );
				$t->addAttribute('title', $rsc->get_title() );
				$nInBlogsearch++;
			}

			
		// This information is extremely important for the future NetVis!!!
		// (how to design the GUI)
		$pilot->netvisLoadItem( strlen($post->post_title), $nOutIntern, $nOutExtern, $nInIntern, $nInBlogsearch, $nInPings );
		$pilot->save();
		print $xml->asXML();
		
		die();		
	}
}

add_action('wp_ajax_netblog_netvis_loaditem', 'nbNetVis::ajaxLoadItem');
?>
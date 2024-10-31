<?php

//---------------------------------------------------------------------------------------------------------------------
// NETBLOG_WIDGETS_REGISTER
//---------------------------------------------------------------------------------------------------------------------
add_action( 'widgets_init', 'netblog_load_widgets' );

function netblog_load_widgets() {
	if( get_option('netblog_widget_outnodes') === 'true' )
		register_widget( 'Netblog_Widget_Outnodes' );
	
	if( get_option('netblog_widget_innodes') === 'true')
		register_widget( 'Netblog_Widget_Innodes' );
}

// DYNAMIC SIDEBAR
// this is used to display widgets below articles; it is turned off by default.
if( get_option('netblog_sidebar') === 'true' ) {
	if (function_exists('register_sidebar')) {
		register_sidebar(array(
			'name'=> 'Post Footer Widget Area',
			'id' => 'netblog_post_footer',
			'description' => 'The primary footer widget area after posts and pages.',
			'before_widget' => '',
			'after_widget' => '',
			'before_title' => '<h2>',
			'after_title' => '</h2>',
		));
	}
	
	function post_footer_filter ($content = '') {
		ob_start();
		dynamic_sidebar('netblog_post_footer');
		$o = ob_get_contents();
		ob_end_clean();
	    return $content.$o;
	}
	
	add_filter('the_content', 'post_footer_filter');
}

//---------------------------------------------------------------------------------------------------------------------
// NETBLOG_WIDGET_OUTNODES
//---------------------------------------------------------------------------------------------------------------------
class Netblog_Widget_Outnodes extends WP_Widget {
	function Netblog_Widget_Outnodes() {
		// WIDGET SETTINGS
		$widget_ops = array( 'classname' => 'netblogs-outnodes', 'description' => __('Link to external/internal webpages, documents. List pingbacks','netblog') );

		/* WIDGET CONTROL SETTINGS. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'netblog-widget-outnodes' );

		$this->WP_Widget( 'netblog-widget-outnodes', __('Further Reading', 'netblog'), $widget_ops );			
	}

	function form($instance) {
		$defaults = array( 'title' => __('Further Reading', 'netblog'), 'intern' => array('size'=>15, 'pos'=>1), 'pingbacks' => array('size'=>0, 'pos'=>2), 
							'extern' => array('size'=>10, 'pos'=>3),
							'dspOnType' => array('post','page'), 'trunc' => 100  );
		$instance = wp_parse_args( (array) $instance, $defaults ); 

		$intNum = $this->get_field_id('internNum');
		$pingNum = $this->get_field_id( 'pingbacksNum' );
		$extNum = $this->get_field_id( 'externNum' );
		$dspOnPost = $this->get_field_id( 'dsp-on-post' );
		$dspOnPage = $this->get_field_id( 'dsp-on-page' );
		$dspOnOther = $this->get_field_id( 'dsp-on-other' );
		$status = $this->get_field_id( 'status' );
		$jsFuncCall['netblog_widget_oneFieldtrue'] = "netblog_widget_oneFieldtrue2('$intNum','$pingNum','$extNum','$dspOnPost','$dspOnPage','$dspOnOther','$status');";
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title','netblog') ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" 
				value="<?php echo $instance['title']; ?>" class="widefat" type="text" />
		</p>
		
		<b><?php _e('Number of visible hyperlinks','netblog') ?></b><br />
		<small>(<?php _e('0 := hidden; -1 := unlimited','netblog') ?>)</small>
		<table style="margin-left: 15px;">
		  <tr>
		    <td><label for="<?php echo $this->get_field_id( 'internNum' ); ?>"><?php _e('within Website','netblog') ?></label></td>
		    <td style="padding-right: 10px"><input type="text" size="3" id="<?php echo $this->get_field_id( 'internNum' ); ?>" 
		    		name="<?php echo $this->get_field_name( 'internNum' ); ?>"  onblur="netblog_check_field2int('<?php echo $this->get_field_id( 'internNum' ); ?>', 15); <?php echo $jsFuncCall['netblog_widget_oneFieldtrue']; ?>"
					value="<?php echo $instance['intern']['size']; ?>" /></td>
		    <td><input type="text" size="1" id="<?php echo $this->get_field_id( 'internPos' ); ?>"
		    		name="<?php echo $this->get_field_name( 'internPos' ); ?>" value="<?php echo $instance['intern']['pos']; ?>"
		    		 onblur="netblog_check_field2int('<?php echo $this->get_field_id( 'internPos' ); ?>', 1);" /></td>
		  </tr>
		  <tr>
		    <td><label for="<?php echo $this->get_field_id( 'pingbacksNum' ); ?>"><?php _e('Pingbacks','netblog') ?></label></td>
		    <td><input type="text" size="3" id="<?php echo $this->get_field_id( 'pingbacksNum' ); ?>" 
		    		name="<?php echo $this->get_field_name( 'pingbacksNum' ); ?>"  onblur="netblog_check_field2int('<?php echo $this->get_field_id( 'pingbacksNum' ); ?>', 0); <?php echo $jsFuncCall['netblog_widget_oneFieldtrue']; ?>"
					value="<?php echo $instance['pingbacks']['size']; ?>" /></td>
		    <td><input type="text" size="1" id="<?php echo $this->get_field_id( 'pingbacksPos' ); ?>"
		    		name="<?php echo $this->get_field_name( 'pingbacksPos' ); ?>" value="<?php echo $instance['pingbacks']['pos']; ?>"
		    		 onblur="netblog_check_field2int('<?php echo $this->get_field_id( 'pingbacksPos' ); ?>', 2);" /></td>
		  </tr>
		  <tr>
		    <td><label for="<?php echo $this->get_field_id( 'externNum' ); ?>"><?php echo __('WWW','netblog') ?></label></td>
		    <td><input type="text" size="3" id="<?php echo $this->get_field_id( 'externNum' ); ?>" 
		    		name="<?php echo $this->get_field_name( 'externNum' ); ?>"  onblur="netblog_check_field2int('<?php echo $this->get_field_id( 'externNum' ); ?>', 15); <?php echo $jsFuncCall['netblog_widget_oneFieldtrue']; ?>"
					value="<?php echo $instance['extern']['size']; ?>" /></td>
		    <td><input type="text" size="1" id="<?php echo $this->get_field_id( 'externPos' ); ?>"
		    		name="<?php echo $this->get_field_name( 'externPos' ); ?>" value="<?php echo $instance['extern']['pos']; ?>" 
		    		 onblur="netblog_check_field2int('<?php echo $this->get_field_id( 'externPos' ); ?>', 3);"/></td>
		  </tr>
		</table>
		<br />
		
		<label for="<?php echo $this->get_field_id( 'trunc' ); ?>"><b><?php _e('Truncate hyperlinks to','netblog') ?></b></label>
		<p style="padding-left: 20px">
			<input type="text" size="3" id="<?php echo $this->get_field_id( 'trunc' ); ?>" 
		    		name="<?php echo $this->get_field_name( 'trunc' ); ?>" onblur="netblog_check_field2int('<?php echo $this->get_field_id( 'trunc' ); ?>', 100);"
		    		value="<?php echo $instance['trunc']; ?>"  />
			<label for="<?php echo $this->get_field_id( 'trunc' ); ?>"> <?php _e('characters','netblog') ?></label>
		</p>

		<b><?php _e('Display on','netblog') ?></b>
		<p style="padding-left: 20px">
			<input class="checkbox" type="checkbox" <?php checked( in_array('post',$instance['dspOnType']) == true, true ) ?> 
				id="<?php echo $this->get_field_id( 'dsp-on-post' ); ?>" 
				name="<?php echo $this->get_field_name( 'dsp-on-post' ); ?>" value="true"
				onclick="<?php echo $jsFuncCall['netblog_widget_oneFieldtrue']; ?>" />
			<label for="<?php echo $this->get_field_id( 'dsp-on-post' ); ?>"><?php _e('posts','netblog') ?></label>&nbsp;
			
			<input class="checkbox" type="checkbox" <?php checked( in_array('page',$instance['dspOnType']) == true, true ); ?> 
				id="<?php echo $this->get_field_id( 'dsp-on-page' ); ?>" 
				name="<?php echo $this->get_field_name( 'dsp-on-page' ); ?>" value="true"
				onclick="<?php echo $jsFuncCall['netblog_widget_oneFieldtrue']; ?>" />
			<label for="<?php echo $this->get_field_id( 'dsp-on-page' ); ?>"><?php _e('pages','netblog') ?></label>&nbsp;
			
			<input class="checkbox" type="checkbox" <?php checked( in_array('other',$instance['dspOnType']) == true, true ); ?> 
				id="<?php echo $this->get_field_id( 'dsp-on-other' ); ?>" 
				name="<?php echo $this->get_field_name( 'dsp-on-other' ); ?>" value="true"
				onclick="<?php echo $jsFuncCall['netblog_widget_oneFieldtrue']; ?>" />
			<label for="<?php echo $this->get_field_id( 'dsp-on-other' ); ?>"><?php _e('others','netblog') ?></label>	
		</p>
		<div id="<?php echo $this->get_field_id( 'status' ); ?>" style="color:red;display:none"><?php _e('No Links will be displayed and widget will not appear.','netblog') ?></div>

		<script type="text/javascript">
		<!--
		<?php echo $jsFuncCall['netblog_widget_oneFieldtrue']; ?>
		//-->
		</script>					
		
		<?php
	}

	function update($new_instance, $old_instance) {

		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['intern'] = array( 'size'=>$new_instance['internNum'], 'pos'=>$new_instance['internPos']);
		$instance['pingbacks'] = array( 'size'=>$new_instance['pingbacksNum'], 'pos'=>$new_instance['pingbacksPos']);
		$instance['extern'] = array( 'size'=>$new_instance['externNum'], 'pos'=>$new_instance['externPos']);
		$instance['trunc'] = $new_instance['trunc'];
		
		$p = array();
		if( isset($new_instance['dsp-on-post']) && $new_instance['dsp-on-post'] == 'true' )
			$p[] = 'post';
		if( isset($new_instance['dsp-on-page']) && $new_instance['dsp-on-page'] == 'true' )
			$p[] = 'page';
		if( isset($new_instance['dsp-on-other']) && $new_instance['dsp-on-other'] == 'true' )
			$p[] = 'other';
		$instance['dspOnType'] = $p;
		
        return $instance;		
	}

	// GENERATE SITE-DEPENDENT WIDGET CONTENT
	function widget($args, $instance) {		
		global $wpdb;
		global $post;
				
		$nodeID = get_the_ID();			
        if( $post->ID == '' || !is_numeric($post->ID) || $post->ID <= '0'  ) {
        	echo 'Widget: get_the_ID() returned null! Not in the loop?';
        	return;
        }
        if(!is_array($instance) || sizeof($instance)==0) {
        	echo 'Widget \'Further Reading\' got no settings!';
        	return;
        }
        
		if( $instance['dspOnType'] == null || !in_array($post->post_type,$instance['dspOnType']) ) 
			return;
		
		// MK QUEUE			        
	        $queue = array();
	        $queue['intern'] = $instance['intern'];
	        $queue['pingbacks'] = $instance['pingbacks'];
	        $queue['extern'] = $instance['extern'];
	        
		// SET UP VARS
			$out;
			$num = 0;
			$truncStr = '...';
			$trunc = $instance['trunc'];
			
	        extract( $args );
	        $title = apply_filters('widget_title', $instance['title']);  		
		
			$out = $before_widget;
			if( $title ) $out.= $before_title.$title.$after_title;			
			$out.= '<ul>';
		
		// URI INDEXER - PREVENT MULTIPLE, IDENTICAL LINKS
			$uris = array();
			$linksNum = 0;
			
		$blockNum = sizeof($queue);
		for( $block=0; $block<$blockNum; $block++ ) {
			// GET NEXT BLOCK
			$pos = -1; $keyNext = '';
			foreach( $queue as $k=>$v )
				if( $pos == -1 || $v['pos'] < $pos ) { 
					$pos = $v['pos'];
					$keyNext = $k;
				}
			
			// PRINT LINKS OF BLOCK
			$size = $queue[$keyNext]['size'];
			switch($keyNext){				
				// INTERN NET
				case 'intern':
		
					if( $chld=nbLinkIntern::LoadByParent($post->ID) )
						foreach($chld as $rel) {
							$xpost = get_post($rel->GetChildID());
							
							$link = get_permalink($xpost->ID);
							if( $post_type == 'attachment' )
								$link = wp_get_attachment_url($xpost->ID);							
							if( in_array( $link, $uris ) ) continue;
							
							// ICONS
							$iconSrc = '';
							if( pathinfo($list, PATHINFO_EXTENSION) == 'pdf' )
								$iconSrc = WP_PLUGIN_URL . '/netblog/images/icons-mini-file_acrobat.gif';
							
							$out.= "<li><a href=\"$link\" title=\"$xpost->post_title\">". Netblog::cstrip($xpost->post_title,$trunc,$truncStr) . '</a>';
							if( strlen($iconSrc) > 0 )
								$out.= ' <img src="'.$iconSrc.'" />';
							$out .= '</li>';
							$uris[] = $link;
							$linksNum++;
						}
					break;

				// PINGBACKS
				case 'pingbacks':
							
					$pung = get_pung( $post->ID );
					$iconSrc = WP_PLUGIN_URL . '/netblog/images/external-link-ltr-icon.png';
					$num = 0;			
					foreach( $pung as $uri ) {
						if( $num >= $size && $size >= 0 ) break;
						if( strlen($uri) == 0 || in_array($uri,$uris) ) continue;
						$out .= '<li><a href="'.$uri.'" title="'.$uri.'">'.$uri.'</a>
								<img src="'.Netblog::cstrip($iconSrc,$trunc,$truncStr).'" /></li>';
						$num++;
						$uris[] = $uri;
					}
					$linksNum += $num;				
					break;
			
				// EXTERN NET	
				case 'extern':	
					$extNet = nbdb::rsc_getAdjs($post->ID, true );
					for( $i=0; $i<sizeof($extNet) && ($i<$size||$size<0); $i++ ) {
						if( in_array($extNet[$i]['uri'],$uris) ) continue;
						
						$iconSrcDft = $iconSrc = WP_PLUGIN_URL . '/netblog/images/external-link-ltr-icon.png';
						if( substr( $extNet[$i]['uri'], -4 ) == '.pdf' )
							$iconSrc = WP_PLUGIN_URL . '/netblog/images/icons-mini-file_acrobat.gif';
						$out.= '<li><a href="'.$extNet[$i]['uri'].'" title="'.$extNet[$i]['uri_title'].'" rel="nofollow">'.
								Netblog::cstrip($extNet[$i]['uri_title'],$trunc,$truncStr).'</a>';
						if( $iconSrc != $iconSrcDft )
							$out .= ' <img src="'.$iconSrcDft.'" />';
						$out .= ' <img src="'.$iconSrc.'" /></li>';
						$uris[] = $extNet[$i]['uri'];
						$linksNum++;
					}				
					break;
			}
			
			unset($queue[$keyNext]);
		}
			
		// PRINT WIDGET
		$out.= '</ul>';
		$out.= $after_widget;		
		if( $linksNum > 0 )
			echo $out;
	}
	
}




//---------------------------------------------------------------------------------------------------------------------
// NETBLOG_WIDGETS_INNODES
//---------------------------------------------------------------------------------------------------------------------
class Netblog_Widget_Innodes extends WP_Widget {
	function Netblog_Widget_Innodes() {
		// WIDGET SETTINGS
		$widget_ops = array( 'classname' => 'netblogs-innodes', 'description' => __('Show incoming links using this blog, Pingbacks and Blogsearch','netblog') );

		// WIDGET CONTROL SETTINGS (OPTIONAL)
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'netblog-widget-outnodes' );

		$this->WP_Widget( 'netblog-widget-innodes', __('Referenced By / Incoming Links', 'netblog'), $widget_ops );			
	}

	function form($instance) {
		$defaults = array( 'title' => __('Referenced By','netblog'), 'intern' => array('size'=>20, 'pos'=>1), 'pingbacks' => array('size'=>5, 'pos'=>2), 
							'blogsearch' => array('size'=>10, 'pos'=>3),
					 		'dspOnType' => array('post','page' ), 'trunc' => 100 );
		$instance = wp_parse_args( (array) $instance, $defaults ); 
		
		$intNum = $this->get_field_id('internNum');
		$pingNum = $this->get_field_id( 'pingbacksNum' );
		$blogNum = $this->get_field_id( 'blogsearchNum' );
		$dspOnPost = $this->get_field_id( 'dsp-on-post' );
		$dspOnPage = $this->get_field_id( 'dsp-on-page' );
		$dspOnOther = $this->get_field_id( 'dsp-on-other' );
		$status = $this->get_field_id( 'status' );
		$jsFuncCall['netblog_widget_oneFieldtrue'] = "netblog_widget_oneFieldtrue2('$intNum','$pingNum','$blogNum','$dspOnPost','$dspOnPage','$dspOnOther','$status');";
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title','netblog') ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" 
					value="<?php echo $instance['title']; ?>"  class="widefat" type="text" />
		</p>
		
		<strong><?php _e('Number of visible hyperlinks','netblog') ?></strong><br />
		<small>(<?php _e('0 := hidden; -1 := unlimited','netblog') ?>)</small>
		<table style="margin-left: 15px;">
		  <tr>
		    <td><label for="<?php echo $this->get_field_id( 'internNum' ); ?>"><?php _e('within Website','netblog') ?></label></td>
		    <td style="padding-right: 10px"><input type="text" size="3" id="<?php echo $this->get_field_id( 'internNum' ); ?>" 
		    		name="<?php echo $this->get_field_name( 'internNum' ); ?>"  onblur="netblog_check_field2int('<?php echo $this->get_field_id( 'internNum' ); ?>', 15); <?php echo $jsFuncCall['netblog_widget_oneFieldtrue']; ?>"
					value="<?php echo $instance['intern']['size']; ?>" /></td>
		    <td><input type="text" size="1" id="<?php echo $this->get_field_id( 'internPos' ); ?>"
		    		name="<?php echo $this->get_field_name( 'internPos' ); ?>" value="<?php echo $instance['intern']['pos']; ?>"
		    		 onblur="netblog_check_field2int('<?php echo $this->get_field_id( 'internPos' ); ?>', 1);" /></td>
		  </tr>
		  <tr>
		    <td><label for="<?php echo $this->get_field_id( 'pingbacksNum' ); ?>"><?php _e('Pingbacks','netblog') ?></label></td>
		    <td><input type="text" size="3" id="<?php echo $this->get_field_id( 'pingbacksNum' ); ?>" 
		    		name="<?php echo $this->get_field_name( 'pingbacksNum' ); ?>"  onblur="netblog_check_field2int('<?php echo $this->get_field_id( 'pingbacksNum' ); ?>', 5); <?php echo $jsFuncCall['netblog_widget_oneFieldtrue']; ?>"
					value="<?php echo $instance['pingbacks']['size']; ?>" /></td>
		    <td><input type="text" size="1" id="<?php echo $this->get_field_id( 'pingbacksPos' ); ?>"
		    		name="<?php echo $this->get_field_name( 'pingbacksPos' ); ?>" value="<?php echo $instance['pingbacks']['pos']; ?>"
		    		 onblur="netblog_check_field2int('<?php echo $this->get_field_id( 'pingbacksPos' ); ?>', 2);" /></td>
		  </tr>
		  <tr>
		    <td><label for="<?php echo $this->get_field_id( 'blogsearchNum' ); ?>"><?php _e('Blogsearch','netblog') ?></label></td>
		    <td><input type="text" size="3" id="<?php echo $this->get_field_id( 'blogsearchNum' ); ?>" 
		    		name="<?php echo $this->get_field_name( 'blogsearchNum' ); ?>"  onblur="netblog_check_field2int('<?php echo $this->get_field_id( 'blogsearchNum' ); ?>', 15); <?php echo $jsFuncCall['netblog_widget_oneFieldtrue']; ?>"
					value="<?php echo $instance['blogsearch']['size']; ?>" /></td>
		    <td><input type="text" size="1" id="<?php echo $this->get_field_id( 'blogsearchPos' ); ?>"
		    		name="<?php echo $this->get_field_name( 'blogsearchPos' ); ?>" value="<?php echo $instance['blogsearch']['pos']; ?>" 
		    		 onblur="netblog_check_field2int('<?php echo $this->get_field_id( 'blogsearchPos' ); ?>', 3);"/></td>
		  </tr>
		</table>
		<br />		
		
		<label for="<?php echo $this->get_field_id( 'trunc' ); ?>"><strong><?php _e('Truncate hyperlink to','netblog') ?></strong></label>
		<p style="padding-left: 20px">
			<input type="text" size="3" id="<?php echo $this->get_field_id( 'trunc' ); ?>" 
		    		name="<?php echo $this->get_field_name( 'trunc' ); ?>" onblur="netblog_check_field2int('<?php echo $this->get_field_id( 'trunc' ); ?>', 100);"
		    		value="<?php echo $instance['trunc']; ?>"  />
			<label for="<?php echo $this->get_field_id( 'trunc' ); ?>"> <?php _e('characters','netblog') ?></label>
		</p>
		
		<strong><?php _e('Display on','netblog') ?></strong>
		<p style="padding-left: 20px">
			<input class="checkbox" type="checkbox" <?php checked( in_array('post',$instance['dspOnType']) == true, true ) ?> 
				id="<?php echo $this->get_field_id( 'dsp-on-post' ); ?>" 
				name="<?php echo $this->get_field_name( 'dsp-on-post' ); ?>" value="true"
				onclick=" <?php echo $jsFuncCall['netblog_widget_oneFieldtrue']; ?>" />
			<label for="<?php echo $this->get_field_id( 'dsp-on-post' ); ?>"><?php _e('posts','netblog') ?></label>&nbsp;
			
			<input class="checkbox" type="checkbox" <?php checked( in_array('page',$instance['dspOnType']) == true, true ); ?> 
				id="<?php echo $this->get_field_id( 'dsp-on-page' ); ?>" 
				name="<?php echo $this->get_field_name( 'dsp-on-page' ); ?>" value="true"
				onclick=" <?php echo $jsFuncCall['netblog_widget_oneFieldtrue']; ?>" />
			<label for="<?php echo $this->get_field_id( 'dsp-on-page' ); ?>"><?php _e('pages','netblog') ?></label>&nbsp;
			
			<input class="checkbox" type="checkbox" <?php checked( in_array('other',$instance['dspOnType']) == true, true ); ?> 
				id="<?php echo $this->get_field_id( 'dsp-on-other' ); ?>" 
				name="<?php echo $this->get_field_name( 'dsp-on-other' ); ?>" value="true"
				onclick="<?php echo $jsFuncCall['netblog_widget_oneFieldtrue']; ?>" />
			<label for="<?php echo $this->get_field_id( 'dsp-on-other' ); ?>"><?php _e('others','netblog') ?></label>	
		</p>		
		<div id="<?php echo $this->get_field_id( 'status' ); ?>" style="color:red;display:none"><?php _e('No Links will be displayed and widget will not appear.','netblog') ?></div>
			
		<script type="text/javascript">
		<!--
		<?php echo $jsFuncCall['netblog_widget_oneFieldtrue']; ?>
		//-->
		</script>
		<?php
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = esc_attr(strip_tags($new_instance['title']));
		$instance['intern'] = array( 'size'=>$new_instance['internNum'], 'pos'=>$new_instance['internPos']);
		$instance['pingbacks'] = array( 'size'=>$new_instance['pingbacksNum'], 'pos'=>$new_instance['pingbacksPos']);
		$instance['blogsearch'] = array( 'size'=>$new_instance['blogsearchNum'], 'pos'=>$new_instance['blogsearchPos']);
		$instance['trunc'] = $new_instance['trunc'];
		
		$p = array();
		if( isset($new_instance['dsp-on-post']) && $new_instance['dsp-on-post'] == 'true' )
			$p[] = 'post';
		if( isset($new_instance['dsp-on-page']) && $new_instance['dsp-on-page'] == 'true' )
			$p[] = 'page';
		if( isset($new_instance['dsp-on-other']) && $new_instance['dsp-on-other'] == 'true' )
			$p[] = 'other';
		$instance['dspOnType'] = $p;
				
        return $instance;		
	}

	// GENERATE SITE-DEPENDENT WIDGET CONTENT
	function widget($args, $instance) {
		global $wpdb;
		global $post;
		global $nodeID;
		
		if( $instance['size'] == '0' ) return;
	
		$nodeID = get_the_ID();		              
        if( $post->ID == '' || !is_numeric($post->ID) || $post->ID <= '0'  ) {
        	echo 'Widget: get_the_ID() returned null! Not in the loop?';
        	return;
        }
        if(!is_array($instance) || sizeof($instance)==0) {
        	echo 'Widget \'Referenced By\' got no settings!';
        	return;
        }      
        if( $instance['dspOnType'] == null || !in_array($post->post_type,$instance['dspOnType']) )
			return;
			
		// SET UP VARS
			$queue = array();
	        $queue['intern'] = $instance['intern'];
	        $queue['pingbacks'] = $instance['pingbacks'];
	        $queue['blogsearch'] = $instance['blogsearch'];
	        
			$out;
			$truncStr = '...';
			$trunc = $instance['trunc'];		
			
	        extract( $args );
	        $title = apply_filters('widget_title', $instance['title']);        
			
		// FORMAT WIDGET - TITLE
			$out = $before_widget;
			if( $title ) $out.= $before_title.$title.$after_title;			
			$out.= '<ul>';			

		// URI INDEXER - PREVENT MULTIPLE, IDENTICAL LINKS
			$uris = array();
			$numLinks = 0;
		
			
		$blockNum = sizeof($queue);
		for( $block=0; $block<$blockNum; $block++ ) {
			// GET NEXT BLOCK
			$pos = -1; $keyNext = '';
			foreach( $queue as $k=>$v )
				if( $pos == -1 || $v['pos'] < $pos ) { 
					$pos = $v['pos'];
					$keyNext = $k;
				}
				
			// PRINT LINKS OF BLOCK
			$size = $queue[$keyNext]['size'];
			switch($keyNext){
				// INTERN
				case 'intern':									
					if( $size == 0 ) break;

					if( $chld=nbLinkIntern::LoadByChild($post->ID) )
						foreach($chld as $rel) {
						$post = get_post($rel->GetParentID());
							
						$link = get_permalink($post->ID);
						if( $post_type == 'attachment' )
							$link = wp_get_attachment_url($post->ID);
						if( in_array( $link, $uris ) ) continue;
							
						// ICONS
						$iconSrc = '';
						if( pathinfo($list, PATHINFO_EXTENSION) == 'pdf' )
							$iconSrc = WP_PLUGIN_URL . '/netblog/images/icons-mini-file_acrobat.gif';
							
						$out.= "<li><a href=\"$link\" title=\"$post->post_title\">". Netblog::cstrip($post->post_title,$trunc,$truncStr) . '</a>';
						if( strlen($iconSrc) > 0 )
							$out.= ' <img src="'.$iconSrc.'" />';
						$out .= '</li>';
						$uris[] = $link;
						$linksNum++;
					}
					break;

				// PINGBACKS
				case 'pingbacks':
					if( $size == 0 ) break;
					
					$com = get_approved_comments($post->ID);
					$iconSrc = WP_PLUGIN_URL . '/netblog/images/external-link-ltr-icon.png';
					$num = 0;
					foreach( $com as $c )
						if( $c->comment_type == 'pingback' && $num < $size && !in_array($c->comment_author_url,$uris) ) {
							$out .= '<li><a href="'.$c->comment_author_url.'" title="'.$c->comment_author.'">'.
									Netblog::cstrip($c->comment_author,$trunc,$truncStr).'</a><img src="'.$iconSrc.'" /></li>';
							$num++;
							$uris[] = $c->comment_author_url;
						}
					$numLinks += $num;
					break;
					
					
				// BLOGSEARCH
				case 'blogsearch':
					if( $size == 0 ) break;
					
					$nodePermalink = get_permalink($post->ID);
					$uri = Netblog::options()->getBlogsearchUri("link:$nodePermalink");

					$rss = fetch_feed( $uri );
					if( !is_wp_error($rss) ) {
						$maxItems = $rss->get_item_quantity( $size>0 ? $size : 0 );	// 0 := all items
						$rss_items = $rss->get_items(0, $maxitems);
	
						if( $maxItems != 0 )
						foreach( $rss_items as $item ) {
							if( in_array( $item->get_permalink(), $uris ) ) continue;
							
							$iconSrc = WP_PLUGIN_URL . '/netblog/images/external-link-ltr-icon.png';
							if( substr( $extNet[$i]['uri'], -4 ) == '.pdf' )
								$iconSrc = WP_PLUGIN_URL . '/netblog/images/icons-mini-file_acrobat.gif';						
							$out.= '<li><a href="'.$item->get_permalink().'" title="'.$item->get_title().'">'.
									Netblog::cstrip($item->get_title(),$trunc,$truncStr).'</a>
									<img src="'.$iconSrc.'" /></li>';
							
							$uris[] = $item->get_permalink();
							$numLinks++;
						}
					}
											
			}
			
			unset($queue[$keyNext]);
		}
		
		// PRINT WIDGET
		$out.= '</ul>';			
		$out.= $after_widget;
		if( $numLinks > 0 )
			echo $out;

	}

}



?>
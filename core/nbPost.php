<?php

class nbPost { 
	/**
	 * Fill a SimpleXMLElement with information about a given post
	 * @param int $post_id
	 * @param SimpleXMLElement $xml
	 */
	static function AsXML( $post_id, $xml ) {
		if( $post=get_post($post_id) ) {
			$xp = $xml->addChild('POST');
			$xp->addAttribute('id', $post_id);
			$xp->addAttribute('title', $post->post_title);
			$xp->addAttribute('uri', get_permalink($post_id));
		}
	}
	
	/**
	 * Fill a SimpleXMLElement with information about a given post
	 * @param int $post_id
	 * @param SimpleXMLElement $xml
	 */
	static function AsXMLExtended( $post_id, $xml ) {
		if( $post=get_post($post_id) ) {
			$xp = $xml->addChild('POST');
			$xp->addAttribute('id', $post_id);
			$xp->addAttribute('title', $post->post_title);
			$xp->addAttribute('author_id', $post->post_author);
			$user = get_userdata($post->post_author);
			$xp->addAttribute('author', strlen($user->user_nicename)>0?$user->user_nicename:$user->user_login);
			$xp->addAttribute('date', $post->post_date);
			$xp->addAttribute('status', $post->post_status);
			$xp->addAttribute('type', $post->post_type);
			$xp->addAttribute('uri', $p=get_permalink($post_id));
			$xp->addAttribute('permalink', $p);
			$xp->addAttribute('editlink', get_edit_post_link($post_id));
			$xp->addAttribute('removable','true');
		}
	}
}
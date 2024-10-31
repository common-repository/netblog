<?php
require_once 'DataTransfer.php';
require_once 'stringVariance.php';

/**
 * Submit user feedback to secure Netblog Server.
 */
class nbFeedbackSubmit {
	
	/**
	 * Set feedback message text
	 *
	 * @param string $msg
	 */
	function setMessage( $msg ) {
		$this->data['message'] = trim(strip_tags($msg));		
	}
	
	/**
	 * Set feedback type
	 *
	 * @param string $type Any of 'happy' | 'sad'
	 */
	function setFeedbackType( $type ) {
		if($type!='happy' && $type!='sad') $type = 'happy';
		$this->data['type'] = $type;
	}
	
	/**
	 * Send feedback to official Netblog feedback server.
	 *
	 * @return bool
	 */
	function send() {
		if(Netblog::options()->getPrivacyLevel()=='ultra') {
			$this->errno = 501;
			$this->error = 'Strict Privacy Level';
			return false;
		}
		if($this->data['message']=='') {
			$this->errno = 502;
			$this->error = 'Missing Feedback Message';
			return false;
		}
		if(strlen($this->data['message']) < 10) {
			$this->errno = 503;
			$this->error = 'Feedback Message to short; at least 10 characters are required!';
			return false;
		}
		if(strlen($this->data['message']) > 255 ) {
			$this->errno = 503;
			$this->error = 'Feedback Message exceeds maximum size of 255 characters!';
			return false;
		}
		$svar = new stringVariance($this->data['message']);
		if($svar->CountDiffChars() < 15) {
			$this->errno = 504;
			$this->error = 'Feedback Message has been categorized as spam. Please write more details.';
			return false;
		}
		
		$this->appendAppData();
		$engine_uri = $this->host . "submit.php";
		Netblog::log("Sending user feedback...");
		Netblog::log("Transfering ".round(strlen(http_build_query($this->data))/1024,3)."KB encrypted data to $engine_uri");
		$result = DataTransfer::SubmitPost($engine_uri, $this->data);
					 
		// CONTACT SERVER AND RETRIEVE RESPONSE
		if($result['content']) {
			$this->parse($result['content']);			
			if($this->errno==0) {
				Netblog::logSuccess("User feedback has been sended");
				return true;
			}
		} else if($result['error']) {
			$this->errno = $result['errno'];
			$this->error = $result['error'];
		} else {
			$this->errno = 503;
			$this->error = 'Service Unavailable';
		}
		Netblog::logError("Failed to send user feedback (Error ".$this->errno.': '.$this->error.')');
		return false;
	}
	
	/**
	 * Append application and host data for better support
	 *
	 */
	private function appendAppData() {
		$this->data['netblog_client'] = Netblog::getClientVersion();			// for statistics and to improve support
		$this->data['netblog_server'] = Netblog::getServerVersion();
		$this->data['host'] = 'wordpress';
		$this->data['host_version'] = get_bloginfo('version');					// helps to find bugs and to improve wp support
		$this->data['host_language'] = get_bloginfo('language');				// to improve multi-language support
		
		if(Netblog::options()->getPrivacyLevel()=='none') {
			$this->data['host_url'] = get_bloginfo('url');							// optional: those who submit a lot of useful feedback
			$this->data['host_description'] = get_bloginfo('description');				// may be promoted to beta tester
			$this->data['host_name'] = get_bloginfo('name');							// - these data are not public (feedback site)
																						// - send an email (feedback site) to prevent storage
		}																				//   of these data
		
		if(Netblog::options()->getPrivacyLevel()=='medium' || Netblog::options()->getPrivacyLevel()=='none') {
			$this->data['host_charset'] = get_bloginfo('charset');
			
			$ct = current_theme_info();
			$this->data['host_theme_name'] = $ct->name;
			$this->data['host_theme_title'] = $ct->title;
			$this->data['host_theme_version'] = $ct->version;
			$this->data['host_theme_parent'] = $ct->parent_theme;
			$this->data['host_theme_template'] = $ct->template;
			
			$pls = get_plugins();$i=0;
			foreach($pls as $pl) {
				$this->data['host_plugin_'.$i.'_name'] = $pl['Name'];
				$this->data['host_plugin_'.$i.'_version'] = $pl['Version'];
				$this->data['host_plugin_'.$i.'_network'] = $pl['Network'];
				$this->data['host_plugin_'.$i.'_uri'] = $pl['PluginURI'];
				$i++;
			}		
			$this->data['host_multisite'] = is_multisite();		
			
			$this->data['useragent'] = $_SERVER['HTTP_USER_AGENT'];					// for better testing of new versions		
			$this->data['php_version'] = phpversion();
			$this->data['mysql_version'] = nbdb::getServerVersion();
			$this->data['php_extensions'] = implode('|',get_loaded_extensions());
		}
	}
	
	/**
	 * Parse special-formatted string from server response.
	 *
	 * @param string $response
	 * @return void
	 */
	private function parse( $response )
	{
		$this->error = '';
		$this->errno = 0;
		$this->resp = $response;
							
		if( $response == '1' || $response == 'ok' ) return;
		if( trim(strlen($response))==0 ) {
			$this->errno = 502;
			$this->error = __('Invalid Server Response','netblog');	
		}
		$string = base64_decode($response);
		
		// TEST FOR ERRORS
		if( ($perr=stripos($string,'error')) !== false ) {
			$perr += 5;
			$this->errno = trim(substr($string,$perr, ($p2=stripos($string,':',$perr))-$perr) );
			$this->error = trim(substr($string,$p2+1));
			return;
		}
	}
	
	/**
	 * Get error number
	 *
	 * @return string
	 */
	function getErrno() { return $this->errno; }
	
	/**
	 * Get error message
	 *
	 * @return string
	 */
	function getErrorMessage() { return $this->error; }
	
	private $data = array();
	private $error = '';
	private $errno = 0;
	private $host = 'http://netblog2.benjaminsommer.com/feedbacks/';
	
	public $resp = '';
}


function netblog_feedback_smilies() 
{
	?>
	<div style="float:right;" id="netblog-feedback">
		<div class="frown"></div>
		<div class="smile"></div>
		
		<div class="feedback window wsmile" style="display:none">
			<h2>Netblog Made Me Happy Because...</h2>
			<p><textarea name="message"></textarea>
				<input type="hidden" name="type" value="happy" /></p>
			<p>
				<small style="float:right; color:#BBB">By Sending, you accept Terms of Privacy.<br />
					Don't submit confidential data.<br />
					Privacy Setting: <?php echo ucfirst(Netblog::options()->getPrivacyLevel())?></small>
				<input type="button" value="Send A Smile" class="button-primary submit">
				<input type="button" value="Cancel" class="button-secondary cancel">
			</p>
		</div>
		<div class="feedback window wfrown" style="display:none">
			<h2>Netblog Made Me Sad Because...</h2>
			<p><textarea name="message"></textarea>
				<input type="hidden" name="type" value="sad" /></p>
			<p>
				<small style="float:right; color:#BBB">By Sending, you accept Terms of Privacy.<br />
					Don't submit confidential data.<br />
					Privacy Setting: <?php echo ucfirst(Netblog::options()->getPrivacyLevel())?></small>
				<input type="button" value="Send A Frown" class="button-primary submit">
				<input type="button" value="Cancel" class="button-secondary cancel">
			</p>
		</div>
		<div class="feedback window thanks" style="display:none">
			<h2>Thanks For Your Feedback</h2>
			<p>
				<blockquote style="line-height:1.7em">
					"Everyone has a story that makes me stronger. I know that the work I do is important and I enjoy it, 
					but it is nice to hear the feedback of what we do to inspire others."<br />
				<small style="float:right; color:#BBB">(Richard Simmons)</small>
				</blockquote>
			</p>
			<p>
				<input type="button" value="Close" class="button-secondary cancel">
			</p>
		</div>	
	</div>
	
	<script type="text/javascript">
	jQuery(document).ready(function(e) {
		jQuery('#netblog-feedback .smile').click(function(e) {
			jQuery('#netblog-feedback .window').hide()
			jQuery('#netblog-feedback .window.wsmile').show()
			jQuery('#netblog-feedback .window.wsmile textarea').focus()
		})
		jQuery('#netblog-feedback .frown').click(function(e) {
			jQuery('#netblog-feedback .window').hide()
			jQuery('#netblog-feedback .window.wfrown').show()
			jQuery('#netblog-feedback .window.wfrown textarea').focus()			
		})
		jQuery('#netblog-feedback input.cancel').click(function(e) {
			jQuery('#netblog-feedback .window').hide()			
		})
		jQuery('#netblog-feedback input.submit').click(function(e) {
			var p = jQuery(this).parent().parent()
			var b = p.find('textarea').val()
			var t = p.find('input[name="type"]').val()
			jQuery.post(ajaxurl, {action: 'nbfeedback_send', message:b, type:t}, function(data) {
				data = data.trim();
				if (data=='ok') {
					jQuery('#netblog-feedback .window').hide()
					jQuery('#netblog-feedback .window.thanks').show()
					jQuery('#netblog-feedback textarea').val('');
					
				} else alert(data);				
			})						
		})
	})
	
	</script>
	
	<?php
}

add_action('wp_ajax_nbfeedback_send','nbfeedback_send');
function nbfeedback_send() {
	if (empty($_POST['message']))
		die('Your Message is empty');
 	$feed = new nbFeedbackSubmit();
 	$feed->setMessage($_POST['message']);
 	$feed->setFeedbackType($_POST['type']);
 	if( $feed->send() )
 		echo 'ok';
 	else echo 'Error '.$feed->getErrno().': '.$feed->getErrorMessage();

	die();
}
?>
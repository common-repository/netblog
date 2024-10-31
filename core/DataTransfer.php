<?php
class DataTransfer { 
	/**
	 * Retrive header and content of a given URL.
	 * This method either requires curl or fopen wrappers.
	 * @param string $url
	 * @return ArrayObject
	 */
	public static function RetrieveUrl( $url ) {
		$out = array('errno'=>null,'error'=>null,'content'=>null);
		if(function_exists('curl_init')) {
			$ch = curl_init();
			$options = array(
				CURLOPT_URL			   => $url,
		        CURLOPT_RETURNTRANSFER => true,     // return web page
		        CURLOPT_HEADER         => false,    // don't return headers
		        CURLOPT_ENCODING       => "",       // handle all encodings
		        CURLOPT_USERAGENT      => $_SERVER['HTTP_USER_AGENT'], // who am i
		        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
		        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
		        CURLOPT_TIMEOUT        => 120,      // timeout on response
		    );
		    curl_setopt_array($ch,$options);	
		    $data = self::curl_exec_follow($ch, 10);		
		    $out  = curl_getinfo( $ch );
		    $out['errno'] = curl_errno( $ch );
		    $out['error'] = curl_error( $ch );
		    $out['content'] = $data;
		    curl_close( $ch );
		} else if(ini_get('allow_url_fopen') == '1') {
			$out['content'] = file_get_contents($url);
		}
		return $out;
	}
	
	/**
	 * Submit post safely either with curl or fopen-wrappers.
	 * @param string $url
	 * @param mixed $fields
	 * @return array with [errno],[error],[content]
	 */
	public static function SubmitPost( $url, $fields ) {
		$out = array('errno'=>null,'error'=>null,'content'=>null);
		if(is_array($fields))
			$fields = http_build_query($fields);
		
		if(function_exists('curl_init')) {
			$ch = curl_init();
			$options = array(
				CURLOPT_POST			=> true,
				CURLOPT_POSTFIELDS		=> $fields,
				CURLOPT_URL			  	=> $url,
		        CURLOPT_RETURNTRANSFER 	=> true,     // return web page
		        CURLOPT_HEADER         	=> false,    // don't return headers
		        //CURLOPT_ENCODING       => "",       // handle all encodings
		        CURLOPT_USERAGENT      	=> $_SERVER['HTTP_USER_AGENT'], // who am i
		        //CURLOPT_AUTOREFERER    => true,     // set referer on redirect
		        CURLOPT_CONNECTTIMEOUT 	=> 120,      // timeout on connect
		        CURLOPT_TIMEOUT        	=> 120,      // timeout on response
		    );
		    curl_setopt_array($ch,$options);
		    $data = self::curl_exec_follow($ch, 10);
		    $out  = curl_getinfo( $ch );
		    $out['errno'] = curl_errno( $ch );
		    $out['error'] = curl_error( $ch );
		    $out['content'] = $data;

		    curl_close( $ch );
	    } else if(ini_get('allow_url_fopen') == '1') {
	    	$context = stream_context_create( 
						array('http'=>array(
								    'method'=>"POST",
								    'header'  => "Content-type: application/x-www-form-urlencoded\r\n", 
	     							'content' => $fields, 
							       )
						)
				 );
			$r = @fopen( $url, "r", null, $context );
			if($r) {
				$out['content'] = stream_get_contents($r);
				fclose($r);			
			}
	    } else {
	    	$out['errno'] = 404;
	    	$out['error'] = "Missing Curl or enabled fopen wrappers in current PHP configuration/installation; required to communicate with remote server";
	    }
		return $out;
	}
	
	public static function hasError($data) {
		return !($data['errno']==null||$data['errno']==0);
	}
	
	/**
	 * 
	 * @param array $ch Curl resource (from curc_init())
	 * @param int $maxredirect Number of maximal redirects
	 */
	private static function curl_exec_follow($ch, $maxredirect = 5) {
	    $mr = $maxredirect;
	    if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
	        curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
	    } else {
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	        if ($mr > 0) {
	            $newurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
	
	            $rch = curl_copy_handle($ch);
	            curl_setopt($rch, CURLOPT_HEADER, true);
	            curl_setopt($rch, CURLOPT_NOBODY, true);
	            curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
	            curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
	            do {
	                curl_setopt($rch, CURLOPT_URL, $newurl);
	                $header = curl_exec($rch);
	                if (curl_errno($rch)) {
	                    $code = 0;
	                } else {
	                    $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
	                    if ($code == 301 || $code == 302) {
	                        preg_match('/Location:(.*?)\n/', $header, $matches);
	                        $newurl = trim(array_pop($matches));
	                    } else {
	                        $code = 0;
	                    }
	                }
	            } while ($code && --$mr);
	            curl_close($rch);
	            if (!$mr)
	            	return false;
	            curl_setopt($ch, CURLOPT_URL, $newurl);
	        }
	    }
	    return curl_exec($ch);
	} 
}
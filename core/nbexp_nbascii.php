<?php
class nbexp_nbascii extends nbexp {
	function getName() { return "Netblog Ascii"; }
	
	function getBuildNo() { return "1.0"; }
	
	function getReleaseDate() { return "Feb 6, 2011"; }
	
	function getAuthorName() { return "Benjamin Sommer"; }
	
	function getAuthorURI() { return "http://netblog.benjaminsommer.com/"; }
		
	function exportData ( $post = null, $name = '', $savePath = null ) {
		if($post==null) $post = $_POST;
				
		$xml = new SimpleXMLElement('<NETBLOG/>');
		$xml->addAttribute('plugin', nbexp::getPluginCodenm(get_class($this)) );
		$xml->addAttribute('pluginName',$this->getName());
		$xml->addAttribute('pluginBuild',$this->getBuildNo());
		$xml->addAttribute('exportTime',time());
		
		
		$users = array();
		
		/* LINKS */
		$pout = nbdb::rsc_getPostsExternLinks();
		$pin = nbdb::rsc_getPostsInternLinks('both');
		$ps = $pout;
		foreach($pin as $k=>$v)
			$ps[$k] = isset($ps[$k]) ? $ps[$k]+$v : $v; 
		
		$xps = $xml->addChild('POSTS');
		foreach($ps as $pid=>$count) {
			$xp = $xps->addChild('POST');
			if(!nbdb::footprt_hasFootprint($pid) || strlen(nbdb::footprt_getID($pid))==0)
				continue;
				
			$xp->addAttribute('footprint', nbdb::footprt_getID($pid));
			$p = &get_post($pid,OBJECT);
			$xp->addAttribute('title',$p->post_title);
			$xp->addAttribute('date',$p->post_date);
			$usr = get_userdata($p->post_author);
			if(!isset($users[$p->post_author]))	{		
				$users[$p->post_author]['tempid'] = count($users);
				$users[$p->post_author]['object'] = $usr;
			}
			$xp->addAttribute('author',$users[$p->post_author]['tempid']);
			$xp->addAttribute('type', $p->post_type);	
			$xp->addAttribute('wpid',$pid);		
			
			// INTERNAL OUTGOING
			$out = nbdb::rsc_getAdjs($pid,false,OBJECT);
			if($out!=null && is_array($out))
			foreach($out as $to) {
				$t = $xp->addChild('OUTLINK');
				$t->addAttribute('footprint', nbdb::footprt_getID($to->ID));
			}
			
			// INTERNAL INCOMING
			$in = nbdb::rsc_getParents($pid,false,OBJECT);
			if($in!=null && is_array($in))
			foreach($in as $to) {
				$t = $xp->addChild('INLINK');
				$t->addAttribute('footprint', nbdb::footprt_getID($to->parID));
			}
			
			// EXTERNAL
			$ext = nbdb::rsc_getAdjs($pid,true,OBJECT);
			if($ext!=null && is_array($ext))
			foreach($ext as $to) {
				$t = $xp->addChild('EXTERN');
				$t->addAttribute('uri', $to->uri);
				$t->addAttribute('title', $to->uri_title);
				$t->addAttribute('flag', $to->flag);
				$t->addAttribute('footprint', $to->footprint);
			}
		}
		
		/* BIBLIOGRAPHY */
		
		
		/* CAPTIONS */
		
		
		/* FOOTNOTES */
		
		
		/* SETTINGS */
		$s = Netblog::options()->getAll();
		$xo = $xml->addChild('OPTIONS');
		foreach($s as $nm=>$vl) {
			if(!is_string($vl))
				$vl = serialize($vl);
			$t = $xo->addChild('OPTION');
			$t->addAttribute('name',$nm);
			$t->addAttribute('value',$vl);
		}
		
		/* USERS */
		$xu = $xml->addChild('USERS');
		foreach($users as $uid=>$ar) {
			$t = $xu->addChild('USER');
			$t->addAttribute('num',$ar['tempid']);
			$o = $ar['object'];
			$t->addAttribute('nickname', $o->nickname);
			$t->addAttribute('url', $o->user_url);
			$t->addAttribute('wpid', $uid);
		}
		
		/* FLAG IDS */
		$xf = $xml->addChild('FLAGS');
		
		$out = $xml->asXML();
		if($post['encryption']!='none'){
			if(strlen($post['encr_key'])<5)
				throw new Exception('Secret key is too short');

			if(!nbExportScheduler::IsRunning())		// scheduled exports stored encrypted keys!
				$post['encr_key'] = md5($post['encr_key']);
			$key = $post['encr_key'];
			$td = mcrypt_module_open($post['encryption'], '', 'ecb', '');
		    $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		    mcrypt_generic_init($td, $key, $iv);
		    $encrypted_data = mcrypt_generic($td, $out);
		    mcrypt_generic_deinit($td);
		    mcrypt_module_close($td);
		    $out = $post['encryption'].'.'.$encrypted_data;
		}
		
		if($savePath!=null)
			file_put_contents($savePath,$out,LOCK_EX);
		
		$this->storeSettings($name,$post);
		
		return $out;
	}
	
	function importData( $data, $post = null ) {
		if($post==null) $post = $_POST;
		throw new Exception('Import Method not implemented');
		return;
	}
	
	function getExportOptions() {
		$t = array(
				'Include Data' => array(
						'' => array(/*'checkbox|name:data_bib|value:true|label:Bibliography',
										'checkbox|name:data_capt|value:true|label:Captions',
										'checkbox|name:data_foot|value:true|label:Footnotes',*/
										'checkbox|checked|name:data_linkout|value:true|label:Links from Further Reading',
										'checkbox|checked|name:data_linkin|value:true|label:Links from Referenced By',
										'checkbox|name:data_opts|value:true|label:Plugin Settings')
						)
			);	
		$enc = array('none'=>'none>No Encryption');
		if(function_exists('mcrypt_module_open')) {
			$tt = mcrypt_list_algorithms();
			if(($k=array_search('rijndael-256',$tt))!==false) {
				$enc[] = "rijndael-256>AES 256-bit";
				unset($tt[$k]);
			}
			if(($k=array_search('rijndael-192',$tt))!==false) {
				$enc[] = "rijndael-192>AES 192-bit";
				unset($tt[$k]);
			}
			if(($k=array_search('rijndael-128',$tt))!==false) {
				$enc[] = "rijndael-128>AES 128-bit";
				unset($tt[$k]);
			}
			if(($k=array_search('serpent',$tt))!==false) {
				$enc[] = "serpent>SERPENT";
				unset($tt[$k]);
			}
			if(($k=array_search('twofish',$tt))!==false) {
				$enc[] = "twofish>TWOFISH";
				unset($tt[$k]);
			}
			sort($tt);
			foreach($tt as $n){
				if($n=='wake' || $n=='rc4') continue;
				$enc[] = "$n>$n";
			}
		} else if(function_exists('mcrypt_cbc')) {
			$enc[] = 'MCRYPT_3DES>MCRYPT_3DES';
		}
		if(sizeof($enc)>1) 
			$t['Encryption'] = array(
						'' => array('select|name:encryption|options:'.implode('@',$enc)),
						'Secret Key' => array('password|name:encr_key')
						);
		else {
			$t['Encryption'] = array(
						'' => array('legend|display:Not supported (missing libmcrypt 2.2+)')
					);	
		}
		return $t;	
	}
	
	function getImportOptions( $data, $post = null, &$isComplete ) {
		if($post==null) $post = $_POST;
		
		$r = $this->decrypt($data,$post,$isComplete,$cipher);
		if(is_array($r)) return $r;
		
		$xml = new SimpleXMLElement($data);		
		
		$d = array(); $tinclOut = array(); $tinclIn = array(); $tinclExt = array(); $tusers = array(); $postsIds = array(); $flags = array();
		$xposts = $outlinks = $inlinks = $extlinks = $options = 0;
		if(isset($xml->FLAGS) && isset($xml->FLAGS->FLAG))
		foreach($xml->FLAGS->FLAG as $xflags) {
			$flags[$xflags['id']] = $xflags;
		}
		if(isset($xml->POSTS) && isset($xml->POSTS->POST))
		foreach($xml->POSTS->POST as $xpost) {
			if(isset($xpost['footprint']) && isset($xpost['title']))
				$postsIds[(string)$xpost['footprint']] = $xpost['title'];
		}
		if(isset($xml->POSTS) && isset($xml->POSTS->POST))
		foreach($xml->POSTS->POST as $xpost) {
			if(isset($xpost->OUTLINK))			
			foreach($xpost->OUTLINK as $outlink) {
				$tp = $this->findPost($xml,$outlink['footprint']);
				$title = $tp!=null ? $tp['title'] : '(broken footprint id '. (string)$outlink['footprint'].')';
				$tinclOut[htmlspecialchars((string)$xpost['title']).'<br />&nbsp;'][] = 
					'checkbox|checked|name:outlink_'.$outlinks.'|label:'.htmlspecialchars($title);
				$outlinks++;
			}
			if(isset($xpost->INLINK))
			foreach($xpost->INLINK as $inlink) {
				if(!isset($inlink['footprint']) || strlen($inlink['footprint'])==0) continue;
				$tp = $this->findPost($xml,$inlink['footprint']);
				$title = $tp!=null ? $tp['title'] : '(broken footprint id '. $inlink['footprint'].')';
				$tinclIn[htmlspecialchars($xpost['title']).'<br />&nbsp;'][] = 
					'checkbox|checked|name:inlink_'.$inlinks.'|label:'.htmlspecialchars($title);
				$inlinks++;
			}
			if(isset($xpost->EXTERN))
			foreach($xpost->EXTERN as $extern) {
				$tf = '';
				if(isset($flags[(string)$extern['flag']])) {
					$tf = '('.$flags[$extern['flag']]['codenm'].')';
				}
				$tinclExt[htmlspecialchars($xpost['title']).'<br />&nbsp;'][] = 
					'checkbox|checked|name:extlink_'.$extlinks.'|label:'.htmlspecialchars($extern['title'].' <small>'.$extern['uri'].'</small>').' '.$tf;
				$extlinks++;
			}
			$xposts++;
		}
		if(isset($xml->OPTIONS) && isset($xml->OPTIONS->OPTION))
		foreach($xml->OPTIONS->OPTION as $option) {
			$options++;
		}
		if(isset($xml->USERS) && isset($xml->USERS->USER)) {
			$tusers = array();
			foreach($xml->USERS->USER as $usr) {
				$tusers['Users']['Import By User'][] = 'checkbox|checked|name:import_user_'.$usr['num'].'|label:'.(strlen($usr['nickname'])>0 ? $usr['nickname'] : 'User #'.$usr['num'].' ');
			}
			$tusers['Users']['Action on Unavailable User'][] = 'select|name:import_users_unavail|options:nothing>Do Nothing@create>Create Missing User@'.
																'usrx>Assign to ...';
		}
		
		$tincl = array();		
		if(isset($post['adv_import']) && $post['adv_import']=='true') {
			if($outlinks>0) { 
				$tincl['Outgoing Links'][''][] = 'checkbox|checked|name:data_linkout|value:true|label:Include';
				foreach($tinclOut as $k=>$v){
					$tincl['Outgoing Links'][$k] = $v;
				}
			}
			if($extlinks>0) { 
				$tincl['External Links'][''][] = 'checkbox|checked|name:data_linkext|value:true|label:Include';
				foreach($tinclExt as $k=>$v){
					$tincl['External Links'][$k] = $v;
				}
			}
			if($inlinks>0) { 
				$tincl['Incoming Links'][''][] = 'checkbox|checked|name:data_linkin|value:true|label:Include';
				foreach($tinclIn as $k=>$v){
					$tincl['Incoming Links'][$k] = $v;
				}
			}
			if($options>0) {
				$tincl['Plugin Settings'][''][] = 'checkbox|checked|name:data_opts|value:true|label:Include All ('.$options.' Options)';
			}
		} else {
			$incl = array();
			if($outlinks>0) 
				$incl[] = "checkbox|checked|name:data_linkout|value:true|label:Links from Further Reading ($outlinks)";
			if($inlinks>0)
				$incl[] = "checkbox|checked|name:data_linkin|value:true|label:Links from Referenced By ($inlinks)";
			if($extlinks>0)
				$incl[] = "checkbox|checked|name:data_linkext|value:true|label:External Links ($extlinks)";
			if($options>0)
				$incl[] = "checkbox|checked|name:data_opts|value:true|label:Plugin Settings ($options)";
			if(sizeof($incl)==0)
				$incl[] = "legend|display:No Data found";
			$tincl = array('Include Data' => array('' => $incl));
		}
		$t = $tincl;
		
		$t = array_merge($t,$tusers);
		
		$t['Import Method'] = array(
						'' => array('select|name:import_method|options:append>Append Non-Existant Only@overwrite>Overwrite Existant Only@incr>Increment (Overwrite+Append)@decr>Decrement (Erase Existant)!'),
						'Simulate Import' => array('checkbox|name:simulate|value:true|label:Enable')
						);
		if(strlen($cipher)>0)
		$t['Encryption'] = array(
						'' => array('legend|display:Detected: '.$cipher,
									'hidden|name:encr_key|value:'.$post['encr_key'],
									'hidden|name:adv_import|value:'.$post['adv_import'])
						);
			
		return $t;
	}
	
	
	/**
	 * Decrypt exported data
	 *
	 * @param string $data
	 * @param array $post
	 * @param bool $isComplete
	 * @param string $cipher
	 * @return array|null
	 */
	function decrypt( &$data, $post, &$isComplete, &$cipher ) {
		$isComplete = false;
		if(strlen($data)==0)
			throw new Exception('No data given to decrypt');
			
		if(substr($data,0,1)!='<') {
			$cipher = substr($data,0,($pcipher=strpos($data,'.')));
			if(function_exists('mcrypt_module_open')) {
				$tt = mcrypt_list_algorithms();
				if(($k=array_search($cipher,$tt))===false)
					throw new Exception('Unsupported encryption method ("'.$cipher.'" by libmcrypt)');

				if(isset($post['encr_key']) && strlen($post['encr_key'])>0) {
					$td = mcrypt_module_open($cipher, '', 'ecb', '');
				    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
				    if( mcrypt_generic_init($td, md5($post['encr_key']), $iv)!=-1 ) {
					    $data = mdecrypt_generic($td, substr($data,$pcipher+1));
					    mcrypt_generic_deinit($td);
					    mcrypt_module_close($td);
				    } else {
				    	throw new Exception('Error while decrypting data');
				    }
				} else {
					return array(
						'Encryption' => array(
								'' => array('legend|display:Detected: '.$cipher),
								'Secret Key' => array('password|name:encr_key')
								),
						'Advanced Import' => array(
								'' => array('checkbox|name:adv_import|value:true|label:Enable')
								),
					);
				}
			} else {
				throw new Exception('Missing libmcrypt 2.4+');
			}
		} else {
			
		}
		if(substr($data,0,1)!='<') {
			throw new Exception('Cannot read data');
		}
		$isComplete = true;
		return null;
	}
	
	/**
	 * Find post for given footprint id
	 *
	 * @param SimpleXMLElement $xml
	 * @param string $footprint
	 * @return SimpleXMLElement|null
	 */
	function findPost( &$xml, $footprint ) {
		if(isset($xml->POSTS) && isset($xml->POSTS->POST))
		foreach($xml->POSTS->POST as $xpost) {
			if((string)$xpost['footprint']==(string)$footprint)
				return $xpost;
		}
		return null;
	}
}
?>
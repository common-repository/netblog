<?php
require_once 'nbExportScheduler.php';

/**
 * Base class for netblog export modules
 *
 */
abstract class nbexp {

	abstract protected function getName();
	
	abstract protected function getBuildNo();
	
	abstract protected function getReleaseDate();
	
	abstract protected function getAuthorName();
	
	abstract protected function getAuthorURI();
		
	abstract protected function exportData ( $post = NULL, $name = '', $savePath = null );
	
	abstract protected function importData( $data, $method = 'overwrite' );
	
	abstract protected function getExportOptions();
	
	abstract protected function getImportOptions( $data, $post = null, &$isComplete );
	

	/**
	 * Get array of installed export/import plugins
	 *
	 * @return array An array of plugin code-names
	 */
	static function getInstalledPlugins( $details = true ) {
		$o = array();
		$d = scandir( dirname(__FILE__) );
		foreach($d as $f) {
			if( substr($f,0, 6) == 'nbexp_' && strlen($p=substr($f,6,-4)) > 0 ) {
				if($details) {
					try {
						$pl = self::loadPlugin($p);
						if($pl==null)
							throw new Exception("Failed to load plugin $p");
						$t['name'] = $pl->getName();
						$t['build'] = $pl->getBuildNo();
						$t['daterl'] = $pl->getReleaseDate();
						$t['author'] = $pl->getAuthorName();
						$t['codenm'] = $p;
						$t['status'] = 'ok';
						$o[] = $t;
					} catch(Exception $e) {
						$t['status'] = 'With error: '.$e->getMessage();
						$t['codenm'] = $p;
						$o[] = $t;
					}
				} else if(self::loadPlugin($p) !== null)
					$o[] = $p;
			}
		}		
		return $o;
	}
	
	/**
	 * Load Export/Import plugin for given codename.
	 *
	 * @param string $name
	 * @return nbexp_nbascii
	 */
	static function loadPlugin( $name ) {
		if( is_string($name) && strlen($name)>0 && file_exists($p=(dirname(__FILE__)."/nbexp_$name.php")) ) {
			require_once $p;
			$c = "nbexp_$name";
			if(class_exists($c)) {
				try {
					$t = new $c;
					return $t;
				} catch (Exception $e) {
					Netblog::log('Cannot load plugin "'.$name.'": '.$e->getMessage());
				}
			} else {
				Netblog::log('Cannot load plugin "'.$name.'"');
				return null;
			}
		} else return null;
	}
	
	/**
	 * Get Plugin codename from its classname
	 *
	 * @param string $classnm
	 * @return string|null
	 */
	static function getPluginCodenm( $classnm ) {
		if( ($p=strpos($classnm,'nbexp_'))!==false )
			return substr($classnm,$p);
		return null;		
	}
		
	/**
	 * Store Settings with nbExportScheduler
	 *
	 * @param string $name
	 * @param array $post
	 */
	protected function storeSettings( $name, $post ) {
		if(isset($post['automation']) && $post['automation']=='true' && strlen($name)>0) {
			Netblog::log('Saving export schedule "'.$name.'"');
			$pilot = new nbTestPilot();	
			$sched = new nbExportScheduler();
			if(isset($post['export_schedule_id']) && ($item=&$sched->getItemById($post['export_schedule_id']))!=null ) {
				if($item->settings != ($hbc=http_build_query($post))) {
					$item->settings = $hbc;
					$pilot->exportSchedulerChange($post['export_schedule_id'],$item->name);
				}
			} else {
				$item = new nbExportItemScheduled($name);
				$post['export_schedule_id'] = $item->getId();
				$item->settings = http_build_query($post);
				$sched->addItem($item);
				$pilot->exportSchedulerAdd($item->getId(),$name);
			}
			$pilot->save();
			$sched->save();
		}
	}
}

?>
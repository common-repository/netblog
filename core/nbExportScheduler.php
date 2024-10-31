<?php
require_once 'nbExportItemScheduled.php';

/**
 * Export Scheduler to manage all schedules, known as nbExportItemScheduled
 *
 * @since Netblog 2.0
 */
class nbExportScheduler {
	
	/**
	 * Constructor and auto load data from nboption
	 *
	 */
	public function __construct() {
		$stored = Netblog::options()->getExportSettingsStorage();
		if(is_array($stored))
		foreach($stored as $item) {
			$s = new nbExportItemScheduled();
			if( $s->parseFromStorage($item) )
				$this->items[] = $s;
		}
	}
	
	/**
	 * Save scheduler to nboption
	 *
	 */
	public function save() {
		$o = array();
		$nextSched = -1;
		foreach($this->items as $item) {
			if( $nextSched < 0 || $nextSched > $item->getNextSchedule() )
				$nextSched = $item->getNextSchedule();
			$o[] = $item->getAsArray();
		}
		$nextSched = max(0,$nextSched);
		Netblog::options()->setExportSchedulerNextTime($nextSched);
		Netblog::options()->setExportSettingsStorage($o);
	}
	
	
	/**
	 * Add a scheduled item
	 *
	 * @param nbExportItemScheduled $item
	 */
	public function addItem( $item ) {
		$this->items[] = $item;
	}
	
	/**
	 * Get an item
	 *
	 * @param int $i
	 * @return nbExportItemScheduled|null
	 */
	public function getItem($i) {
		if($i>=0 && $i<sizeof($this->items))
			return $this->items[$i];
		return null;
	}
	
	/**
	 * Get an item by its id
	 *
	 * @param int|string $id
	 * @return nbExportItemScheduled|null
	 */
	public function &getItemById($id) {
		foreach($this->items as $item)
			if($item->getId() == $id)
				return $item;
		return null;
	}
	
	/**
	 * Remove an item
	 *
	 * @param int $i
	 * @return bool
	 */
	public function removeItem($i) {
		if($i>=0 && $i<sizeof($this->items)) {
			$this->items[$i]->archive();
			unset($this->items[$i]);
			return true;
		}
		return false;
	}
		
	/**
	 * Remove an item by its id
	 *
	 * @param int|string $id
	 * @return bool
	 */
	public function removeItemById($id) {
		foreach($this->items as $i=>$item)
			if($item->getId() == $id) {
				$this->items[$i]->archive();
				unset($this->items[$i]);
				return true;
			}
		return false;
	}

	/**
	 * Get number of items in scheduler
	 *
	 * @return int
	 */
	public function numItems() {
		return sizeof($this->items);
	}
	
	/**
	 * Run schedules that are in the past but have not been performed yet 
	 * (due to php and script nature, no exact timing can be supported directly with php)
	 * Therefore, this method have to be called every time in admin area (see polling methods)
	 *
	 */
	public function runSchedules() {
		try {
			//Netblog::log("Running Export Scheduler");
			self::$running = true;
			foreach($this->items as $item)
				$item->run();
			self::$running = false;
			$this->save();
		} catch (Exception $e) {
			Netblog::logError($e->getMessage());
		}
	}
	
	/**
	 * Get backup location for scheduled exports
	 *
	 * @param int|optional $scheduleId If 0, method returns parent location containing all backups
	 * @return string
	 */
	static function getBackupLocation($scheduleId = 0) {
		return dirname(__FILE__).'/../data/exportSchedulerBac/'.($scheduleId > 0 ? "$scheduleId/" : '');
	}
	
	static function IsRunning() { return  self::$running; }
	
	/**
	 * List of items
	 *
	 * @var nbExportItemScheduled[]
	 */
	private $items = array();
	static private $running = false;
}
?>
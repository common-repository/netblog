<?php
require_once 'timeSpan.php';

/**
 * Scheduled item for export scheduler
 *
 */
class nbExportItemScheduled {
	
	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param string $settings
	 * @param string $scheduleType Any of 'once' || 'every'
	 * @param timestamp|seconds $scheduleTime The timestamp for 'once', the time interval in seconds for 'every'
	 * @param timestamp $scheduleTimeStart The start of scheduling; if 0, start equals beginning of timestamp
	 * @param timestamp $scheduleTimeEnd The end of scheduling; if 0, scheduling is unlimited
	 */
	public function __construct($name = '', $settings = '', $scheduleType = 'once', $scheduleTime = 0, $scheduleTimeStart = 0, $scheduleTimeEnd = 0) {
		$this->name = $name;
		$this->settings = $settings;
		$this->scheduleType = $scheduleType;
		$this->scheduleTime = $scheduleTime;
		$this->scheduleTimeStart = $scheduleTimeStart;
		$this->scheduleTimeEnd = $scheduleTimeEnd;
		$this->id = time();
		$this->verifySchedule();
		$this->calculateSchedule();
	}
	
	/**
	 * Calculate the schedule timings.
	 * ONLY CALL THIS METHOD IF PARAMETER CHANGED!
	 *
	 */
	public function calculateSchedule() {
		if($this->scheduleType == 'once') {
			$this->nextSchedule = $this->scheduleTime > time() ? $this->scheduleTime : 0;
		} else if($this->scheduleTime > 0) {
			$this->nextSchedule = $this->scheduleTime - ((time()-$this->scheduleTimeStart)%$this->scheduleTime) + time();
			if( $this->scheduleTimeEnd > 0 && $this->nextSchedule > $this->scheduleTimeEnd )
				$this->nextSchedule = 0;
		}
	}
	
	/**
	 * Get next schedule as a timestamp, in seconds.
	 * This maybe in the past if the schedule is overdue.
	 *
	 * @return timestamp
	 */
	public function getNextSchedule() {
		return $this->nextSchedule;
	}
	
	/**
	 * Get an array formatted for storage by nboption.
	 *
	 * @return array
	 */
	public function getAsArray() {
		$a = array();
		$a['name'] = $this->name;
		$a['settings'] = $this->settings;
		$a['scheduleType'] = $this->scheduleType;
		$a['scheduleTime'] = $this->scheduleTime;
		$a['scheduleTimeStart'] = $this->scheduleTimeStart;
		$a['scheduleTimeEnd'] = $this->scheduleTimeEnd;
		$a['id'] = $this->id;
		$a['scheduleNext'] = $this->nextSchedule;
		$a['schedulesPast'] = $this->pastSchedules;		
		return $a;
	}
	
	/**
	 * Parse array from storage by nboption.
	 *
	 * @param array $stored
	 * @return bool
	 */
	public function parseFromStorage( $stored ) {
		$t = array('name','settings','scheduleType','scheduleTime','scheduleTimeStart','scheduleTimeEnd');
		foreach($t as $k)
			if(!isset($stored[$k]))
				return false;
				
		$this->name = $stored['name'];
		$this->settings = $stored['settings'];
		$this->scheduleType = $stored['scheduleType'];
		$this->scheduleTime = $stored['scheduleTime'];
		$this->scheduleTimeStart = $stored['scheduleTimeStart'];
		$this->scheduleTimeEnd = $stored['scheduleTimeEnd'];
		$this->id = $stored['id'];
		//$this->verifySchedule();
		$this->nextSchedule = isset($stored['scheduleNext']) && $stored['scheduleNext'] > 0 ? $stored['scheduleNext'] : $this->calculateSchedule(); 
		$this->pastSchedules = isset($stored['schedulesPast']) ? $stored['schedulesPast'] : array();
		return true;
	}
	
	/**
	 * Get this item in xml format
	 *
	 * @param SimpleXMLElement $xmlElem
	 */
	public function asXML( &$xmlElem ) {
		if($xmlElem==null) return;
		$timezone = ini_get('date.timezone');
		date_default_timezone_set($timezone);
		
		$xitem = $xmlElem->addChild('item');
		$xitem->addAttribute('id',$this->id);
		$xitem->addAttribute('name',$this->name);
		$xitem->addAttribute('settings',$this->settings);
		$xitem->addAttribute('scheduleType',$this->scheduleType);
		$xitem->addAttribute('scheduleTime',$this->scheduleTime);
		$timeNice = '';
		if($this->scheduleTime <= 0 ) {
			$timeNice = 'never';
		} else if($this->scheduleType == 'every') {
			$span = new timeSpan($this->scheduleTime);
			$timeNice = $span->getFormatted(true,3);
		} else {
			$timeNice = date('r',$this->scheduleTime);
		}
		$xitem->addAttribute('scheduleTimeNice', $timeNice );
		$xitem->addAttribute('scheduleTimeStart',$this->scheduleTimeStart);
		$xitem->addAttribute('scheduleTimeStartNice', $this->scheduleTimeStart > 0 ? date('r',$this->scheduleTimeStart) : 'never');
		$xitem->addAttribute('scheduleTimeEnd',$this->scheduleTimeEnd);
		$xitem->addAttribute('scheduleTimeEndNice', $this->scheduleTimeEnd > 0 ? date('r',$this->scheduleTimeEnd) : 'never');
		
		$xitem->addAttribute('scheduleNext',$this->nextSchedule);		
		$span = new timeSpan($this->nextSchedule-time());
		$xitem->addAttribute('scheduleNextNice', $this->nextSchedule > 0 ? 'In '.$span->getFormatted(true,2) : 'Never Run');
		
		$xpast = $xitem->addChild('pastSchedules');
		foreach($this->pastSchedules as $time) {
			$t = $xpast->addChild('pastSchedule');
			$t->addAttribute('time',$time);
		}
	}
	
	/**
	 * Archive this schedule
	 *
	 */
	public function archive() {
		$xml = new SimpleXMLElement('<ExportScheduleArchive/>');
		$this->asXML($xml);
		if( !is_dir($path=nbExportScheduler::getBackupLocation($this->id)) )
			mkdir($path,0777,true);
		$xml->asXML($path.time().'.setting.xml');
	}
	
	/**
	 * Verify parameter for schedule; make them ready for proper and save storage.
	 * Always call this method on changes before saving
	 *
	 */
	public function verifySchedule() {
		if( strlen($this->name=trim($this->name))==0 )
			$this->name = "item_$this->id";
		if($this->scheduleType == 'periodic')
			$this->scheduleType = 'every';
		else if($this->scheduleType != 'once' && $this->scheduleType != 'every')
			$this->scheduleType = 'once';
		
		//$timezoneOld = date_default_timezone_get();
		$timezone = ini_get('date.timezone');
		date_default_timezone_set($timezone);
	
		if( !ctype_digit($this->scheduleTime) ) {
			$this->scheduleTime = $this->parseTimeString($this->scheduleTime, $this->scheduleType=='once' ? 'date' : 'interval');
		}
		if( !ctype_digit($this->scheduleTimeStart)) {
			$this->scheduleTimeStart = $this->parseTimeString($this->scheduleTimeStart,'date');
		}
		if( !ctype_digit($this->scheduleTimeEnd)) {
			$this->scheduleTimeEnd = $this->parseTimeString($this->scheduleTimeEnd,'date');
		}
		
		//date_default_timezone_set($timezoneOld);
	}
	
	/**
	 * Parse a time string either for a fixed date or for an time interval
	 *
	 * @param string $timeStr
	 * @param string $type Either 'date' || 'interval'
	 * @return timestamp|seconds
	 */
	public function parseTimeString( $timeStr, $type ) {
		if($type == 'date') {
			if( ($time=strtotime($timeStr))===false || $time==-1 )
				return 0;
			else return $time;
		} else if($type == 'interval') {
			if(substr($timeStr,0,1)!='+') 
				$timeStr = "+$timeStr";
			if( ($time=strtotime($timeStr))===false || $time==-1 )
				return 0;
			else return $time - time();
		} else return 0;
	}
	
	/**
	 * Get this id.
	 *
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Run this schedule. 
	 * This method does not check, whether the next schedule is smaller or equal to current time
	 * so that a schedule can be forced to run, even if the timing is still in the future.
	 *
	 * @param bool $force TRUE if to run the task even if it is not scheduled for now
	 */
	public function run($force = false) {
		if(($force || $this->nextSchedule <= time()) && is_int($this->nextSchedule) && $this->nextSchedule > 0) {
			require_once 'nbexp.php';
			if( ($plugin=nbexp::loadPlugin($this->getPluginCodename()))!=null ) {
				parse_str(html_entity_decode($this->settings),$post);
				
				if( !is_dir($path=nbExportScheduler::getBackupLocation($this->id)) )
					mkdir($path,0777,true);
					
				require_once 'nbTestPilot.php';
				$pilot = new nbTestPilot();
				$pilot->exportModule($plugin->getName(), $this->getPluginCodename(), $plugin->getBuildNo(), $plugin->getAuthorURI(), true);
				
				$plugin->exportData($post, $this->name, $location=($path.sizeof($this->pastSchedules)."-$this->name.bac") );
				$this->pastSchedules[] = $this->nextSchedule;
				if(sizeof($this->pastSchedules)>$this->max_history)
					array_shift($this->pastSchedules);
				$this->calculateSchedule();
				Netblog::logSuccess("Completed scheduled export \"$this->name\". Path: $location");
			} else {
				Netblog::logError('Illegal plugin name "'.$this->getPluginCodename().'" while running export schedule "'.$this->name.'"');
			}
		}		
	}
	
	/**
	 * Get the history of a schedule
	 *
	 * @return array
	 */
	public function getHistory() {
		return $this->pastSchedules;
	}
	
	/**
	 * Get codename for the export plugin, with its settings being scheduled here.
	 *
	 * @return string
	 */
	public function getPluginCodename() {
		parse_str(html_entity_decode($this->settings),$set);
		if(!isset($set['plugin']))
			Netblog::log('EXPORT ITEM SCHEDULED missing plugin name');
		return isset($set['plugin']) ? $set['plugin'] : '';
	}
	
	/**
	 * Name of scheduled item
	 *
	 * @var string
	 */
	public $name = '';
	
	/**
	 * Settings generated by export plugin
	 *
	 * @var string
	 */
	public $settings = '';
	
	/**
	 * Type of scheduler: once || every 
	 *
	 * @var string
	 */
	public $scheduleType = 'once';
	
	/**
	 * Timestamp for scheduler.
	 * For scheduler type 'once', this is the time when to schedule task.
	 * For scheduler type 'every', this is the time interval in seconds to calculate the next scheduling, based
	 * upon the current time and the beginning of scheduling, if defined.
	 *
	 * @var timestamp|seconds
	 */
	public $scheduleTime = 0;
	
	/**
	 * The start time of 'every' scheduler.
	 *
	 * @var timestamp
	 */
	public $scheduleTimeStart = 0;
	
	/**
	 * The end time of 'every' scheduler; if no end, then this value must be 0.
	 *
	 * @var timestamp
	 */
	public $scheduleTimeEnd = 0;
	
	/**
	 * Timestamp for next schedule. If 0, scheduling has stopped.
	 *
	 * @var timestamp
	 */
	private $nextSchedule = 0;
	
	/**
	 * The unique id, to allow multiple same names
	 *
	 * @var int
	 */
	private $id = 0;

	/**
	 * An array of past schedules, in timestamp
	 *
	 * @var array(timestamps)
	 */
	private $pastSchedules = array();
	
	private $max_history = 10;
	
}
?>
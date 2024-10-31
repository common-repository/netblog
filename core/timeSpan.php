<?php
class timeSpan {
	
	/**
	 * Constructor
	 *
	 * @param uint $seconds
	 */
	public function __construct($seconds) {
		$this->years = floor(($s=$seconds) / ($d=365*24*3600));
		$this->months = floor(($s=($s%$d)) / ($d=365/12*24*3600));
		$this->weeks = floor(($s=($s%$d)) / ($d=7*24*3600));
		$this->days = floor(($s=($s%$d)) / ($d=24*3600));
		$this->hours = floor(($s=($s%$d)) / ($d=3600));
		$this->minutes = floor(($s=($s%$d)) / ($d=60));
		$this->seconds = floor(($s=($s%$d)) / ($d=1));
	}
	
	/**
	 * Get nice formatted time intervall
	 *
	 * @param bool $longFormat
	 * @param int $maxNumEntities
	 * @param string $format
	 * @return string
	 */
	public function getFormatted( $longFormat = true, $maxNumEntities = -1 , $format = 'ymwdhns' ) {
		$f = array();
		$z = str_split($format);
		$e = array();
		foreach($z as $c) $e[$c] = $c;
		$t = $longFormat ? $this->formatLong : $this->formatShort;
		
		if($this->years > 0 && isset($e['y'])) $f[] = "$this->years ".$t['y'].($this->years>1 ? 's' : '');
		if($this->months > 0 && isset($e['m'])) $f[] = "$this->months ".$t['m'].($this->months>1 ? 's' : '');
		if($this->weeks > 0 && isset($e['w'])) $f[] = "$this->weeks ".$t['w'].($this->weeks>1 ? 's' : '');
		if($this->days > 0 && isset($e['d'])) $f[] = "$this->days ".$t['d'].($this->days>1 ? 's' : '');
		if($this->hours > 0 && isset($e['h'])) $f[] = "$this->hours ".$t['h'].($this->hours>1 ? 's' : '');
		if($this->minutes > 0 && isset($e['n'])) $f[] = "$this->minutes ".$t['n'].($this->minutes>1 ? 's' : '');
		if($this->seconds > 0 && isset($e['s'])) $f[] = "$this->seconds ".$t['s'].($this->seconds>1 ? 's' : '');
		
		for($i=sizeof($f); $i>0 && $i>$maxNumEntities && $maxNumEntities>=0; $i--)
			unset($f[$i-1]);	
		
		return implode(' ',$f);
	}
	
	private $years = 0;
	private $months = 0;
	private $weeks = 0;
	private $days = 0;
	private $hours = 0;
	private $minutes = 0;
	private $seconds = 0;
	private $formatLong = array('y'=>'Year','m'=>'Month','w'=>'Week','d'=>'Day','h'=>'Hour','n'=>'Minute','s'=>'Second');
	private $formatShort = array('y'=>'a','m'=>'m','w'=>'w','d'=>'d','h'=>'h','n'=>'min','s'=>'s');
}
?>
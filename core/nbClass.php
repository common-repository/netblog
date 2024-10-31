<?php

class nbClass { 
	/**
	 * Setter for a variable and modifies this->changed appropriately
	 * 
	 * @param mixed $var The variable to be copied
	 * @param mixed $dest The destination variable where var should be copied to.
	 */
	protected function SetVar( $var, &$dest ) {
		if($var!=$dest) {
			$dest = $var;
			$this->changed = true;
		}
	}
	
	/**
	 * Returns whether this or one of its child class has been changed. This method is used to reduce 
	 * computation costs and saves (i.e. database connections)
	 * 
	 * @return bool Returns true if class has been changed, false otherwise.
	 */
	public function HasChanged() {
		return $this->changed;
	}
	
	/**
	 * The state whether the class has been modified
	 * @var bool
	 */
	protected $changed = false;
}
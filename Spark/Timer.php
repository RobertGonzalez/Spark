<?php
/**
 * Simple PHP Application Release Kit
 * 
 * @category Spark
 * @package Spark_Library
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 */
/**
 * Timer object
 * 
 * Handles timings 
 * 
 * @category Spark
 * @package Spark_Library
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 * @version @package_version@
 */
class Spark_Timer {
	/**
	 * Timers list
	 *
	 * @access protected
	 * @var array
	 */
	protected $_timers = array();
	
	/**
	 * Timer starter for entry $name
	 *
	 * @access public
	 * @param string $name
	 */
	public function start($name = '_default') {
		$this->_mark($name, 'start');
	}
	
	/**
	 * Timer stopper for entry $name
	 *
	 * @access public
	 * @param string $name
	 */
	public function stop($name = '_default') {
		$this->_mark($name, 'stop');
	}
	
	/**
	 * Timer totalizer for entry $name
	 *
	 * @access public
	 * @param string $name
	 */
	public function total($name = '_default') {
		// If we are not started yet, make a timer of 0 times
		if (!$this->started($name)) {
			// Zero out this timer to reflect an error
			$this->_zeroTime($name);
			
			// Get back to business
			return;
		}
		
		// Stop this timer if it is not yet stopped
		$this->stop($name);
        
		// Do the math 
		$this->_timers[$name]['total'] = $this->_timers[$name]['stop'] - $this->_timers[$name]['start'];
	}
	
	/**
	 * Timer total fetcher for entry $name
	 *
	 * @access public
	 * @param string $name
	 * @return float The time, in milliseconds, of execution or false on failure
	 */
	public function getTotal($name = '_default', $precision = 0) {
		// If we are not started yet, zero out the timer
		if (!$this->started($name)) {
			$this->_zeroTime($name);
		}
		
		// If we are not totaled yet, total us up
		if (!$this->totaled($name)) {
			$this->total($name);
		}
		
		// Send back the formatted time
		return $precision ? intval(sprintf("%.{$precision}f", $this->_timers[$name]['total'])) : $this->_timers[$name]['total'];
	}
	
	/**
	 * Gets all the totals in this timer object
	 * 
	 * @access public
	 * @return array List of [timer] => [formatted total] entries
	 */
	public function getTotals() {
		// Initialize the return array
		$return = array();
		
		// Loop over all the timers, closing out and totaling all times 
		foreach ($this->_timers as $timer => $times) {
			// Total this timer
			$this->total($timer);
			
			// Get the formatted, total time for this timer
			$return[$timer] = $this->getTotal($timer);
		}
		
		// Send it back
		return $return;
	}
	
	/**
	 * Checks if the named timer has been started
	 * 
	 * @access public
	 * @param string $name The name of the timer to check
	 * @return boolean
	 */
	public function started($name) {
		return isset($this->_timers[$name]['start']);
	}
	
	/**
	 * Checks if the named timer has been stopped
	 * 
	 * @access public
	 * @param string $name The name of the timer to check
	 * @return boolean
	 */
	public function stopped($name) {
		return isset($this->_timers[$name]['stop']);
	}
	
	/**
	 * Checks if the named timer has been totaled
	 * 
	 * @access public
	 * @param string $name The name of the timer to check
	 * @return boolean
	 */
	public function totaled($name) {
		return isset($this->_timers[$name]['total']);
	}
	
	/**
	 * Magic method that dumps the list of closed timers when this object is
	 * echoed
	 * 
	 * @access public
	 * @return string
	 */
	public function __toString() {
		// If we are at the CLI we don't need the markup surrounding it
		$cli = PHP_SAPI == 'cli';
		
		// Build our output
		$out = '';
		
		// Dress it up if we are not CLI
		if (!$cli) {
			$out = '<ul>';
		}
		
		// New line it for cleanliness
		$out .= "\n";
		
		// Loop and set
		foreach ($this->getTotals() as $timer => $total) {
			// Again, window dressing for non CLI output
			if (!$cli) {
				$out .= '<li>';
			}
			
			// Timer: Total
			$out .= $timer. ': ' . $total;
			
			// Window dressing yet again
			if (!$cli) {
				$out .= '</li>';
			}
			
			// Add new lines for cleanliness
			$out .= "\n";
		}
		
		// Last bit of window dressing and new lining
		if (!$cli) {
			$out .= '</ul>';
		}
		$out .= "\n";
		
		// Return it
		return $out;
	}
	
	/**
	 * Marks a time as either a start or stop
	 * 
	 * @access protected
	 * @param string $name The name of the timer to work on
	 * @param string $mark The marker to set, either start or stop
	 */
	protected function _mark($name, $mark = 'start') {
		// Lowercase the mark type
		$mark = strtolower($mark);
		
		// If we are not a start then we are a stop
		if ($mark != 'start') {
			$mark = 'stop';
		}
		
		// Set it if it is not yet set
		if (!isset($this->_timers[$name][$mark])) {
			$this->_timers[$name][$mark] = microtime(true);
		}
	}
	
	/**
	 * Makes a 0 value set of times for a named timer
	 * 
	 * This is useful for recognizing errors in timings. If you see a zero time
	 * there is a pretty good chance that the timer was never started and was 
	 * instead totaled with a zero value.
	 * 
	 * @access protected
	 * @param string $name The name of the timer to set to zero
	 */
	protected function _zeroTime($name) {
		$this->_timer[$name] = array(
			'start' => 0,
			'stop'  => 0,
			'total' => 0,
		);
	}
}
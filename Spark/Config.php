<?php
/**
 * Simple PHP Application Release Kit
 * 
 * @category Spark
 * @package Spark
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 */
/**
 * Library configuration object
 *
 * Iterator and Countable implementer that allows the setting of configuration
 * values into a recursive cloned object for easy access to configuration 
 * elements throughout the application
 *
 * @category Spark
 * @package Spark
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 * @version @package_version@
 */
class Spark_Config implements Iterator, Countable {
	/**
	 * The config data as an array
	 *
	 * @access protected
	 * @var array
	 */
	protected $_config = array();
	
	/**
	 * The current index of the config array
	 *
	 * This is used in looping constructs when merging/appending an existing
	 * config object of this type into this object
	 *
	 * @access protected
	 * @var integer
	 */
	protected $_index = 0;
	
	/**
	 * The current count of config items
	 *
	 * @access protected
	 * @var integer
	 */
	protected $_count = 0;
	
	/**
	 * Flag that sets the overwrite rule
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $_merge = false;
	
	/**
	 * Object constructor
	 *
	 * @access public
	 * @param array $config List of config params
	 * @param boolean $merge Flag that tells the app to merge values or not
	 */
	public function __construct($config = array(), $merge = null) {
		$this->setFrom($config, $merge);
	}
	
	/**
	 * Abstracted setter method that makes a small decision as to how to load
	 * config values based on where the values are coming from.
	 *
	 * @param mixed $config The config source - could be an array, a file or an object
	 * @param boolean $merge Flag that tells the object whether to merge with existing config values
	 * @return void
	 */
	public function setFrom($config, $merge = null) {
		$this->setFromArray($this->_getFrom($config), $merge);
	}
	
	/**
	 * Array setter method
	 *
	 * @access public
	 * @param array $config List of config params
	 * @param boolean $merge Flag that tells the app to merge values or not
	 */
	public function setFromArray(array $config, $merge = null) {
		foreach ($config as $k => $v) {
			$this->set($k, $v, $merge);
		}
	}
	
	/**
	 * Array from file setter method
	 *
	 * @param string $config String name of the file to fetch values from
	 */
	public function setFromFile($config) {
		$this->setFromArray($this->_getFromFile($config));
	}
	
	/**
	 * Abstracted setter method that makes a small decision as to how to merge
	 * config values based on where the values are coming from.
	 *
	 * @param mixed $config The config source - could be an array, a file or an object
	 * @return void
	 */
	public function mergeWith($config) {
		$this->mergeWithArray($this->_getFrom($config));
	}
	
	/**
	 * Array merge utility method
	 * 
	 * @access public
	 * @param array $array
	 */
	public function mergeWithArray(array $array) {
		return $this->setFromArray($this->_merge($this->toArray(), $array), true);
	}
	
	/**
	 * Array from file merge method
	 *
	 * @param string $config String name of the file to fetch values from
	 */
	public function mergeWithFile($config) {
		$this->setFromArray($this->_merge($this->toArray(), $this->_getFromFile($config)), true);
	}
	
	/**
	 * Config data append routine
	 *
	 * @access public
	 * @param mixed $config Object of this type or list of config params or config param name
	 * @param mixed $value Can be just about anything
	 * @param boolean $merge Flag that tells the app to merge values or not
	 * @return Lib_Config
	 */
	public function append($config, $merge = null) {
		// Let the decision maker decide
		$this->setFrom($config, $merge);
		
		// Return this object
		return $this;
	}
	
	/**
	 * Single item setter method
	 *
	 * @access public
	 * @param string $label Name of the config entry
	 * @param mixed $value Value for this config label
	 * @param boolean $merge Flag that tells the app to merge values or not
	 */
	public function set($label, $value, $merge = null) {
		// Merging makes no sense if we don't check existence
		$exists = $this->__isset($label);
		
		// Now handle existence checking and merging
		if ( ($exists && ($merge === null || $merge === true || ($merge === false && $this->_merge === true))) || !$exists )  {
			// Reset the value if it exists, set it if it doesn't
			$this->_config[$label] = is_array($value) ? new self($value, $merge) : $value;
			
			// If it is was created then increment the count
			if (!$exists) {
				++$this->_count;
			}
		}
	}
	
	/**
	 * Single item getter method
	 *
	 * @access public
	 * @param string $label Name of the config entry
	 * @param mixed $default Default value to return if the requested config is not found
	 * @return mixed The value for the requested config name
	 */
	public function get($label, $default = null) {
		return array_key_exists($label, $this->_config) ? $this->_config[$label] : $default;
	}
	
	/**
	 * Array fetch method
	 *
	 * This method returns this object as an array
	 *
	 * @access public
	 * @return array Collection of this config object as an array
	 */
	public function toArray() {
		$array = array();
		
		foreach ($this->_config as $k => $v) {
			$array[$k] = $v instanceof self ? $v->toArray() : $v;
		}
		
		return $array;
	}
	
	/**
	 * Merge directive setter
	 *
	 * @access public
	 * @param boolean $on Flag that tells the object whether to allow merges
	 */
	public function setMerge($on) {
		$this->_merge = (bool) $on;
	}
	
	/**
	 * Iterator declared method, resets the data array pointer
	 *
	 * @access public
	 */
	public function rewind() {
		reset($this->_config);
		$this->_index = 0;
	}
	
	/**
	 * Iterator declared method, moves the data array pointer to the next index
	 *
	 * @access public
	 */
	public function next() {
		next($this->_config);
		$this->_index++;
	}

	/**
	 * Iterator declared method, gets the current data array member
	 *
	 * @access public
	 * @return mixed Current iterator index value
	 */
	public function current() {
		return current($this->_config);
	}

	/**
	 * Iterator declared method, resets the index of the array pointer
	 *
	 * @access public
	 * @return string The current config collection array pointer index
	 */
	public function key() {
		return key($this->_config);
	}
	
	/**
	 * Iterator declared method, checks validity of current iterator index
	 *
	 * @access public
	 * @return boolean
	 */
	public function valid() {
		return $this->_index < $this->_count;
	}
	
	/**
	 * Gets the count of config entries from the current state of the object
	 *
	 * @access public
	 * @return integer Count of the current collection
	 */
	public function count() {
		return $this->_count;
	}
	
	/**
	 * Overload setter method
	 *
	 * This will NOT take a merge directive so to control merging when using
	 * overloading you need to set the merge directive with setMerge()
	 *
	 * @access public
	 * @param string $label Name of the config entry
	 * @param mixed $value Value for this config label
	 */
	public function __set($label, $value) {
		$this->set($label, $value);
	}
	
	/**
	 * Overload getter method
	 *
	 * This will NOT take a default value so if the label is not found this
	 * method will always return null.
	 *
	 * @access public
	 * @param string $label Name of the config entry
	 * @return mixed The value for the requested config name
	 */
	public function __get($label) {
		return $this->get($label);
	}
	
	/**
	 * Overload isset method, checks whether the requested label is set
	 *
	 * @access public
	 * @param string $label Name of the config entry
	 * @return boolean True if the label is set
	 */
	public function __isset($label) {
		return array_key_exists($label, $this->_config);
	}
	
	/**
	 * Recursive fetch method that grabs the next child node in a SimpleXML
	 * xml object.
	 *
	 * @param SimpleXML $element SimpleXML object
	 * @return array Array of child nodes
	 */
	protected function _nextXmlNode(Simple_XML $element) {
		$return = array();
		
		foreach ($element->children() as $name => $value) {
			$return[$name] = $this->_nextXmlNode($value);
		}
		
		if (empty($return)) {
			$return = (string) $element;
		}
		
		return $return;
	}
	
	/**
	 * Deep array merge routine
	 * 
	 * PHP's array_merge() function is broken in its current state, not merging
	 * deep enough to make sense. This method fixes that brokenness
	 * 
	 * @access protected
	 * @param array $array The array to merge into
	 * @param array $merge The array to merge with
	 * @return array The new array to merge into, merged with the merge with
	 */
	protected function _merge($array, $merge) {
		// If what was passed is an array, loop over it and set from it
		if (is_array($merge)) {
			foreach ($merge as $k => $v) {
				// If the array key in the merge does not exist in our array
				if (!array_key_exists($k, $array)) {
					$array[$k] = $merge[$k];
				} else {
					// If the key exists, check to see if it is an array
					if (is_array($array[$k])) {
						// If it is an array, merge it with the second one
						$array[$k] = $this->_merge($array[$k], $merge[$k]);
					} else {
						// It is not an array, so reset this key value with the new
						$array[$k] = $merge[$k];
					}
				}
			}
		} else {
			$array = $merge;
		}
		
		return $array;
	}
	
	/**
	 * File parser method to gather values from a file
	 * 
	 * @access protected
	 * @param string $file String name of the file to fetch values from
	 * @return array An array of key value pairs
	 */
	protected function _getFromFile($file) {
		// Initialize a return array
		$return = array();
		
		// This is not the best way to do this, but it is effective
		$ext = substr((string) $file, -3);
		
		// Check to make sure the file exists and work it only if it does
		if (file_exists($file)) {
			// Make a decision based on the file type
			switch ($ext) {
				case 'php':
					// Duh, get the file first, loading it into a var to parse
					$array = require_once $file;
					
					// See if there is anything in it
					if (!empty($array)) {
						$return = $array;
					}
					
					break;
					
				case 'ini':
					// Parse the ini file
					$return = parse_ini_file($file);
					break;
					
				case 'xml':
					// Load the XML file into an object
					$xml = simplexml_load_file($file);
					
					// Set a return array - ok to use $config since we are done with that var
					$array = array();
					
					// Begin the looping
					foreach ($xml->children() as $config) {
						/**
						 * Get the id attribute of the leading config node
						 *
						 *  Note, this could have been done as ''.$config['id'] but was not as readable
						 */
						$id = (string) $config['id'];
						
						// Now loop the elements
						foreach ($config->children() as $element) {
							// Key the name and make sure the keys are not spaced
							$name = (string) $element['name'];
							
							// And now on to the values of this element
							$array[$id][$name] = $this->_nextXmlNode($element);
						}
					}
					
					// XML file needs to be config->elements->element->value
					$return = $array;
					break;
			}
		}
		
		// Send back the return now
		return $return;
	}
	
	/**
	 * Gets an array of config values form a source
	 * 
	 * @access protected
	 * @param  $config A config source
	 * @return array
	 */
	protected function _getFrom($config) {
		// If we are working on an object of this type then just set form it
		if ($config instanceof self) {
			// Initialize our return
			$return = array();
					
			// The joys of the iterator interface
			for ($config->rewind(); $config->valid(); $config->next()) {
				$return[$config->key()] = $config->current();
			}
			
			return $return;
		}
		
		// Ultimately everything will come from an array or an array like object
		if (is_array($config)) {
			return $config;
		}
		
		// Even if it is a file then it needs to be converted to array
		if (is_file($config)) {
			return $this->_getFromFile($config);
		}
	} 
}
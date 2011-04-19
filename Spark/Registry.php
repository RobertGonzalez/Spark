<?php
/**
 * Simple PHP Application Release Kit
 * 
 * @category Spark
 * @package Spark
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 */
/**
 * Registry object handles warm data storage per request 
 * 
 * Typical Registry object pattern, simply acts as a data store for the request
 * 
 * @category Spark
 * @package Spark
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 * @version @package_version@
 */
class Spark_Registry {
    /**
     * Holds the registry labels and values data
     * 
     * @access protected
     * @var array
     */                        
    protected $_register = array();
    
    /**
     * Singleton instance holder
     * 
     * @access private
     * @var Lib_Registry
     */
    private static $_instance = null;
    
    /**
     * Make this a private unextendable function so as to enforce singleton
     * 
     * @access private
     */
    final private function __construct() {}
    
    /**
     * Set up the singleton instance getter method
     *
     * This has to be static (else how would we ever get it?)
     * 
     * @access public
     * @return Lib_Registry
     */
    public static function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }
        
        return self::$_instance;
    }
    
    /**
     * Sets a value into the registry if there is no value with this label already
     * 
     * @access public
     * @param string $label The name of the registry entry
     * @param mixed $value The value to apply to this label     
     */                        
    public function set($label, $value) {
        if (!$this->has($label)) {
            $this->_register[$label] = $value;
        }
    }
    
    /**
     * Magic setter method to allow direct assignment of values
     * 
     * @access public
     * @param string $label The name of the registry entry
     * @param mixed $value The value to apply to this label     
     */                        
    public function __set($label, $value) {
        $this->set($label, $value);
    }
    
    /**
     * Gets a value from the registry if there is a label with this value
     * 
     * @access public
     * @param string $label The name of the registry entry
     * @param mixed $default Default value to send back if the requested label is not found
     * @return mixed The value that is assigned to this label
     */                        
    public function get($label, $default = null) {
        return $this->has($label) ? $this->_register[$label] : $default;
    }
    
    /**
     * Magic getter that fetches a registered value directly as a property
     * 
     * @access public
     * @param string $label The name of the registry entry
     * @param mixed $default Default value to send back if the requested label is not found
     * @return mixed The value that is assigned to this label
     */                        
    public function __get($label) {
        return $this->get($label);
    }
    
    /**
     * Checks the existence of an entry in the register
     * 
     * Normally you would want to just check isset(), but since isset returns 
     * false for null values and there may be times when you want to register a
     * null value you are better off checking the if the key exists which will 
     * return true even for null values of an index.
     * 
     * @access public
     * @param string $label The name of the registry entry
     * @return boolean True if the label is in the register
     */
    public function has($label) {
        return array_key_exists($label, $this->_register);
    }
    
    /**
     * Magic overloader check method to see if a label exists
     * 
     * @access public
     * @param string $label The name of the registry entry
     * @return boolean True if the label is in the register
     */
    public function __isset($label) {
        return $this->has($label);
    }
	
	/**
	 * Removes an entry from the registry
	 * 
	 * @access public
	 * @param string $label The registry entry to unset
	 * @return void
	 */
	public function remove($label) {
		if ($this->_has($label)) {
			unset($this->_register[$label]);
		}
	}
	
	/**
	 * Magic overloader to remove an entry from the registry
	 * 
	 * @access public
	 * @param string $label The registry entry to unset
	 * @return void
	 */
		public function __unset($label) {
		$this->remove($label);
	}
	
	/**
	 * Resets a registry value to something else
	 * 
	 * @access public
	 * @param string $label The registry entry to reset
	 * @param mixed $value The value you want it to be now
	 * @return void
	 */
	public function reset($label, $value) {
		$this->remove($label);
		$this->set($label, $value);
	}
}
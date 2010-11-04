<?php
/**
 * Simple PHP Application Release Kit
 * 
 * @category Spark
 * @package Spark
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 */

/**
 * Get the session element object
 * 
 * This is done here because when session values that are objects are serialized
 * then unserialized they need the class that objects instantiate setup before
 * they try to create those objects 
 */
require_once 'Session/Element.php';

/**
 * Session Object manages session interaction
 * 
 * @category Spark
 * @package Spark
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 * @version @package_version@
 */
class Spark_Session {
	/**
	 * The session garbage collection max lifetime
	 * 
	 * If this is set to something other than 0 and call time it will be used as
	 * the garbage collection time.
	 * 
	 * @access protected
	 *
	 * @var integer
	 */
	public static $maxLifetime = 0;
	
	/**
	 * The session identifier
	 * 
	 * @access public
	 * @var string 
	 */
	public $sessionid;
	
	/**
	 * Instance holder for this singleton instance
	 * 
	 * @access private
	 * @var Spark_Session
	 */
	private static $_instance = null;
	
	/**
	 * Object constructor
	 * 
	 * @access public
	 */
	final protected function __construct() {		
		/**
		 * Start a session if one does not exist.
		 * 
		 * If we're at the command line it needs to be started manually.
		 */ 
		if (session_id() === '' && PHP_SAPI != 'cli') {
			// Get the garbage collection lifetime's integer value
			$gclimit = intval(self::$maxLifetime);
			
			// If lifetime is greater than 0 use that instead of the default 1440
			if ($gclimit > 0) {
				ini_set('session.gc_maxlifetime', $gclimit);
			}
			
			// Start the session
			session_start();
			$this->sessionid = session_id();
		}
	}
	
	/**
	 * Handles destruction of this object
	 * 
	 * Basically just writes the information from the session to the session
	 * stack
	 * 
	 * @access public
	 */
	public function __destruct() {
    	session_write_close();
    }
	
    /**
	 * Overload getter method
	 *
	 * This will NOT take a default value so if the label is not found this
	 * method will always return null.
	 *
	 * @access public
	 * @param string $label Name of the session element
	 * @return mixed The value for the requested session element
	 */
	public function __get($label) {
    	return $this->get($label);
    }
    
    /**
	 * Overload setter method
	 *
	 * @access public
	 * @param string $label Name of the session element
	 * @param mixed $value Value for this session element
	 */
	public function __set($label, $value) {
    	$this->set($label, $value);
    }
    
	/**
	 * Singleton instantiator
	 * 
	 * @access public
	 * @return Spark_Session
	 */
    public static function getInstance() {
		if (self::$_instance === null) {
			self::$_instance = new self;
		}
		
		return self::$_instance;
	}
	
	/**
	 * Sets a session var
	 * 
	 * This acts the same way as $_SESSION[$label] = $value
	 * 
	 * @param string $label The session key to set 
	 * @param mixed $value The value to assign to this session key
	 */
	public function set($label, $value) {
		$_SESSION[(string) $label] = is_array($value) ? new Spark_Session_Element($value) : $value;
	}
	
	/**
	 * Gets a session value from a key
	 * 
	 * @param string $label The label to fetch the value for
	 * @param mixed $default Default value to return when the key is not found
	 * @return mixed Whatever the value of this session var is
	 */
	public function get($label, $default = null) {
		return $this->has($label) ? $_SESSION[$label] : $default;
	}
	
	/**
	 * Utility accessor to the entire SESSION array
	 * 
	 * @access public
	 * @return array
	 */
	public function getAll() {
		return $_SESSION;
	}
	
	/**
	 * Checks the existence of a session key
	 * 
	 * @param string $label The label to check
	 * @return boolean True if the label is found in the session stack
	 */
	public function has($label) {
		return array_key_exists($label, $_SESSION); 
	}
	
	/**
	 * Unsets a label from the session array
	 * 
	 * @param string $label The label to unset from the session stack
	 */
	public function clear($label) {
		if ($this->has($label)) {
			unset($_SESSION[$label]); 
		}
	}
	
	/**
	 * Clears the entire session stack
	 * 
	 * As a stop gap security fix this will also call regenerateId
	 */
	public function clearAll() {
		session_destroy();
		$this->regenerateId();
	}
	
	/**
     * Regenerates the session ID and deletes the previous session store
     * 
     * @access public
     * @see [[php::session_regenerate_id()]]
     */
    public function regenerateId() {
        if (! headers_sent()) {
            session_regenerate_id(true);
        }
    }
}
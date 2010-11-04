<?php
/**
 * Simple PHP Application Release Kit
 * 
 * @category Spark
 * @package Spark_Session
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 */
/**
 * Session element object is a member of the session object. It maps to keys in
 * the session array.
 * 
 * @category Spark
 * @package Spark_Session
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 * @version @package_version@
 */
class Spark_Session_Element implements Iterator, Countable {
	/**
	 * The element data as an array
	 *
	 * @access protected
	 * @var array
	 */
	protected $_element = array();
	
	/**
	 * The current index of the element array
	 *
	 * This is used in looping constructs
	 *
	 * @access protected
	 * @var integer
	 */
	protected $_index = 0;
	
	/**
	 * The current count of elements
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
	protected $_merge = true;
	
	/**
	 * Object constructor
	 *
	 * @access public
	 * @param array $element List of named elements
	 * @param boolean $merge Flag that tells the app to merge values or not
	 */
	public function __construct($element = array(), $merge = true) {
		$this->setFrom($element, $merge);
	}
	
	/**
	 * Abstracted setter method that makes a small decision as to how to load
	 * elements based on where the values are coming from.
	 *
	 * @param mixed $element The element source - could be an array or an object
	 * @param boolean $merge Flag that tells the object whether to merge with existing values
	 * @return void
	 */
	public function setFrom($element, $merge = true) {
		// If we are working on an object of this type then just set form it
		if ($element instanceof self) {
			// The joys of the iterator interface
			for ($element->rewind(); $element->valid(); $element->next()) {
				$this->set($element->key(), $element->current(), $merge);
			}
			
			return;
		}
		
		// Ultimately everything will come from an array or an array like object
		if (is_array($element)) {
			return $this->setFromArray($element, $merge);
		}
	}
	
	/**
	 * Array setter method
	 *
	 * @access public
	 * @param array $element List of elements
	 * @param boolean $merge Flag that tells the app to merge values or not
	 */
	public function setFromArray(array $element, $merge = true) {
		foreach ($element as $k => $v) {
			$this->set($k, $v, $merge);
		}
	}
	
	/**
	 * Element data append routine
	 *
	 * @access public
	 * @param mixed $element Object of this type or list of element or element name
	 * @param mixed $value Can be just about anything
	 * @param boolean $merge Flag that tells the app to merge values or not
	 * @return Spark_Session_Element
	 */
	public function append($element, $merge = true) {
		// Let the decision maker decide
		$this->setFrom($element, $merge);
		
		// Return this object
		return $this;
	}
	
	/**
	 * Single item setter method
	 *
	 * @access public
	 * @param string $label Name of the element
	 * @param mixed $value Value for this element
	 * @param boolean $merge Flag that tells the app to merge values or not
	 */
	public function set($label, $value, $merge = true) {
		// Merging makes no sense if we don't check existence
		$exists = $this->__isset($label);
		
		// Now handle existence checking and merging
		if ( ($exists && ($merge === true || ($merge === false && $this->_merge === true))) || !$exists )  {
			$this->_element[$label] = is_array($value) ? new self($value) : $value;
			++$this->_count;
		}
	}
	
	/**
	 * Single item getter method
	 *
	 * @access public
	 * @param string $label Name of the element
	 * @param mixed $default Default value to return if the requested element is not found
	 * @return mixed The value for the requested element
	 */
	public function get($label, $default = null) {
		return array_key_exists($label, $this->_element) ? $this->_element[$label] : $default;
	}
	
	/**
	 * Array fetch method
	 *
	 * This method returns this object as an array
	 *
	 * @access public
	 * @return array Collection of this element object as an array
	 */
	public function toArray() {
		$array = array();
		
		foreach ($this->_element as $k => $v) {
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
		reset($this->_element);
		$this->_index = 0;
	}
	
	/**
	 * Iterator declared method, moves the data array pointer to the next index
	 *
	 * @access public
	 */
	public function next() {
		next($this->_element);
		$this->_index++;
	}

	/**
	 * Iterator declared method, gets the current data array member
	 *
	 * @access public
	 * @return mixed Current iterator index value
	 */
	public function current() {
		return current($this->_element);
	}

	/**
	 * Iterator declared method, resets the index of the array pointer
	 *
	 * @access public
	 * @return string The current element array pointer index
	 */
	public function key() {
		return key($this->_element);
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
	 * Gets the count of elements from the current state of the object
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
	 * @param string $label Name of the element
	 * @param mixed $value Value for this element
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
	 * @param string $label Name of the element
	 * @return mixed The value for the requested element
	 */
	public function __get($label) {
		return $this->get($label);
	}
	
	/**
	 * Overload isset method, checks whether the requested label is set
	 *
	 * @access public
	 * @param string $label Name of the element
	 * @return boolean True if the label is set
	 */
	public function __isset($label) {
		return array_key_exists($label, $this->_element);
	}
	
	/**
	 * Overload unset method, removes an element from the stack 
	 * 
	 * @access public
	 * @param string $label Name of the element to unset
	 */
	public function __unset($label) {
		if ($this->__isset($label)) {
			unset($this->_element[$label]);
		}
	}
}
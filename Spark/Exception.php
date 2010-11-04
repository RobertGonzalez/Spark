<?php
/**
 * Simple PHP Application Release Kit
 * 
 * @category Spark
 * @package Spark
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 */
/**
 * Library Exception object 
 * 
 * Extends the base PHP Exception object
 * 
 * @category Spark
 * @package Spark
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 * @version @package_version@
 */
class Spark_Exception extends Exception {
	/**
	 * The name of the class that threw the exception
	 * 
	 * @access public
	 * @var string
	 */
	public $class;
	
	/**
	 * Object constructor sets necessary vitals into object
	 * 
	 * @access public
	 */
	public function __construct($message, $class = null, $line = 0, $code = 0) {
		// First thing to do is set the Exception message
		if (empty($message)) {
			$message = 'And unknown exception is being reported by the SPARK library';
		}
		
		// Instantiate the base PHP Exception object
		parent::__construct($message);
		
		// Set the exception object properties
		$this->code = $code;
		$this->class = $class;
		$this->file = str_replace('_', '/', $class) . '.php'; // Easy file path builder
		$this->line = $line;
	}
	
	/**
	 * Magic rendering method that will echo this object as a string
	 * 
	 * @access public
	 * @return string Exception data dump
	 */
	public function __toString() {
		// Prepare the output string so that it is somewhat useful	
		$dump  = "Exception: \n\n";
		$dump .= "Thrown by: \n\t$this->class\n\n";
		$dump .= "Thrown in: \n\t$this->file\n\n";
		$dump .= "Thrown at: \n\t$this->line\n\n";
		$dump .= "Exception code \n\t$this->code\n\n";
		$dump .= "Exception message: \n\t$this->message\n\n";
		$dump .= "Exception stack trace: \n\t" . str_replace("\n", "\n\t", $this->getTraceAsString()) ."\n";
		$dump .= "Exception stack trace array: \n";
		foreach ($this->getTrace() as $v) {
			$dump .= "\t" . print_r($v, true) . "\n";
		}
		
		return $dump;
	}
}
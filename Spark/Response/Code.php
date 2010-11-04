<?php
/**
 * Simple PHP Application Release Kit
 * 
 * @category Spark
 * @package Spark_Response
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 */
/**
 * Hash table of HTTP header response status codes
 * 
 * @category Spark
 * @package Spark_Response
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 * @version @package_version@
 */
class Spark_Response_Code {
	/**
	 * Array of HTTP response status codes
	 * 
	 * @access protected
	 * @var array
	 */
	protected static $_codes = array(
		// 100 codes
		100 => 'Continue',
		101 => 'Switching Protocols',
		
		// 200 codes
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		
		// 300 codes
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		
		// 400 codes
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request Uri Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		
		// 500 codes
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
	);
	
	/**
	 * Static getter method to grab the status text from the code list
	 * 
	 * @access public
	 * @param int|string $code Value of the code to return a status for
	 * @return string The string value of the status code or null if not found
	 */
	
	public static function getStatus($code) {
		$index = intval($code);
		return isset(self::$_codes[$index]) ? self::$_codes[$index] : null;
	}
}
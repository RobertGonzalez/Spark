<?php
/**
 * Simple PHP Application Release Kit
 * 
 * @category Spark
 * @package Spark
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 */
/**
 * Library HTTP response object
 * 
 * The response object handles all HTTP responses. All output should be handled 
 * by this object. For page/layout rendering, page controllers will pass parsed 
 * response data to the response object for rendering and outputting.
 * 
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 * @category Spark
 * @package Spark
 * @version @package_version@
 */
class Spark_Response {
	/**
	 * The output content
	 * 
	 * @access public
	 * @var string
	 */
	public $body = null;
	
	/**
	 * The header list to send
	 * 
	 * @access protected
	 * @var array 
	 */
	protected $_headers = array();
	
	/**
	 * The cookie list to send
	 * 
	 * @access protected
	 * @var array
	 */
	protected $_cookies = array();
	
	/**
	 * Flag to tell this object whether to send cookies as HTTP accessible only
	 * 
	 * @access protected
	 * @var boolean
	 */
	protected $_cookiesHttpOnly = false;
	
	/**
	 * Default HTTP status code to send
	 * 
	 * @access protected
	 * @var integer
	 */
	protected $_statusCode = 200;
	
	/**
	 * Default HTTP status text
	 * 
	 * @access protected
	 * @var string
	 */
	protected $_statusText = null;
	
	/**
	 * Default HTTP spec to follow
	 * 
	 * @access protected
	 * @var string
	 */
	protected $_version = '1.1';
	
	/**
	 * Sets the content body for output
	 * 
	 * @access public
	 * @param string $body The output content to send
	 * @return Spark_Response
	 */
	public function setBody($body) {
		$this->body = (string) $body;
		return $this;
	}
	
	/**
	 * Sets the HTTP response details
	 * 
	 * @access public
	 * @param integer $code The HTTP response code to set 
	 * @return Spark_Response
	 */
	public function setStatusCode($code) {
		$code = intval($code);
		
		if ($code < 100 || $code > 599) {
			require_once 'Exception.php';
			throw new Spark_Exception('The response code ' . $code . ' is out of acceptable range', 'Spark_Response', __LINE__, 'RESPONSE_CODE_OUTOFRANGE');
		}
		
		$this->_statusCode = $code;
		$this->setStatusText();
		return $this;
	}
	
	/**
	 * Sets the HTTP response status text from a known status code
	 * 
	 * @access public
	 * @param integer $code The status code to set the status text from
	 * @return Spark_Response
	 */
	public function setStatusText($code = 0) {
		if (!$code) {
			$code = $this->_statusCode; 
		}
		
		require_once 'Response/Code.php';
		$this->_statusText = Spark_Response_Code::getStatus($code);
		return $this;
	}
	
	/**
	 * Sends all waiting headers and the string output
	 * 
	 * @access public
	 * @param string $content Content to send 
	 * @return string Output
	 */
	public function send($content = null) {
		$this->_sendHeaders();
		
		if ($content) {
			return $content;
		} else {
			if ($this->body) {
				return $this->body;
			}
		}
		
		return null;
	}
	
	/**
	 * Overload method that sends all headers and returns the respons
	 * 
	 * @access public
	 * @return string The string content to output
	 */
	public function __toString() {
		return $this->send();
	}
	
	/**
	 * Sets a header onto the response header stack
	 * 
	 * @access public
	 * @param string $name Name of the header to send 
	 * @param string $value Value of the header to send
	 * @param boolean $override Flag that decides whether to add to the stack or update the stack
	 * @return Spark_Response
	 */
	public function setHeader($name, $value, $override = true) {
		if (empty($this->_headers[$name]) || $override) {
			$this->_headers[$name] = $value;
		}
		
		return $this;
	}
	
	/**
	 * Sets a cookie response header 
	 * 
	 * @access public
	 * @param string $name The name of the cookie to set
	 * @param mixed $value The value of the cookie
	 * @param integer $expiry Timestamp to expire the cookie
	 * @param string $path Path of the cookie
	 * @param string $domain Domain for which this cookie is accessible
	 * @param boolean $secure Flag to set this cookie as a secure cookie
	 * @param boolean $httponly Flag that tells this cookie to be available only over HTTP
	 * @return Spark_Response
	 */
	public function cookie($name, $value = '', $expiry = 0, $path = '', $domain = '', $secure = false, $httponly = null) {
		$this->_cookies[$name] = array(
			'value'		=> $value,
			'expire'	=> !is_numeric($expiry) ? strtotime($expiry) : $expiry, // Accept a string date/time as well as timestamp
			'path'		=> (string) $path,
			'domain'	=> (string) $domain,
			'secure'	=> (bool) $secure,
			'httponly'	=> $httponly === null ? $this->_cookiesHttpOnly : (bool) $httponly
		);
		
		return $this;
	}
	
	/**
	 * Redirects a response to another location
	 * 
	 * @access public
	 * @param string $uri Either a full or partial URI
	 * @param string $status Status code to issue prior to redirecting, typically 302
	 * @param boolean $die Flag that tells the method whether to issue a die() after the redirect
	 * @return void
	 */
	public function redirect($uri, $status = '302', $die = true) {
		/**
		 * Get the registry object setup
		 */
		require_once 'Registry.php';
		$registry = Spark_Registry::getInstance();
		
		// If this is a full URL use it as is except clean up the string
		if (strpos($uri, '://') !== false) {
			$href = str_replace(array("\r", "\n"), '', $uri);
		} else {
			/**
			 * We only need one request object, so if it is already set get it, 
			 * otherwise, get the one that is set.
			 */
			if ($registry->has('_request')) {
				 $request = $registry->get('_request');
			} else {
				require_once 'Request.php';
				$request = Spark_Request::getInstance();
				$registry->set('_request', $request);
			}
			 
			$href = $request->uri('url') . '/' . $uri;
		}
		
		// Clear out the output buffer
		while(@ob_end_clean());
		
		// Save the session if there is one to save
		$registry->get('_session')->__destruct();
		
		// Set the redirect status code
		$this->setStatusCode($status);
		
		// Set the location header
		$this->setHeader('Location', $href);
		
		// Set the body to nothing
		$this->body = null;
		
		// Send the headers and return the content of the body
		$this->send();
		
		// if we are on autodie kill it here
		if ($die) {
			die();
		}
	}
	
	/**
	 * Send the response headers in preparation for sending output
	 * 
	 * @access protected
	 * @return void
	 */
	protected function _sendHeaders() {
		$status = 'HTTP/' . $this->_version . ' ' . $this->_statusCode;
		if ($this->_statusText) {
			$status .= '' . $this->_statusText;
		}
		
		// Send the status header
		header($status, true, $this->_statusCode);
		
		// Send the cookies
		foreach ($this->_cookies as $name => $attr) {
			setcookie($name, $attr['value'], $attr['expire'], $attr['path'], $attr['domain'], $attr['secure'], $attr['httponly']);
		}
		
		// Send the headers
		foreach ($this->_headers as $key => $val) {
			header("$key: $val");
		} 
	}
}
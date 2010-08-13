<?php
/**
 * Simple PHP Application Release Kit
 * 
 * @category Spark
 * @package Spark
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 * @version $Id$
 */
/**
 * Library request object
 *
 * The request object handles fetching, getting and checking of request related
 * data, like POST, GET, COOKIE, etc
 *
 * @category Spark
 * @package Spark
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 * @version @package_version@
 */
class Spark_Request {
	/**
	 * $_ENV values, processed and sanitize a pinch.
	 *
	 * @access public
	 * @var array
	 */
	public $env = array();
	
	/**
	 * $_GET values.
	 *
	 * @access public
	 * @var array
	 */
	public $get = array();
	
	/**
	 * $_POST values.
	 *
	 * @access public
	 * @var array
	 */
	public $post = array();
	
	/**
	 * $_COOKIE values.
	 *
	 * @access public
	 * @var array
	 */
	public $cookie = array();
	
	/**
	 * $_SERVER values.
	 *
	 * @access public
	 * @var array
	 */
	public $server = array();
	
	/**
	 * $_FILES values.
	 *
	 * @access public
	 * @var array
	 */
	public $files = array();
	
	/**
	 * $_SERVER['HTTP_*'] values.
	 * 
	 * Keys are normalized and lower-cased; keys and values are
	 * filtered for control characters.
	 *
	 * @access public
	 * @var array
	 */
	public $http = array();
	
	/**
	 * $_SERVER['argv'] values.
	 * 
	 * @access public
	 * @var array
	 */
	public $argv = array();
	
	/**
	 * The parts of the uri being requested
	 * 
	 * @access public
	 * @var array
	 */
	public $uri = array();
	
	/**
	 * Object constructor
	 */
	public function __construct() {
		$this->fetchGlobals();
	}
	
	/**
	 * Gets a value from the get array or an alternate, caller supplied value
	 * 
	 * @access public
	 * @param string $key The $get key to retrieve the value of
	 * @param string $alt The value to return if the key does not exist
	 * @return mixed The value of $get[$key], or the alternate value
	 */
	public function get($key = null, $alt = null) {
		return $this->_fetchValue('get', $key, $alt);
	}
	
	/**
	 * Gets a value from the post array or an alternate, caller supplied value
	 * 
	 * @access public
	 * @param string $key The $get key to retrieve the value of
	 * @param string $alt The value to return if the key does not exist
	 * @return mixed The value of $post[$key], or the alternate value
	 */
	public function post($key = null, $alt = null) {
		return $this->_fetchValue('post', $key, $alt);
	}
	
	/**
	 * Gets a value from the cookie array or an alternate, caller supplied value
	 * 
	 * @access public
	 * @param string $key The $get key to retrieve the value of
	 * @param string $alt The value to return if the key does not exist
	 * @return mixed The value of $cookie[$key], or the alternate value
	 */
	public function cookie($key = null, $alt = null) {
		return $this->_fetchValue('cookie', $key, $alt);
	}
	
	/**
	 * Gets a value from the environment array or an alternate, caller supplied value
	 * 
	 * @access public
	 * @param string $key The $get key to retrieve the value of
	 * @param string $alt The value to return if the key does not exist
	 * @return mixed The value of $env[$key], or the alternate value
	 */
	public function env($key = null, $alt = null) {
		return $this->_fetchValue('env', $key, $alt);
	}
	
	/**
	 * Gets a value from the server array or an alternate, caller supplied value
	 * 
	 * @access public
	 * @param string $key The $get key to retrieve the value of
	 * @param string $alt The value to return if the key does not exist
	 * @return mixed The value of $server[$key], or the alternate value
	 */
	public function server($key = null, $alt = null) {
		return $this->_fetchValue('server', $key, $alt);
	}
	
	/**
	 * Gets a value from the files array or an alternate, caller supplied value
	 * 
	 * @access public
	 * @param string $key The $get key to retrieve the value of
	 * @param string $alt The value to return if the key does not exist
	 * @return mixed The value of $files[$key], or the alternate value
	 */
	public function files($key = null, $alt = null) {
		return $this->_fetchValue('files', $key, $alt);
	}
	
	/**
	 * Gets a value from the argv array or an alternate, caller supplied value
	 * 
	 * @access public
	 * @param string $key The $get key to retrieve the value of
	 * @param string $alt The value to return if the key does not exist
	 * @return mixed The value of $argv[$key], or the alternate value
	 */
	public function argv($key = null, $alt = null) {
		return $this->_fetchValue('argv', $key, $alt);
	}
	
	/**
	 * Gets a value from the uri parts or an alternate, caller supplied value
	 * 
	 * @access public
	 * @param string $key The $uri key to retrieve the value of
	 * @param string $alt The value to return if the key does not exist
	 * @return mixed The value of $uri[$key], or the alternate value
	 */
	public function uri($key = null, $alt = null) {
		return $this->_fetchValue('uri', $key, $alt);
	}
	
	/**
	 * Gets a value from the http property array or an alternate, caller supplied value
	 * 
	 * @access public
	 * @param string $key The $get key to retrieve the value of
	 * @param string $alt The value to return if the key does not exist
	 * @return mixed The value of $http[$key], or the alternate value
	 */
	public function http($key = null, $alt = null) {
		return $this->_fetchValue('http', strtolower($key), $alt);
	}
	
	/**
	 * Determines if request is a CLI request
	 * 
	 * @return boolean True if CLI, false otherwise
	 */
	public function isCli() {
		return PHP_SAPI == 'cli';
	}
	
	/**
	 * Determines if the request method is a GET request
	 * 
	 * @return boolean True if request method is GET, false otherwise
	 * 
	 */
	public function isGet() {
		return $this->server('REQUEST_METHOD') == 'GET';
	}
	
	/**
	 * Determines if the request was sent by POST
	 * 
	 * @return boolean True if request is POST, false otherwise
	 */
	public function isPost() {
		return $this->server('REQUEST_METHOD') == 'POST';
	}
	
	/**
	 * Determines if the request is a PUT request
	 * 
	 * @return boolean True if the request is PUT, false otherwise
	 */
	public function isPut() {
		return $this->server('REQUEST_METHOD') == 'PUT';
	}
	
	/**
	 * 
	 * Determines if the request is a DELETE request
	 * 
	 * @return boolean True if request is DELETE, false otherwise
	 */
	public function isDelete() {
		return $this->server('REQUEST_METHOD') == 'DELETE';
	}
	
	/**
	 * 
	 * Determines if the request is an XmlHttpRequest
	 * 
	 * Checks if the `X-Requested-With` HTTP header is `XMLHttpRequest`, the 
	 * request type used most often with Ajax type requests (and in conjunction 
	 * with [[isPost()]] and/or [[isGet()]]).
	 * 
	 * @return boolean True if the request is an XMLHttpRequest
	 * 
	 */
	public function isXmlHttp() {
		return strtolower($this->http('X-Requested-With')) == 'xmlhttprequest';
	}
	
	/**
	 * Loads properties from the superglobal arrays.
	 * 
	 * Fetches information from the SUPER GLOBAL arrays, normalizes HTTP header 
	 * keys and handles magic quotes.
	 */
	public function fetchGlobals() {
		/**
		 * Create a mock global array here for looping and setting from next
		 * 
		 * For some reason PHP is being a twit and not letting me set this array
		 * in the call to each. But it will let me make an array then send the 
		 * array as a variable to each, so we are going to do that.
		 */
		$globs = array('get', 'post', 'cookie', 'files');
		
		/**
		 * Loop through a mock global array (without getting SERVER or ENV vars)
		 * and check to see if there is anything that needs fetching from them.
		 */
		while (list(,$prop) = each($globs)) {
			// Makes a "get" into a "_GET"
			$var = '_' . strtoupper($prop);
			
			/**
			 * Is this var in the GLOBAL array (ex. $GLOBALS["_GET"])?
			 * 
			 * This will work on POST, GET, COOKIE and FILES all the time. If 
			 * there is no known reference to $_ENV or $_SERVER, they will not 
			 * be in the GLOBAL array. 
			 */
			if (isset($GLOBALS[$var])) {
				// Add it to the object property arrays (ex. $this->get = $GLOBALS["_GET"])
				$this->$prop = $GLOBALS[$var];
			}
		}
		
		// Now get $_SERVER and $_ENV vars
		foreach ($_SERVER as $k => $v) {
			$this->server[$k] = $v;
		}
		
		foreach ($_ENV as $k => $v) {
			$this->env[$k] = $v;
		}
		// Undo magic quotes if they are enabled.
		// More information can be found:
		// http://talks.php.net/show/php-best-practices/26
		if (get_magic_quotes_gpc()) {
			$in = array(&$_GET, &$_POST, &$_COOKIE);
			while (list($k, $v) = each($in)) {
				foreach ($v as $key => $val) {
					if (! is_array($val)) {
						$in[$k][$key] = stripslashes($val);
						continue;
					}
					$in[] =& $in[$k][$key];
				}
			}
			unset($in);
		}
		
		// load the object argv request var
		$this->argv = (array) $this->server('argv');
		
		// load the object http request var
		foreach ($this->server as $key => $val) {
			
			// We are only interested in the HTTP_* family 
			if (substr($key, 0, 4) == 'HTTP') {
				// normalize the header key to lower-case
				$nicekey = strtolower(
					str_replace('_', '-', substr($key, 5))
				);
				
				// strip control characters from keys and values
				$nicekey = preg_replace('/[\x00-\x1F]/', '', $nicekey);
				$this->http[$nicekey] = preg_replace('/[\x00-\x1F]/', '', $val);
				
				// no control characters wanted in $this->server for these
				$this->server[$key] = $this->http[$nicekey];
				
				// disallow external setting of X-JSON headers.
				if ($nicekey == 'x-json') {
					unset($this->http[$nicekey]);
					unset($this->server[$key]);
				}
			}
		}
		
		/**
		 * Handle the URI bits now
		 * 
		 * I am doing this as part of the request so I don't have to weigh the
		 * library down with a URI object. 
		 */
		// build a default scheme (with '://' in it)
		$ssl = $this->server('HTTPS', 'off');
		$scheme = (($ssl == 'on') ? 'https' : 'http') . '://';
		
		// get the current host
		$host = $this->server('HTTP_HOST');
		
		// Make a URL of this site for use throughout
		$url = $scheme . $host;
		
		// Make a URL for this request for use throughout
		$uri = $url . $_SERVER['REQUEST_URI'];
		
		// Set up the parse_url parts
		$parts = array(
			'scheme' => null,
			'host' => null,
			'port' => null,
			'user' => null,
			'pass'=> null,
			'path' => null,
			'query' => null,
			'fragment' => null,
		);
		
		// And put them together
		$this->uri = array_merge($parts, parse_url($uri));
		
		// Add our own two cents, starting with the base uri 
		$this->uri['base'] = $url;
		
		// Now add the full, requested uri
		$this->uri['full'] = $uri;
		
		/**
		 * Now set some basics of the request, like the page name, action and 
		 * param list
		 */
		$request = explode('/', trim($this->server('REQUEST_URI'), '/'));
		
		// Get the registry and config objects
		$registry = Lib_Registry::getInstance();
		$config = $registry->get('libconfig');
		
		// Initialize our request vars
		$page = $config->default->page;
		$action = $config->default->action;
		$params = array();
		
		// If we have a requested page set it
		if (!empty($request[0])) {
			$page = strtolower($request[0]);
			
			// If we have a requested action, set that too$class, 
			if (!empty($request[1])) {
				$action = strtolower($request[1]);
				
				// Lastly if we have any params, handle those
				if (!empty($request[2])) {
					for ($i = 2, $j = 3, $max = count($request); $i < $max; $i += 2, $j += 2) {
						$params[$request[$i]] = array_key_exists($j, $request) ? $request[$j] : null;
					}
				}
			}
		}
		
		// Set them into the uri property
		$this->uri['page'] = $page;
		$this->uri['action'] = $action;
		$this->uri['params'] = $params;
		
		// Read the page, action and params into the registry and be done
		$registry->set('page', $page);
		$registry->set('action', $action);
		$registry->set('params', $params);
	}
	
	/**
	 * Common method to get a request value and return it.
	 * 
	 * @access protected
	 * @param string $var The request variable to fetch from: get, post, etc.
	 * @param string $key The array key, if any, to get the value of.
	 * @param string $alt The alternative default value to return if the requested key does not exist.
	 * @return mixed The requested value, or the alternative default value.
	 */
	protected function _fetchValue($var, $key, $alt) {
		// get the whole property, or just one key?
		if ($key === null) {
			// no key selected, return the whole array
			return $this->{$var};
		} elseif (array_key_exists($key, $this->{$var})) {
			// found the requested key, so return it
			return $this->{$var}[$key];
		} else {
			// requested key does not exist
			return $alt;
		}
	}
}
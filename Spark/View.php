<?php
/**
 * Simple PHP Application Release Kit
 * 
 * @category Spark
 * @package Spark_Library
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 */
/**
 * View object
 *
 * Handles rendering of output
 *
 * @category Spark
 * @package Spark_Library
 * @author Robert Gonzalez <robert@robert-gonzalez.com>
 * @version @package_version@
 */
class Spark_View {
	/**
	 * The template to parse
	 * 
	 * @access public
	 * @var string
	 */
	public $template;
	
	/**
	 * The template vars holder
	 * 
	 * @access protected
	 * @var array
	 */
	protected $_vars = array();
	
	/**
	 * Escape mechanism for output escaping
	 * 
	 * @access protected
	 * @var array
	 */
	protected $_escape = array(
	 	'quotes'	=> ENT_COMPAT,
		'charset'	=> 'UTF-8',
	);
	
	/**
	 * Sets a template for the view to use
	 * 
	 * This will only set the template if it exists
	 * 
	 * @access public
	 * @param string $template Template to use for output rendering
	 */		
	public function setTemplate($template) {
		if (file_exists($template)) {
			$this->template = $template;
		}
	}
	
	/**
	 * Renders output from the buffer and returns that output
	 *
	 * @access public
	 * @param string $layout If passed, will return a parsed layout after 
	 *                       parsing the view, otherwise only the parse view is
	 *                       returned 
	 * @return string Parsed template
	 */
	public function render($layout = null) {
		// Template names cannot be empty
		if (empty($this->template)) {
			require_once 'Exception.php';
			throw new Spark_Exception('There is no output file to parse for viewing', 'Spark_View', __LINE__, 'NO_VIEW_SET');
		}
		
		// Get the template variables
		extract($this->_vars);
		
		// Turn on output buffering so we can capture the parsed view
		ob_start();
		
		// Include the view template
		require_once $this->template;
		
		// Capture the view template after being parsed
		$_parsed = ob_get_clean();
		
		// If the return flag is set send back the return
		if (!$layout) {
			return $_parsed;
		}
		
		/**
		 * Only try to parse the template file if it exists
		 */
		if (file_exists($layout)) {
			// Turn on output buffering
			ob_start();
			
			/**
			 * Get the layout file
			 * 
			 * This should have a placeholder named $_parsed where the parsed 
			 * view will go.
			 */
			require_once $layout;
			
			// Return the parsed view AND parsed layout
			return ob_get_clean();
		}
		
		// Fallback is to just return the parsed view if the template is not found
		return $_parsed;
	}
	
	/**
	 * Renders a layout rather than a simple view
	 * 
	 * @access public
	 * @param string $layout Full path to a layout template
	 * @param boolean $return Whether to echo or return the parsed layout
	 * @return string
	 */
	public function renderLayout($layout, $return = false) {
		$output = $this->render($layout);
		
		// Handle the requested step
		if ($return) {
			return $output;
		}
		
		// Echo out otherwise
		echo $output;
	}
	
	/**
	 * Escapes data for output
	 * 
	 * This is mildly recursive in that if the $data var that is passed is an array
	 * then the array is looped and escaped with this same method.
	 * 
	 * @param mixed $data Data to escape
	 * @return mixed Escaped data
	 */
	public function escape($data) {
		// Handle arrays first and recurse until we are at a scalar value
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$data[$key] = $this->escape($value);
			}
		} elseif (is_string($data)) {
			// Handle string data
			$data = htmlspecialchars($data, $this->_escape['quotes'], $this->_escape['charset']);
		}
		
		/**
		 * Note, this was put in place because Robert kept wondering why his 
		 * numbers we being eaten by the framework. Robert knew numbers were 
		 * tasty, and that the framework had not been fed in a few days. But 
		 * Robert also knew that the framework is very well trained and very 
		 * disciplined. "How could this be happening?", Robert thought. Then it 
		 * hit him.
		 * 
		 * Robert wasn't returning the data that was passed to the escape method
		 * when it didn't fall into the array|string data types. Silly Robert. 
		 * He knows better now.
		 * 
		 * For all non string, non array data types, do nothing with them at all.
		 */
		return $data;
	}
	
	/**
	 * Sets a group of variables from an array
	 * 
	 * @access public
	 * @param array $array String key => value pairs to assign
	 */
	public function assignVars(Array $array) {
		// Loop the array and set template vars by key => value pair
		foreach ($array as $k => $v) {
			// But only do this for string keys
			if (is_string($k)) {
				$this->assign($k, $v);
			}
		}
	}
	
	/**
	 * Template variable assignment method
	 *
	 * @access public
	 * @param string $name Name of the variable to set
	 * @param mixed $value Value for the $name variable
	 */
	public function assign($name, $value) {
		$this->_vars[$name] = $value;
	}
	
	/**
	 * Fetch method that gets a template variables value from outside the view template
	 *
	 * @access public
	 * @param string $name Name of the varible to fetch the data for
	 * @return mixed The value of $name if found or null otherwise
	 */
	public function fetch($name) {
		return $this->__isset($name) ? $this->_vars[$name] : null;
	}
	
	/**
	 * Overloaded magic setter method sets variables into the view object
	 *
	 * @access public
	 * @param string $name Name of the variable to set
	 * @param mixed $value Value of the $name variable
	 */
	public function __set($name, $value) {
		$this->assign($name, $value);
	}
	
	/**
	 * Overloaded magic getter method gets a variable value from the object
	 *
	 * @access public
	 * @param string $name Name of the variable to get the value for
	 * @return mixed Value for the variable or null if not found
	 */
	public function __get($name) {
		return $this->fetch($name);
	}
	
	/**
	 * Magic overloaded method to check if a template var is set
	 * 
	 * @access public
	 * @param string $name Name of the var to check isset
	 * @return boolean True if the var is set, false otherwise
	 */
	public function __isset($name) {
		return array_key_exists($name, $this->_vars);
	}
}
<?php
/**
 * Qadwa
 * 
 * PHP Version 5
 *
 * @category  Qadwa
 * @package   Qadwa
 * @author    Rodrigo Arce <rsarce@gmail.com>
 * @copyright 2010 Rodrigo Arce
 * @license   MIT License (http://qadwa.com.ar/LICENSE.txt)
 * @version   0.1
 * @link      http://qadwa.com.ar
 */

/**
 * Qadwa (http://qadwa.com.ar)
 * 
 * Qadwa is a tiny framework.
 * This class act as 
 * - MVC's Front Controller
 * - Views loader
 * - Configuration wrapper
 * 
 * @category  Qadwa
 * @package   Qadwa
 * @author    Rodrigo Arce <rsarce@gmail.com>
 * @copyright 2010 Rodrigo Arce
 * @license   MIT License (http://qadwa.com.ar/LICENSE.txt)
 * @version   0.1
 * @link      http://qadwa.com.ar
 */
class Qadwa
{
    /**
     * PHP > 5.3 namespace separator
     * 
     * @var string
     */
    const NAMESPACE_5_3 = '\\';
    /**
     * PHP <= 5.2 namespace separator
     * 
     * @var string
     */    
    const NAMESPACE_5_2 = '_';
    /**
     * Configuration vars
     * 
     * @var array
     */
    private $_configuration = array(
        /**
         * Debug application mode
         * @var boolean
         */
        'debug' => false,
        /**
         * Base URL
         * @var string
         */
        'baseUrl' => '/',
        /**
         * Base application path
         * @var string
         */
        'basePath'=> './',
        /**
         * Define autoload
         * @var boolean
         */
        'autoload' => true,
        /**
         * Use ini configuration
         * @var mixed
         */
        'iniConfig' => true,
        /**
         * Use ini configuration
         * @var mixed
         */
        'defaultConfig' => 'default',    
        /**
         * Default module name
         * @var string
         */
        'defaultModule'=> 'index',
        /**
         * Default action name
         * @var string
         */
        'defaultAction'=> 'index',
        /**
         * Param separator
         * @var string
         */
        'paramSeparator'=> '/',
        /**
         * Default layout
         * @var string
         */
        'layout' => 'layout',
        /**
         * Templates filenames
         * @var string
         */
        'templateFile' => 'views/%s.phtml',
        /**
         * Layout filenames
         * @var string
         */
        'layoutFile' => 'layout/%s.phtml',
        /**
         * Controllers filenames
         * @var string
         */
        'controllerFile'=> 'controllers/%s.php',
        /**
         * Library filenames
         * @var string
         */
        'libFile' => 'lib/%s.php',
        /**
         * Configuration filenames
         * @var string
         */
        'configurationFile'=> 'etc/%s.ini',
        /**
         * Request param for module
         * @var mixed
         */
        'moduleParam' => 0,
        /**
         * Request param for action
         * @var mixed
         */
        'actionParam' => 1,
        /**
         * Page not found template
         * @var string
         */
        'error400Template' => 'error400',
        /**
         * Internal error template
         * @var string
         */
        'error500Template' => 'error500',
        /**
         * Message error
         * @var string
         */
        'errorMessage' => "Error:\n\n %s",
        /**
         * Database handler
         * @var mixed
         */
        'dbh' => null,
    );
    /**
     * Template vars
     * 
     * @var array
     */
    private $_templateVars = array();
    /**
     * Class namespace separator
     * 
     * @var string
     */
    private $_namespaceSeparator;
    /**
     * Singleton instance
     * 
     * @var Qadwa
     */
    static private $_instance;
    /**
     * Singleton
     * 
     * @param array $params Configuration params
     * 
     * @return Qadwa
     */
    static public function getInstance(array $params = array())
    {
        if (!self::$_instance) {
            self::$_instance = new self($params);
        } elseif (count($params)) {
            foreach ($params as $key => $value) {
                self::$_instance->__set($key, $value);
            }
        }
        return self::$_instance;
    }
    /**
     * Constructor.
     * Load configuration array and register autoload.
     * 
     * @param array $params Configuration params
     */
    private function __construct(array $params)
    {
        array_walk($params, array($this, '_sanitize'));
        $config = (isset($params['iniConfig'])) ? 
            $params['iniConfig']:
            $this->_configuration['iniConfig'];
        $this->_configuration = array_merge($this->_configuration, $params);        
        if ($config && $iniParams = $this->loadConfiguration($config)) {
            $this->_configuration = array_merge($this->_configuration, $iniParams);
        }
        if ($this->autoload) {
            spl_autoload_register(array($this, 'autoload'));
        }
        $this->_initDb();
    }
    /**
     * Load the configuration file
     * 
     * @param mixed $config Name of the configuration file or boolean
     * 
     * @return array
     */
    public function loadConfiguration($config)
    {
        $originalConfig = $config;
        if (is_bool($config)) {
            // configuration name
            $config = str_replace('www.', '', $_SERVER['HTTP_HOST']);
            $this->basePath = isset($params['basePath']) ? 
            $params['basePath']:
            $this->_configuration['basePath'];  
        }
        $configFile = $this->_getFile($config, 'configuration', true);
        if (!$configFile) {
            if (is_string($originalConfig)) {
                $message = "Configuration file `$config` not exists";
                throw new Exception($message);    
            }
            $default = $this->defaultConfig;
            $configFile = $this->_getFile($default, 'configuration', true);
        }
        if (!$configFile) {
            return null;
        }
        $params = parse_ini_file($configFile, true);
        array_walk($params, array($this, '_sanitize'));
        return $params;
    }
    /**
     * Initializate the database
     * 
     * @return void
     */
    private function _initDb()
    {
        if (!is_array($this->database)) {
            return;            
        }
        $config = $this->database;
        if (is_array($config)) {
            $name = $config['name'];
            $user = (isset($config['user'])) ? $config['user'] : 'root';
            $pass = (isset($config['pass'])) ? $config['pass'] : 'root';
            $host = (isset($config['host'])) ? $config['host'] : 'localhost';
            $driver = (isset($config['host'])) ? $config['driver'] : 'mysql';        
            if ($pass) {
                $user = "$user:$pass";
            }        
            $conn = "$driver://$user@$host/$name";
        } else {
            $conn = $config;
        }
        $this->conn = Doctrine_Manager::connection($conn, 'doctrine');
        $this->conn->setCharset('utf8');
    }
    /**
     * Autoload class method
     * 
     * @param string $class Class to autoload
     * 
     * @see http://php.net/autoload
     *
     * @return void
     */
    public function autoload($class)
    {
        if (!$this->_namespaceSeparator) {
            $versionCompare = version_compare(PHP_VERSION, '5.3.0', '>=');
            $this->_namespaceSeparator = $versionCompare ?
                self::NAMESPACE_5_3:
                self::NAMESPACE_5_2;                
        }
        $ns = $this->_namespaceSeparator;
        $class = str_replace($ns, DIRECTORY_SEPARATOR, $class);
        $file = $this->_getFile($class, 'lib');
        include_once $file;
    }
    /**
     * Set a configuration value 
     * 
     * @param string $var   Name of the var
     * @param mixed  $value Value to set
     * 
     * @return mixed
     */
    public function __set($var, $value)
    {
        $this->_configuration[$var] = $this->_sanitize($value, $var);
    }
    /**
     * Retrieve a configuration value
     * 
     * @param string $var Name of the var
     * 
     * @return mixed
     */
    public function __get($var)
    {
        if (isset($this->_configuration[$var])) {
            return $this->_configuration[$var];
        }
    }
    /**
     * Dispatch the controller and the view.
     * 
     * @return string
     */
    public function dispatch()
    {
        try {        
            // load the /module/action controller
            $this->module = $this->getParam(
                $this->moduleParam,
                $this->defaultModule
            );
            $this->action = $this->getParam(
                $this->actionParam,
                $this->defaultAction
            );
            $moduleController = $this->module . '/' . $this->action;
            $actionController = $this->module;
            $vars = $this->loadController($moduleController);
            // if not exists try to load the /action controller
            if ($vars) {
                $controller = $moduleController;
            } else {
                $vars = $this->loadController($actionController);
                if ($vars) {
                    $controller = $actionController;                    
                }
            }
            // Template
            if (isset($vars['template'])) {
                $template = $vars['template'];
                if ($template === false) {
                    $template = $this->error400Template;
                }
            } else {
                $template = false;
                if ($this->_getFile($moduleController, 'template', true)) {
                    $template = $moduleController;
                } elseif ($this->_getFile($actionController, 'template', true)) {
                    $template = $actionController;
                }
            }
            if (!$template || !$this->_getFile($template, 'template', true)) {
                if ($this->debug) {
                    throw new Exception("Template `$template` not found.");
                }
                $template = $this->error400Template;
            }
            
        } catch(Exception $e) {
            $this->error = $e;
            $template = $this->error500Template;
            if (!$this->_getFile($template, 'template', true)) {
                $this->debug = true;
                echo "No error page found.\n\n";
            }
            if ($this->debug) {
                throw $e;
            }
        }
        if (!is_array($vars)) {
            $vars = array();
        }
        $content = $this->loadTemplate($template);
        $vars['content'] = PHP_EOL . $content . PHP_EOL;
        $layout = isset($vars['layout']) ? $vars['layout'] : $this->layout;
        if ( $layout 
            && ($site = $this->loadTemplate($layout, $vars, true))
        ) {
            return $site;
        }
        return $content;
    }
    /**
     * Add vars to template vars
     * 
     * @param array $vars Vars to extract
     * 
     * @return void
     */
    public function addTemplateVars(array $vars)
    {
        $this->_templateVars = array_merge($this->_templateVars, $vars);
    }
    /**
     * Load the template
     * 
     * @param string  $template Template name
     * @param array   $vars     Vars to extract
     * @param boolean $isLayout True if the template to load is layout
     * 
     * @return string
     */
    public function loadTemplate($template, $vars = array(), $isLayout = false)
    {
        $type = !$isLayout ? 'template' : 'layout';
        $this->template = $this->_getFile($template, $type);
        unset($type, $template);
        if (file_exists($this->template)) { 
            extract($this->_templateVars);
            extract($vars);
            ob_start();
            include $this->template;
            return ob_get_clean();
        }
    }
    /**
     * Get the param received by position or name or if not exist or is empty
     * returns the default value. By name search in the form /name/value.
     * 
     * @param mixed $param   Param name or position 
     * @param mixed $default Default value
     * 
     * @return string
     */
    public function getParam($param, $default = null)
    {
        if (!$this->request) {
            $requestUri = $_SERVER['REQUEST_URI']; 
            if ($pos = strpos($requestUri, '?')) {
                $requestUri = substr($requestUri, 0, $pos);
            }
            $requestUri = substr($requestUri, strlen($this->baseUrl));
            $this->request = explode($this->paramSeparator, $requestUri);
        }
        if (is_numeric($param)) {
            if (isset($this->request[$param]) && $this->request[$param]) {
                return $this->request[$param];
            }
        } else {
            foreach ($this->request as $key => $value) {
                if ($param == $value) {
                    return $this->getParam($key + 1, $default);
                }
            }
        }
        return $default;
    }
    /**
     * Return true if the request is post.
     * 
     * @return boolean
     */
    public function isPost()
    {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'post';        
    }
    /**
     * Redirect to url
     * 
     * @param string $url URL to redirect
     * 
     * @return void
     */
    public function redirect($url)
    {
        if ($url[0] == '/') {
            $url = $this->baseUrl . substr($url, 1);
        }
        header('Location: ' . $url);
        exit;        
    }
    /**
     * Load the controller
     * 
     * @param string $controller Controller name
     * 
     * @return mixed
     */
    public function loadController($controller)
    {
        $controllerFile = $this->_getFile($controller, 'controller');
        if (file_exists($controllerFile)) {
            $vars = include $controllerFile;
            if (is_array($vars)) {
                $this->addTemplateVars($vars);
            }
            return $vars;
        }
        return false;
    }
    /**
     * Dispatch
     * 
     * @see dispatch()
     * 
     * @return string
     */
    public function __toString()
    {
        // __toString method could not throw exceptions!
        try {
            return (string) $this->dispatch();
        } catch(Exception $e) {
            $this->_error($e);
        }
    }
    /**
     * Get the file path. If $checkExists is true return false when
     * the file not exists.  
     * 
     * @param string  $name        Filename
     * @param string  $type        Type of file (not extension, just Qadwa type)
     * @param boolean $checkExists Check if exists
     * 
     * @return string
     */
    private function _getFile($name, $type = null, $checkExists = false)
    {
        $typeVar = $type . 'File';
        if ($this->{$typeVar}[0] != DIRECTORY_SEPARATOR) {
            $this->$typeVar = $this->basePath . $this->$typeVar; 
        } 
        $file = ($type && $this->$typeVar) ? 
            sprintf($this->$typeVar, $name):
            $name;
        if ($checkExists) {
            if (!file_exists($file)) {
                return false;
            }
        }
        return $file;
    }
    /**
     * Sanitize the configuration value
     * 
     * @param mixed  &$value Value to sanitize
     * @param string $var    Var name to sanitize
     * 
     * @return mixed
     */
    private function _sanitize(&$value, $var)
    {
        switch ($var) { 
        case 'baseUrl':
        case 'basePath':
            if (substr($value, -1) != '/') {
                return $value = $value . '/';
            }
        }
        return $value;
    }
    /**
     * Print a formatted error and exits.
     * 
     * @param Exception $error Exception error
     * 
     * @return void;
     */
    private function _error(Exception $error)
    {
        printf($this->errorMessage, $error);
        exit;
    }
}

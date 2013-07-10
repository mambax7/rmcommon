<?php
// $Id$
// --------------------------------------------------------------
// Common Utilities 2
// A modules framework by Red Mexico
// Author: Eduardo Cortes
// Email: i.bitcero@gmail.com
// License: GPL 2.0
// URI: http://www.redmexico.com.mx
// --------------------------------------------------------------

abstract class RMModuleController {

    protected $module_data = array();

    protected function construct_module($module){

        global $xoopsModule;

        if( $xoopsModule && $xoopsModule->getVar('dirname') == $module )
            $moduleObject = $xoopsModule;
        else
            $moduleObject = RMModules::load_module($module);

        // Initialize properties
        $this->module_data = array(

            'settings'              => \RMSettings::module_settings($module),
            'path'                  => XOOPS_ROOT_PATH . '/modules/' . $module,
            'real_url'              => XOOPS_URL . '/modules/' . $module,
            'data'                  => (object) $moduleObject->getInfo(),
            'directory'             => $module,
            'controller'            => '',
            'action'                => '',
            'default_controller'    => '',
            'default_action'        => '',
            'parameters'            => array(),

        );

        if ( defined( 'XOOPS_CPFUNC_LOADED') )
            $this->module_data['menu'] = $moduleObject->getAdminMenu();

        $this->module_data['url'] = $this->url;


    }

    /**
     * Get methods and properties
     * @param string $name Method or property name
     * @return mixed
     * @throws RMException
     */
    public function __get($name){

        $method = 'get_'.$name;

        if( method_exists($this, $method) )
            return $this->{$method}();
        elseif ( isset( $this->module_data[$name] ) )
            return $this->module_data[$name];

        throw new RMException( sprintf( __( 'Property %s.%s does not exists.', 'rmcommon' ), ucfirst( $this->module_data['directory'] ), $name ), E_USER_WARNING);

    }

    /**
     * Set property values
     * @param string $name Property or method name
     * @param mixed $value New value
     * @return mixed
     * @throws RMException
     */
    public function __set($name, $value){

        $method = 'set_'.$name;

        if( method_exists($this, $method) )
            return $this->$method($value);
        elseif ( isset( $this->module_data[$name] ) )
            return $this->module_data[$name] = $value;

        throw new RMException( sprintf( __( 'Property %s.%s does not exists.', 'rmcommon' ), ucfirst( $this->module_data['directory'] ), $name ), E_USER_WARNING);

    }

    /**
     * Get the requested controller.
     * A controller is determined by getting the _GET['s] paramater. This parameter is a string passed trough
     * URL as follow:
     *
     * <pre>controller/action</pre>
     *
     * This means that the well formed URL must have the next structure:
     *
     * <pre>http://my-site/modules/module/index.php?s=controller/action</pre>
     *
     * or
     *
     * <pre>http://my-site/module/<strong>controller</strong>/<strong>action</strong>/</pre>
     *
     * This method will obtain controller and will call as follow:
     * <pre>Controller::action()</pre>
     *
     * All logic must be added in action() method.
     *
     * If a the corresponding class is not found, then an 404 status error will be send to browser.
     */
    protected function launch(){

        // Get data from URL
        $parameters = RMHttpRequest::get( 's', 'string', '');

        if( $parameters=='' ){

            $controller_name = defined( 'XOOPS_CPFUNC_LOADED' ) ? 'main' : $this->default_controller;
            $action = defined( 'XOOPS_CPFUNC_LOADED' ) ? 'dashboard' : $this->default_action;;

        } else {

            $parameters = explode( "/", trim( $parameters, "/" ) );
            $controller_name = $parameters[0];
            $action = count( $parameters ) > 1 ? $parameters[1] : 'index';

        }

        $class = ucfirst( $controller_name ) . 'Controller';
        $file = $this->path . '/' . ( defined('XOOPS_CPFUNC_LOADED') ? 'admin' : 'controllers' ) . '/' . $class . '.php';

        if( !file_exists($file) )
            return $this->send_status( 404, $controller_name, $action );

        include_once $file;


        if( !class_exists( $class ) )
            return $this->send_status( 404, $controller_name, $action );

        if( is_array($parameters) && count($parameters) > 2)
            $parameters = array_slice($parameters, 2);
        else
            $parameters = array();

        $controller = new $class();
        $controller->parent = $this;
        $this->parameters = $parameters;

        if( !method_exists( $controller, $action ) )
            return $this->send_status( 404, $controller_name, $action );

        $this->controller = $controller_name;
        $this->action = strtolower( $action );

        return $controller->$action();

    }

    /**
     * Include the appropiate template/view to use with current action
     * The view is related to controller and action names.
     *
     * e.g. <code>Controller::action()</code> will return in <code>templates/<strong>controller</strong>/action.php</code> file.
     *
     * A different case is when the controller is located in <em>admin</em> section. In this case the controller will
     * be located as <code>templates/backend/controller/action.php</code>.
     *
     * If the $name parameter is provided, then a file with this name will be requested.
     *
     * @param string $name Name of template
     * @return string File path
     */
    public function get_view($name = ''){

        if ( $name == '' )
            $name = ( str_replace( 'controller', '', $this->controller ) ) . '/' . $this->action . '.php';

        if ( defined( 'XOOPS_CPFUNC_LOADED' ) )
            $file = $GLOBALS['rmTpl']->get_template('backend/' . $name, 'module', $this->directory);
        else
            $file = $GLOBALS['rmTpl']->get_template($name, 'module', $this->directory);

        return $file;

    }

    /**
     * Format the URL according to given controller, action and parameters.
     * The URL is based on module URL mode.
     * @param string $controller Name of the controller to access
     * @param string $action Name of the action
     * @param array $parameters Parameters to pass trough URL
     * @return string URL formatted
     */
    public function anchor( $controller, $action = '', $parameters = array() ){

        if($controller=='')
            return;

        $url = $this->url . ( $this->settings->permalinks ? '/' . $controller . '/' : '/?s=' . $controller . '/');
        $url .= $action != '' ? $action . '/' : '';

        foreach( $parameters as $name => $value ){
            $url .= $this->settings->permalinks ? $name . '/' . urlencode($value) . '/' : '&amp;' . $name . '=' . urlencode($value);
        }

        return $url;

    }

    protected function send_status($status_code, $controller, $action){

        header("HTTP/1.0 $status_code Not Found");
        http_response_code($status_code);

        include $GLOBALS['rmTpl']->get_template("rm-error-404.php", 'module', 'rmcommon');

        return true;

    }

    abstract public function get_url();

}
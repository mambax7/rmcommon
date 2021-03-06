<?php
/**
 * Common Utilities Framework for XOOPS
 *
 * Copyright © 2015 Eduardo Cortés http://www.eduardocortes.mx
 * -------------------------------------------------------------
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * -------------------------------------------------------------
 * @copyright    Eduardo Cortés (http://www.redmexico.com.mx)
 * @license      GNU GPL 2
 * @package      rmcommon
 * @author       Eduardo Cortés (AKA bitcero)    <i.bitcero@gmail.com>
 * @url          http://www.eduardocortes.mx
 */
require_once RMCPATH . '/include/tpl_functions.php';

/**
 * This file can handle templates for all modules and themes
 */
class RMTemplate
{
    private $type = 'front';
    /**
     * Stores the information for 'HEAD' section of template
     */
    public $tpl_head = [];
    /**
     * Stores the scripts information to include in theme
     */
    public $tpl_scripts  = [];
    public $tpl_hscripts = [];
    public $tpl_fscripts = [];

    private $attributes = [];

    /**
     * Stores all styles for HEAD section
     */
    public $tpl_styles = [];
    /**
     * Menu options for current element
     */
    private $tpl_menus = [];
    /**
     * Template Vars
     */
    private $tpl_vars = ['charset' => 'UTF-8'];
    /**
     * Messages for template
     */
    private $messages = [];
    /**
     * Menus for admin gui
     */
    private $menus = [];
    /**
     * Toolbar for admin gui
     */
    private $toolbar = [];
    /**
     * Help link
     */
    private $help_link = [];
    /**
     * Metas
     */
    private $metas = [];
    /**
     * Version to add as parameter to scripts and styles
     */
    private $version = '';

    /**
     * Body classes
     */
    private $body_classes = [];

    public function __construct()
    {
        global $cuSettings;

        $this->version = str_replace(' ', '-', RMCVERSION);

        if (defined('XOOPS_CPFUNC_LOADED')) {
            $this->add_jquery(true, true);

            return true;
        }

        if ($cuSettings->jquery) {
            return true;
        }

        $this->add_jquery(true);
    }

    /**
     * Use this method to instantiate EXMTemplate
     * @staticvar <type> $instance
     * @return RMTemplate
     */
    public static function getInstance()
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * @return RMTemplate
     * @deprecated
     */
    public static function get()
    {
        return self::getInstance();
    }

    public function header()
    {
        global $xoopsModule, $xoopsConfig, $xoopsOption, $xoopsTpl;

        if (defined('XOOPS_CPFUNC_LOADED')) {
            xoops_cp_header();
        } //ob_start();
        else {
            include XOOPS_ROOT_PATH . '/header.php';
        }
    }

    public function footer()
    {
        global $xoopsModule, $cuSettings, $xoopsConfig, $xoopsModuleConfig, $xoopsConfigMetaFooter, $xoopsOption, $xoopsUser, $common, $xoopsTpl;

        if (defined('XOOPS_CPFUNC_LOADED')) {
            $content = ob_get_clean();

            $content = $common->events()->trigger('rmcommon.raw.content', $content);

            ob_start();

            $cuSettings = RMSettings::cu_settings();
            $theme      = isset($cuSettings->theme) ? $cuSettings->theme : 'default';

            if (!file_exists(RMCPATH . '/themes/' . $theme . '/admin-gui.php')) {
                $theme = 'helium';
            }

            $rm_theme_url = RMCURL . '/themes/' . $theme;

            // Check if there are redirect messages
            $redirect_messages = [];
            if (isset($_SESSION['redirect_message'])) {
                foreach ($_SESSION['redirect_message'] as $msg) {
                    $redirect_messages[] = $msg;
                }
                unset($_SESSION['redirect_message']);
            }

            require_once RMCPATH . '/themes/' . $theme . '/admin-gui.php';
            $output = ob_get_clean();

            $output = RMEvents::get()->trigger('rmcommon.admin.output', $output);

            echo $output;

            RMEvents::get()->trigger('rmcommon.footer.admin.end', $output);
        } else {
            $vars = $this->get_vars();

            $xoopsTpl->assign($vars);

            require XOOPS_ROOT_PATH . '/footer.php';
        }
    }

    /**
     * Get a template from Current RMCommon Theme
     * @param string $file Template file name
     * @param string $type Elemernt type: module, builder or plugin
     * @param string $module Module name
     * @param string $element Element name, only when type is plugin or builder
     * @return string Template path
     */
    public static function path($file, $type = 'module', $module = '', $element = '')
    {
        global $cuSettings, $xoopsConfig, $xoopsModule;

        $type = '' == $type ? 'module' : $type;

        if ('' == $module && !$xoopsModule) {
            $module = 'rmcommon';
        } elseif ('' == $module && $xoopsModule) {
            $module = $xoopsModule->getVar('dirname');
        }

        if (!function_exists('xoops_cp_header')) {
            $theme = $xoopsConfig['theme_set'];
            $where = XOOPS_THEME_PATH . '/' . $theme;
            $where .= 'module' == $type ? '/modules/' : '/' . $element . 's/';
            $where .= $module . ('' != $element ? '/' . $element : '');

            if (is_file($where . '/' . $file)) {
                return $where . '/' . $file;
            }

            $where = XOOPS_ROOT_PATH . '/modules/' . $module . '/templates';
            $where .= 'module' != $type ? "/$type" : '';
            $where .= "/$file";

            if (is_file($where)) {
                return $where;
            }
        }

        $theme = isset($cuSettings->theme) ? $cuSettings->theme : 'default';

        if (!is_dir(RMCPATH . '/themes/' . $theme)) {
            $theme = 'default';
        }

        /**
         * Construct path according to element type
         */
        $where = 'modules/' . $module;
        $where .= 'plugin' == $type ? '/plugins/' . $element : '';

        if ('module' == $type || 'plugin' == $type) {
            $where = 'modules/' . $module;
            $where .= 'plugin' == $type ? '/plugins/' . $element : '';
            $lpath = RMCPATH . '/themes/' . $theme . '/' . $where . '/' . $file;
        } else {
            $where = 'modules/' . $module . '/builders/' . $module;
            $lpath = RMCPATH . '/themes/' . $theme . '/builders/' . $file;
        }

        if (file_exists($lpath)) {
            return $lpath;
        }

        if ('builder' == $type) {
            return XOOPS_ROOT_PATH . '/modules/' . $module . '/templates/builders/' . $file;
        }

        return XOOPS_ROOT_PATH . '/' . $where . '/templates/' . $file;
    }

    /**
     * <p><strong>Render a specific template file.</strong>
     * This method needs that variables are declared previously</p>
     *
     * <p>Example:</p>
     *
     * <p><em>We need to assign variables before to call <code>render()</code> method:</p>
     *
     * <pre>$template->assign('var', 'value');
     * $template->assign(array(
     *     'var1' => 'value',
     *     'var2' => 'value'
     * ));
     * </pre>
     *
     * <p>then we can call the method:</p>
     *
     * <pre>
     * $template->render('template-file.php', 'module', 'my-module');
     * </pre>
     *
     * @param        $file
     * @param string $type
     * @param string $module
     * @param string $element
     * @return string
     */
    public function render($file, $type = '', $module = '', $element = '')
    {
        global $cuSettings, $xoopsConfig, $xoopsModule, $xoopsModuleConfig, $cuIcons, $cuServices, $xoopsSecurity, $common, $xoopsTpl, $xoopsUser;

        if ('' == $type && is_file($file)) {
            $template = $file;
        } else {
            $template = static::path($file, $type, $module, $element);
        }

        if (!file_exists($template)) {
            throw new RMException(__("Template $template does not exists!", 'rmcommon'));

            return false;
        }

        /**
         * We extract all assigned variables
         */
        extract($this->tpl_vars);
        ob_start();
        require $template;
        $content = ob_get_clean();

        return $content;
    }

    public function display($file, $type = 'module', $module = '', $element = '')
    {
        echo $this->render($file, $type, $module, $element);
    }

    /**
     * Set the location identifier for current page
     * This identifier will help to RMCommon to find widgets, forms, etc
     * @param mixed $id
     */
    public function location_id($id)
    {
    }

    /**
     * Add a help lint to manage diferents sections
     * @param string $link Link to help resource
     */
    public function set_help($link)
    {
        //trigger_error(__('RMTemplate::set_help is deprecated. Use add_help instead.','rmcommon'), E_USER_WARNING);
        //$this->add_help($caption, $link);
        $this->add_help(__('Help', 'rmcommon'), $link);
    }

    public function help($single = 0)
    {
        if ($single) {
            return $this->help_link[0]['link'];
        }

        return $this->help_link;
    }

    /**
     * Add a help item to list of help links. This links will be shown in admin GUI only.
     *
     * @param string $caption Title of link
     * @param string $link    URL to load when this link is clicked
     */
    public function add_help($caption, $link)
    {
        $this->help_link[] = [
            'caption' => $caption,
            'link'    => $link,
        ];
    }

    /**
     * Add a message to show in theme
     * @param string $message Message to show
     * @param int $level Level of message (1 will show error)
     */
    public function add_message($message, $level = 0)
    {
        $this->messages[] = ['text' => $message, 'level' => $level];
    }

    /**
     * Get all messages
     * @return array
     */
    public function get_messages()
    {
        return $this->messages;
    }

    /**
     * Add elements to the &lt;head&gt; section of the HTML file.
     * If you need to add scripts or styles consider to use add_script() or add_style() methods instead.
     * @param string|array $head Elements to add
     */
    public function add_head($head)
    {
        // Dynamic header (It must be be an array)
        if (is_array($head)):
            array_merge($this->tpl_head, $head);
        else:
            $this->tpl_head[] = $head;
        endif;
    }

    /**
     * Get all items in head
     * @return array
     */
    public function get_head()
    {
        return $this->tpl_head;
    }

    /**
     * Add a script to a theme template that will be shown in < head > section or at the bottom of HTML code.
     *
     * Example of use:
     * <pre>
     * global $rmTpl;
     * $rmTpl->add_script( 'my-script', 'my-script.css', 'mywords', array(
     *              'version' => '1.0',
     *              'directory' => 'include',
     *              'footer' => 1
     *          ) );
     * </pre>
     *
     * <h4>Specifying a file</h4>
     *
     * The second parameter (file) must be a valid full URL that begins with http:// or https://, or a file name
     * that exists in directory "<em>js</em>" of module. There exists an exception to this behaviour when option
     * 'directory' is declared.
     *
     * <h4>Specifying a module</h4>
     *
     * The third parameter must correspond to a existing module or rmcommon plugin.
     * When you provide this parameter, then the script will be searched in the modules directory:
     *
     * e.g. /modules/<em>mywords</em>/js
     *
     * If this parameter is not present, then current module will be used. This means that if you ara in "mywords"
     * module, the script will be searched in this module.
     *
     * <h4>Available options</h4>
     *
     * The fourth parameter (options) is optional. This parameter must be an array with all options that you will to
     * add to your script.
     *
     * Exists a set of basic options that will be used in order to format the script. Aditionally, you can specify
     * arbitrary options that you need to use.
     *
     * The basic options are:
     *
     * <ul>
     *  <li>
     *      <strong>version</strong>. Indicate the version that will be added to script. This version can be the module
     *      version, the script version, etc. If this option is not provided, then Common Utilities version will be
     *      added automatically. The finality of this parameter is to prevent issues with browsers cache.
     *  </li>
     *  <li>
     *      <strong>directory</strong>. Indicates that script is located in a subdirectory of the module directory.
     *      e.g. If you provide <code>'directory' => 'includes'</code> then script will be searches in
     *      <code>modules/my-module/includes/js</code> directory.
     *  </li>
     *  <li>
     *      <strong>type</strong>. Indicates the tyoe of the script. If this parameter is not provided, then
     *      "text/javascript" will be used as default.
     *  </li>
     *  <li>
     *      <strong>footer</strong>. Indicates that the script must be included to the end of HTML file, just before
     *      of &lt;/body&gt; tag.
     *  </li>
     * </ul>
     *
     * In addition, you can add your own parameter to script, and they will be included.
     * For example, if you add next custom parameters:
     * <pre>
     * $options = array(
     *      'data-something' => 'arbitrary content',
     *      'rel'            => 'script'
     * );
     * </pre>
     *
     * then the script tag will be formatted as foillow:
     * <pre>
     * &lt;script type="text/javascript" src="script url" data-something="arbitrary-content" rel="script"&gt;&lt;/script&gt;
     * </pre>
     *
     * @param string $file    File name or full URL
     * @param string $element Owner element name
     * @param array  $options Array with options to be added to script
     * @param string $owner   Owner type for the script|style. Can be 'theme' or empty
     * @return bool
     */
    public function add_script($file, $element = '', $options = [], $owner = '')
    {
        global $xoopsModule, $cuSettings, $xoopsConfig;

        $idProvided = false;

        if (array_key_exists('id', $options) && '' != $options['id']) {
            $id         = $options['id'];
            $idProvided = true;
            unset($options['id']);
        }

        if ('jquery.min.js' == $file || $cuSettings->cdn_jquery_url == $file) {
            return $this->add_jquery(false);
        }

        if (false !== mb_strpos($file, 'bootstrap.js') || (false !== mb_strpos($file, 'bootstrap.min.js') && 'theme' != $owner)) {
            return $this->add_bootstrap('js');
        }

        // Check if file is a full URL
        $remote_script = preg_match("/^(\/\/)|(http:\/\/)|(https:\/\/)|(\/\/)/", $file);

        $version   = isset($options['version']) ? $options['version'] : '';
        $directory = isset($options['directory']) ? $options['directory'] : '';

        if ('' == $element) {
            $remote_script = 1;
        }

        if (!$idProvided) {
            $parts = pathinfo($file);
            $id    = str_replace('.min', '', $parts['filename']);
            $id    = mb_strtolower($element) . '-' . TextCleaner::getInstance()->sweetstring($id) . '-js';
            unset($parts);
        }

        if ($remote_script > 0) {
            $script_url = $file;
        } else {
            $script_url = $this->generate_url($file, $element, 'theme' == $owner ? 'theme-js' : 'js', $directory, $version);
        }

        if ('' == $script_url) {
            return false;
        }

        if (array_key_exists($id, $this->tpl_scripts) && $this->tpl_scripts[$id]['url'] != $script_url) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            trigger_error(sprintf(
                __('Script %s will be replaced for new value in file %s on line %s', 'classifieds'),
                '<strong>' . $id . '</strong>',
                '<strong>' . $trace[0]['file'] . '</strong>',
                '<strong>' . $trace[0]['line'] . '</strong>'
            ));
        }

        // Add the new script to array (replacing old if exists)
        $this->tpl_scripts[$id] = [
            'url'    => $script_url,
            'type'   => isset($options['type']) ? $options['type'] : 'text/javascript',
            'footer' => isset($options['footer']) ? $options['footer'] : (isset($options['location']) && $options['location'] = 'footer' ? 1 : 0),
        ];

        // Delete unused options
        unset($options['version']);
        unset($options['directory']);
        unset($options['footer']);
        unset($options['type']);

        $this->tpl_scripts[$id] = array_merge($this->tpl_scripts[$id], $options);

        return true;
    }

    /**
     * Create the URL for scripts or styles according to given parameters.
     *
     * @param string $file      File to locate
     * @param string $element   Module or plugin
     * @param string $type      Can be 'js' or 'css', 'theme-js' or 'theme-css'
     * @param string $directory Subdirectory where the script|style will be searched
     * @param string $version   The version that will be added to script|style URL
     * @return string
     */
    public function generate_url($file, $element, $type = 'js', $directory = '', $version = '')
    {
        global $xoopsConfig, $rmEvents, $cuSettings;

        if ('' == $file) {
            return '';
        }

        if ($cuSettings->development) {
            $version = date('dmyhis', time());
        } else {
            $version = '' == $version ? str_replace(' ', '-', RMCVERSION) : $version;
        }

        if ('js' == $type || 'css' == $type) {
            // Possibles paths in order of importance
            // 1. Theme
            if (defined('XOOPS_CPFUNC_LOADED')) {
                $paths['theme'] = RMCPATH . '/themes/' . $cuSettings->theme . "/$type/" . $element;
                $paths['theme'] .= '' != $directory ? '/' . $directory : '';
            } else {
                $paths['theme'] = XOOPS_THEME_PATH . '/' . $xoopsConfig['theme_set'] . "/$type/" . $element;
                $paths['theme'] .= '' != $directory ? '/' . $directory : '';
            }

            $paths['theme'] .= '/' . ltrim($file, '/');

            // 2. Module
            $paths['module'] = XOOPS_ROOT_PATH . '/modules/' . $element;
            $paths['module'] .= '' != $directory ? '/' . $directory : '';
            $paths['module'] .= "/$type/" . ltrim($file, '/');
        } else {
            $type = 'theme-css' == $type ? 'css' : 'js';

            // Add path for theme script|style
            if (defined('XOOPS_CPFUNC_LOADED')) {
                $paths['theme'] = RMCPATH . '/themes/' . $cuSettings->theme;
            } else {
                $paths['theme'] = XOOPS_THEME_PATH . '/' . $xoopsConfig['theme_set'];
            }
            $paths['theme'] .= '' != $directory ? '/' . $directory : '';
            $paths['theme'] .= "/$type/" . ltrim($file, '/');
        }

        // Allow other components to add new paths where scripts can be searched
        $paths = RMEvents::get()->run_event('rmcommon.scripts.paths', $paths, $file, $element, $directory, $version);

        foreach ($paths as $path) {
            if (!file_exists(preg_replace("/(.*)(\?.*)$/", '$1', $path))) {
                continue;
            }

            $url = RMUris::relative_url(str_replace(XOOPS_ROOT_PATH, XOOPS_URL, $path));
            // Check if parameter 'version' exists in url
            if (!preg_match("/.*(\?.*)$/", $url)) {
                return $url . '?version=' . $version;
            }

            if (!preg_match('/.*(version=).*$/', $url)) {
                return $url . '&version=' . $version;
            }

            return $url;
        }

        return null;
    }

    /**
     * This method add explicit scripts to HTML code.
     * The scripts are added in a single &lt;script&gt; tag:
     * <pre>
     * &lt;script type="text/javascript"&gt;
     * // All added scripts
     * &lt;/script&gt;
     * </pre>
     *
     * If you provide the $footer parameter, then the script will be added to bottom of page, just before of
     * &lt;body&gt; tag.
     *
     * NOTE: If you need to add scripts or styles files, consider to use add_script() or add_style() methods.
     *
     * @param     $script
     * @param int $footer
     * @return bool
     */
    public function add_inline_script($script, $footer = 0)
    {
        if ('' == $script) {
            return false;
        }

        if ($footer) {
            $this->tpl_fscripts[] = $script;
        } else {
            $this->tpl_hscripts[] = $script;
        }

        return true;
    }

    /**
     * Get inline scripts
     *
     * @param int $footer
     *
     * @return string
     */
    public function inline_scripts($footer = 0)
    {
        $ret = '<script type="text/javascript">' . "\n";

        $scripts = $footer ? $this->tpl_fscripts : $this->tpl_hscripts;

        foreach ($scripts as $script) {
            $ret .= $script . "\n";
            $ret .= '//' . str_repeat('-', 20) . "\n";
        }

        $ret .= '</script>';

        return $ret;
    }

    /**
     * Add rmcommon JS handler
     */
    public function addCuHandler()
    {
        $this->tpl_scripts['rmcommon-js'] = [
            'url'    => RMUris::relative_url(RMCURL . '/js/cu-handler.js'),
            'type'   => 'text/javascript',
            'footer' => 1,
        ];
    }

    /**
     * Add jQuery script to site header
     * @param mixed $ui
     * @param mixed $force
     * @return bool
     */
    public function add_jquery($ui = true, $force = false)
    {
        global $cuSettings;

        if (!$cuSettings->jquery && !$force) {
            return true;
        }

        if (!isset($this->tpl_scripts['jquery'])) {
            if ($cuSettings->cdn_jquery) {
                $this->tpl_scripts['jquery'] = [
                    'url'    => $cuSettings->cdn_jquery_url,
                    'type'   => 'text/javascript',
                    'footer' => 0,
                ];
            } else {
                $this->tpl_scripts['jquery'] = [
                    'url'    => RMUris::relative_url(RMCURL . '/include/js/jquery.min.js'),
                    'type'   => 'text/javascript',
                    'footer' => 0,
                ];
            }
        }

        if ($ui && !isset($this->tpl_scripts['jqueryui'])) {
            if ($cuSettings->cdn_jquery) {
                $this->tpl_scripts['jqueryui'] = [
                    'url'    => $cuSettings->cdn_jqueryui_url,
                    'type'   => 'text/javascript',
                    'footer' => 0,
                ];

                $this->tpl_styles['jqueryui-css'] = [
                    'url'    => 'https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css',
                    'type'   => 'text/css',
                    'footer' => 0,
                ];
            } else {
                $this->tpl_scripts['jqueryui'] = [
                    'url'    => RMUris::relative_url(RMCURL . '/include/js/jquery-ui.min.js'),
                    'type'   => 'text/javascript',
                    'footer' => 0,
                ];

                $this->tpl_styles['jqueryui-css'] = [
                    'url'    => RMUris::relative_url(RMCURL . '/css/js-widgets.css'),
                    'type'   => 'text/css',
                    'footer' => 0,
                ];
            }
        }

        return true;
    }

    /**
     * Add jQuery script to site header
     * @param mixed $type
     * @return bool
     */
    public function add_bootstrap($type = 'css')
    {
        global $cuSettings;

        if ('css' == $type && isset($this->tpl_styles['bootstrap'])) {
            return true;
        }

        if ('js' == $type && isset($this->tpl_scripts['bootstrap'])) {
            return true;
        }

        if ($cuSettings->cdn_bootstrap) {
            if ('js' == $type) {
                $this->tpl_scripts['jsbootstrap'] = [
                    'url'    => $cuSettings->cdn_jsbootstrap_url,
                    'type'   => 'text/javascript',
                    'footer' => 1,
                ];
            } else {
                $this->tpl_styles['bootstrap'] = [
                    'url'    => $cuSettings->cdn_bootstrap_url,
                    'type'   => 'text/css',
                    'footer' => 0,
                ];
            }

            return true;
        }

        if ('js' == $type) {
            $this->tpl_scripts['jsbootstrap'] = [
                'url'    => RMUris::relative_url(RMCURL . '/js/bootstrap.min.js'),
                'type'   => 'text/javascript',
                'footer' => 1,
            ];
        } else {
            $this->tpl_styles['bootstrap'] = [
                'url'    => RMUris::relative_url(RMCURL . '/css/bootstrap.min.css'),
                'type'   => 'text/css',
                'footer' => 0,
            ];
        }

        return true;
    }

    public function add_fontawesome()
    {
        global $cuSettings;

        if (isset($this->tpl_styles['fontawesome'])) {
            return true;
        }

        if ($cuSettings->cdn_fa) {
            $this->tpl_styles['fontawesome'] = [
                'url'    => $cuSettings->cdn_fa_url,
                'type'   => 'text/css',
                'footer' => 0,
            ];

            return true;
        }

        $this->tpl_styles['fontawesome'] = [
            'url'    => RMUris::relative_url(RMCURL . '/css/font-awesome.min.css'),
            'type'   => 'text/css',
            'footer' => 0,
        ];

        return true;
    }

    /**
     * Get all scripts stored in class
     * @param mixed $make
     * @return array|mixed
     */
    public function get_scripts($make = false)
    {
        $ev                = RMEvents::get();
        $this->tpl_scripts = $ev->run_event('rmcommon.get.scripts', $this->tpl_scripts);

        $this->process_scripts();

        if (!$make) {
            return $this->tpl_scripts;
        }

        $rtn = ['footer' => '', 'header' => ''];
        foreach ($this->tpl_scripts as $id => $script) {
            if ($script['footer']) {
                $rtn['footer'] .= "\n" . '<script type="' . $script['type'] . '" id="' . $id . '" src="' . $script['url'] . '"></script>';
            } else {
                $rtn['header'] .= "\n" . '<script type="' . $script['type'] . '" id="' . $id . '" src="' . $script['url'] . '"></script>';
            }
        }

        if (!empty($this->tpl_head)) {
            foreach ($this->tpl_head as $script) {
                $rtn['heads'] = $script . "\n";
            }
        } else {
            $rtn['heads'] = '';
        }

        return $rtn;
    }

    /**
     * This function recreates the scripts array in order to
     * verify accomplish of dependencies
     */
    private function process_scripts()
    {
        $scripts = [];
        $missing = [];

        if (array_key_exists('jquery', $this->tpl_scripts)) {
            $scripts['jquery'] = $this->tpl_scripts['jquery'];
            unset($this->tpl_scripts['jquery']);
        }

        if (array_key_exists('rmcommon-js', $this->tpl_scripts)) {
            $scripts['rmcommon-js'] = $this->tpl_scripts['rmcommon-js'];
            unset($this->tpl_scripts['rmcommon-js']);
        }

        foreach ($this->tpl_scripts as $id => $item) {
            if (!array_key_exists('required', $item)) {
                $scripts[$id] = $item;
            }

            if (array_key_exists('required', $item) && array_key_exists($item['required'], $scripts)) {
                $scripts = $this->insert_script_after($scripts, $item['required'], $id, $item);
            } else {
                $missing[$id] = $item;
            }
        }

        // Now read $missing array
        foreach ($missing as $id => $script) {
            // Check if script has been added
            if (array_key_exists('required', $script) && array_key_exists($script['required'], $scripts)) {
                $scripts = $this->insert_script_after($scripts, $script['required'], $id, $script);
                continue;
            }

            // Check is required script exists in missing array
            if (array_key_exists('required', $script) && array_key_exists($script['required'], $missing)) {
                $scripts[$script['required']] = $missing[$script['required']];
                $scripts                      = $this->insert_script_after($scripts, $script['required'], $id, $script);
                continue;
            }

            $scripts[$id] = $script;
        }

        $this->tpl_scripts = $scripts;

        return true;
    }

    private function insert_script_after($scripts, $required, $id, $data)
    {
        $total  = count($scripts);
        $return = [];

        foreach ($scripts as $ids => $script) {
            $return[$ids] = $script;

            if ($ids == $required && !array_key_exists($id, $scripts)) {
                $return[$id] = $data;
            }
        }

        return $return;
    }

    /**
     * Clear all stored scripts. Be careful when use this method.
     */
    public function clear_scripts()
    {
        $this->tpl_scripts = [];
    }

    /**
     * Add stylesheets to the HTML code according to given parameters.
     * This function allows to add styles from modules and themes, in a similar way to add_script() method.
     *
     * @param string $file    File to add. Can be a file name that exists locally, or a well formed URL.
     * @param string $element Name of the owner element. Can be the name of a module or a theme.
     * @param array  $options Additional parameters to add to style code.
     * @param string $owner   Type of owner element. Possible values can be 'theme' or empty;
     * @return bool|void
     */
    public function add_style($file, $element = '', $options = [], $owner = '')
    {
        global $xoopsModule, $cuSettings, $xoopsConfig;
        // Check if file is a full URL
        $remote_script = preg_match("/^(http:\/\/)|(https:\/\/)|(\/\/)/", $file);

        if ('theme' != $owner && (false !== mb_strpos($file, 'bootstrap.css') || false !== mb_strpos($file, 'bootstrap.min.css'))) {
            return $this->add_bootstrap('css');
        }

        if ($cuSettings->cdn_fa && (false !== mb_strpos($file, 'font-awesome.css') || false !== mb_strpos($file, 'font-awesome.min.css'))) {
            return $this->add_fontawesome();
        }

        $version   = isset($options['version']) ? $options['version'] : '';
        $directory = isset($options['directory']) ? $options['directory'] : '';

        if ('theme' == $owner) {
            $element = '' == $element ? (defined('XOOPS_CPFUNC_LOADED') ? $cuSettings->theme : $xoopsConfig['theme_set']) : $element;
        } else {
            $element = '' == $element ? ($xoopsModule ? $xoopsModule->getVar('dirname') : '') : $element;
        }

        if (isset($options['id']) && '' != $options['id']) {
            $id = $options['id'];
            unset($options['id']);
            $providedId = true;
        } else {
            $providedId = false;
        }

        if (!$providedId) {
            $parts = pathinfo($file);
            $id    = str_replace('.min', '', $parts['filename']);
            $id    = mb_strtolower($element) . '-' . TextCleaner::getInstance()->sweetstring($id) . '-css';
            unset($parts);
        }

        if ($remote_script > 0) {
            $style_url = $file;
        } else {
            $style_url = $this->generate_url($file, $element, 'theme' == $owner ? 'theme-css' : 'css', $directory, $version);
        }

        if ('' == $style_url) {
            return;
        }

        // Add the new script to array (replacing old if exists)
        $this->tpl_styles[$id] = [
            'url'    => $style_url,
            'type'   => isset($options['type']) ? $options['type'] : 'text/css',
            'footer' => isset($options['footer']) ? $options['footer'] : 0,
        ];

        // Delete unused options
        unset($options['version']);
        unset($options['directory']);
        unset($options['footer']);
        unset($options['type']);

        $this->tpl_styles[$id] = array_merge($this->tpl_styles[$id], $options);
    }

    /**
     * Get the redirection messages
     * @return array
     */
    public function get_redirection_messages()
    {
        if (isset($_SESSION['redirect_message'])) {
            return $_SESSION['redirect_message'];
        }

        return [];
    }

    /**
     * Get all styles stored in class
     * @param mixed $make
     * @return array|mixed|string
     */
    public function get_styles($make = false)
    {
        $ev               = RMEvents::get();
        $this->tpl_styles = $ev->run_event('rmcommon.get.styles', $this->tpl_styles);

        $this->process_styles();

        if (!$make) {
            return $this->tpl_styles;
        }

        $rtn = '';
        foreach ($this->tpl_styles as $id => $style) {
            $style['type'] = !isset($style['type']) || '' == $style['type'] ? 'text/css' : $style['type'];

            $rtn .= "\n" . '<link rel="stylesheet" type="' . $style['type'] . '" id="' . $id . '" href="' . $style['url'] . '">';
        }

        return $rtn;
    }

    /**
     * This function recreates the styles array in order to
     * verify accomplish of dependencies
     */
    private function process_styles()
    {
        $styles  = [];
        $missing = [];

        foreach ($this->tpl_styles as $id => $item) {
            if (!array_key_exists('required', $item)) {
                $styles[$id] = $item;
            }

            if (array_key_exists('required', $item) && array_key_exists($item['required'], $styles)) {
                $styles = $this->insert_script_after($styles, $item['required'], $id, $item);
            } else {
                $missing[$id] = $item;
            }
        }

        // Now read $missing array
        foreach ($missing as $id => $style) {
            // Check if script has been added
            if (array_key_exists('required', $style) && array_key_exists($style['required'], $styles)) {
                $styles = $this->insert_script_after($styles, $style['required'], $id, $style);
                continue;
            }

            // Check is required script exists in missing array
            if (array_key_exists('required', $style) && array_key_exists($style['required'], $missing)) {
                $styles[$style['required']] = $missing[$style['required']];
                $styles                     = $this->insert_script_after($styles, $style['required'], $id, $style);
                continue;
            }

            $styles[$id] = $style;
        }

        $this->tpl_styles = $styles;

        return true;
    }

    /**
     * Clear all styles stored previously.
     * @param null|mixed $id Style ID
     * @return bool
     */
    public function clear_styles($id = null)
    {
        if (null === $id) {
            $this->tpl_styles = [];

            return true;
        }

        if (array_key_exists($id, $this->tpl_styles)) {
            unset($this->tpl_styles[$id]);

            return true;
        }

        return false;
    }

    /**
     * Assign template vars
     * @param string|array $var Variable name or array with multiple variables and their values
     * @param null|mixed $value
     */
    public function assign($var, $value = null)
    {
        if (is_array($var)) {
            foreach ($var as $name => $value) {
                $this->tpl_vars[$name] = $value;
            }
        } else {
            $this->tpl_vars[$var] = $value;
        }
    }

    /**
     * Store vars inside template as array
     * @param string $varname name
     * @param mixed $value Var value
     */
    public function append($varname, $value)
    {
        $this->tpl_vars[$varname][] = $value;
    }

    /**
     * Get all template vars as an array
     */
    public function get_vars()
    {
        return $this->tpl_vars;
    }

    /**
     * Get a single template var
     *
     * @param string $varname Var name
     * @return mixed
     */
    public function get_var($varname)
    {
        if (isset($this->tpl_vars[$varname])) {
            return $this->tpl_vars[$varname];
        }

        return false;
    }

    /**
     * Add option to menu. This method is only functional in admin section or with the themes
     * that support this feature
     *
     * @param string $caption Caption, Menu parent name
     * @param string $link Option link url
     * @param string $icon Option icon url
     * @param mixed $class
     * @param string $target Target window (_clank, _self, etc.)

     */
    public function add_menu_option($caption, $link, $icon = '', $class = '', $target = '')
    {
        if ('' == $caption || '' == $link) {
            return;
        }

        $id = crc32($link);

        if (isset($this->tpl_menus[$id])) {
            return;
        }

        $this->tpl_menus[$id] = ['caption' => $caption, 'link' => $link, 'icon' => $icon, 'class' => $class, 'target' => $target, 'type' => 'normal'];
    }

    public function add_separator()
    {
        $this->tpl_menus = ['type' => 'separator'];
    }

    /**
     * Get all menu options
     */
    public function menu_options()
    {
        $this->tpl_menus = RMEvents::get()->run_event('rmcommon.menus_options', $this->tpl_menus, $this);

        return $this->tpl_menus;
    }

    /**
     * Menu Widgets
     * @param mixed $title
     * @param mixed $link
     * @param mixed $icon
     * @param mixed $class
     * @param mixed $location
     * @param mixed $options
     */
    public function add_menu($title, $link, $icon = '', $class = '', $location = '', $options = [])
    {
        $this->menus[] = [
            'title'    => $title,
            'link'     => $link,
            'icon'     => $icon,
            'class'    => $class,
            'location' => $location,
            'options'  => $options,
        ];
    }

    public function get_menus()
    {
        return $this->menus;
    }

    /**
     * Add a new element to toolbar array
     * Example:
     * <code>RMTemplate::getInstance()->add_tool(
     *     array(
     *         'title'    => 'Caption of tool',
     *         'link'     => 'http://...',
     *         'icon'     => 'url_to_icon',
     *         'location' => 'name of page where tool will be activated',
     *         'attributes' => 'array with html attributes to add to tag',
     *         'options'  => array(
     *              array(
     *                  'caption' => 'Text',
     *                  'url'     => 'Link for item',
     *                  'icon'    => 'url to icon',
     *                  'attributes' => 'array with HTML attributes'
     *             )
     *         )
     *     )
     * );</code>
     * @param string|array $data       <p>Could be a title that will be uses as caption for button or you can pass an array with all button properties</p>
     * @param string       $link       <p>URL for link</p>
     * @param string       $icon       <p>The icon could be a image URL relative to module path or a full URL.</p>
     * @param string       $location
     * @param array        $attributes <p>HTML attributes</p>
     * @param array        $options    <p>Options to add to this tool</p>
     */
    public function add_tool($data, $link = '', $icon = '', $location = '', $attributes = [], $options = [])
    {
        if (is_array($data)) {
            $this->toolbar[] = $data;
        } else {
            $this->toolbar[] = [
                'title'      => $data,
                'link'       => $link,
                'icon'       => $icon,
                'location'   => $location,
                'attributes' => $attributes,
                'options'    => $options,
            ];
        }
    }

    public function get_toolbar()
    {
        global $common, $xoopsModule;

        /*
         * Call preloaders in order to include new toolbar buttons
         * Parameters:
         *     Current toolbar controls
         *     Current XOOPS module (verify if variable is defined
         *     Is admin side or not
         */
        $this->toolbar = $common->events()->trigger('rmcommon.render.toolbar', $this->toolbar, $xoopsModule, XOOPS_CPFUNC_LOADED);

        return $this->toolbar;
    }

    /**
     * Add metas to head
     * @param mixed $name
     * @param mixed $content
     */
    public function add_meta($name, $content)
    {
        $this->metas[$name] = $content;
    }

    public function get_metas()
    {
        return $this->metas;
    }

    /**
     * Gets an instance of XoopsTpl if exists, or create a new one if not.
     * @return XoopsTpl
     */
    public function xo_tpl()
    {
        global $xoopsTpl, $xoopsConfig;

        if ($xoopsTpl instanceof \XoopsTpl) {
            return $xoopsTpl;
        }

        // include Smarty template engine and initialize it
        require_once $GLOBALS['xoops']->path('class/template.php');
        require_once $GLOBALS['xoops']->path('class/theme.php');
        require_once $GLOBALS['xoops']->path('class/theme_blocks.php');

        $xoopsThemeFactory                = null;
        $xoopsThemeFactory                = new xos_opal_ThemeFactory();
        $xoopsThemeFactory->allowedThemes = $xoopsConfig['theme_set_allowed'];
        $xoopsThemeFactory->defaultTheme  = $xoopsConfig['theme_set'];

        $xoTheme  = $xoopsThemeFactory->createInstance(['contentTemplate' => @$xoopsOption['template_main']]);
        $xoopsTpl =& $xoTheme->template;

        return $xoopsTpl;
    }

    public function fetch_smarty($tpl_file, $element, $type = 'module', $plugin = '')
    {
        $file = $this::path($tpl_file, $type, $element, $plugin);

        $tpl = $this->xo_tpl();

        return $tpl->fetch($file);
    }

    public function add_body_class($class)
    {
        if ('' == $class) {
            return null;
        }

        $this->body_classes[] = $class;
    }

    public function body_classes()
    {
        return implode(' ', $this->body_classes);
    }

    /**
     * Assign attributes to an element
     * Currently, valid elements only are html and body
     * @param       $element
     * @param array $attributes
     * @return mixed
     */
    public function add_attribute($element, $attributes)
    {
        if (!in_array($element, ['html', 'body'], true)) {
            return false;
        }

        foreach ($attributes as $id => $value) {
            if (!array_key_exists($element, $this->attributes)) {
                $this->attributes[$element][$id] = $value;
                continue;
            }

            if (array_key_exists($id, $this->attributes[$element])) {
                $this->attributes[$element][$id] = $this->attributes[$element][$id] . ' ' . $value;
            } else {
                $this->attributes[$element][$id] = $value;
            }
        }

        return true;
    }

    public function clear_attributes($element)
    {
        if (!in_array($element, ['html', 'body'], true)) {
            return false;
        }

        $attributes = [];

        foreach ($this->attributes as $id => $attrs) {
            if ($id == $element) {
                continue;
            }

            $attributes[$id] = $attrs;
        }

        return true;
    }

    public function render_attributes($element = '')
    {
        /*if (!in_array($element, ['html', 'body'])) {
            return false;
        }*/

        if ('' != $element && !array_key_exists($element, $this->attributes)) {
            return null;
        }

        if ('' == $element) {
            $ret = [];

            foreach ($this->attributes as $element => $values) {
                $ret[$element] = $this->render_attributes($element);
            }

            return $ret;
        }
        $return = '';
        foreach ($this->attributes[$element] as $id => $value) {
            if (null === $value) {
                $return .= $id . ' ';
            } else {
                $return .= $id . '="' . $value . '" ';
            }
        }

        return trim($return);
    }

    /*
    DEPRECATED METHODS
    =======================================
    */

    /**
     * This function add a script directly from an element
     * @param mixed $file
     * @param mixed $element
     * @param mixed $subfolder
     * @param mixed $type
     * @param mixed $more
     * @param mixed $footer
     * @deprecated
     */
    public function add_local_script($file, $element = 'rmcommon', $subfolder = '', $type = 'text/javascript', $more = '', $footer = false)
    {
        trigger_error(sprintf(__('Method %s is deprecated. Use %s::%s instead.', 'rmcommon'), __METHOD__, 'RMTemplate', 'add_script'), E_USER_DEPRECATED);

        $this->add_script($file, $element, [
                                   'directory'  => $subfolder,
                                   'type'       => $type,
                                   'footer'     => $footer,
                                   'data-extra' => $more,
                               ]);
    }

    /**
     * @param mixed $script
     * @param mixed $theme
     * @param mixed $subfolder
     * @param mixed $type
     * @param mixed $more
     * @param mixed $footer
     * @deprecated Use add_script() instead
     */
    public function add_theme_script($script, $theme = '', $subfolder = '', $type = 'text/javascript', $more = '', $footer = false)
    {
        trigger_error(sprintf(__('Method %s is deprecated. Use %s::%s instead.', 'rmcommon'), __METHOD__, 'RMTemplate', 'add_script'), E_USER_DEPRECATED);

        $this->add_script($script, $theme, [
                                     'footer'     => $footer,
                                     'type'       => $type,
                                     'data-extra' => $more,
                                 ], 'theme');
    }

    /**
     * @param mixed $sheet
     * @param mixed $element
     * @param mixed $subfolder
     * @param mixed $media
     * @param mixed $more
     * @param mixed $footer
     * @deprecated Use add_style() instead.
     */
    public function add_xoops_style($sheet, $element = 'rmcommon', $subfolder = '', $media = 'all', $more = '', $footer = false)
    {
        trigger_error(sprintf(__('Method %s is deprecated. Use %s::%s instead.', 'rmcommon'), __METHOD__, 'RMTemplate', 'add_style()'), E_USER_DEPRECATED);

        $this->add_style($sheet, $element, [
                                   'directory'  => $subfolder,
                                   'media'      => $media,
                                   'data-extra' => $more,
                                   'footer'     => $footer,
                               ]);
    }

    /**
     * @param mixed $sheet
     * @param mixed $theme
     * @param mixed $subfolder
     * @param mixed $media
     * @param mixed $more
     * @param mixed $footer
     * @deprecated Use add_style() instead
     */
    public function add_theme_style($sheet, $theme = '', $subfolder = '', $media = 'all', $more = '', $footer = false)
    {
        trigger_error(sprintf(__('Method %s is deprecated. Use %s::%s instead.', 'rmcommon'), __METHOD__, 'RMTemplate', 'add_style()'), E_USER_DEPRECATED);

        $this->add_style($sheet, $theme, [
                                   'directory'  => $subfolder,
                                   'media'      => $media,
                                   'data-extra' => $more,
                                   'footer'     => $footer,
                               ], 'theme');
    }

    /**
     * @param mixed $sheet
     * @param mixed $element
     * @param mixed $subfolder
     * @deprecated Use generate_url() instead.
     * @return string
     */
    public function style_url($sheet, $element = 'rmcommon', $subfolder = '')
    {
        trigger_error(sprintf(__('Method %s is deprecated. Use %s::%s instead.', 'rmcommon'), __METHOD__, 'RMTemplate', 'generate_url()'), E_USER_DEPRECATED);

        return $this->generate_url($sheet, $element, 'css');
    }

    /**
     * @param     $script
     * @param int $footer
     * @deprecated Use add_inline_script() instead.
     */
    public function add_head_script($script, $footer = 0)
    {
        $this->add_inline_script($script, 0);
    }

    /**
     * @deprecated Use inline_scripts() instead.
     * Get all head scripts
     */
    public function head_scripts()
    {
        return $this->inline_scripts(0);
    }

    /**
     * @param        $file
     * @param string $type
     * @param string $module
     * @param        $element
     * @return string
     * @deprecated Use path() instead
     */
    public function get_template($file, $type = 'module', $module = '', $element = '')
    {
        return static::path($file, $type, $module, $element);
    }
}

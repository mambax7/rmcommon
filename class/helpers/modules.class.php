<?php
/**
 * Common Utilities Framework for Xoops
 *
 * Copyright © 2015 Eduardo Cortés http://www.redmexico.com.mx
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
 * @url          http://www.redmexico.com.mx
 * @url          http://www.eduardocortes.mx
 */

/**
 * This class contains methods that allow to work with modules directly
 */
class RMModules
{
    /**
     * Retrieves and format the version of a given module.
     * If $module parameter is not given, then will try to get the current module version.
     *
     * Example with <strong>formatted</strong> result:
     * <pre>
     * $version = RMModules::get_module_version('mywords');
     * </pre>
     *
     * Get the same result:
     * <pre>
     * $version = RMModules::get_module_version('mywords', true, 'verbose');
     * </pre>
     *
     * Both previous examples return something like this:
     * <pre>MyWords 2.1.3 production</pre>
     *
     * Example 2. Get an array of values:
     * <pre>
     * print_r(RMModules::get_module_version('mywords', true, 'raw');
     * </pre>
     *
     * Will return:
     * <pre>
     * array
     *
     * @param string $module Module directory to check
     * @param bool   $name   Return the name and version (true) or only version (false)
     * @param string $type   Type of data to get: 'verbose' get a formatted string, 'raw' gets an array with values.
     * @return array|string
     */
    public static function get_module_version($module = '', $name = true, $type = 'verbose')
    {
        global $xoopsModule;

        //global $version;
        if ('' != $module) {
            if ($xoopsModule && $xoopsModule->dirname() == $module) {
                $mod = $xoopsModule;
            } else {
                $mod = new XoopsModule();
            }
        } else {
            $mod = new XoopsModule();
        }

        $mod->loadInfoAsVar($module);
        $version = &$mod->getInfo('rmversion');
        $version = is_array($version) ? $version : ['major' => $version, 'minor' => 0, 'revision' => 0, 'stage' => 0, 'name' => $mod->getInfo('name')];

        if ('raw' == $type) {
            return $version;
        }

        return self::format_module_version($version, $name);
    }

    /**
     * Format a given array with version information for a module.
     *
     * @param array $version Array with version values
     * @param bool  $name    Include module name in return string
     * @return string
     */
    public static function format_module_version($version, $name = false)
    {
        return RMFormat::version($version, $name);
    }

    /**
     * Load a given Xoops Module
     * @param string|int $id Indentifier of module. Could be dirname or numeric ID
     * @return XoopsModule
     */
    public static function load($id)
    {
        $moduleHandler = xoops_getHandler('module');

        if (is_numeric($id)) {
            $module = $moduleHandler->get($id);
        } else {
            $module = $moduleHandler->getByDirname($id);
        }

        if ($module) {
            load_mod_locale($module->getVar('dirname'));
        }

        return $module;
    }

    /**
     * @param $id
     * @return XoopsModule
     * @deprecated
     */
    public static function load_module($id)
    {
        return self::load($id);
    }

    /**
     * Retrieves the list of installed modules
     *
     * Accepted $status values: 'all', 'active', 'inactive'
     *
     * @param string $status Type of modules to get
     * @return array
     */
    public static function get_modules_list($status = 'all')
    {
        $db = XoopsDatabaseFactory::getDatabaseConnection();

        $active = null;

        if ('inactive' == $status) {
            $active = 0;
        } elseif ('active' == $status) {
            $active = 1;
        }

        $sql = 'SELECT mid, name, dirname FROM ' . $db->prefix('modules');

        if (isset($active)) {
            $sql .= " WHERE isactive=$active";
        }

        $sql     .= ' ORDER BY name';
        $result  = $db->query($sql);
        $modules = [];

        while (false !== ($row = $db->fetchArray($result))) {
            $modules[] = $row;
        }

        return $modules;
    }

    /**
     * Create the files to store the modules list
     */
    public static function build_modules_cache()
    {
        $modules = XoopsLists::getModulesList();
    }

    /**
     * Get the main menu for a module
     * @param string $dirname Directory name of module
     * @return array|bool
     */
    public static function main_menu($dirname)
    {
        global $xoopsModule;

        if ($xoopsModule && $xoopsModule->getVar('dirname') == $dirname) {
            $mod = $xoopsModule;
        } else {
            $mod = self::load($dirname);
        }

        if ($mod->getInfo('main_menu') && '' != $mod->getInfo('main_menu') && file_exists(XOOPS_ROOT_PATH . '/modules/' . $mod->getVar('dirname') . '/' . $mod->getInfo('main_menu'))) {
            $main_menu = [];
            include XOOPS_ROOT_PATH . '/modules/' . $mod->getVar('dirname') . '/' . $mod->getInfo('main_menu');

            return $main_menu;
        }

        return false;
    }

    public static function icon($dirname)
    {
        global $xoopsModule;

        if ($xoopsModule && $xoopsModule->getVar('dirname') == $dirname) {
            $mod = $xoopsModule;
        } else {
            $mod = self::load($dirname);
        }

        $icon = &$mod->getInfo('icon');
        $icon = '' != $icon ? $icon : XOOPS_URL . '/modules/' . $dirname . '/' . $mod->getInfo('image');

        return $icon;
    }

    /**
     * Get the permalink for a specific module.@deprecatedThis method is useful when the
     * module supports rmcommon rewrite feature.
     *
     * @param string $directory
     * @param bool   $admin
     * @return string
     */
    public static function permalink($directory, $admin = false)
    {
        global $cuSettings;

        $paths = $cuSettings->modules_path;

        if (isset($paths[$directory])) {
            return XOOPS_URL . ($admin ? '/admin/' : '') . trim($paths[$directory], '/');
        }

        return XOOPS_URL . '/modules/' . $directory;
    }
}

<?php
// $Id: image.php 825 2011-12-09 00:06:11Z i.bitcero $
// --------------------------------------------------------------
// Red México Common Utilities
// A framework for Red México Modules
// Author: Eduardo Cortés <i.bitcero@gmail.com>
// Email: i.bitcero@gmail.com
// License: GPL 2.0
// --------------------------------------------------------------

class RMUser extends RMObject
{
    private $groups = [];

    public function __construct($id = '', $use_email = false, $pass = null)
    {
        $this->db       = XoopsDatabaseFactory::getDatabaseConnection();
        $this->_dbtable = $this->db->prefix('users');
        $this->setNew();
        $this->initVarsFromTable();

        /**
         * Find user using the email
         */
        if ($use_email) {
            if ('' == $id) {
                return null;
            }

            $this->primary = 'email';
            $loaded        = $this->loadValues($id);
            $this->primary = 'uid';
        } elseif ('' != $id && is_numeric($id)) {
            $loaded = $this->loadValues((int)$id);
        } elseif ('' != $id) {
            $this->primary = 'uname';
            $loaded        = $this->loadValues($id);
            $this->primary = 'uid';
        }

        if ($loaded && null === $pass) {
            $this->unsetNew();

            return;
        }

        if (password_verify($pass, $this->pass)) {
            $this->unsetNew();
        }
    }

    public function setGroups($groupsArr)
    {
        $this->groups = [];
        if (is_array($groupsArr)) {
            $this->groups = &$groupsArr;
        }
    }

    public function getGroups()
    {
        if (!empty($this->groups)) {
            return $this->groups;
        }

        $sql    = 'SELECT groupid FROM ' . $this->db->prefix('groups_users_link') . ' WHERE uid=' . (int)$this->getVar('uid');
        $result = $this->db->query($sql);

        if (!$result) {
            return false;
        }
        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $this->groups[] = $myrow['groupid'];
        }

        return $this->groups;
    }

    public function groups($data = false, $fields = 'groupid')
    {
        $groups = &$this->getGroups();

        if (!$data || '' == $fields) {
            return $groups;
        }

        // Gets all groups based in their id
        $sql    = 'SELECT ' . ('' != $fields ? (string)$fields : '') . ' FROM ' . $this->db->prefix('groups') . ' WHERE groupid IN(' . implode(',', $groups) . ')';
        $result = $this->db->query($sql);
        $groups = [];
        while (false !== ($row = $this->db->fetchArray($result))) {
            $groups[] = $row;
        }

        return $groups;
    }

    public function isAdmin($module_id = null)
    {
        if (null === $module_id) {
            $module_id = isset($GLOBALS['xoopsModule']) ? $GLOBALS['xoopsModule']->getVar('mid', 'n') : 1;
        } elseif ((int)$module_id < 1) {
            $module_id = 0;
        }
        $modulepermHandler = xoops_getHandler('groupperm');

        return $modulepermHandler->checkRight('module_admin', $module_id, $this->getGroups());
    }

    public function save()
    {
        $ret    = true;
        $status = $this->isNew();
        /**
         * Guardmaos los datos del usuarios
         */
        if ($this->isNew()) {
            $ret = $this->saveToTable();
        } else {
            $ret = $this->updateTable();
        }
        /**
         * Si ocurrió un error al guardar los datos
         * entonces salimos del método. No se pueden
         * guardar los grupos hasta que esto se haya realizado
         */
        if (!$ret) {
            return $ret;
        }
        /**
         * Asignamos los grupos
         */
        if (!empty($this->groups)) {
            if (!$this->isNew()) {
                $this->db->queryF('DELETE FROM ' . $this->db->prefix('groups_users_link') . " WHERE uid='" . $this->getVar('uid') . "'");
            }

            $sql = 'INSERT INTO ' . $this->db->prefix('groups_users_link') . ' (`groupid`,`uid`) VALUES ';
            foreach ($this->groups as $k) {
                $sql .= "('$k','" . $this->getVar('uid') . "'),";
            }

            $sql = mb_substr($sql, 0, mb_strlen($sql) - 1);

            $this->db->queryF($sql);
        }

        return $ret;
    }

    public function delete()
    {
        $this->deleteFromTable();
    }
}

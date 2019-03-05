<?php
// $Id: language.class.php 825 2011-12-09 00:06:11Z i.bitcero $
// --------------------------------------------------------------
// Red México Common Utilities
// A framework for Red México Modules
// Author: Eduardo Cortés <i.bitcero@gmail.com>
// Email: i.bitcero@gmail.com
// License: GPL 2.0
// --------------------------------------------------------------

class RMFormLanguageField extends RMFormElement
{
    private $multi    = 0;
    private $type     = 0;
    private $selected = [];
    private $cols     = 2;

    /**
     * Constructor
     * @param string       $caption
     * @param string       $name     Field Name
     * @param int          $multi    Select multiple activated or deactivated
     * @param int          $type     0 = Select, 1 = Table
     * @param null|array   $selected  Group of Values selected by default
     * @param int          $cols     Number of columns for the table or rows for a multi select field
     */
    public function __construct($caption, $name, $multi = 0, $type = 0, $selected = null, $cols = 2)
    {
        if (is_array($caption)) {
            parent::__construct($caption);
        } else {
            parent::__construct([]);
            $this->setWithDefaults('caption', $caption, '');
            $this->setWithDefaults('name', $name, 'name_error');
            if ($multi) {
                $this->set('multiple', null);
            }

            if (is_array($selected)) {
                $this->set('selected', $selected);
            }
        }

        $this->setIfNotSet('type', $type ? 'radio' : 'select');
        $this->setIfNotSet('selected', []);

        $this->suppressList[] = 'value';
    }

    public function multi()
    {
        return $this->multi;
    }

    public function setMulti($value)
    {
        return $this->multi = $value;
    }

    public function type()
    {
        return $this->type;
    }

    public function setType($value)
    {
        return $this->type = $value;
    }

    public function selected()
    {
        return $this->selected;
    }

    public function setSelected($value)
    {
        return $this->selected = $value;
    }

    public function render()
    {
        $files          = XoopsLists::getFileListAsArray(XOOPS_ROOT_PATH . '/modules/rmcommon/lang', '');
        $langs          = [];
        $langs['en_US'] = 'en';
        foreach ($files as $file => $v) {
            if ('.mo' != mb_substr($file, -3)) {
                continue;
            }

            $langs[mb_substr($file, 0, -3)] = mb_substr($file, 0, -3);
        }

        $type     = $this->get('type');
        $selected = $this->get('selected');

        if ('radio' == $type || 'checkbox' == $type) {
            $rtn = '<div class="' . $type . '"><ul class="rmoptions_container">';
            $i   = 1;

            if ('checkbox' == $type) {
                $this->set('name', $this->get('name') . '[]');
            }
            $attributes = $this->renderAttributeString();

            foreach ($langs as $k) {
                $rtn .= "<li><label><input $attributes value='$k'" . (is_array($selected) ? (in_array($k, $selected, true) ? ' checked' : '') : '') . "> $k</label></li>";
            }

            $rtn .= '</ul></div>';
        } else {
            $this->setIfNotSet('class', 'form-control');
            $attributes = $this->renderAttributeString();
            $rtn        = "<select $attributes>";
            foreach ($langs as $k) {
                $rtn .= "<option value='$k'" . (is_array($selected) ? (in_array($k, $selected, true) ? " selected='selected'" : '') : '') . ">$k</option>";
            }
            $rtn .= '</select>';
        }

        return $rtn;
    }
}

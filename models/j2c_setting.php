<?php

class J2cSetting extends J2cAppModel {

	var $useTable = false;
	var $_key = 'j2c_settings';

	function save($data = null, $validate = true, $fieldList = array()) {
		return Cache::write($this->_key, $data);
	}

	function read($fields = null, $id = null) {
		$settings = Cache::read($this->_key);
		return $settings;
	}

}

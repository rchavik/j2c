<?php

class J2cSetting extends J2cAppModel {

	var $useTable = false;
	var $_key = 'j2c_settings';

	function save($data = null, $validate = true, $fieldList = array()) {
		$cm =& ConnectionManager::getInstance();
		$ds = $cm->create('joomla', array(
			'driver' => 'mysql',
			'persistent' => false,
			'login' => $data['db']['login'],
			'password' => $data['db']['password'],
			'host' => $data['db']['host'],
			'port' => $data['db']['port'],
			'prefix' => $data['db']['prefix'],
			'database' => $data['db']['database'],
			)
		);
		if ($ds && $ds->connect()) {
			$JosSection = ClassRegistry::init(array(
				'ds' => 'joomla',
				'class' => 'J2c.JosSection',
				'table' => 'sections',
				'type' => 'Model',
				));
			$count = $JosSection->find('count');
			if ($count > 0) {
				return Cache::write($this->_key, $data);
			}
		}
		unset($cm->config->joomla);
		unset($cm->_connectionsEnum['joomla']);
		return false;
	}

	function read($fields = null, $id = null) {
		$settings = Cache::read($this->_key);
		return $settings;
	}

}

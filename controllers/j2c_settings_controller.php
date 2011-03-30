<?php

class J2cSettingsController extends J2cAppController {
	var $name = 'J2cSettings';

	function beforeFilter() {
		$j2c = $this->Session->read('j2c');
		$cm =& ConnectionManager::getInstance();
		@$cm->create('joomla', array(
			'driver' => 'mysql',
			'persistent' => false,
			'login' => $j2c['db']['login'],
			'password' => $j2c['db']['password'],
			'host' => $j2c['db']['host'],
			'port' => $j2c['db']['port'],
			'prefix' => $j2c['db']['prefix'],
			'database' => $j2c['db']['database'],
			)
		);
		return parent::beforeFilter();
	}

	function admin_index() {
		$this->set('title_for_layout', __('Settings', true));
	}

	function admin_edit() {
		if ($this->data) {
			if ($this->J2cSetting->save($this->data)) {
				$this->Session->setFlash(__('Configuration has been saved', true));
			}
		} else {
			$this->data = $this->J2cSetting->read();
		}
		$this->Session->write('j2c', $this->data);
		$this->set('title_for_layout', __('Joomla Settings', true));
	}

	function admin_test_connection() {
		$this->set('title_for_layout', __('Test Connection', true));
		$options = array(
			'ds' => 'joomla',
			'type' => 'Model',
			'table' => 'content',
			'class' => 'J2c.JosContent',
			);
		try {
			$count = ClassRegistry::init($options)->find('count');
		}
		catch (Exception $ex) {
		}
		if ($count > 0) {
			$this->Session->setFlash(sprintf(__('Connection seems okay. I can see %d contents from joomla database', true), $count));
		} else {
			$this->Session->setFlash(__('I cannot see any contents. Check log files from connection failure or other errors', true));
		}
	}
}

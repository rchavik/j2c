<?php

class J2cSettingsController extends J2cAppController {
	var $name = 'J2cSettings';

	function beforeFilter() {
		$dbconfig = $this->Session->read($this->J2cSetting->key);
		if (!empty($dbconfig)) {
			$cm =& ConnectionManager::getInstance();
			@$cm->create('joomla', array(
				'driver' => 'mysql',
				'persistent' => false,
				'login' => $dbconfig['db']['login'],
				'password' => $dbconfig['db']['password'],
				'host' => $dbconfig['db']['host'],
				'port' => $dbconfig['db']['port'],
				'prefix' => $dbconfig['db']['prefix'],
				'database' => $dbconfig['db']['database'],
				)
			);
		}
		return parent::beforeFilter();
	}

	function admin_index() {
		$this->set('title_for_layout', __('Settings', true));
	}

	function admin_edit() {
		$this->J2cSetting->Session = $this->Session;
		if ($this->data) {
			if ($result = $this->J2cSetting->save($this->data)) {
				$this->Session->setFlash(__('Configuration has been saved', true));
			} else {
				$this->Session->setFlash(__('Configuration cannot be saved. Check your credentials', true));
			}
		} else {
			$this->data = $this->J2cSetting->read();
		}
		$this->set('title_for_layout', __('Joomla Settings', true));
	}

	function admin_migrate() {
		$migratedUsers = $this->J2c->migrate_users();
		$migratedTaxonomies = $this->J2c->migrate_taxonomies();
		$migratedContents = $this->J2c->migrate_contents();
		$this->set(compact('migratedUsers', 'migratedTaxonomies', 'migratedContents'));
		$this->Session->write('J2c.migrated', true);
	}

	function admin_test_connection() {
		$this->set('title_for_layout', __('Test Connection', true));
		$options = array(
			'ds' => 'joomla',
			'type' => 'Model',
			'table' => 'content',
			'class' => 'J2c.JosContent',
			);
		$cm =& ConnectionManager::getInstance();
		if (property_exists($cm->config, 'joomla')) {
			$count = ClassRegistry::init($options)->find('count');
		} else {
			$count = 0;
		}
		if ($count > 0) {
			$this->Session->setFlash(sprintf(__('Connection seems okay. I can see %d contents from joomla database', true), $count));
			$canMigrate = true;
		} else {
			$this->Session->setFlash(__('I cannot see any contents. Check log files from connection failure or other errors', true));
			$canMigrate = false;
		}
		$migrated = $this->Session->read('J2c.migrated');
		$this->set(compact('canMigrate', 'migrated'));
	}
}

<?php

class J2cComponent extends Object {

	function __construct() {
		$this->JosUser = ClassRegistry::init('J2c.JosUser');
		$this->User = ClassRegistry::init('User');
	}

	function startup(&$controller) {
		$this->controller =& $controller;
	}

	function log($msg) {
		CakeLog::write('j2c', $msg);
	}

	function migrate_user($josUser) {
		$data = $this->User->create(array(
			'id' => $josUser['JosUser']['id'],
			'role_id' => 2,
			'name' => $josUser['JosUser']['name'],
			'username' => $josUser['JosUser']['username'],
			'email' => $josUser['JosUser']['email'],
			'password' => $josUser['JosUser']['password'],
			'created' => $josUser['JosUser']['registerDate'],
			)
		);
		return $this->User->save($data);
	}

	function migrate_users() {
		$migrated = 0;
		$josUsers = $this->JosUser->find('all');

		foreach ($josUsers as $josUser) {
			if ($this->migrate_user($josUser)) {
				$migrated++;
			}
		}

		$this->log(sprintf('Migrated: %d user(s)', $migrated));
		return true;
	}

}

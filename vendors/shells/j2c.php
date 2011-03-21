<?php

class J2cShell extends Shell {

	function help() {
		$msg =<<<EOF

j2c: a simple tool to migrate joomla to croogo

Usage:
	cake j2c migrate

EOF;
		$this->out($msg);
	}

	function _preflight_check() {
		App::import('Model', 'ConnectionManager');
		$errorMessage = "\nYou need to create an entry for your joomla " .
			"database in croogo's configuration file.\n";
		$connectionManager = ConnectionManager::getInstance();
		if (!property_exists($connectionManager->config, 'joomla')) {
			$this->out($errorMessage);
			exit();
		}
	}

	function migrate() {
		$this->_preflight_check();

		App::import('Component', 'J2c.J2c');
		$J2c = new J2cComponent;
		$J2c->migrate_users();
		$J2c->migrate_taxonomies();
		$J2c->migrate_contents();

		clearCache();
		clearCache(null, 'queries', null);
	}

	function main() {
		$this->help();
	}
}

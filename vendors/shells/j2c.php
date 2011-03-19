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

	function migrate() {
		App::import('Component', 'J2c.J2c');
		$J2c = new J2cComponent;
		$J2c->migrate_users();
	}

	function main() {
		$this->help();
	}
}

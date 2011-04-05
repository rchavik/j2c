<?php

class JosSection extends J2cAppModel {

	var $name = 'JosSection';
	var $useTable = false;

	var $hasMany = array(
		'JosCategory' => array(
			'className' => 'J2c.JosCategory',
			'foreignKey' => 'section',
			'dependent' => true,
			),
		);

	var $_findMethods = array(
		'migrateable' => true,
		);

	function _findMigrateable($state, $query, $results = array()) {
		$query = Set::merge($query, array(
			'conditions' => array(
				),
			)
		);
		if ($state == 'before') {
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}
}

<?php

class JosContent extends J2cAppModel {

	var $name = 'JosContent';
	var $useTable = false;

	var $belongsTo = array(
		'JosSection' => array(
			'className' => 'J2c.JosSection',
			'foreignKey' => 'sectionid',
			),
		'JosCategory' => array(
			'className' => 'J2c.JosCategory',
			'foreignKey' => 'catid',
			),
		);

	var $_findMethods = array(
		'migrateable' => true,
		);

	function _findMigrateable($state, $query, $results = array()) {
		$query = Set::merge(array(
			'conditions' => array(
				'NOT' => array(
					'JosSection.title' => array('About Joomla!', 'FAQs'),
					),
				),
			), $query);
		if ($state == 'before') {
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}
}

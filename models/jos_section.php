<?php

class JosSection extends J2cAppModel {

	var $name = 'JosSection';
	var $useDbConfig = 'joomla';
	var $useTable = 'jos_sections';

	var $hasMany = array(
		'JosCategory' => array(
			'className' => 'J2c.JosCategory',
			'foreignKey' => 'section',
			'dependent' => true,
			),
		);

	function __findMigrateable($options = array()) {
		$options = Set::merge(array(
			'conditions' => array(
				'JosSection.title <>' => 'About Joomla!',
				),
			), $options);
		return $this->find('all', $options);
	}
}

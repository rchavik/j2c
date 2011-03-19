<?php

class JosContent extends J2cAppModel {

	var $name = 'JosContent';
	var $useDbConfig = 'joomla';
	var $useTable = 'jos_content';

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

	function __findMigrateable($options = array()) {
		$options = Set::merge(array(
			'conditions' => array(
				'NOT' => array(
					'JosSection.title' => array('About Joomla!', 'FAQs'),
					),
				),
			), $options);
		return $this->find('all', $options);
	}
}

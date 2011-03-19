<?php

class J2cComponent extends Object {

	function __construct() {
		$this->JosUser = ClassRegistry::init('J2c.JosUser');
		$this->User = ClassRegistry::init('User');

		$this->JosSection = ClassRegistry::init('J2c.JosSection');
		$this->JosCategory = ClassRegistry::init('J2c.JosCategory');
		$this->Vocabulary = ClassRegistry::init('Vocabulary');
		$this->Term = ClassRegistry::init('Term');
		$this->Taxonomy = ClassRegistry::init('Taxonomy');

		$this->JosContent = ClassRegistry::init('J2c.JosContent');
		$this->Node = ClassRegistry::init('Node');
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

	function migrate_section_and_categories($sectionVocabulary, $josSection) {
		if (!isset($josSection['JosCategory'])) return;

		$section = $this->Term->create(array(
			'title' => $josSection['JosSection']['title'],
			'slug' => $josSection['JosSection']['alias'],
			'description' => $josSection['JosSection']['description'],
			)
		);

		if ($this->Term->save($section)) {
			$section['Term']['id'] = $this->Term->id;
		} else {
			$section = $this->Term->findBySlug($josSection['JosSection']['alias']);
		}

		$data = $this->Taxonomy->create(array(
			'term_id' => $section['Term']['id'],
			'vocabulary_id' => $sectionVocabulary['Vocabulary']['id'],
			)
		);
		$this->Taxonomy->save($section);

		$categoryVocabulary = $this->Vocabulary->findByAlias('categories');
		$migrated = 0;
		foreach ($josSection['JosCategory'] as $josCategory) {
			$term = $this->Term->create(array(
				'title' => $josCategory['title'],
				'slug' =>  $josCategory['alias'],
				'description' => $josCategory['description'],
				)
			);
			if ($this->Term->save($term)) {
				$term['Term']['id'] = $this->Term->id;
			} else {
				$term = $this->Term->findBySlug($josCategory['alias']);
			}

			$taxonomy = $this->Taxonomy->create(array(
				'term_id' => $term['Term']['id'],
				'vocabulary_id' => $categoryVocabulary['Vocabulary']['id'], 
				)
			);
			$this->Taxonomy->save($taxonomy);
			$migrated++;
		}
		$this->log(sprintf('Migrated: %d categories in section: %s', $migrated, $section['Term']['title']));
	}

	// create a new vocabulary to contain joomla section
	function _create_section() {
		$data = $this->Vocabulary->create(array(
			'title' => 'Sections',
			'alias' => 'sections',
			)
		);

		if ($vocabulary = $this->Vocabulary->findByAlias('sections')) {
		} else {
			$Type = ClassRegistry::init('Type');
			$types = $Type->find('list');
			$vocabulary = $this->Vocabulary->save($data);
			$vocabulary['Vocabulary']['id'] = $this->Vocabulary->id;
			$vocabulary['Type']['Type'] = array_keys($types);
		}
		$vocabulary = $this->Vocabulary->save($vocabulary);
		return $vocabulary;
	}

	function migrate_taxonomies() {
		$sectionVocabulary = $this->_create_section();

		$josSections = $this->JosSection->find('migrateable');
		$migrated = 0;
		foreach ($josSections as $josSection) {
			$this->migrate_section_and_categories($sectionVocabulary, $josSection);
			$migrated++;
		}

		$this->log(sprintf('Migrated: %d sections', $migrated));
		return true;
	}

	function _map_terms($josContent) {
		return json_encode(array('1' => 'uncategorized'));
	}

	function migrate_content($josContent) {
		$data = $this->Node->create(array(
			'user_id' => 1,
			'title' => $josContent['JosContent']['title'],
			'slug' => $josContent['JosContent']['alias'],
			'body' => join("\n", array($josContent['JosContent']['introtext'], $josContent['JosContent']['fulltext'])),
			'status' => $josContent['JosContent']['state'],
			'promote' =>$josContent['JosContent']['title'],
			'type' => 'blog',$josContent['JosContent']['title'],
			'created' => $josContent['JosContent']['created'],
			'updated' => $josContent['JosContent']['modified'],
			'path' => '/blog/' . $josContent['JosContent']['alias'],
			'terms' => $this->_map_terms($josContent),
			'Taxonomy' => array(
				'Taxonomy' => array(1),
				)
			)
		);
		$this->Node->save($data);
	}

	function migrate_contents() {
		$josContents = $this->JosContent->find('migrateable');

		$migrated = 0;
		foreach ($josContents as $josContent) {
			$this->migrate_content($josContent);
		}
	}
}

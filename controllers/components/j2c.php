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

	function _map_user_tz($params) {
		$configs = explode("\n", $params);
		foreach ($configs as $config) {
			$c = parse_str($config);
			if (isset($timezone)) {
				return $timezone;
			}
		}
		return '';
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
			'timezone' => $this->_map_user_tz($josUser['JosUser']['params']),
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

	/** @deprecated */
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
	// @deprecated
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


	function _create_category($vocab, $data) {

		$new = array(
			'title' => $data['title'],
			'slug' => $data['slug'],
			);
		if (isset($data['Taxonomy']['parent_id'])) {
			$new['Taxonomy'] = array(
				'parent_id' => $data['Taxonomy']['parent_id'],
				);
		}

		$category = $this->Term->findBySlug($data['slug']);
		if (empty($category)) {
			$category = $this->Term->create($new);
			$termId = $this->Term->saveAndGetId($category['Term']);
		} else {
			$termId = $category['Term']['id'];
		}

		$termInVocabulary = $this->Term->Taxonomy->hasAny(array(
			'Taxonomy.vocabulary_id' => $vocab['Vocabulary']['id'],
			'Taxonomy.term_id' => $termId,
			));

		if (! $termInVocabulary) {
			$this->Term->Taxonomy->Behaviors->attach('Tree', array(
				'scope' => array(
					'Taxonomy.vocabulary_id' => $vocab['Vocabulary']['id'],
					),
				));

			$taxonomy = $this->Term->Taxonomy->find('first', array(
				'conditions' => array(
					'Taxonomy.term_id' => $termId,
					'Taxonomy.vocabulary_id' => $vocab['Vocabulary']['id'],
					)
				)
			);

			if (! isset($taxonomy['Taxonomy']['id'])) {
				$taxonomy = $this->Term->Taxonomy->create(array(
					'parent_id' => $data['Taxonomy']['parent_id'],
					'term_id' => $termId,
					'vocabulary_id' => $vocab['Vocabulary']['id'],
					)
				);
				$this->Term->Taxonomy->save($taxonomy);
			}
		}

		return $termId;
	}

	function migrate_sections($josSection) {
		$categoryVocab = $this->Term->Vocabulary->findByAlias('categories');

		$termId = $this->_create_category($categoryVocab, array(
			'title' => $josSection['JosSection']['title'],
			'slug' => $josSection['JosSection']['alias'],
			'Taxonomy' => array(
				'parent_id' => null,
				),
			)
		);

		foreach ($josSection['JosCategory'] as $josCategory) {
			$this->_create_category($categoryVocab, array(
				'title' => $josCategory['title'],
				'slug' => $josCategory['alias'],
				'Taxonomy' => array(
					'parent_id' => $termId,
					),
				)
			);
		}
	}

	function migrate_taxonomies() {
		$josSections = $this->JosSection->find('migrateable');
		$migrated = 0;
		foreach ($josSections as $josSection) {
			$this->migrate_sections($josSection);
			$migrated++;
		}

		$this->log(sprintf('Migrated: %d sections', $migrated));
		return true;
	}

	function _map_terms($josContent) {

		$sections = $this->Term->find('all', array(
			'recursive' => -1,
			'fields' => array('id', 'slug'),
			'conditions' => array(
				'slug' => $josContent['JosSection']['alias'],
				)
			)
		);

		$categories = $this->Term->find('all', array(
			'recursive' => -1,
			'fields' => array('id', 'slug'),
			'conditions' => array(
				'slug' => $josContent['JosCategory']['alias'],
				)
			)
		);

		$combined  = Set::combine($sections, '{n}.Term.id', '{n}.Term.slug');
		$combined += Set::combine($categories, '{n}.Term.id', '{n}.Term.slug');
		return $combined;
	}

	function _map_creator($josContent) {
		$this->JosUser->id = $josContent['JosContent']['created_by'];
		$username = $this->JosUser->field('username');

		$user = $this->User->findByUsername($username);
		return $user['User']['id'];
	}

	function migrate_content($josContent) {
		$terms = $this->_map_terms($josContent);
		$data = $this->Node->create(array(
			'user_id' => $this->_map_creator($josContent),
			'title' => $josContent['JosContent']['title'],
			'slug' => $josContent['JosContent']['alias'],
			'body' => join("\n", array($josContent['JosContent']['introtext'], $josContent['JosContent']['fulltext'])),
			'status' => $josContent['JosContent']['state'],
			'promote' =>$josContent['JosContent']['title'],
			'type' => 'blog',$josContent['JosContent']['title'],
			'created' => $josContent['JosContent']['created'],
			'updated' => $josContent['JosContent']['modified'],
			'path' => '/blog/' . $josContent['JosContent']['alias'],
			'terms' => json_encode($terms),
			)
		);
		$data['Taxonomy'] = array(
			'Taxonomy' => array_keys($terms),
		);
		$this->Node->saveWithMeta($data);
	}

	function migrate_contents() {
		$josContents = $this->JosContent->find('migrateable');

		$migrated = 0;
		foreach ($josContents as $josContent) {
			$this->migrate_content($josContent);
		}
	}
}

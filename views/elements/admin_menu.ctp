<?php

echo $html->link(__('J2c Settings', true),
	array(
		'plugin' => 'j2c',
		'controller' => 'j2c_settings',
		'action' => 'index',
		'admin' => true,
	), array(
		'escape' => false
	)
);

?>


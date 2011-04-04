<div class="j2c edit">
	<h2><?php echo $title_for_layout; ?></h2>

	<?php echo $this->element('admin_actions', array('plugin' => 'j2c')); ?>

	<div class="test-connection">
	<?php echo $this->Session->flash(); ?>

	<?php
	if ($canMigrate):
		if ($migrated):
			echo $this->Html->tag('p', 'Content has already been migrated', array('class' => 'success'));
		endif;
		echo $form->create('J2cSetting', array('action' => 'migrate'));
		echo $form->end('Migrate now');
	endif;
	?>
	</div>
</div>
<div class="j2c edit">
	<h2><?php echo $title_for_layout; ?></h2>

	<?php echo $this->element('admin_actions', array('plugin' => 'j2c')); ?>

	<div class="form">
	<?php
		echo $this->Form->create('J2cSetting');
		echo $this->Form->input('db.login');
		echo $this->Form->input('db.password');
		echo $this->Form->input('db.host');
		echo $this->Form->input('db.port');
		echo $this->Form->input('db.database');
		echo $this->Form->input('db.prefix');
		echo $this->Form->end('Save');
	?>
	</div>
</div>
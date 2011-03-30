<div class="j2c edit">
	<h2><?php echo $title_for_layout; ?></h2>

	<?php echo $this->element('admin_actions', array('plugin' => 'j2c')); ?>

	<div class="test-connection">
	<?php echo $this->Session->flash(); ?>
	</div>
</div>
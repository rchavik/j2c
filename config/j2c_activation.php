<?php // vim: set ts=4 sts=4 sw=4 si noet:

class J2cActivation {

	public function beforeActivation(&$controller) {
		return true; // onActivate will be called if this return true
	}

	public function onActivation(&$controller) {
		// ACL: set ACos with permissions
		$controller->Croogo->addAco('J2cSettings');
		$controller->Croogo->addAco('J2cSettings/admin_index');
	}

	public function beforeDeactivation(&$controller) {
		return true; //onDeactivate will be call if this return true
	}

	public function onDeactivation(&$controller) {
		// remove Acos with permissions
		$controller->Croogo->removeAco('J2cSettings');
	}
}

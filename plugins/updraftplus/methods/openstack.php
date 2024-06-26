<?php

if (!defined('UPDRAFTPLUS_DIR')) die('No direct access.');

// Necessary to place the code in a separate file, because it uses namespaces, which cause a fatal error in PHP 5.2
if (version_compare(phpversion(), '5.3.3', '>=')) {
	updraft_try_include_file('methods/openstack2.php', 'include_once');
} else {
	updraft_try_include_file('methods/insufficient.php', 'include_once');
	class UpdraftPlus_BackupModule_openstack extends UpdraftPlus_BackupModule_insufficientphp {
		public function __construct() {
			parent::__construct('openstack', 'OpenStack', '5.3.3');
		}
	}
}

<?php

if (!defined('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE == 'BE') {

		// first include the class file
	include_once(t3lib_extMgm::extPath('roles2').'class.rolemenu.php');

		// now register the class as toolbar item
	$GLOBALS['TYPO3backend']->addToolbarItem('rolemenu', 'RoleMenu');

}
?>
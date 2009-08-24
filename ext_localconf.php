<?php

#if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['fetchGroupQuery'][]='EXT:roles2/class.roles2_t3lib_userauthgroup.php:user_roles2_t3lib_userauthgroup';
	include_once(t3lib_extMgm::extPath($_EXTKEY).'class.roles2_itemsProcFunc.php');
if (TYPO3_MODE=="BE")	{

	$roles2Path = t3lib_extMgm::extPath('roles2');

		// register toolbar item
	$GLOBALS['TYPO3_CONF_VARS']['typo3/backend.php']['additionalBackendItems'][] = $roles2Path.'registerToolbarItem.php';
	
		// register AJAX calls
	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['RoleMenu::getRoleInfo'] = $roles2Path.'class.rolemenu.php:RoleMenu->getAjaxRoleInfo';
	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['RoleMenu::getActivatedRole'] = $roles2Path.'class.rolemenu.php:RoleMenu->getAjaxActivatedRole';
	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['RoleMenu::saveRole'] = $roles2Path.'class.rolemenu.php:RoleMenu->setAjaxRole';
	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['RoleMenu::render'] = $roles2Path.'class.rolemenu.php:RoleMenu->renderAjax';
	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['RoleMenu::getRoleName'] = $roles2Path.'class.rolemenu.php:RoleMenu->renderRoleNameAjax';
	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['RoleMenu::delete'] = $roles2Path.'class.rolemenu.php:RoleMenu->deleteAjaxRole';
	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['RoleMenu::activateRole'] = $roles2Path.'class.rolemenu.php:RoleMenu->activateAjaxRole';
	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['RoleMenu::create'] = $roles2Path.'class.rolemenu.php:RoleMenu->createAjaxRole';
}

/*if (TYPO3_MODE=='BE')	{
}*/
?>

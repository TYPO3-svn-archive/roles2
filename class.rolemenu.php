<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2008 Ingo Renner <ingo@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

if(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_AJAX) {
	require_once('interfaces/interface.backend_toolbaritem.php');
	require_once(PATH_t3lib.'class.t3lib_loadmodules.php');
	require_once(PATH_typo3.'sysext/lang/lang.php');

	$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
	$GLOBALS['LANG']->init($GLOBALS['BE_USER']->uc['lang']);
	$GLOBALS['LANG']->includeLLFile('EXT:roles2/locallang.xml');
}


/**
 * class to render the role menu
 *
 * $Id: class.rolemenu.php 3669 2008-05-20 08:34:50Z flyguide $
 *
 * @author	Ingo Renner <ingo@typo3.org>
 * @package TYPO3
 * @subpackage core
 */
class RoleMenu implements backend_toolbarItem {

	protected $roles;

	/**
	 * all available roles
	 *
	 * @var array
	 */
	protected $shortcuts;

	/**
	 * labels of all groups.
	 * If value is 1, the system will try to find a label in the locallang array.
	 *
	 * @var array
	 */
	protected $roleLabels;

	/**
	 * reference back to the backend object
	 *
	 * @var	TYPO3backend
	 */
	protected $backendReference;

	/**
	 * constructor
	 *
	 * @param	TYPO3backend	TYPO3 backend object reference
	 * @return	void
	 */
	public function __construct(TYPO3backend &$backendReference = null) {
		$this->shortcutsession=$GLOBALS['BE_USER']->getSessionData("txroles2M1");
		$loadModules = t3lib_div::makeInstance('t3lib_loadModules');
		$loadModules->load($GLOBALS['TBE_MODULES']);
		$this->backendReference = $backendReference;
		$this->shortcuts        = array();
		$_GET['module'] = 'web_list';

		//If there are assigned no roles or if you are admin, the shortcuts shouldn't be fetched
		if(!$GLOBALS['BE_USER']->isAdmin()) {
			$this->roles  = $this->initRoles();
			if($this->ALL_roles_LIST!='') $this->shortcuts = $this->initShortcuts();
		}
	}

	
	/**
	 * checks whether the user has access to this toolbar item
	 *
	 * @return  boolean  true if user has access, false if not
	 */
	public function checkAccess() {
			// Role module is enabled for everybody
		return true;
	}

	/**
	 * Creates the role menu (default renderer)
	 *
	 * @return	string		workspace selector as HTML select
	 */
	public function render() {

		$this->addJavascriptToBackend();
		$this->addCssToBackend();

		$roleMenu = array();
		$roleMenu[] = '<div></div></li><li id="role-menu">';
		$roleMenu[] = '<a href="#" class="toolbar-item"><img'.t3lib_iconWorks::skinImg($this->backPath, t3lib_extMgm::extRelPath('roles2').'ext_icon.gif', 'width="16" height="16"').' title="Roles" alt="" /></a>';
		$roleMenu[] = '<div class="toolbar-item-menu" style="display: none;">';
		$roleMenu[] = $this->renderMenu();
		$roleMenu[] = '</div>';
		//Only show the roles menu if there are any roles.
		if($this->ALL_roles_LIST!=',-1' OR $GLOBALS['BE_USER']->isAdmin()) {
			return implode("\n", $roleMenu);
		} else {
			return;
		}
	}
	
	public function getCurrentRoleName() {
		$currentRole = $this->roles[$this->shortcutsession['currentRole']];
		return $currentRole?'<span class="toolbar-item"><p>'.$currentRole.':</p></span>':'<div></div>';
	}
	

	/**
	 * renders the pure contents of the menu
	 *
	 * @return	string		the menu's content
	 */
	public function renderMenu() {
		if($GLOBALS['BE_USER']->isAdmin()) {
			$testusers=t3lib_BEfunc::getUserNames('uid,username',' AND username LIKE \'testuser_%\'');
			if(count($testusers)) {
				foreach($testusers as $user) {
					$params='&edit[be_users][' . $user['uid'] . ']=edit';
					$edit = '<a href="#" onclick="' . t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH'],'') . '"><img ' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif') . ' border="0" align="top" title="' . $GLOBALS['LANG']->sL('LLL:EXT:roles2/locallang.xml:edit', true) . '" alt="" /></a>';
					$roleMenu[] = 
					'<span style="float:left;">'.t3lib_iconWorks::getIconImage('be_users',$user,$GLOBALS['BACK_PATH'],'align="top" title="' . $user['uid'] . '"') .'&nbsp;&nbsp;'. $user['username'] .'</span><span style="float:right;">'. $edit .'
					<a target="_top" href="' . $GLOBALS['BACK_PATH'] . 'mod.php?M=tools_beuser&amp;SwitchUser='.$user['uid'].'"><img height="16" border="0" align="top" width="16" alt="" title="Switch user to: 123123 [change-to mode]" src="sysext/t3skin/icons/gfx/su.gif"/></a>
					<a target="_top" href="' . $GLOBALS['BACK_PATH'] . 'mod.php?M=tools_beuser&amp;SwitchUser='.$user['uid'].'&amp;switchBackUser=1"><img height="16" border="0" align="top" width="16" alt="" title="Switch user to: 123123 [switch-back mode]" src="sysext/t3skin/icons/gfx/su_back.gif"/></a></span>
					';
				}
			} else {
				$roleMenu[] = 'Create a new backend user with a username prepended with \'testuser_\'. Hereafter you can access the new backend user from this menu. Use it to test roles and a apply new global shortcuts for roles through backend user simulation.<br />
				When you build up your roles and assign them to this user, it\'s important that you don\'t give extra rights besides the roles themselves - else users can get problems if you create a shortcut which they don\'t have access permissions for.';
			}
		} else {
			if($this->shortcutsession['roleReceived']==1) {
				unset($this->shortcutsession['roleReceived']);
				$GLOBALS['BE_USER']->setAndSaveSessionData('txroles2M1',$this->shortcutsession);
				$roleMenu[]='
				<script type="text/javascript">
				  	/*<![CDATA[*/
					'.$this->shortcutsession['roleURL'].'
				 	/*]]>*/
				</script>';
			}
			$groupIcon  = '<img'.t3lib_iconWorks::skinImg($this->backPath, 'gfx/i/sysf.gif', 'width="18" height="16"').' title="Role Group" alt="" />';
			$editIcon   = '<img'.t3lib_iconWorks::skinImg($this->backPath, 'gfx/edit2.gif', 'width="11" height="12"').' title="Edit Shortcut" alt=""';
			$deleteIcon = '<img'.t3lib_iconWorks::skinImg($this->backPath, 'gfx/garbage.gif', 'width="11" height="12"').' title="Delete Shortcut" alt="" />';
			$globalIcon = '<img'.t3lib_iconWorks::skinImg($this->backPath, 'gfx/zoom2.gif', 'width="11" height="12"').' title="Cannot delete global shortcut" alt="" />';
			$globalBackUserIcon = '<img'.t3lib_iconWorks::skinImg($this->backPath, 'gfx/zoom2.gif', 'width="11" height="12"').' title="Delete global shortcut" alt="" />';				
			$roleMenu[] = '<table border="0" cellspacing="0" cellpadding="0" class="role-list">';

			// now render roles and the contained shortcuts
			$roles = $this->getShortcutsFromRoles();
			krsort($roles, SORT_NUMERIC);
			foreach($roles as $roleId => $roleLabel) {
				if($roleId != 0 ) {
					$roles = $this->getShortcutsByRole($roleId);
					$roleMenuPart = '
					<tr id="role-'.$roleId.'#" class="role role-group">
						<td class="role-icon">'.$groupIcon.'</td>
						<td class="role-label" colspan="3">
							<a id="role-label-'.$roleId.'#" href="#" onclick="this.blur();return false;" sessionsave="top.content.nav_frame.Tree.refresh();top.content.list_frame.location.reload(true);">'.$roleLabel.'</a>
						</td>
					</tr>';

					$global = 0;
					$i = 0;
					if(count($roles)) {
						foreach($roles as $role) {
							$i++;
							$firstRow = '';
							if($i == 1) {
								$firstRow = ' first-row';
							}
							$firstafterglobal = $role['global']==0&&$global==1?' firstafterglobal':''; 
							$roleMenuPart .= '
							<tr id="role-'.$roleId.'#'.$role['raw']['uid'].'" class="role'.$firstRow.$firstafterglobal.'">
								<td class="role-icon">'.$role['icon'].'</td>
								<td class="role-label">
									<a id="role-label-'.$roleId.'#'.$role['raw']['uid'].'" href="#" onclick="this.blur();return false;" sessionsave="'.$role['action'].'">'.$role['label'].'</a>
								</td>
								<td class="role-edit"'.($role['global']==1?' colspan="1"':'').'>'.$editIcon.' id="role-edit-'.$role['raw']['uid'].'" /></td>
								'.($role['global']==1&&!$GLOBALS['BE_USER']->user['ses_backuserid']?'<td class="role-global">'.$globalIcon.'</td>':'<td class="role-delete">'.($role['global']==1&&$GLOBALS['BE_USER']->user['ses_backuserid']?$globalBackUserIcon:$deleteIcon).'</td>').'
							</tr>';
							$global=$role['global'];
						}
					}
					$roleMenu[] = $roleMenuPart;

				}
			}
			if($this->shortcutsession['currentRole']) {
			$roleMenu[] = '
					<tr id="role-0#" class="role role-group">
						<td class="role-icon">'.$groupIcon.'</td>
						<td class="role-label" colspan="3">
							<a id="role-label-0#" href="#" onclick="this.blur();return false;" sessionsave="top.content.nav_frame.Tree.refresh();top.content.list_frame.location.reload(true);">Deactivate role</a>
						</td>
					</tr>';
			}
			if(count($roleMenu) == 1) {
					//no roles added yet, show a small help message how to add roles
				$icon  = '<img'.t3lib_iconWorks::skinImg($backPath,'gfx/shortcut.gif','width="14" height="14"').' title="role icon" alt="" />';
				$label = str_replace('%icon%', $icon, $GLOBALS['LANG']->sL('LLL:EXT:roles2/locallang.xml:shortcutDescription'));
	
				$roleMenu[] = '<tr><td style="padding:1px 2px; color: #838383;">'.$label.'</td></tr>';
			}
			
			$roleMenu[] = '</table>';
		}
		$compiledRoleMenu = implode("\n", $roleMenu);
		return $compiledRoleMenu;
	}

	/**
	 * renders the menu so that it can be returned as response to an AJAX call
	 *
	 * @param	array		array of parameters from the AJAX interface, currently unused
	 * @param	TYPO3AJAX	object of type TYPO3AJAX
	 * @return	void
	 */
	public function renderAjax($params = array(), TYPO3AJAX &$ajaxObj = null) {
		$menuContent = $this->renderMenu();

		$ajaxObj->addContent('roleMenu', $menuContent);
	}
	
	/**
	 * renders current Role Name
	 *
	 * @param	array		array of parameters from the AJAX interface, currently unused
	 * @param	TYPO3AJAX	object of type TYPO3AJAX
	 * @return	void
	 */
	public function renderRoleNameAjax($params = array(), TYPO3AJAX &$ajaxObj = null) {
		$menuContent = $this->getCurrentRoleName();

		$ajaxObj->addContent('roleMenu2', $menuContent);
	}

	/**
	 * adds the necessary JavaScript to the backend
	 *
	 * @return	void
	 */
	protected function addJavascriptToBackend() {
		if($this->shortcutsession['roleReceived']==1) {
			unset($this->shortcutsession['roleReceived']);
			$GLOBALS['BE_USER']->setAndSaveSessionData('txroles2M1',$this->shortcutsession);
		}

		if($GLOBALS['BE_USER']->uc['startupShortcut']!="") {
			unset($GLOBALS['BE_USER']->uc['startModule'],$GLOBALS['BE_USER']->uc['startInTaskCenter'],$_GET['module']);
			$this->backendReference->addJavascript('
				Event.observe(window, \'load\', function() {
					var roleTest = $(\'role-label-'.$GLOBALS['BE_USER']->uc['startupShortcut'].'\').readAttribute(\'sessionsave\');
					new Ajax.Request(\'ajax.php\', {
						parameters : \'ajaxID=RoleMenu::activateRole&roleId='.strtok($GLOBALS['BE_USER']->uc['startupShortcut'],'#').'\',
						asynchronous : false,
						onComplete : function() {top.TYPO3BackendRoleMenu.reRenderMenu(null, null, null);top.TYPO3BackendShortcutMenu.reRenderMenu(null, null, null);} 
					});
					new Ajax.Updater(\'typo3-menu\', TS.PATH_typo3 + \'ajax.php\', {
						parameters   : \'ajaxID=ModuleMenu::render\',
						asynchronous : false,
						evalScripts  : true
					});
					$(\'typo3-backend-php\').insert( { bottom: \'<script type="text/javascript">\' + roleTest + \'</scr\'+\'ipt>\' } );
				});
			');
		}
		$this->backendReference->addJavascriptFile(t3lib_extMgm::extRelPath('roles2') . 'res/rolemenu.js');
	}

	/**
	 * adds the neccessary CSS to the backend
	 *
	 * @return	void
	 */
	protected function addCssToBackend() {
		$this->backendReference->addCssFile('roles2', t3lib_extMgm::extRelPath('roles2') . 'res/rolemenu.css');
	}

	/**
	 * returns additional attributes for the list item in the toolbar
	 *
	 * @return	string		list item HTML attibutes
	 */
	public function getAdditionalAttributes() {
		$returnid = ' id="role-name"';
		return $returnid;
	}

	/**
	 * retrieves the roles for the current user
	 *
	 * @return	array		array of roles
	 */
	protected function initShortcuts() {
		//<- needed to get the correct icons when reloading the menu after saving it
		$loadModules = t3lib_div::makeInstance('t3lib_loadModules');
		foreach($GLOBALS['TBE_MODULES'] as $mainmod=>$submodlist) {
			$submodArr = explode(',',$submodlist);
			foreach($submodArr as $submod) {
				$moduleList[]= $mainmod.'_'.$submod;
			}
		}
		$string = implode(',',$moduleList);
		$temp = $GLOBALS['BE_USER']->groupData['modules'];
		$GLOBALS['BE_USER']->groupData['modules'] = $string;
		$loadModules->load($GLOBALS['TBE_MODULES']);
		$GLOBALS['BE_USER']->groupData['modules'] = $temp;
		//->
		$roles    = array();

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'sys_be_shortcuts',
			'(userid = '.$GLOBALS['BE_USER']->user['uid'].') OR usergroup IN ('.'-'.str_replace(',',',-',$this->ALL_roles_LIST).')',
			'',
			'sc_group,sorting'
		);

			// Traverse roles
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$role             = array('raw' => $row);
			$moduleParts          = explode('|', $row['module_name']);
			$row['module_name']   = $moduleParts[0];
			$row['M_module_name'] = $moduleParts[1];
			$moduleParts          = explode('_', $row['M_module_name'] ?
				$row['M_module_name'] :
				$row['module_name']
			);
			$queryParts           = parse_url($row['url']);
			$queryParameters      = t3lib_div::explodeUrl2Array($queryParts['query'], 1);

			if($row['module_name'] == 'xMOD_alt_doc.php' && is_array($queryParameters['edit'])) {
				$role['table']    = key($queryParameters['edit']);
				$role['recordid'] = key($queryParameters['edit'][$role['table']]);

				if($queryParameters['edit'][$role['table']][$role['recordid']] == 'edit') {
					$role['type'] = 'edit';
				} elseif($queryParameters['edit'][$role['table']][$role['recordid']] == 'new') {
					$role['type'] = 'new';
				}

				if(substr($role['recordid'], -1) == ',') {
					$role['recordid'] = substr($role['recordid'], 0, -1);
				}
			} else {
				$role['type'] = 'other';
			}


			if($row['description']) {
				$role['label'] = $row['description'];
			} else {
				$role['label'] = t3lib_div::fixed_lgd(rawurldecode($queryParts['query']), 150);
			}

			$role['usergroup'] 	= abs($row['usergroup']);
			$role['global'] 	= ($row['usergroup']>0?0:1);
			$role['moduleParts']= $moduleParts;
			$role['url']		= $row['url'];
			$role['group']     	= $row['sc_group'];
			$role['icon']      	= $this->getShortcutIcon($row, $role);
			$role['iconTitle'] 	= $this->getShortcutIconTitle($roleLabel, $row['module_name'], $row['M_module_name']);
			$role['action']    = 'jump(unescape(\''.rawurlencode($row['url']).'\'),\''.implode('_',$moduleParts).'\',\''.$moduleParts[0].'\');';


			$lastGroup   = $row['sc_group'];
			$roles[] = $role;
			if($row['usergroup']<0) {
				$this->globalShortcuts[] = $row['uid'];
			}
		}

		return $roles;
	}

	/**
	 * gets roles for a specific group
	 *
	 * @param	integer		group Id
	 * @return	array		array of roles that matched the group
	 */
	protected function getShortcutsByRole($roleId) {
		$roles = array();

		foreach($this->shortcuts as $shortcut) {
//			if($role['group'] == $roleId) {
			if(t3lib_div::inList($shortcut['usergroup'],$roleId)) {
				 $shortcuts[] = $shortcut;
			}
		}

		return $shortcuts;
	}

	/**
	 * gets a role by its uid
	 *
	 * @param	integer		role id to get the complete role for
	 * @return	mixed		an array containing the role's data on success or false on failure
	 */
	protected function getRoleById($roleId) {
		$returnRole = false;

		foreach($this->shortcuts as $role) {
			if($role['raw']['uid'] == (int) $roleId) {
				$returnRole = $role;
				continue;
			}
		}

		return $returnRole;
	}
	
	/**
	 * gets the available roles
	 * 
	 * @param	array		array of parameters from the AJAX interface, currently unused
	 * @param	TYPO3AJAX	object of type TYPO3AJAX
	 * @return	array
	 */
	protected function initRoles($params = array(), TYPO3AJAX &$ajaxObj = null) {
		$grList=$GLOBALS['BE_USER']->user[$GLOBALS['BE_USER']->usergroup_column];
		$grList=($grList?$grList:'0');
		$this->getRoles($grList);
	    
			// add labels
		if (is_array($this->roles)) {
			foreach($this->roles as $roleId => $roleLabel) {
				$this->roles[$roleId] = $roleLabel;
			}
		}
		return $this->roles;
	}
	protected function getRoles($grList,$notList='') {
	    $lockToDomain_SQL = ' AND (lockToDomain=\'\' OR lockToDomain=\''.t3lib_div::getIndpEnv('HTTP_HOST').'\')';
	    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $GLOBALS['BE_USER']->usergroup_table, 'deleted=0 AND hidden=0 AND pid=0 AND uid IN ('.$grList.')'.$lockToDomain_SQL.' ORDER BY title');
	    $this->ALL_roles_ARRAY = array();
	    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			if($row['tx_roles_role']==1) {
			    $this->ALL_roles_ARRAY[] = $row['uid'];
	    		$this->roles[$row['uid']] = $row['title'];
			}
			if(trim($row['subgroup'])) {
			    $theList = implode(',',t3lib_div::intExplode(',',$row['subgroup']));
			    $this->getRoles($theList,$notList);
			}
	    }
	    $this->ALL_roles_LIST = implode(",",$this->ALL_roles_ARRAY);
		return $this->roleList;
    }
	
	
	/**
	 * gets the role info
	 *
	 * @param	array		array of parameters from the AJAX interface, currently unused
	 * @param	TYPO3AJAX	object of type TYPO3AJAX
	 * @return	void
	 */
	public function getAjaxroleInfo($params = array(), TYPO3AJAX &$ajaxObj = null) {
		$startupShortcutRole = $GLOBALS['BE_USER']->uc['startupShortcut'];
		$roleId = (int) t3lib_div::_GET('roleId');
		$globalShortcutEditAccess = (in_array($roleId,$this->globalShortcuts)&&!$GLOBALS['BE_USER']->user['ses_backuserid']?0:1);
		$ajaxObj->addContent('roleGroups', $startupShortcutRole.','.$globalShortcutEditAccess);
		$ajaxObj->setContentFormat('json');
	}

/**
	 * get which role is activated
	 *
	 * @param	array		array of parameters from the AJAX interface, currently unused
	 * @param	TYPO3AJAX	object of type TYPO3AJAX
	 * @return	void
	 */
	public function getAjaxActivatedRole($params = array(), TYPO3AJAX &$ajaxObj = null) {
		$ajaxObj->addContent('roleActivated', $this->shortcutsession['currentRole']);
		$ajaxObj->setContentFormat('json');
	}

	/**
	 * set a role through an AJAX call
	 *
	 * @param	array		array of parameters from the AJAX interface, currently unused
	 * @param	TYPO3AJAX	object of type TYPO3AJAX
	 * @return	void
	 */
	public function activateAjaxRole($params = array(), TYPO3AJAX &$ajaxObj = null) {
		$array['activateRoleId'] = (int) t3lib_div::_POST('roleId');
		$array['roleURL'] = (string) t3lib_div::_POST('roleURL');
		if(!$array['roleURL']) $array['roleURL'] = 'eval(top.content.nav_frame.Tree.refresh());';
		$GLOBALS['BE_USER']->setAndSaveSessionData('txroles2M1',$array);
		$ajaxReturn = 'roleSetOk';
		$ajaxObj->addContent('delete', $ajaxReturn);
	}

	/**
	 * deletes a role through an AJAX call
	 *
	 * @param	array		array of parameters from the AJAX interface, currently unused
	 * @param	TYPO3AJAX	object of type TYPO3AJAX
	 * @return	void
	 */
	public function deleteAjaxRole($params = array(), TYPO3AJAX &$ajaxObj = null) {
		$roleId   = (int) t3lib_div::_POST('roleId');
		$fullRole = $this->getRoleById($roleId);
		$ajaxReturn   = 'failed';

		if($fullRole['raw']['userid'] == $GLOBALS['BE_USER']->user['uid'] OR isset($GLOBALS['BE_USER']->user['ses_backuserid'])) {
			$GLOBALS['TYPO3_DB']->exec_DELETEquery(
				'sys_be_shortcuts',
				'uid = '.$roleId
			);

			if($GLOBALS['TYPO3_DB']->sql_affected_rows() == 1) {
				$ajaxReturn = 'deleted';
			}
		}

		$ajaxObj->addContent('delete', $ajaxReturn);
	}

	/**
	 * creates a role through an AJAX call
	 *
	 * @param	array		array of parameters from the AJAX interface, currently unused
	 * @param	TYPO3AJAX	object of type TYPO3AJAX
	 * @return	void
	 */
	public function createAjaxRole($params = array(), TYPO3AJAX &$ajaxObj = null) {
		global $TCA, $LANG;
		$roleCreated     = 'failed';
		$roleName        = 'Role'; // default name
		$roleNamePrepend = '';

		$url             = urldecode(t3lib_div::_POST('url'));
		$module          = t3lib_div::_POST('module');
		$motherModule    = t3lib_div::_POST('motherModName');

			// determine role type
		$queryParts      = parse_url($url);
		$queryParameters = t3lib_div::explodeUrl2Array($queryParts['query'], 1);

		if(is_array($queryParameters['edit'])) {
			$role['table']    = key($queryParameters['edit']);
			$role['recordid'] = key($queryParameters['edit'][$role['table']]);

			if($queryParameters['edit'][$role['table']][$role['recordid']] == 'edit') {
				$role['type']    = 'edit';
				$roleNamePrepend = $GLOBALS['LANG']->getLL('shortcut_edit', 1);
			} elseif($queryParameters['edit'][$role['table']][$role['recordid']] == 'new') {
				$role['type']    = 'new';
				$roleNamePrepend = $GLOBALS['LANG']->getLL('shortcut_create', 1);
			}
		} else {
			$role['type'] = 'other';
		}

			// Lookup the title of this page and use it as default description
		$pageId = $shortcut['recordid'] ? $shortcut['recordid'] : $this->getLinkedPageId($url);//		$pageId = $this->getLinkedPageId($url);

		if(t3lib_div::testInt($pageId)) {
			$page = t3lib_BEfunc::getRecord('pages', $pageId);
			if(count($page)) {
					// set the name to the title of the page
				if($role['type'] == 'other') {
					$roleName = $page['title'];
				} else {
					$roleName = $roleNamePrepend.' '.$LANG->sL($TCA[$role['table']]['ctrl']['title']).' ('.$page['title'].')';
				}
			}
		} else {
			$dirName = urldecode($pageId);
			if (preg_match('/\/$/', $dirName))	{
					// if $pageId is a string and ends with a slash,
					// assume it is a fileadmin reference and set
					// the description to the basename of that path
				$roleName .= basename($dirName);
			}
		}

			// adding the role
		if($module && $url) {
			$fieldValues = array(
				'userid'      => $GLOBALS['BE_USER']->user['uid'],
				'module_name' => $module.'|'.$motherModule,
				'url'         => $url,
				'description' => $roleName,
				'sorting'     => time(),
			);
			
			/*
			 * Adding usergroup data if role is active when shortcut is created.
			 */
			if(!$GLOBALS['BE_USER']->isAdmin() AND $this->shortcutsession['currentRole']!=""){
				$fieldValues['sc_group'] = 100;
				if($GLOBALS['BE_USER']->user['ses_backuserid']){
					$fieldValues['usergroup'] = (int)$this->shortcutsession['currentRole'] * -1;
					$fieldValues['userid'] = $GLOBALS['BE_USER']->user['ses_backuserid'];
					$fieldValues['sorting'] *= -1;
				} else {
					$fieldValues['usergroup'] = $this->shortcutsession['currentRole'];						
				}
			} else {
				$fieldValues['usergroup'] = '';
			}			
			
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_be_shortcuts', $fieldValues);

			if($GLOBALS['TYPO3_DB']->sql_affected_rows() == 1) {
				$roleCreated = 'success';
			}
		}

		$ajaxObj->addContent('create', $roleCreated);
	}

	/**
	 * gets called when a role is changed, checks whether the user has
	 * permissions to do so and saves the changes if everything is ok
	 *
	 * @param	array		array of parameters from the AJAX interface, currently unused
	 * @param	TYPO3AJAX	object of type TYPO3AJAX
	 * @return	void
	 */
	public function setAjaxRole($params = array(), TYPO3AJAX &$ajaxObj = null) {
		$roleId      = (int) t3lib_div::_POST('roleId');
		$roleRoleId      = (int) t3lib_div::_POST('roleRoleId');
		$roleName    = strip_tags(t3lib_div::_POST('value'));
		$startupShortcut = (int) t3lib_div::_POST('role-startupShortcut');
		if($startupShortcut==1){
			$GLOBALS['BE_USER']->uc['startupShortcut'] = $roleRoleId.'#'.$roleId;
			$GLOBALS['BE_USER']->writeUC($GLOBALS['BE_USER']->uc);
		} elseif($startupShortcut==0 AND $GLOBALS['BE_USER']->uc['startupShortcut'] == $roleRoleId.'#'.$roleId) {
			unset($GLOBALS['BE_USER']->uc['startupShortcut']);
			$GLOBALS['BE_USER']->writeUC($GLOBALS['BE_USER']->uc);
		}

		if($roleRoleId > 0 || isset($GLOBALS['BE_USER']->user['ses_backuserid'])) {
				// users can delete only their own roles (except admins)
			$addUserWhere = (!$GLOBALS['BE_USER']->user['ses_backuserid'] ?
				' AND userid='.intval($GLOBALS['BE_USER']->user['uid'])
				: ''
			);

			$fieldValues = array(
				'description' => $roleName,
/*				'sc_group'    => $roleGroupId*/
			);


			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				'sys_be_shortcuts',
				'uid='.$roleId.$addUserWhere,
				$fieldValues
			);

			$affectedRows = $GLOBALS['TYPO3_DB']->sql_affected_rows();
			if($affectedRows == 1) {
				$ajaxObj->addContent('role', $roleName);
			} else {
				$ajaxObj->addContent('role', 'no change');
			}
		}

		$ajaxObj->setContentFormat('plain');
	}

	/**
	 * gets the label for a role group
	 *
	 * @param	integer		a role group id
	 * @return	string		the role group label, can be an empty string if no group was found for the id
	 */
	protected function getRoleGroupLabel($roleId) {
		$label = '';

		if($this->roles[$roleId]) {
			$label = $this->roles[$roleId];
		}

		return $label;
	}


	/**
	 * runs through the available roles an collects their shortcuts
	 *
	 * @return	array	array of groups which have roles
	 */
	protected function getShortcutsFromRoles() {
		$roles = $this->roles;
		foreach($this->shortcuts as $role) {
			$roles[$role['usergroup']] = $this->roles[$role['usergroup']];
		}

		return is_array($roles) ? array_unique($roles) : array();
	}

	/**
	 * gets the icon for the role
	 *
	 * @param	string		backend module name
	 * @return	string		role icon as img tag
	 */
	protected function getShortcutIcon($row, $role) {
		global $TCA;

		switch($row['module_name']) {
			case 'xMOD_alt_doc.php':
				$table 				= $role['table'];
				$recordid			= $role['recordid'];

				if($role['type'] == 'edit') {
						// Creating the list of fields to include in the SQL query:
					$selectFields = $this->fieldArray;
					$selectFields[] = 'uid';
					$selectFields[] = 'pid';

					if($table=='pages') {
						if(t3lib_extMgm::isLoaded('cms')) {
							$selectFields[] = 'module';
							$selectFields[] = 'extendToSubpages';
						}
						$selectFields[] = 'doktype';
					}

					if(is_array($TCA[$table]['ctrl']['enablecolumns'])) {
						$selectFields = array_merge($selectFields,$TCA[$table]['ctrl']['enablecolumns']);
					}

					if($TCA[$table]['ctrl']['type']) {
						$selectFields[] = $TCA[$table]['ctrl']['type'];
					}

					if($TCA[$table]['ctrl']['typeicon_column']) {
						$selectFields[] = $TCA[$table]['ctrl']['typeicon_column'];
					}

					if($TCA[$table]['ctrl']['versioningWS']) {
						$selectFields[] = 't3ver_state';
					}

					$selectFields     = array_unique($selectFields); // Unique list!
					$permissionClause = ($table=='pages' && $this->perms_clause) ?
						' AND '.$this->perms_clause :
						'';

	         		$sqlQueryParts = array(
						'SELECT' => implode(',', $selectFields),
						'FROM'   => $table,
						'WHERE'  => 'uid IN ('.$recordid.') '.$permissionClause.
						t3lib_BEfunc::deleteClause($table).
						t3lib_BEfunc::versioningPlaceholderClause($table)
					);
					$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($sqlQueryParts);
					$row    = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
					$row['hidden'] = (string) 0;
					$icon = t3lib_iconWorks::getIcon($table, $row, $this->backPath);
				} elseif($role['type'] == 'new') {
					$icon = t3lib_iconWorks::getIcon($table, '', $this->backPath);
				}

				$icon = t3lib_iconWorks::skinImg($this->backPath, $icon, '', 1);
				break;
			case 'xMOD_file_edit.php':
				$icon = 'gfx/edit_file.gif';
				break;
			case 'xMOD_wizard_rte.php':
				$icon = 'gfx/edit_rtewiz.gif';
				break;
			default:
				if($GLOBALS['LANG']->moduleLabels['tabs_images'][$row['module_name'].'_tab']) {
					$icon = $GLOBALS['LANG']->moduleLabels['tabs_images'][$row['module_name'].'_tab'];

						// change icon of fileadmin references - otherwise it doesn't differ with Web->List
					$icon = str_replace('mod/file/list/list.gif', 'mod/file/file.gif', $icon);

					if(t3lib_div::isAbsPath($icon)) {
						$icon = '../'.substr($icon, strlen(PATH_site));
					}
				} else {
					$icon = 'gfx/dummy_module.gif';
				}
		}

		return '<img src="'.$icon.'" alt="role icon" />';
	}

	/**
	 * Returns title for the role icon
	 *
	 * @param	string		role label
	 * @param	string		backend module name (key)
	 * @param	string		parent module label
	 * @return	string		title for the role icon
	 */
	protected function getShortcutIconTitle($roleLabel, $moduleName, $parentModuleName = '') {
		$title = '';

		if(substr($moduleName, 0, 5) == 'xMOD_') {
			$title = substr($moduleName, 5);
		} else {
			$splitModuleName = explode('_', $moduleName);
			$title = $GLOBALS['LANG']->moduleLabels['tabs'][$splitModuleName[0].'_tab'];

			if(count($splitModuleName) > 1) {
				$title .= '>'.$GLOBALS['LANG']->moduleLabels['tabs'][$moduleName.'_tab'];
			}
		}

		if($parentModuleName) {
			$title .= ' ('.$parentModuleName.')';
		}

		$title .= ': '.$roleLabel;

		return $title;
	}

	/**
	 * Return the ID of the page in the URL if found.
	 *
	 * @param	string		The URL of the current role link
	 * @return	string		If a page ID was found, it is returned. Otherwise: 0
	 */
	protected function getLinkedPageId($url)	{
		return preg_replace('/.*[\?&]id=([^&]+).*/', '$1', $url);
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/classes/class.rolemenu.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/classes/class.rolemenu.php']);
}

?>

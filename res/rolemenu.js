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
/*Event.observe(window, 'load', function() {
    $('shortcut-menu').hide();
});*/
top.nextLoadModuleUrl = unescape('%2Ftypo3%2Falt_doc.php%3F%26returnUrl%3Ddb_list.php%3Fid%3D1%26table%3Dpages%26edit%5Bpages%5D%5B3%2C2%5D%3Dedit%26defVals%3D%26overrideVals%3D%26columnsOnly%3Dtitle%26returnNewPageId%3D%26editRegularContentFromId%3D%26disHelp%3D1%26noView%3D%26SET%5BshowPalettes%5D%3D1');

	/**
	 * Overwrites the method in the shortcutmenu class
	 * makes a call to the backend class to create a new shortcut,
	 * when finished it reloads the menu
	 */
TYPO3BackendShortcutMenu.createShortcut = function(backPath, moduleName, url){
		if(document.getElementById('role-active')) {
			$$('#role-menu .toolbar-item img')[0].src = 'gfx/spinner.gif';
		} else {
			$$('#shortcut-menu .toolbar-item img')[0].src = 'gfx/spinner.gif';
		}
		new Ajax.Request('ajax.php', {
			method: 'get',
			parameters: 'ajaxID=RoleMenu::getActivatedRole',
			asynchronous: false, // needs to be synchronous to build the options before adding the selectfield
			requestHeaders: {Accept: 'application/json'},
			onSuccess: function(transport, json) {
				var roleGroups = transport.responseText.evalJSON(true);
				var ajaxresponse = Object.values(roleGroups);
				if(ajaxresponse!=0 || ajaxresponse!='') {
					var createShortcutHandler = 'RoleMenu';
				} else {
					var createShortcutHandler = 'ShortcutMenu';					
				}

					// synchrous call to wait for it to complete and call the render
					// method with backpath _afterwards_
		
				new Ajax.Request(backPath + 'ajax.php', {
					parameters : 'ajaxID=' + createShortcutHandler + '::create&module=' + moduleName + '&url=' + url,
					asynchronous : false
				});
			}
		});
		if(document.getElementById('role-active')) {
			top.TYPO3BackendRoleMenu.reRenderMenu(null, null, backPath);
			$$('#role-menu .toolbar-item img')[0].src = top.TYPO3BackendRoleMenu.toolbarItemIcon;

		} else {
			top.TYPO3BackendShortcutMenu.reRenderMenu(null, null, backPath);			
			$$('#shortcut-menu .toolbar-item img')[0].src = this.toolbarItemIcon;			

		}
/*
		if(document.getElementById('shortcut-menu')) {
			top.TYPO3BackendShortcutMenu.reRenderMenu(null, null, backPath);			
			$$('#shortcut-menu .toolbar-item img')[0].src = this.toolbarItemIcon;			
		}
		if(document.getElementById('role-menu')) {
			top.TYPO3BackendRoleMenu.reRenderMenu(null, null, backPath);
			$$('#role-menu .toolbar-item img')[0].src = top.TYPO3BackendRoleMenu.toolbarItemIcon;
		}
		* */
}

/**
 * class to handle the role menu
 *
 * $Id$
 */
var RoleMenu = Class.create({

	/**
	 * registers for resize event listener and executes on DOM ready
	 */
	initialize: function() {
/*		top.refreshMenu();*/
		Event.observe(window, 'resize', this.positionMenu);
		Event.observe(window, 'load', function(){
			this.initControls();
			this.positionMenu();
			this.toolbarItemIcon = $$('#role-menu .toolbar-item img')[0].src;
			Event.observe($$('#role-menu .toolbar-item')[0], 'click', this.toggleMenu);
		}.bindAsEventListener(this));
/*		top.TYPO3ModuleMenu.refreshMenu();*/
	},


	/**
	 * initializes the controls to follow, edit, and delete roles
	 *
	 */
	initControls: function() {

		$$('.role-label a').each(function(element) {
			var roleString = element.up('tr.role').identify().slice(5);
			var roleRoleId = roleString.split("#",1);
			var roleRoleId = roleRoleId.join();
			if(roleString.search("#")) {
				var roleId = roleString.substr(roleString.lastIndexOf("#")+1);
			} else {
				var roleId = 0;
			}
			var roleTest = $('role-label-'+roleRoleId+'#'+roleId).readAttribute('sessionsave');
				// map InPlaceEditor to edit icons
			if(roleId) {
			new Ajax.InPlaceEditor('role-label-'+roleRoleId+'#'+roleId, 'ajax.php?ajaxID=RoleMenu::saveRole', {
				externalControl     : 'role-edit-' + roleId,
				externalControlOnly : true,
				highlightcolor      : '#f9f9f9',
				highlightendcolor   : '#f9f9f9',
				onFormCustomization : this.addGroupSelect,
				onComplete          : this.reRenderMenu.bind(this),
				callback            : function(form, nameInputFieldValue) {
					var params = form.serialize();
					params += '&roleId=' + roleId + '&roleRoleId=' + roleRoleId;

					return params;
				},
				textBetweenControls : ' ',
				cancelControl       : 'button',
				clickToEditText     : '',
				htmlResponse        : true
			});
			}
				// follow/execute roles
			element.observe('click', function(event) {
				var that = this;
				new Ajax.Request('ajax.php', {
					parameters : 'ajaxID=RoleMenu::activateRole&roleId=' + roleRoleId + '&roleURL=' + roleTest ,
					asynchronous : false,
					onComplete : function() {that.reRenderMenu(null, null, null);that.toggleMenu();} 
				});

/*				top.TYPO3ModuleMenu.refreshMenu();*/
				new Ajax.Updater('typo3-menu', TS.PATH_typo3 + 'ajax.php', {
					parameters   : 'ajaxID=ModuleMenu::render',
					asynchronous : false,
					evalScripts  : true
				});
				$('typo3-backend-php').insert( { bottom: '<script type="text/javascript">' + roleTest + '</scr'+'ipt>' } );
				top.TYPO3ModuleMenu.registerEventListeners();
				top.TYPO3ModuleMenu.highlightModule(top.TYPO3ModuleMenu.currentlyHighlightedModuleId, top.TYPO3ModuleMenu.currentlyHighLightedMainModule);
/*				this.reRenderMenu.bind(this);*/
/*				this.toggleMenu();*/
/*				top.content.nav_frame.refresh_nav();*/
/*				Event.stop(event);*/
			}.bind(this));

		}.bind(this));

			// activate delete icon
		$$('.role-delete img').each(function(element) {
			element.observe('click', function(event) {
				if(confirm('Do you really want to remove this role?')) {
					var deleteControl = event.element();
					var roleId = deleteControl.up('tr.role').identify().slice(5);
					var roleString = element.up('tr.role').identify().slice(5);
					var roleRoleId = roleString.split("#",1);
					var roleRoleId = roleRoleId.join();
					if(roleString.search("#")) {
						var roleId = roleString.substr(roleString.lastIndexOf("#")+1);
					} else {
						var roleId = 0;
					}
					new Ajax.Request('ajax.php', {
						parameters : 'ajaxID=RoleMenu::delete&roleId=' + roleId,
						onComplete : this.reRenderMenu.bind(this)
					});
				}
			}.bind(this));
		}.bind(this));

	},

	/**
	 * positions the menu below the toolbar icon, let's do some math!
	 */
	positionMenu: function() {
		var calculatedOffset = 0;
		var parentWidth      = $('role-menu').getWidth();
		var ownWidth         = $$('#role-menu .toolbar-item-menu')[0].getWidth();
		var parentSiblings   = $('role-menu').previousSiblings();

		parentSiblings.each(function(toolbarItem) {
			calculatedOffset += toolbarItem.getWidth() - 1;
			// -1 to compensate for the margin-right -1px of the list items,
			// which itself is necessary for overlaying the separator with the active state background

			if(toolbarItem.down().hasClassName('no-separator')) {
				calculatedOffset -= 1;
			}
		});
		calculatedOffset = calculatedOffset - ownWidth + parentWidth;


		$$('#role-menu .toolbar-item-menu')[0].setStyle({
			left: calculatedOffset + 'px'
		});
	},

	/**
	 * toggles the visibility of the menu and places it under the toolbar icon
	 */
	toggleMenu: function(event) {
		var toolbarItem = $$('#role-menu > a')[0];
		var menu        = $$('#role-menu .toolbar-item-menu')[0];
		toolbarItem.blur();

		if(!toolbarItem.hasClassName('toolbar-item-active')) {
			toolbarItem.addClassName('toolbar-item-active');
			Effect.Appear(menu, {duration: 0.2});
			TYPO3BackendToolbarManager.hideOthers(toolbarItem);
		} else {
			toolbarItem.removeClassName('toolbar-item-active');
			Effect.Fade(menu, {duration: 0.1});
		}
		Event.stop(event);
	},

	/**
	 * adds a select field for the groups
	 */
	addGroupSelect: function(inPlaceEditor, inPlaceEditorForm) {
		var selectField = $(document.createElement('select'));
		var selectField2 = $(document.createElement('input'));

			// determine the role id
		var roleString  = inPlaceEditorForm.identify().slice(11, -14);

					var roleRoleId = roleString.split("#",1);
					var roleRoleId = roleRoleId.join();
					if(roleString.search("#")) {
						var roleId = roleString.substr(roleString.lastIndexOf("#")+1);
					} else {
						var roleId = 0;
					}

			// now determine the role's group id
//		var role        = $('role-' + roleId).up('tr.role');
		var firstInGroup    = null;
		var roleGroupId = 0;

/*		if(role.hasClassName('first-row')) {
			firstInGroup = role;
		} else {
			firstInGroup = role.previous('.first-row');
		}

		if(undefined != firstInGroup) {
			roleGroupId = firstInGroup.previous().identify().slice(15);
		}*/

		selectField2.type = 'checkbox';
		selectField2.name = 'role-startupShortcut';
		selectField2.value = '1';
/*		selectField2.checked = true;*/
		selectField.name = 'role-group';
		selectField.id = 'role-group-select-' + roleId;
		selectField.size = 1;
		selectField.setStyle({marginBottom: '5px'});

			// create options
		var option;
			// first create an option for "no group"
		option = document.createElement('option');
		option.value = 0;
		option.selected = (roleGroupId == 0 ? true : false);
		option.appendChild(document.createTextNode('No Group'));
		selectField.appendChild(option);



		inPlaceEditor._form.appendChild(document.createElement('br'));
		inPlaceEditor._form.appendChild(document.createTextNode('Startup shortcut? '));
		inPlaceEditor._form.appendChild(selectField2);
			// get the groups
		new Ajax.Request('ajax.php', {
			method: 'get',
			parameters: 'ajaxID=RoleMenu::getRoleInfo&roleId=' + roleId,
			asynchronous: false, // needs to be synchronous to build the options before adding the selectfield
			requestHeaders: {Accept: 'application/json'},
			onSuccess: function(transport, json) {
				var roleGroups = transport.responseText.evalJSON(true);
				var ajaxresponse = Object.values(roleGroups);
				var ajaxresponseArr = ajaxresponse[0].split(",",2);
				var startupShortcut = ajaxresponseArr[0];
				var globalShortcutEditAccess = ajaxresponseArr[1];
				if(startupShortcut==roleString) {
					selectField2.setAttribute("checked","checked");
					selectField2.title = startupShortcut;
				}
				if(globalShortcutEditAccess==0) {
					inPlaceEditor._controls.editor.disabled = true;
					inPlaceEditor._controls.editor.style.border = '0px';
				}
					// explicitly make the object a Hash
				/*roleGroups = $H(json.roleGroups);*/
			}
		});
		inPlaceEditor._form.appendChild(document.createElement('br'));
	},


	/**
	 * gets called when the update was succesfull, fetches the complete menu to
	 * honor changes in group assignments
	 */
	reRenderMenu: function(transport, element, backPath) {
		var container_rolename = $$('#role-name')[0];
		if(container_rolename) {
			if(!backPath) {
				var backPath = '';
			}
	
			container_rolename.setStyle({
				height: container_rolename.getHeight() + 'px'
			});
	
			new Ajax.Updater(
				container_rolename,
				backPath + 'ajax.php',
				{
					parameters : 'ajaxID=RoleMenu::getRoleName',
					asynchronous : false
				}
			);
	
			container_rolename.setStyle({
				height: 'auto'
			});
		}

		var container = $$('#role-menu .toolbar-item-menu')[0];
		if(!backPath) {
			var backPath = '';
		}

		container.setStyle({
			height: container.getHeight() + 'px'
		});
		container.update('LOADING');
		new Ajax.Updater(
			container,
			backPath + 'ajax.php',
			{
				parameters : 'ajaxID=RoleMenu::render',
				asynchronous : false
			}
		);

		container.setStyle({
			height: 'auto'
		});
		top.TYPO3BackendShortcutMenu.reRenderMenu(null, null, backPath);
		this.positionMenu();
		this.initControls();
	},

	/**
	 * makes a call to the backend class to create a new role,
	 * when finished it reloads the menu
	 */
	createRole: function(backPath, moduleName, url) {
		$$('#role-menu .toolbar-item img')[0].src = 'gfx/spinner.gif';

			// synchrous call to wait for it to complete and call the render
			// method with backpath _afterwards_
		new Ajax.Request(backPath + 'ajax.php', {
			parameters : 'ajaxID=RoleMenu::create&module=' + moduleName + '&url=' + url,
			asynchronous : false
		});

		this.reRenderMenu(null, null, backPath);
		$$('#role-menu .toolbar-item img')[0].src = this.toolbarItemIcon;
	}

});

var TYPO3BackendRoleMenu = new RoleMenu();
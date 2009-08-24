<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Stig Nørgaard Færch
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
/**
 * Function for prepending roles2-be_groups with [role]
 *
 * @author	Stig Nørgaard Færch
 */


 /**
  * 'itemsProcFunc' for the 'tt_news' extension.
  *
  * @author	Stig Nørgaard Færch
  * @package TYPO3
  * @subpackage roles2
  */
class user_roles2_itemsProcFunc {
/**
 * insert 'codes', found in the ['what_to_display'] array to the selector in the BE.
 *
 * @param	array		$config: extension configuration array
 * @return	array		$config array with extra codes merged in
 */
	function user_markroles($config) {
		require_once(PATH_t3lib.'class.t3lib_db.php');
		$TYPO3_DB = t3lib_div::makeInstance('t3lib_DB');
		$TYPO3_DB->sql_pconnect(TYPO3_db_host, TYPO3_db_username, TYPO3_db_password);
		$TYPO3_DB->sql_select_db(TYPO3_db);
		$testres = $TYPO3_DB->exec_SELECTquery('* ', 'be_groups ', ' tx_roles_role=1 AND hidden=0 AND deleted=0', '', 'title DESC');
		unset($config['items'][0]);
		while($row=$TYPO3_DB->sql_fetch_assoc($testres)) {
			array_unshift($config['items'],array(0=>'[role] '.$row['title'],1=>$row['uid'],2=>'EXT:roles2/ext_icon.gif'));
		}
		return $config;
	}

}
/*if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/roles2/class.roles2_itemsProcFunc.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/roles2/class.roles2_itemsProcFunc.php']);
}
?>*/
<?php
t3lib_div::loadTCA("be_users");
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_roles_role" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:roles2/locallang_db.php:be_groups.tx_roles_role",
		"config" => Array (
			"type" => "check",
		)
	),
);
$tempColumns2['usergroup'] = $TCA['be_users']['columns']['usergroup'];
$tempColumns2['usergroup']['config']['itemsProcFunc'] = "user_roles2_itemsProcFunc->user_markroles";
$tempColumns2['usergroup']['config']['foreign_table_where'] = 'AND be_groups.tx_roles_role=0 ORDER BY be_groups.tx_roles_role DESC,be_groups.title ASC';
$tempColumns2['usergroup']['config']['allowNonIdValues'] = 1;
$tempColumns2['usergroup']['config']['items'] = Array (Array('',0));
$tempColumns['subgroup'] = $TCA['be_groups']['columns']['subgroup'];
$tempColumns['subgroup']['config']['itemsProcFunc'] = "user_roles2_itemsProcFunc->user_markroles";
$tempColumns['subgroup']['config']['foreign_table_where'] = 'AND NOT(be_groups.uid = ###THIS_UID###) AND be_groups.hidden=0 AND be_groups.tx_roles_role=0 ORDER BY be_groups.tx_roles_role DESC,be_groups.title ASC';
$tempColumns['subgroup']['config']['allowNonIdValues'] = 1;
$tempColumns['subgroup']['config']['items'] = Array (Array('',0));

t3lib_div::loadTCA("be_groups");
t3lib_extMgm::addTCAcolumns("be_groups",$tempColumns,1);
t3lib_extMgm::addTCAcolumns("be_users",$tempColumns2,1);
t3lib_extMgm::addToAllTCAtypes("be_groups","tx_roles_role;;;;1-1-1");
?>
<?php
class user_roles2_t3lib_userauthgroup {
    var $shortcuts;

    function fetchGroupQuery_processQuery(&$obj,$grList, $idList, $whereSQL){
   		if($GLOBALS['BE_USER']->isAdmin()) return;
    	$rolesession=$obj->getSessionData("txroles2M1");
		if(isset($rolesession['activateRoleId'])) {
			$rolesession['roleReceived'] = 1;
			$rolesession['currentRole'] = $rolesession['activateRoleId'];
			unset($rolesession['activateRoleId']);
			$obj->setAndSaveSessionData('txroles2M1',$rolesession);
		}
		return $whereSQL.$this->returnAndSQL($rolesession['currentRole']); 
    }

    function returnAndSQL($role){
		if(is_null($role) OR $role===""){
		    return " AND !(tx_roles_role=1)";
		} else {
		    return " AND !(tx_roles_role=1 AND uid NOT IN (".$role."))";
		}
    }
    
}
?>
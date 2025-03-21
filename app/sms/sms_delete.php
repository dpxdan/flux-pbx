<?php
/*
	FluxPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FluxPBX

	The Initial Developer of the Original Code is
	Daniel Paixao <daniel@flux.net.br>
	Portions created by the Initial Developer are Copyright (C) 2008-2012
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Daniel Paixao <daniel@flux.net.br>

	Call Block is written by Gerrit Visser <gerrit308@gmail.com>
*/

//set the include path
	$conf = glob("{/usr/local/etc,/etc}/fluxpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

//includes files
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

if (permission_exists('sms_delete')) {
	//access granted
}
else {
	echo "access denied";
	exit;
}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//set the variable
	if (count($_GET)>0) {
		$id = $_GET["id"][0];
	}

//delete the extension
	if (strlen($id)>0) {

		//delete the call block
			$sql = "delete from v_sms_destinations ";
			$sql .= "where domain_uuid = '".$_SESSION['domain_uuid']."' ";
			$sql .= "and sms_destination_uuid = '$id' ";
			$prep_statement = $db->prepare(check_sql($sql));
			$prep_statement->execute();
			unset($prep_statement, $sql);
	}

	//redirect the browser
		$_SESSION["message"] = $text['label-delete-complete'];
		header("Location: sms.php");
		return;

?>

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
	Portions created by the Initial Developer are Copyright (C) 2008-2023
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Daniel Paixao <daniel@flux.net.br>
*/


//includes
	include "root.php";
	require_once "resources/require.php";

//check permissions
	require_once "resources/check_auth.php";
	if (permission_exists('dialplan_edit')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get the provider
	if (isset($_GET["provider"])) {
		$provider = new providers;
		$reports->domain_uuid = $_SESSION['domain_uuid'];
		$provider->delete($_GET["provider"]);
	}

//clear the cache
	$cache = new cache;
	$cache->delete("dialplan:".$_SESSION["context"]);

//set the add message
	messages::add($text['message-delete']);

//redirect the user
	header("Location: providers.php");
	return;

?>

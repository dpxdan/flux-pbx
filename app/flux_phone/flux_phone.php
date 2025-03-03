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
Portions created by the Initial Developer are Copyright (C) 2008-2020
the Initial Developer. All Rights Reserved.

Contributor(s):
Daniel Paixao <daniel@flux.net.br>
 */

//includes
//set the include path
$conf = glob("{/usr/local/etc,/etc}/fluxpbx/config.conf", GLOB_BRACE);
set_include_path(parse_ini_file($conf[0])['document.root']);

require_once "resources/require.php";
require_once "resources/check_auth.php";
require_once "resources/paging.php";

//check permissions
if (permission_exists('flux_phone_view')) {
	//access granted
} else {
	echo "access denied";
	exit;
}

//create token
$object = new token;
$token = $object->create($_SERVER['PHP_SELF']);

//add multi-lingual support
$language = new text;
$text = $language->get();

//verify the id is as uuid then set as a variable
if (is_uuid($_GET['id'])) {
	$extension_uuid = $_GET['id'];
}

//get the extension(s)
if (permission_exists('flux_phone_edit')) {
	//admin user
	$sql = "select * from view_api_phone ";
	$sql .= "where domain_uuid = :domain_uuid ";
	$sql .= "and extension_enabled = 'true' ";
	$sql .= "order by extension asc ";
} else {
	//normal user
	$sql = "select e.* ";
	$sql .= "from v_extensions as e, ";
	$sql .= "v_extension_users as eu ";
	$sql .= "where e.extension_uuid = eu.extension_uuid ";
	$sql .= "and eu.user_uuid = :user_uuid ";
	$sql .= "and e.domain_uuid = :domain_uuid ";
	$sql .= "and e.enabled = 'true' ";
	$sql .= "order by e.extension asc ";
	$parameters['user_uuid'] = $_SESSION['user']['user_uuid'];
}
$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
$database = new database;
$extensions = $database->select($sql, $parameters, 'all');
unset($sql, $parameters);


//include the header
//$document['title'] = "Flux Phone";
$document['title'] = $text['title-fluxphone'];
require_once "resources/header.php";

echo "<script>\n";
echo "function reloadPage() {\n";
echo "  window.location.assign('/core/dashboard')\n";
echo "}\n";
echo "</script>\n";

echo "<form name='frm' id='frm' method='get'>\n";
echo "<div class='action_bar' id='action_bar'>\n";
echo "	<div class='heading'><b>" . $text['title-fluxphone'] . "</b></div>\n";
echo "	<div class='actions'></div>\n";
echo "	\n";
echo "	<div style='clear: both;'></div>\n";
echo "</div>\n";
echo $text['title-description-fluxphone'] . "\n";
echo "<br /><br />\n";

echo "<div style='text-align: center; white-space: nowrap; margin: 10px 0 40px 0;'>";
echo $text['label-select_extension'] . "<br />\n";
echo "<select name='id' class='formfld' onchange='this.form.submit();'>\n";
echo "	<option value='' >" . $text['label-select'] . "...</option>\n";
if (is_array($extensions) && @sizeof($extensions) != 0) {
	foreach ($extensions as $row) {
		$selected = $row['extension_uuid'] == $extension_uuid ? "selected='selected'" : null;
		echo "	<option value='" . escape($row['extension_uuid']) . "' " . $selected . ">" . escape($row['extension']) . " " . escape($row['outbound_caller_id_name']) . " " . escape($row['description']) . "</option>\n";
	}
}
echo "</select>\n";
echo "<input type='hidden' name='" . $token['name'] . "' value='" . $token['hash'] . "'>\n";
echo "</form>\n";

//begin the content
if (strlen($extension_uuid) > 0) {
	echo "  <a href=\"#\" target=\"fluxAppPhone\" id=\"launchPhoneFluxApp\">" . $text['label-fluxphone_launch'] . "</a>\n";
}
echo "  <script>\n";
echo "  var url      = 'fluxdev.php?id=" . escape($extension_uuid) . "',\n";
echo "      features = 'name=fluxAppPhone,popup=yes,left=100,top=100,width=340,height=820'\n";
echo "      $('#launchPhoneFluxApp').on('click', function(event) { \n";
echo "          event.preventDefault() \n";
// This is set when the phone is open and removed on close
echo "          if (!localStorage.getItem('fluxPhone')) { \n";
echo "              window.open(url, 'fluxAppPhone', features)\n";
echo "              return false\n";
//echo "              reloadPage();\n";
echo "          } else { \n";
echo "              window.alert('Seu ramal já está em uso.')\n";
echo "          }\n";
echo "      })\n";
echo "	function updatevariable(data) { \n";
echo "		value = data;\n";
echo "  } \n";
echo "  </script>\n";

/*	echo "<script>\n";
echo "(function () {\n";
echo "  var rcs = document.createElement('script');\n";
echo " rcs.src = './phone/widget/adapter.js?id=".escape($extension_uuid)."&clientId=zhM8mKwgRtiWacwylQYH5A&clientSecret=s4-Z7R3bQ3ybZb2ppeqWIgsHDQaSZyT9uWjLMeBT71lw&jwt=eyJraWQiOiI4NzYyZjU5OGQwNTk0NGRiODZiZjVjYTk3ODA0NzYwOCIsInR5cCI6IkpXVCIsImFsZyI6IlJTMjU2In0.eyJhdWQiOiJodHRwczpcL1wvcGxhdGZvcm0uZGV2dGVzdC5yaW5nY2VudHJhbC5jb21cL3Jlc3RhcGlcL29hdXRoXC90b2tlbiIsInN1YiI6IjcyNDc0MDAwNSIsImlzcyI6Imh0dHBzOlwvXC9wbGF0Zm9ybS5kZXZ0ZXN0LnJpbmdjZW50cmFsLmNvbSIsImV4cCI6MTY4MzI0NDc5OSwiaWF0IjoxNjU3NDg2NDk0LCJqdGkiOiJ3Q1Mxek5FZ1NPLTdGOWlQWVJTUXZnIn0.RXSf0qRh8FQoZEJgChzq7G2O5EuPPlqeLnpNeO2V5PpT2ZQHpJeub7RjDmfHX-RSMHHpDIh6TurDz4UNGXQiGP7avejCpkg0X2RF78IaJlEqrREFlp6xWaefHHlj9v71BpGnZ384qrmnp7UauVoRjLI8zzCeAzwxIG2_UcC6ZFqRgvyoq_2E1fVCI_azzC4MnWrtFKGL_V2W4mq0-jzxA2doB51vNQSicrmUG0D38ySDIxnWThcGxHohQpmzIP7ToCZH8WtPiwV30P_swsmnwyRViLdaLtlEBWqf8UCyBWsJ7uedYumzup1QoR-xdneDHvB1D3tlwMteREmSpu9cDQ&appKey=".escape($extension_uuid)."&newAdapterUI=1&notification=1&disableGlip=false&multipleTabsSupport=1&enablePopup=1&enableRingtoneSettings=1&appServer=https://teams.flux.cloud/api&discoverAppServer=https://teams.flux.cloud/api';\n";
echo "  var rcs0 = document.getElementsByTagName('script')[0];\n";
echo " rcs0.parentNode.insertBefore(rcs, rcs0);\n";
echo " })();\n";
echo "</script>\n";*/

echo "</div>\n";
//show the footer
require_once "resources/footer.php";

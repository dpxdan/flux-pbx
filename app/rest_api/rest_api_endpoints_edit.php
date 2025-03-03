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
	Portions created by the Initial Developer are Copyright (C) 2017-2022
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

//check permissions
	if (!permission_exists('restapi_endpoint_add') && !permission_exists('restapi_endpoint_edit')) {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//action add or update
	if (is_uuid($_REQUEST["id"])) {
		$action = "update";
		$restapi_endpoint_uuid = $_REQUEST["id"];
		$id = $_REQUEST["id"];
	}
	else {
		$action = "add";
	}

//get http post variables and set them to php variables
	if (is_array($_POST)) {
		$restapi_endpoint_uuid = $_POST["api_endpoint_uuid"];
		$api_endpoint_global = $_POST["api_endpoint_global"];
		$api_endpoint_name = $_POST["api_endpoint_name"];
		$api_endpoint_category = $_POST["api_endpoint_category"];
		$api_endpoint_method = $_POST["api_endpoint_method"];
		$api_endpoint_uri = $_POST["api_endpoint_uri"];
		$api_endpoint_redirect = $_POST["api_endpoint_redirect"];
		$api_endpoint_carrier = $_POST["api_endpoint_carrier"];
		$api_endpoint_token = $_POST["api_endpoint_token"];
		$api_endpoint_authentication = $_POST["api_endpoint_authentication"];
		$api_endpoint_username = $_POST["api_endpoint_username"];
		$api_endpoint_password = $_POST["api_endpoint_password"];
		$api_endpoint_enabled = $_POST["api_endpoint_enabled"];
		$api_endpoint_description = $_POST["api_endpoint_description"];
	}

//process the user data and save it to the database
	if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {

		//delete the restapi
			if (permission_exists('restapi_endpoint_delete')) {
				if ($_POST['action'] == 'delete' && is_uuid($restapi_endpoint_uuid)) {
					//prepare
						$array[0]['checked'] = 'true';
						$array[0]['uuid'] = $restapi_endpoint_uuid;
					//delete
						$obj = new rest_api;
						$obj->delete($array);
					//redirect
						header('Location: rest_api_endpoints.php');
						exit;
				}
			}

		//get the uuid from the POST
			if ($action == "update") {
				$restapi_endpoint_uuid = $_POST["api_endpoint_uuid"];
			}

		//validate the token
			$token = new token;
			if (!$token->validate($_SERVER['PHP_SELF'])) {
				message::add($text['message-invalid_token'],'negative');
				header('Location: rest_api_endpoints.php');
				exit;
			}

		//check for all required data
			$msg = '';
			if (strlen($api_endpoint_name) == 0) { $msg .= $text['message-required']." ".$text['label-api_name']."<br>\n"; }
			if (strlen($api_endpoint_category) == 0) { $msg .= $text['message-required']." ".$text['label-api_category']."<br>\n"; }
			if (strlen($api_endpoint_method) == 0) { $msg .= $text['message-required']." ".$text['label-api_method']."<br>\n"; }
			if (strlen($api_endpoint_uri) == 0) { $msg .= $text['message-required']." ".$text['label-api_uri']."<br>\n"; }
			if (strlen($api_endpoint_carrier) == 0) { $msg .= $text['message-required']." ".$text['label-api_carrier']."<br>\n"; }
			if (strlen($api_endpoint_authentication) == 0) { $msg .= $text['message-required']." ".$text['label-restapi_authentication']."<br>\n"; }
			if (strlen($api_endpoint_enabled) == 0) { $msg .= $text['message-required']." ".$text['label-api_enabled']."<br>\n"; }
			if (strlen($msg) > 0 && strlen($_POST["persistformvar"]) == 0) {
				require_once "resources/header.php";
				require_once "resources/persist_form_var.php";
				echo "<div align='center'>\n";
				echo "<table><tr><td>\n";
				echo $msg."<br />";
				echo "</td></tr></table>\n";
				persistformvar($_POST);
				echo "</div>\n";
				require_once "resources/footer.php";
				return;
			}

		//add the api_endpoint_uuid
			if (strlen($restapi_endpoint_uuid) == 0) {
				$restapi_endpoint_uuid = uuid();
			}

		//prepare the array
			$array['restapi_endpoints'][0]['restapi_endpoint_uuid'] = $restapi_endpoint_uuid;
			if ($api_endpoint_global == 'true') {
				$array['restapi_endpoints'][0]['domain_uuid'] = NULL;
			} else {
				$array['restapi_endpoints'][0]['domain_uuid'] = $_SESSION["domain_uuid"];
			}
			$array['restapi_endpoints'][0]['api_endpoint_name'] = $api_endpoint_name;
			$array['restapi_endpoints'][0]['api_endpoint_category'] = $api_endpoint_category;
			$array['restapi_endpoints'][0]['api_endpoint_method'] = $api_endpoint_method;
			$array['restapi_endpoints'][0]['api_endpoint_uri'] = $api_endpoint_uri;
			$array['restapi_endpoints'][0]['api_endpoint_redirect'] = $api_endpoint_redirect;
			$array['restapi_endpoints'][0]['api_endpoint_carrier'] = $api_endpoint_carrier;
			$array['restapi_endpoints'][0]['api_endpoint_token'] = $api_endpoint_token;
			$array['restapi_endpoints'][0]['api_endpoint_authentication'] = $api_endpoint_authentication;
			$array['restapi_endpoints'][0]['api_endpoint_username'] = $api_endpoint_username;
			$array['restapi_endpoints'][0]['api_endpoint_password'] = $api_endpoint_password;
//			$array['restapi'][0]['api_endpoint_sql'] = $api_endpoint_sql;
			$array['restapi_endpoints'][0]['api_endpoint_enabled'] = $api_endpoint_enabled;
			$array['restapi_endpoints'][0]['api_endpoint_description'] = $api_endpoint_description;

		//save to the data
			$database = new database;
			$database->app_name = 'FluxPBX RestAPIs';
			$database->app_uuid = '41669f92-ed54-4851-8b98-e244fa71f38c';
			$database->save($array);
			$message = $database->message;




		//redirect the user
			if (isset($action)) {
				if ($action == "add") {
					$_SESSION["message"] = $text['message-add'];
				}
				if ($action == "update") {
					$_SESSION["message"] = $text['message-update'];
				}
				header('Location: rest_api_endpoints.php');
				return;
			}
	}

	//assign the user to the ring group
		if (is_uuid($_REQUEST["user_uuid"]) && is_uuid($_REQUEST["id"]) && $_GET["a"] != "delete" && permission_exists("restapi_endpoint_edit")) {
			//set the variables
				$user_uuid = $_REQUEST["user_uuid"];
			//build array
				$array['restapi_endpoints_users'][0]['restapi_endpoints_user_uuid'] = uuid();
				$array['restapi_endpoints_users'][0]['domain_uuid'] = $domain_uuid;
				$array['restapi_endpoints_users'][0]['restapi_endpoint_uuid'] = $restapi_endpoint_uuid;
				$array['restapi_endpoints_users'][0]['user_uuid'] = $user_uuid;
			//grant temporary permissions
				$p = new permissions;
				$p->add('restapi_endpoint_edit', 'temp');
			//execute delete
				$database = new database;
				$database->app_name = 'FluxPBX RestAPIs';
				$database->app_uuid = '41669f92-ed54-4851-8b98-e244fa71f38c';
				$database->save($array);
				unset($array);
			//revoke temporary permissions
				$p->delete('restapi_endpoint_user_add', 'temp');
			//set message
				message::add($text['message-add']);
			//redirect the browser
				header("Location: rest_api_endpoints_edit.php?id=".urlencode($restapi_endpoint_uuid));
				exit;
		}

//pre-populate the form
	if (is_array($_GET) && $_POST["persistformvar"] != "true") {
		$restapi_endpoint_uuid = $_GET["id"];
		$sql = "select * from v_restapi_endpoints ";
		$sql .= "where restapi_endpoint_uuid = :api_endpoint_uuid ";
		$parameters['api_endpoint_uuid'] = $restapi_endpoint_uuid;
		$database = new database;
		$row = $database->select($sql, $parameters, 'row');
		if (is_array($row) && sizeof($row) != 0) {
			$api_endpoint_domain_uuid = $row["domain_uuid"];
			$api_endpoint_name = $row["api_endpoint_name"];
			$api_endpoint_category = $row["api_endpoint_category"];
			$api_endpoint_method = $row["api_endpoint_method"];
			$api_endpoint_uri = $row["api_endpoint_uri"];
			$api_endpoint_redirect = $row["api_endpoint_redirect"];
			$api_endpoint_carrier = $row["api_endpoint_carrier"];
			$api_endpoint_token = $row["api_endpoint_token"];
			$api_endpoint_authentication = $row["api_endpoint_authentication"];
			$api_endpoint_username = $row["api_endpoint_username"];
			$api_endpoint_password = $row["api_endpoint_password"];
			//$api_endpoint_sql = $row["api_endpoint_sql"];
			$api_endpoint_enabled = $row["api_endpoint_enabled"];
			$api_endpoint_description = $row["api_endpoint_description"];
		} else {
			$api_endpoint_method = "GET";
			$api_endpoint_domain_uuid = "";
		}
		unset($sql, $parameters, $row);
	}

	
	//get the ring group users
		if (is_uuid($restapi_endpoint_uuid)) {
			$sql = "select u.username, r.user_uuid, r.restapi_endpoint_uuid ";
			$sql .= "from v_restapi_endpoints_users as r, v_users as u ";
			$sql .= "where r.user_uuid = u.user_uuid  ";
			$sql .= "and u.user_enabled = 'true' ";
			$sql .= "and r.domain_uuid = :domain_uuid ";
			$sql .= "and r.restapi_endpoint_uuid = :restapi_endpoint_uuid ";
			$sql .= "order by u.username asc ";
			$parameters['domain_uuid'] = $domain_uuid;
			$parameters['restapi_endpoint_uuid'] = $restapi_endpoint_uuid;
			$database = new database;
			$restapi_endpoints_users = $database->select($sql, $parameters, 'all');
			unset($sql, $parameters);
		}
	
	//get the users
		$sql = "select * from v_users ";
		$sql .= "where domain_uuid = :domain_uuid ";
		$sql .= "and user_enabled = 'true' ";
		$sql .= "order by username asc ";
		$parameters['domain_uuid'] = $domain_uuid;
		$database = new database;
		$users = $database->select($sql, $parameters, 'all');
		unset($sql, $parameters);


//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//show the header
	$document['title'] = $text['title-restapis_endpoint'];
	require_once "resources/header.php";


	//option to change select to text
		if (if_group("superadmin")) {
			echo "<script>\n";
			echo "var Objs;\n";
			echo "\n";
			echo "function changeToInput(obj){\n";
			echo "	tb=document.createElement('INPUT');\n";
			echo "	tb.type='text';\n";
			echo "	tb.name=obj.name;\n";
			echo "	tb.setAttribute('class', 'formfld');\n";
			//echo "	tb.setAttribute('style', 'width: 380px;');\n";
			echo "	tb.value=obj.options[obj.selectedIndex].value;\n";
			echo "	tbb=document.createElement('INPUT');\n";
			echo "	tbb.setAttribute('class', 'btn');\n";
			echo "	tbb.setAttribute('style', 'margin-left: 4px;');\n";
			echo "	tbb.type='button';\n";
			echo "	tbb.value=$('<div />').html('&#9665;').text();\n";
			echo "	tbb.objs=[obj,tb,tbb];\n";
			echo "	tbb.onclick=function(){ Replace(this.objs); }\n";
			echo "	obj.parentNode.insertBefore(tb,obj);\n";
			echo "	obj.parentNode.insertBefore(tbb,obj);\n";
			echo "	obj.parentNode.removeChild(obj);\n";
			echo "}\n";
			echo "\n";
			echo "function Replace(obj){\n";
			echo "	obj[2].parentNode.insertBefore(obj[0],obj[2]);\n";
			echo "	obj[0].parentNode.removeChild(obj[1]);\n";
			echo "	obj[0].parentNode.removeChild(obj[2]);\n";
			echo "}\n";
			echo "</script>\n";
			echo "\n";
		}


//show the content
	echo "<form name='frm' id='frm' method='post' action=''>\n";

	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>".$text['title-restapis_endpoint']."</b></div>\n";
	echo "	<div class='actions'>\n";
	echo button::create(['type'=>'button','label'=>$text['button-back'],'icon'=>$_SESSION['theme']['button_icon_back'],'style'=>'margin-right: 15px;','link'=>'rest_api_endpoints.php']);
	if ($action == 'update' && permission_exists('restapi_endpoint_delete')) {
		echo button::create(['type'=>'submit','label'=>$text['button-delete'],'icon'=>$_SESSION['theme']['button_icon_delete'],'name'=>'action','value'=>'delete','onclick'=>"if (confirm('".$text['confirm-delete']."')) { document.getElementById('frm').submit(); } else { this.blur(); return false; }",'style'=>'margin-right: 15px;']);
	}
	echo button::create(['type'=>'submit','label'=>$text['button-save'],'icon'=>$_SESSION['theme']['button_icon_save'],'name'=>'action','value'=>'save']);
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";

	echo "<tr>\n";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_name']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='api_endpoint_name' maxlength='255' value='".escape($api_endpoint_name)."'>\n";
	echo "<br />\n";
	echo $text['description-restapi_name']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_global']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<select class='formfld' name='api_endpoint_global'>\n";
	if (strlen($api_endpoint_domain_uuid) < 1) {
		echo "		<option value='true' selected='selected'>".$text['label-true']."</option>\n";
	}
	else {
		echo "		<option value='true'>".$text['label-true']."</option>\n";
	}
	if (strlen($api_endpoint_domain_uuid) > 0) {
		echo "		<option value='false' selected='selected'>".$text['label-false']."</option>\n";
	}
	else {
		echo "		<option value='false'>".$text['label-false']."</option>\n";
	}
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-restapi_global']."\n";
	echo "</td>\n";
	echo "</tr>\n";


	echo "<tr>\n";
	echo "<td width='30%' class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_category']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='api_endpoint_category' maxlength='255' value='".escape($api_endpoint_category)."'>\n";
	echo "<br />\n";
	echo $text['description-restapi_category']."\n";
	echo "</td>\n";
	echo "</tr>\n";


	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_method']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<select class='formfld' name='api_endpoint_method'>\n";
	echo "		<option value='GET' selected='selected'>GET</option>\n";
	echo "		<option value='POST' selected='selected'>POST</option>\n";
	echo "		<option value='PUT' selected='selected'>PUT</option>\n";
	echo "		<option value='DELETE' selected='selected'>DELETE</option>\n";
	echo "		<option value='".escape($api_endpoint_method)."' selected='selected'>".escape($api_endpoint_method)."</option>\n";
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-restapi_method']."\n";
	echo "</td>\n";
	echo "</tr>\n";



	echo "<tr>\n";
	echo "<td width='30%' class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_uri']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='api_endpoint_uri' maxlength='255' size='70' value='".escape($api_endpoint_uri)."'>\n";
	echo "<br />\n";
	echo $text['description-restapi_uri']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td width='30%' class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_redirect']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='api_endpoint_redirect' maxlength='255' size='70' value='".escape($api_endpoint_redirect)."'>\n";
	echo "<br />\n";
	echo $text['description-restapi_redirect']."\n";
	echo "</td>\n";
	echo "</tr>\n";


	echo "<tr>\n";
	echo "<td width='30%' class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_carrier']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='api_endpoint_carrier' maxlength='255' size='70' value='".escape($api_endpoint_carrier)."'>\n";
	echo "<br />\n";
	echo $text['description-restapi_carrier']."\n";
	echo "</td>\n";
	echo "</tr>\n";
	
	
	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_authentication']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<select class='formfld' name='api_endpoint_authentication'>\n";
	if ($api_endpoint_authentication == "username") {
		echo "		<option value='username' selected='selected'>".$text['label-username']."</option>\n";
	}
	else {
		echo "		<option value='username'>".$text['label-username']."</option>\n";
	}
	if ($api_endpoint_authentication == "token") {
		echo "		<option value='token' selected='selected'>".$text['label-token']."</option>\n";
	}
	else {
		echo "		<option value='token'>".$text['label-token']."</option>\n";
	}
	if ($api_endpoint_authentication == "both") {
		echo "		<option value='both' selected='selected'>".$text['label-both']."</option>\n";
	}
	else {
		echo "		<option value='both'>".$text['label-both']."</option>\n";
	}
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-restapi_authentication']."\n";
	echo "</td>\n";
	echo "</tr>\n";
	


	echo "<tr>\n";
	echo "<td width='30%' class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-username']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='api_endpoint_username' maxlength='255' size='70' value='".escape($api_endpoint_username)."'>\n";
	echo "<br />\n";
	echo $text['description-restapi_username']."\n";
	echo "</td>\n";
	echo "</tr>\n";
	
	
	
	echo "<tr>\n";
	echo "<td width='30%' class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_password']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='password' name='api_endpoint_password' maxlength='255' size='70' value='".escape($api_endpoint_password)."'>\n";
	echo "<br />\n";
	echo $text['description-restapi_password']."\n";
	echo "</td>\n";
	echo "</tr>\n";


	
	echo "<tr>\n";
	echo "<td width='30%' class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_token']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='api_endpoint_token' maxlength='255' size='70' value='".escape($api_endpoint_token)."'>\n";
	echo "<br />\n";
	echo $text['description-restapi_token']."\n";
	echo "</td>\n";
	echo "</tr>\n";


	echo "	<tr>";
	echo "		<td class='vncell' valign='top'>".$text['label-user_list']."</td>";
	echo "		<td class='vtable'>";
	if (is_array($restapi_endpoints_users) && @sizeof($restapi_endpoints_users) != 0) {
		echo "		<table width='300px'>\n";
		foreach ($restapi_endpoints_users as $field) {
			echo "			<tr>\n";
			echo "				<td class='vtable'>".escape($field['username'])."</td>\n";
			echo "				<td>\n";
			echo "					<a href='restapi_endpoint_edit.php?id=".urlencode($restapi_endpoint_uuid)."&user_uuid=".urlencode($field['user_uuid'])."&a=delete' alt='".$text['button-delete']."' onclick=\"return confirm('".$text['confirm-delete']."')\">".$v_link_label_delete."</a>\n";
			echo "				</td>\n";
			echo "			</tr>\n";
		}
		echo "		</table>\n";
		echo "		<br />\n";
	}
	echo "			<select name=\"user_uuid\" class='formfld' style='width: auto;'>\n";
	echo "			<option value=\"\"></option>\n";
	if (is_array($users) && @sizeof($users) != 0) {
		foreach ($users as $field) {
			foreach ($restapi_endpoints_users as $user) {
				if ($user['user_uuid'] == $field['user_uuid']) { continue 2; } //skip already assigned
			}
			echo "			<option value='".escape($field['user_uuid'])."'>".escape($field['username'])."</option>\n";
		}
	}
	echo "			</select>";
	echo button::create(['type'=>'submit','label'=>$text['button-add'],'icon'=>$_SESSION['theme']['button_icon_add'],'collapse'=>'never']);
	echo "			<br>\n";
	echo "			".$text['description-user_list']."\n";
	echo "			<br />\n";
	echo "		</td>";
	echo "	</tr>";



	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_enabled']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<select class='formfld' name='api_endpoint_enabled'>\n";
	if ($api_endpoint_enabled == "true") {
		echo "		<option value='true' selected='selected'>".$text['label-true']."</option>\n";
	}
	else {
		echo "		<option value='true'>".$text['label-true']."</option>\n";
	}
	if ($api_endpoint_enabled == "false") {
		echo "		<option value='false' selected='selected'>".$text['label-false']."</option>\n";
	}
	else {
		echo "		<option value='false'>".$text['label-false']."</option>\n";
	}
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-restapi_enabled']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_description']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='api_endpoint_description' maxlength='255' size='70' value=\"".escape($api_endpoint_description)."\">\n";
	echo "<br />\n";
	echo $text['description-restapi_description']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "</table>";
	echo "<br /><br />";
	echo "<input type='hidden' name='api_endpoint_uuid' value='".escape($restapi_endpoint_uuid)."'>\n";
	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";

	echo "</form>";

//include the footer
	require_once "resources/footer.php";

?>
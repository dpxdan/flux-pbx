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
	Portions created by the Initial Developer are Copyright (C) 2010-2020
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Daniel Paixao <daniel@flux.net.br>

*/

//set the include path
	$conf = glob("{/usr/local/etc,/etc}/fluxpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

//includes files
	require_once "resources/require.php";
	require_once "resources/check_auth.php";
	require_once "resources/classes/ringbacks.php";

//check permissions
	if (permission_exists('flux_provider_add') || permission_exists('flux_provider_edit')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//initialize the destinations object
	$destination = new destinations;

//get total domain ring group count
	$sql = "select count(*) from v_flux_providers ";
	$sql .= "where domain_uuid = :domain_uuid ";
	$parameters['domain_uuid'] = $domain_uuid;
	$database = new database;
	$total_ring_groups = $database->select($sql, $parameters, 'column');
	unset($sql, $parameters);

//action add or update
	if (is_uuid($_REQUEST["id"]) || is_uuid($_REQUEST["flux_provider_uuid"])) {
		$action = "update";

		//get the flux_provider_uuid
		$flux_provider_uuid = $_REQUEST["id"];
		if (is_uuid($_REQUEST["flux_provider_uuid"])) {
			$flux_provider_uuid = $_REQUEST["flux_provider_uuid"];
		}

		//get the domain_uuid
		if (is_uuid($flux_provider_uuid) && permission_exists('flux_provider_all')) {
			$sql = "select domain_uuid from v_flux_providers ";
			$sql .= "where flux_provider_uuid = :flux_provider_uuid ";
			$parameters['flux_provider_uuid'] = $flux_provider_uuid;
			$database = new database;
			$domain_uuid = $database->select($sql, $parameters, 'column');
			unset($sql, $parameters);
		}
		else {
			$domain_uuid = $_SESSION['domain_uuid'];
		}
	}
	else {
		$action = "add";
		$domain_uuid = $_SESSION['domain_uuid'];
	}

//delete the user from the ring group
	if (
		$_GET["a"] == "delete"
		&& is_uuid($_REQUEST["user_uuid"])
		&& permission_exists("flux_provider_edit")
		) {
		//set the variables
			$user_uuid = $_REQUEST["user_uuid"];
		//build array
			$array['flux_provider_users'][0]['domain_uuid'] = $domain_uuid;
			$array['flux_provider_users'][0]['flux_provider_uuid'] = $flux_provider_uuid;
			$array['flux_provider_users'][0]['user_uuid'] = $user_uuid;
		//grant temporary permissions
			$p = new permissions;
			$p->add('flux_provider_user_delete', 'temp');
		//execute delete
			$database = new database;
			$database->app_name = 'flux_providers';
			$database->app_uuid = '04802d00-0920-4a9e-8da1-2edc294e9585';
			$database->delete($array);
			unset($array);
		//revoke temporary permissions
			$p->delete('flux_provider_user_delete', 'temp');
		//save the message to a session variable
			message::add($text['message-delete']);
		//redirect the browser
			header("Location: flux_provider_edit.php?id=$flux_provider_uuid");
			exit;
	}

//get total ring group count from the database, check limit, if defined
	if ($action == 'add') {
		if ($_SESSION['limit']['flux_providers']['numeric'] != '') {
			$sql = "select count(*) from v_flux_providers ";
			$sql .= "where domain_uuid = :domain_uuid ";
			$parameters['domain_uuid'] = $domain_uuid;
			$database = new database;
			$total_ring_groups = $database->select($sql, $parameters, 'column');
			unset($sql, $parameters);

		}
	}

//get http post variables and set them to php variables
	if (count($_POST) > 0) {

		//process the http post data by submitted action
			if ($_POST['action'] != '' && is_uuid($flux_provider_uuid)) {
				$array[0]['checked'] = 'true';
				$array[0]['uuid'] = $flux_provider_uuid;

				switch ($_POST['action']) {
					case 'copy':
						if (permission_exists('flux_provider_add')) {
							$obj = new flux_providers;
							$obj->copy($array);
						}
						break;
					case 'delete':
						if (permission_exists('flux_provider_delete')) {
							$obj = new flux_providers;
							$obj->delete($array);
						}
						break;
				}

				header('Location: flux_providers.php');
				exit;
			}

		//set variables from http values
		$user_uuid = $_POST["user_uuid"];
		$flux_provider_name = $_POST["flux_provider_name"];
		$domain_uuid = $_POST["domain_uuid"];
		$flux_provider_api_url = $_POST["flux_provider_api_url"];
		$flux_provider_auth_url = $_POST["flux_provider_auth_url"];
		$flux_provider_auth_type = $_POST["flux_provider_auth_type"];
		$flux_provider_auth_method = $_POST["flux_provider_auth_method"];
		$flux_provider_enabled = $_POST["flux_provider_enabled"] ?: 'false';
    $flux_provider_description = $_POST["flux_provider_description"];

	}

//assign the user to the ring group
	if (is_uuid($_REQUEST["user_uuid"]) && is_uuid($_REQUEST["id"]) && $_GET["a"] != "delete" && permission_exists("flux_provider_edit")) {
		//set the variables
			$user_uuid = $_REQUEST["user_uuid"];
		//build array
			$array['flux_provider_users'][0]['flux_provider_user_uuid'] = uuid();
			$array['flux_provider_users'][0]['domain_uuid'] = $domain_uuid;
			$array['flux_provider_users'][0]['flux_provider_uuid'] = $flux_provider_uuid;
			$array['flux_provider_users'][0]['user_uuid'] = $user_uuid;
			$array['flux_provider_users'][0]['flux_provider_user_enabled'] = 'true';
		//grant temporary permissions
			$p = new permissions;
			$p->add('flux_provider_user_add', 'temp');
		//execute delete
			$database = new database;
			$database->app_name = 'flux_providers';
			$database->app_uuid = '04802d00-0920-4a9e-8da1-2edc294e9585';
			$database->save($array);
			unset($array);
		//revoke temporary permissions
			$p->delete('flux_provider_user_add', 'temp');
		//set message
			message::add($text['message-add']);
		//redirect the browser
			header("Location: flux_provider_edit.php?id=".urlencode($flux_provider_uuid));
			exit;
	}

//process the HTTP POST
	if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {

		//validate the token
			$token = new token;
			if (!$token->validate($_SERVER['PHP_SELF'])) {
				message::add($text['message-invalid_token'],'negative');
				header('Location: flux_providers.php');
				exit;
			}

		//check for all required data
			$msg = '';
			if (strlen($flux_provider_name) == 0) { $msg .= $text['message-name']."<br>\n"; }
//			if (strlen($flux_provider_api_url) == 0) { $msg .= $text['message-extension']."<br>\n"; }
			//if (strlen($flux_provider_greeting) == 0) { $msg .= $text['message-greeting']."<br>\n"; }
	//		if (strlen($flux_provider_auth_type) == 0) { $msg .= $text['message-strategy']."<br>\n"; }
//			if (strlen($flux_provider_auth_url) == 0) { $msg .= $text['message-call_timeout']."<br>\n"; }
			//if (strlen($flux_provider_timeout_app) == 0) { $msg .= $text['message-timeout_action']."<br>\n"; }
			//if (strlen($flux_provider_cid_name_prefix) == 0) { $msg .= "Please provide: Caller ID Name Prefix<br>\n"; }
			//if (strlen($flux_provider_cid_number_prefix) == 0) { $msg .= "Please provide: Caller ID Number Prefix<br>\n"; }
			//if (strlen($flux_provider_ringback) == 0) { $msg .= "Please provide: Ringback<br>\n"; }
			//if (strlen($flux_provider_description) == 0) { $msg .= "Please provide: Description<br>\n"; }
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

	

		//add a uuid to flux_provider_uuid if it is empty
			if ($action == 'add') {
				$flux_provider_uuid = uuid();
			}

		//build the array
			$array["flux_providers"][0]["flux_provider_uuid"] = $flux_provider_uuid;
			$array["flux_providers"][0]["domain_uuid"] = $domain_uuid;
			$array['flux_providers'][0]["flux_provider_name"] = $flux_provider_name;
			$array['flux_providers'][0]["flux_provider_api_url"] = $flux_provider_api_url;
			$array['flux_providers'][0]["flux_provider_auth_type"] = $flux_provider_auth_type;
			$array['flux_providers'][0]["flux_provider_auth_method"] = $flux_provider_auth_method;
			$array["flux_providers"][0]["flux_provider_auth_url"] = $flux_provider_auth_url;
			$array["flux_providers"][0]["flux_provider_enabled"] = $flux_provider_enabled;
			$array["flux_providers"][0]["flux_provider_description"] = $flux_provider_description;

			$y = 0;
		//save to the data
			$database = new database;
			$database->app_name = 'flux_providers';
			$database->app_uuid = '04802d00-0920-4a9e-8da1-2edc294e9585';
			$database->save($array);
			$message = $database->message;

		//remove checked destinations
			if ($action == 'update') {
				$obj = new flux_providers;
				$obj->flux_provider_uuid = $flux_provider_uuid;
			}

		//set the message
			if ($action == "add") {
				//save the message to a session variable
					message::add($text['message-add']);
			}
			if ($action == "update") {
				//save the message to a session variable
					message::add($text['message-update']);
			}

		//redirect the browser
			header("Location: flux_provider_edit.php?id=".urlencode($flux_provider_uuid));
			exit;
	}

//pre-populate the form
	if (is_uuid($flux_provider_uuid)) {
		$sql = "select * from v_flux_providers ";
		$sql .= "where flux_provider_uuid = :flux_provider_uuid ";
		$parameters['flux_provider_uuid'] = $flux_provider_uuid;
		$database = new database;
		$row = $database->select($sql, $parameters, 'row');
		if (is_array($row) && @sizeof($row) != 0) {
			$domain_uuid = $row["domain_uuid"];
		  $flux_provider_user_uuid = $row["flux_provider_user_uuid"];
		  $flux_provider_name = $row["flux_provider_name"];	
			$flux_provider_api_url = $row["flux_provider_api_url"];
			$flux_provider_auth_url = $row["flux_provider_auth_url"];
			$flux_provider_auth_type = $row["flux_provider_auth_type"];
			$flux_provider_enabled = $row["flux_provider_enabled"];
			$flux_provider_auth_method = $row["flux_provider_auth_method"];
			$flux_provider_description = $row["flux_provider_description"];
		}
		unset($sql, $parameters, $row);
	}



//get the ring group users
	if (is_uuid($flux_provider_uuid)) {
		$sql = "select u.username, r.user_uuid, r.flux_provider_user_uuid, r.flux_provider_uuid ";
		$sql .= "from v_flux_provider_users as r, v_users as u ";
		$sql .= "where r.user_uuid = u.user_uuid  ";
		$sql .= "and u.user_enabled = 'true' ";
		$sql .= "and r.domain_uuid = :domain_uuid ";
		$sql .= "and r.flux_provider_uuid = :flux_provider_uuid ";
		$sql .= "order by u.username asc ";
		$parameters['domain_uuid'] = $domain_uuid;
		$parameters['flux_provider_uuid'] = $flux_provider_uuid;
		$database = new database;
		$flux_provider_users = $database->select($sql, $parameters, 'all');
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

//set defaults
	if (strlen($flux_provider_enabled) == 0) { $flux_provider_enabled = 'true'; }


//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//show the header
	$document['title'] = $text['title-flux_provider'];
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
		echo "	tb.setAttribute('style', 'width: 380px;');\n";
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
	echo "<form method='post' name='frm' id='frm'>\n";

	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>".$text['title-flux_provider']."</b></div>\n";
	echo "	<div class='actions'>\n";
	echo button::create(['type'=>'button','label'=>$text['button-back'],'icon'=>$_SESSION['theme']['button_icon_back'],'id'=>'btn_back','link'=>'flux_providers.php']);
	if ($action == 'update') {
		$button_margin = 'margin-left: 15px;';
		if (permission_exists('flux_provider_add') && (!is_numeric($_SESSION['limit']['flux_providers']['numeric']) || ($total_ring_groups < $_SESSION['limit']['flux_providers']['numeric']))) {
			echo button::create(['type'=>'button','label'=>$text['button-copy'],'icon'=>$_SESSION['theme']['button_icon_copy'],'name'=>'btn_copy','style'=>$button_margin,'onclick'=>"modal_open('modal-copy','btn_copy');"]);
			unset($button_margin);
		}
		if (permission_exists('flux_provider_delete') || permission_exists('flux_provider_destination_delete')) {
			echo button::create(['type'=>'button','label'=>$text['button-delete'],'icon'=>$_SESSION['theme']['button_icon_delete'],'name'=>'btn_delete','style'=>$button_margin,'onclick'=>"modal_open('modal-delete','btn_delete');"]);
			unset($button_margin);
		}
	}
	echo button::create(['type'=>'submit','label'=>$text['button-save'],'icon'=>$_SESSION['theme']['button_icon_save'],'id'=>'btn_save','style'=>'margin-left: 15px;']);
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	if ($action == "update") {
		if (permission_exists('flux_provider_add') && (!is_numeric($_SESSION['limit']['flux_providers']['numeric']) || ($total_ring_groups < $_SESSION['limit']['flux_providers']['numeric']))) {
			echo modal::create(['id'=>'modal-copy','type'=>'copy','actions'=>button::create(['type'=>'submit','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_copy','style'=>'float: right; margin-left: 15px;','collapse'=>'never','name'=>'action','value'=>'copy','onclick'=>"modal_close();"])]);
		}
		if (permission_exists('flux_provider_delete') || permission_exists('flux_provider_destination_delete')) {
			echo modal::create(['id'=>'modal-delete','type'=>'delete','actions'=>button::create(['type'=>'submit','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_delete','style'=>'float: right; margin-left: 15px;','collapse'=>'never','name'=>'action','value'=>'delete','onclick'=>"modal_close();"])]);
		}
	}

	echo $text['description']."\n";
	echo "<br /><br />\n";

	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";

	echo "<tr>\n";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-provider_name']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='flux_provider_name' maxlength='255' value='".escape($flux_provider_name)."'>\n";
	echo "<br />\n";
	echo $text['description-provider_name']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	
	if (permission_exists('flux_provider_all')) {
		echo "<tr>\n";
		echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
		echo "	".$text['label-domain']."\n";
		echo "</td>\n";
		echo "<td class='vtable' align='left'>\n";
		echo "  <select class='formfld' name='domain_uuid' id='domain_uuid'>\n";
		if (!is_uuid($domain_uuid)) {
			echo "  <option value='' selected='selected'>".$text['select-global']."</option>\n";
		}
		else {
			echo "  <option value=''>".$text['select-global']."</option>\n";
		}
		foreach ($_SESSION['domains'] as $row) {
			if ($row['domain_uuid'] == $domain_uuid) {
				echo "  <option value='".escape($row['domain_uuid'])."' selected='selected'>".escape($row['domain_name'])."</option>\n";
			}
			else {
				echo "  <option value='".escape($row['domain_uuid'])."'>".escape($row['domain_name'])."</option>\n";
			}
		}
		echo "  </select>\n";
		echo "<br />\n";
		echo $text['description-domain_name']."\n";
		echo "</td>\n";
		echo "</tr>\n";
	}
	else {
		echo "	<input type='hidden' name='domain_uuid' id='domain_uuid' value=\"".$_SESSION['domain_uuid']."\"/>\n";
	}


	echo "	<tr>";
	echo "		<td class='vncell' valign='top'>".$text['label-user_list']."</td>";
	echo "		<td class='vtable'>";
	if (is_array($flux_provider_users) && @sizeof($flux_provider_users) != 0) {
		echo "		<table width='300px'>\n";
		foreach ($flux_provider_users as $field) {
		  $list_row_url = "flux_provider_user_edit.php?id=".urlencode($field['flux_provider_user_uuid']);
			echo "			<tr>\n";
      echo "<td class='vtable'><a href='".$list_row_url."'>".escape($field['username'])."</a></td>\n";
//			echo "				<td class='vtable'><a href=".escape($field['username'])."</td>\n";
			echo "				<td>\n";
			echo "					<a href='flux_provider_edit.php?id=".urlencode($flux_provider_uuid)."&user_uuid=".urlencode($field['user_uuid'])."&a=delete' alt='".$text['button-delete']."' onclick=\"return confirm('".$text['confirm-delete']."')\">".$v_link_label_delete."</a>\n";
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
			foreach ($flux_provider_users as $user) {
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
	echo "<td width='30%' class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-provider_api_url']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='flux_provider_api_url' maxlength='255' size='70' value='".escape($flux_provider_api_url)."'>\n";
	echo "<br />\n";
	echo $text['description-provider_api_url']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-provider_auth_url']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='flux_provider_auth_url' maxlength='255' size='70' value='".escape($flux_provider_auth_url)."'>\n";
	echo "<br />\n";
	echo $text['description-provider_auth_url']."\n";
	echo "</td>\n";
	echo "</tr>\n";



	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-provider_auth_type']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<select class='formfld' name='flux_provider_auth_type'>\n";
	if ($flux_provider_auth_type == "username") {
		echo "		<option value='username' selected='selected'>".$text['label-username']."</option>\n";
	}
	else {
		echo "		<option value='username'>".$text['label-username']."</option>\n";
	}
	if ($flux_provider_auth_type == "token") {
		echo "		<option value='token' selected='selected'>".$text['label-provider_token']."</option>\n";
	}
	else {
		echo "		<option value='token'>".$text['label-provider_token']."</option>\n";
	}
	if ($flux_provider_auth_type == "both") {
		echo "		<option value='both' selected='selected'>".$text['label-provider_both']."</option>\n";
	}
	else {
		echo "		<option value='both'>".$text['label-provider_both']."</option>\n";
	}
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-provider_auth_type']."\n";
	echo "</td>\n";
	echo "</tr>\n";


	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-provider_auth_method']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<select class='formfld' name='flux_provider_auth_method'>\n";
	echo "		<option value='GET' selected='selected'>GET</option>\n";
	echo "		<option value='POST' selected='selected'>POST</option>\n";
	echo "		<option value='PUT' selected='selected'>PUT</option>\n";
	echo "		<option value='DELETE' selected='selected'>DELETE</option>\n";
	echo "		<option value='".escape($flux_provider_auth_method)."' selected='selected'>".escape($flux_provider_auth_method)."</option>\n";
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-provider_method']."\n";
	echo "</td>\n";
	echo "</tr>\n";


	//enabled
	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-enabled']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	if (substr($_SESSION['theme']['input_toggle_style']['text'], 0, 6) == 'switch') {
		echo "	<label class='switch'>\n";
		echo "		<input type='checkbox' id='flux_provider_enabled' name='flux_provider_enabled' value='true' ".($flux_provider_enabled == 'true' ? "checked='checked'" : null).">\n";
		echo "		<span class='slider'></span>\n";
		echo "	</label>\n";
	}
	else {
	echo "	<select class='formfld' name='flux_provider_enabled'>\n";
	switch ($flux_provider_enabled) {
		case "true" :	$selected[1] = "selected='selected'";	break;
		case "false" :	$selected[2] = "selected='selected'";	break;
	}
	echo "	<option value='true' ".$selected[1].">".$text['label-true']."</option>\n";
	echo "	<option value='false' ".$selected[2].">".$text['label-false']."</option>\n";
	unset($selected);
	echo "	</select>\n";
	}
	echo "<br />\n";
	echo $text['description-enabled']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "    ".$text['label-description']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <input type='text' class='formfld' name='flux_provider_description' value=\"".$flux_provider_description."\">\n";
	echo "<br />\n";
	echo $text['description-description']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "</table>";
	echo "<br><br>";


	if (is_uuid($flux_provider_uuid)) {
		echo "<input type='hidden' name='flux_provider_uuid' value='".escape($flux_provider_uuid)."'>\n";
	}
	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";

	echo "</form>";

//include the footer
	require_once "resources/footer.php";

?>

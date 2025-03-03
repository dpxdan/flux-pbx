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
	/*include "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";*/

	require_once dirname(__DIR__, 2) . "/resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('flux_text_edit')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//set the action with add or update
	if (is_uuid($_REQUEST["id"])) {
		$action = "update";
		$flux_text_uuid = $_REQUEST["id"];
	}
	else {
		$action = "add";
	}


//get the http post variables and set them to php variables
	if (count($_POST)>0) {
		$flux_text_name = $_POST["flux_text_name"];
		$flux_text_description = $_POST["flux_text_description"];
		$flux_text_txt = $_POST["flux_text_txt"];
		$flux_text_recording_path = '/var/www/html/fluxpbx/app/flux_text_speech/recordings/'.$flux_text_name.'/audio/';
		$flux_text_recording_name = ''. $flux_text_name.'.wav';

		if (if_group("superadmin")) {
			$flux_text_accountcode = $_POST["flux_text_accountcode"];
		}
		else{
			$flux_text_accountcode = $_POST["flux_text_accountcode"];
		}
	}

if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {

		if (permission_exists('flux_text_delete')) {
			if ($_POST['action'] == 'delete' && is_uuid($flux_text_uuid)) {
				//prepare
					$flux_text[0]['checked'] = 'true';
					$flux_text[0]['uuid'] = $flux_text_uuid;
				//delete
					$obj = new flux_text;
					$obj->delete($flux_text);
				//redirect
					header('Location: flux_text_speech.php');
					exit;
			}
		}

	$msg = '';
	if ($action == "update") {
		$flux_text_uuid = $_POST["flux_text_uuid"];
	}

	//validate the token
		$token = new token;
		if (!$token->validate($_SERVER['PHP_SELF'])) {
			message::add($text['message-invalid_token'],'negative');
			header('Location: flux_text_speech.php');
			exit;
		}

	//check for all required data
		if (strlen($flux_text_name) == 0) { $msg .= "".$text['confirm-name']."<br>\n"; }
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

	//add or update the database
	if ($_POST["persistformvar"] != "true") {

		//prep insert
			if ($action == "add" && permission_exists('flux_text_add')) {
				//begin insert array
					$flux_text_uuid = uuid();
					$array['flux_text'][0]['flux_text_uuid'] = $flux_text_uuid;

				//set message
					message::add($text['confirm-add']);

				//set return url on error
					$error_return_url = "flux_text_edit.php";
			}

		//prep update
			if ($action == "update" && permission_exists('flux_text_edit')) {
				//begin update array
					$array['flux_text'][0]['flux_text_uuid'] = $flux_text_uuid;

				//set message
					message::add($text['confirm-update']);

				//set return url on error
					$error_return_url = "flux_text_edit.php?id=".urlencode($_GET['id']);
			}

		//execute
			if (is_array($array) && @sizeof($array) != 0) {

				//common array items
					$array['flux_text'][0]['domain_uuid'] = $domain_uuid;
					$array['flux_text'][0]['flux_text_name'] = $flux_text_name;
					$array['flux_text'][0]['flux_text_accountcode'] = $flux_text_accountcode;
					$array['flux_text'][0]['flux_text_description'] = $flux_text_description;
					$array['flux_text'][0]['flux_text_txt'] = $flux_text_txt;
					$array['flux_text'][0]['flux_text_recording_path'] = $flux_text_recording_path;
					$array['flux_text'][0]['flux_text_recording_name'] = $flux_text_recording_name;

				//execute
					$database = new database;
					$database->app_name = 'flux_text';
					$database->app_uuid = 'efc21f6b-ed73-9955-4d4d-3a1bed75a056';
					$database->save($array);
					unset($array);
				//redirect
				shell_exec('nohup /var/www/html/fluxpbx/app/flux_text_speech/resources/scripts/tts-convert "'.$flux_text_name.'" "'.$flux_text_uuid.'" "'.$domain_uuid.'" 2>&1 &');
					header("Location: flux_text_speech.php");
					exit;

			}

	}
}

//pre-populate the form
	if (count($_GET) > 0 && $_POST["persistformvar"] != "true") {
		$flux_text_uuid = $_GET["id"];
		$sql = "select * from v_flux_text ";
		$sql .= "where domain_uuid = :domain_uuid ";
		$sql .= "and flux_text_uuid = :flux_text_uuid ";
		$parameters['domain_uuid'] = $domain_uuid;
		$parameters['flux_text_uuid'] = $flux_text_uuid;
		$database = new database;
		$row = $database->select($sql, $parameters, 'row');
		if (is_array($row) && @sizeof($row) != 0) {
			$flux_text_name = $row["flux_text_name"];
			$flux_text_accountcode = $row["flux_text_accountcode"];
			$flux_text_description = $row["flux_text_description"];
			$flux_text_txt = $row["flux_text_txt"];
			$flux_text_recording_path = $row["flux_text_recording_path"];
		    $flux_text_recording_name =$row["flux_text_recording_name"];
			
		}
		unset($sql, $parameters, $row);
	}

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//begin header
	$document['title'] = $text['title-flux_text'];
	require_once "resources/header.php";

//begin content
	echo "<form name='frm' id='frm' method='post' enctype='multipart/form-data'>\n";

	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>".$text['title-flux_text']."</b></div>\n";
	echo "	<div class='actions'>\n";
	echo button::create(['type'=>'button','label'=>$text['button-back'],'icon'=>$_SESSION['theme']['button_icon_back'],'id'=>'btn_back','link'=>'flux_text_speech.php']);
	if ($action == "update") {
		if (permission_exists('flux_text_delete')) {
			echo button::create(['type'=>'button','label'=>$text['button-delete'],'icon'=>$_SESSION['theme']['button_icon_delete'],'name'=>'btn_delete','style'=>'margin-left: 15px;','onclick'=>"modal_open('modal-delete','btn_delete');"]);
		}
	}
	echo button::create(['type'=>'submit','label'=>$text['button-save'],'icon'=>$_SESSION['theme']['button_icon_save'],'id'=>'btn_save','style'=>'margin-left: 15px;']);
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	if ($action == 'update' && permission_exists('flux_text_delete')) {
		echo modal::create(['id'=>'modal-delete','type'=>'delete','actions'=>button::create(['type'=>'submit','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_delete','style'=>'float: right; margin-left: 15px;','collapse'=>'never','name'=>'action','value'=>'delete','onclick'=>"modal_close();"])]);
	}

	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";

	echo "<tr>\n";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "	".$text['label-name']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='flux_text_name' maxlength='255' value=\"".escape($flux_text_name)."\" required='required'>\n";
	echo "<br />\n";
	echo "".$text['description-name']."\n";
	echo "</td>\n";
	echo "</tr>\n";

		echo "<tr>\n";
		echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
		echo "    ".$text['label-accountcode']."\n";
		echo "</td>\n";
		echo "<td class='vtable' align='left'>\n";
		echo "    <input class='formfld' type='text' name='flux_text_accountcode' maxlength='255' value=\"".escape($flux_text_accountcode)."\">\n";
		echo "<br />\n";
		echo $text['description-accountcode']."\n";
		echo "</td>\n";
		echo "</tr>\n";

	
	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "	".$text['label-text']."\n";
	echo "</td>\n";

	echo "<td class='vtable' align='left'>\n";
	echo "	<textarea class='formfld' style='width: 300px; height: 200px;' type='text' name='flux_text_txt'\">".escape($flux_text_txt)."</textarea>";
	echo "<br />";
	echo "".$text['label-text-field']." <br /><br />\n";
    echo "<audio preload='none' controls><source src='recordings/".escape($flux_text_name)."/audio/".escape($flux_text_name).".wav' type='audio/wav'></audio>";
	echo "</td>\n";
	echo "</tr>\n";	
	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "	".$text['label-description']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='flux_text_description' maxlength='255' value=\"".escape($flux_text_description)."\">\n";
	echo "<br />\n";
	echo "".$text['description-info']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "</table>";
	echo "<br><br>";

	if ($action == "update") {
		echo "<input type='hidden' name='flux_text_uuid' value='".escape($flux_text_uuid)."'>\n";
	}
	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";

	echo "</form>";


//include the footer
	require_once "resources/footer.php";

?>

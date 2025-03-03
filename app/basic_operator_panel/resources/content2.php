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
	Portions created by the Initial Developer are Copyright (C) 2008-2022
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Daniel Paixao <daniel@flux.net.br>
*/

//includes
//set the include path
$conf = glob("{/usr/local/etc,/etc}/fluxpbx/config.conf", GLOB_BRACE);
set_include_path(parse_ini_file($conf[0])['document.root']);

//includes files
require_once "resources/require.php";
require_once "resources/check_auth.php";

//check permissions
if (permission_exists('operator_panel_view')) {
	//access granted
}
else {
	echo "access denied";
	exit;
}

//add multi-lingual support
$language = new text;
$text = $language->get(null,'app/basic_operator_panel');
$textcalls = $language->get($_SESSION['domain']['language']['code'], 'core/user_settings');


//create assigned extensions array
	if (is_array($_SESSION['user']['extension'])) {
		foreach ($_SESSION['user']['extension'] as $assigned_extension) {
			$assigned_extensions[$assigned_extension['extension_uuid']] = $assigned_extension['user'];
		}
	}

//if also viewing system status, show more recent calls (more room avaialble)
	$recent_limit = (is_array($selected_blocks) && in_array('counts', $selected_blocks)) ? 10 : 5;

//get the recent calls from call detail records
	$sql = "
		select
			direction,
			start_stamp,
			start_epoch,
			caller_id_name,
			caller_id_number,
			destination_number,
			answer_stamp,
			bridge_uuid,
			sip_hangup_disposition
		from
			v_xml_cdr
		where
			domain_uuid = :domain_uuid ";
			if (is_array($assigned_extensions) && sizeof($assigned_extensions) != 0) {
				$x = 0;
				foreach ($assigned_extensions as $assigned_extension_uuid => $assigned_extension) {
					$sql_where_array[] = "extension_uuid = :extension_uuid_".$x;
					$sql_where_array[] = "caller_id_number = :caller_id_number_".$x;
					$sql_where_array[] = "destination_number = :destination_number_1_".$x;
					$sql_where_array[] = "destination_number = :destination_number_2_".$x;
					$parameters['extension_uuid_'.$x] = $assigned_extension_uuid;
					$parameters['caller_id_number_'.$x] = $assigned_extension;
					$parameters['destination_number_1_'.$x] = $assigned_extension;
					$parameters['destination_number_2_'.$x] = '*99'.$assigned_extension;
					$x++;
				}
				if (is_array($sql_where_array) && sizeof($sql_where_array) != 0) {
					$sql .= "and (".implode(' or ', $sql_where_array).") ";
				}
				unset($sql_where_array);
			}
			$sql .= "
			and start_epoch > ".(time() - 86400)."
		order by
			start_epoch desc";
	$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
	$database = new database;
	$result = $database->select($sql, $parameters, 'all');
	//view_array($result, true);
	$num_rows = is_array($result) ? sizeof($result) : 0;
	
	
	$sql_contacts = "
		select
			user_uuid,
			domain_name,
			username,
			contact_name,
			contact_uuid,
			extension,
			extension_uuid,
			email_address,
			outbound_caller_id_number
		from
			view_api_contacts
		where
			domain_uuid = :domain_uuid ";
			
			$sql_contacts .= "order by username desc";
	$parameters_contacts['domain_uuid'] = $_SESSION['domain_uuid'];
	$database = new database;
	$result_contacts = $database->select($sql_contacts, $parameters_contacts, 'all');
	//view_array($result, true);
	$num_contact_rows = is_array($result_contacts) ? sizeof($result_contacts) : 0;
	

//get the call activity
$operator_panel = new basic_operator_panel;
$activity = $operator_panel->call_activity();
if (is_array($activity)) {
	foreach ($activity as $extension => $fields) {
		if (substr_count($fields['call_group'], ',')) {
			$tmp = explode(',', $fields['call_group']);
			if (is_array($tmp)) foreach ($tmp as $tmp_index => $tmp_value) {
				if (trim($tmp_value) == '') { unset($tmp[$tmp_index]); }
				else { $groups[] = $tmp_value; }
			}
		}
		else if ($fields['call_group'] != '') {
			$groups[] = $fields['call_group'];
		}
	}
}
if (is_array($groups)) {
	$groups = array_unique($groups);
	sort($groups); 
}

//get the valet info
$fp = event_socket_create($_SESSION['event_socket_ip_address'], $_SESSION['event_socket_port'], $_SESSION['event_socket_password']);
if ($fp) {
	$valet_info = event_socket_request($fp, 'api valet_info park@'.$_SESSION['domain_name']);

	//get an array of the valet call uuid and park numbers
	if (isset($valet_info)) {
		preg_match_all('/<extension uuid=\"(.*?)\">(.*?)<\/extension>/s', $valet_info, $valet_matches, PREG_SET_ORDER);
		//view_array($valet_matches, false);
	}
	//view_array($valet_matches, false);

	//unset($_SESSION['valet']);
	foreach($valet_matches as $row) {
		if (!isset($_SESSION['valet']['uuid']['caller_id_name'])) {
			$_SESSION['valet'][$row[1]]['caller_id_name'] = event_socket_request($fp, 'api uuid_getvar '.$row[1].' caller_id_name');
		}
		if (!isset($_SESSION['valet']['uuid']['caller_id_number'])) {
			$_SESSION['valet'][$row[1]]['caller_id_number'] = event_socket_request($fp, 'api uuid_getvar '.$row[1].' caller_id_number');
		}
		if (!isset($_SESSION['valet']['uuid']['direction'])) {
			$_SESSION['valet'][$row[1]]['direction'] = event_socket_request($fp, 'api uuid_getvar ' . $row[1] . ' direction');
		}
	}

	//unset the array
	//view_array($_SESSION['valet']);

	//reformat the array and add the caller ID name and numbers
	$x = 0;
	foreach($valet_matches as $row) {
		$valet_array[$x]['uuid'] = $row[1];
		$valet_array[$x]['extension'] = $row[2];
		$valet_array[$x]['direction'] = $row[3];
		if (isset($_SESSION['valet'][$row[1]]['caller_id_name'])) {
			$valet_array[$x]['caller_id_name'] = $_SESSION['valet'][$row[1]]['caller_id_name'];
		}
		if (isset($_SESSION['valet'][$row[1]]['caller_id_number'])) {
			$valet_array[$x]['caller_id_number'] = $_SESSION['valet'][$row[1]]['caller_id_number'];
		}
		if (isset($_SESSION['valet'][$row[1]]['direction'])) {
			$valet_array[$x]['direction'] = $_SESSION['valet'][$row[1]]['direction'];
		}
		$x++;
	}
	//view_array($valet, false);

}

		
//prevent warnings
if (!is_array($_SESSION['user']['extensions'])) {
	$_SESSION['user']['extensions'] = array();
}

//get registrations -- All SIP profiles
$obj = new registrations;
$registrations = $obj->get("all");
$user_group_level = user_group_level();
$user_uuid = $_SESSION['user_uuid'];
$user_extension_uuid = user_extension_uuid();
$user_extension_group = user_extension_group();
$user_extension_status = user_extension_status($user_extension_uuid);
$user_extension_profile = user_extension_profile($user_extension_uuid);
$user_extension_number = user_extension_exists();


//set the onhover paush refresh
$onhover_pause_refresh = " onmouseover='refresh_stop();' onmouseout='refresh_start();'";

echo "<table width='100%'>\n";
echo "	<tr>\n";
echo "		<td valign='top' align='left' width='50%' nowrap>\n";
echo "			<b>" . $text['title-operator_panel'] . "</b>\n";
echo "		</td>\n";
echo "				</tr>\n";
echo "</table>\n";
echo "<br>\n";
echo "<table width='100%'>\n";
echo "			<tr>\n";

if (permission_exists('operator_panel_groups')) {
	if (is_array($groups) && @sizeof($groups) > 0) {
		echo "				<td valign='top' nowrap='nowrap'>\n";
		echo "					<input type='hidden' id='group' value=\"" . escape($_REQUEST['group']) . "\">\n";
		if (sizeof($groups) > 0) {

			//show select box
			echo "				<select class='formfld' onchange=\"document.getElementById('group').value = this.options[this.selectedIndex].value; refresh_start();\" onfocus='refresh_stop();'>\n";
			echo "					<option value='' " . (($_REQUEST['group'] == '') ? "selected" : null) . ">" . $text['label-call_group'] . "</option>\n";
			echo "					<option value=''>" . $text['button-all'] . "</option>\n";
			if (is_array($groups)) {
				foreach ($groups as $group) {
					echo "				<option value='" . escape($group) . "' " . (($_REQUEST['group'] == $group) ? "selected" : null) . ">" . escape($group) . "</option>\n";
				}
			}

			echo "				</select>\n";
		} else {

			//show buttons
			echo "				<input type='button' class='btn' title=\"" . $text['label-call_group'] . "\" value=\"" . $text['button-all'] . "\" onclick=\"document.getElementById('group').value = '';\" " . $onhover_pause_refresh . ">\n";
			if (is_array($groups)) {
				foreach ($groups as $group) {
					echo "			<input type='button' class='btn' title=\"" . $text['label-call_group'] . "\" value=\"" . escape($group) . "\" " . (($_REQUEST['group'] == $group) ? "disabled='disabled'" : null) . " onclick=\"document.getElementById('group').value = this.value;\" " . $onhover_pause_refresh . ">\n";
				}
			}

		}
		echo "				</td>\n";
	}
}

if (permission_exists("operator_panel_manage") && sizeof($_SESSION['user']['extensions']) > 0) {
	$status_options[1]['status'] = "Available";
	$status_options[1]['label'] = $text['label-status_available'];
	$status_options[1]['style'] = "op_btn_status_available";

	if (permission_exists('operator_panel_on_demand')) {
		$status_options[2]['status'] = "Available (On Demand)";
		$status_options[2]['label'] = $text['label-status_on_demand'];
		$status_options[2]['style'] = "op_btn_status_available_on_demand";
	}
	$status_options[3]['status'] = "On Break";
	$status_options[3]['label'] = $text['label-status_on_break'];
	$status_options[3]['style'] = "op_btn_status_on_break";

	$status_options[4]['status'] = "Do Not Disturb";
	$status_options[4]['label'] = $text['label-status_do_not_disturb'];
	$status_options[4]['style'] = "op_btn_status_do_not_disturb";

	$status_options[5]['status'] = "Logged Out";
	$status_options[5]['label'] = $text['label-status_logged_out'];
	$status_options[5]['style'] = "op_btn_status_logged_out";

	$status_options[6]['status'] = "On Meeting";
	$status_options[6]['label'] = $text['label-meeting'];
	$status_options[6]['style'] = "op_btn_status_meeting";

	$status_options[7]['status'] = "Lunch";
	$status_options[7]['label'] = $text['label-lunch'];
	$status_options[7]['style'] = "op_btn_status_lunch";

	$status_options[8]['status'] = "Snack";
	$status_options[8]['label'] = $text['label-snack'];
	//$status_options[8]['style'] = "op_btn_status_snack";

	$status_options[9]['status'] = "Bathroom";
	$status_options[9]['label'] = $text['label-bathroom'];
	$status_options[9]['style'] = "op_btn_status_bathroom";

	$status_options[10]['status'] = "Feedback";
	$status_options[10]['label'] = $text['label-feedback'];
	$status_options[10]['style'] = "op_btn_status_feedback";

	$status_options[11]['status'] = "Training";
	$status_options[11]['label'] = $text['label-training'];
	$status_options[11]['style'] = "op_btn_status_training";

	echo "		<td valign='top' nowrap>\n";

	if (is_array($status_options)) {
		foreach ($status_options as $status_option) {
			echo "	<input type='button' id='" . $status_option['style'] . "' class='btn' value=\"" . $status_option['label'] . "\" onclick=\"send_cmd('index.php?status='+escape('" . $status_option['status'] . "')); this.disabled='disabled'; refresh_start();\" " . $onhover_pause_refresh . ">\n";
		}
	}

}

echo "		</td>\n";

echo "		<td valign='top' align='right' width='50%' nowrap>\n";
echo "			<table cellpadding='0' cellspacing='0' border='0'>\n";
echo "				<tr>\n";
if (permission_exists('operator_panel_eavesdrop')) {
	echo "				<td valign='top' nowrap='nowrap'>\n";
	if (sizeof($_SESSION['user']['extension']) > 1) {
		echo "				<input type='hidden' id='eavesdrop_dest' value=\"" . (($_REQUEST['eavesdrop_dest'] == '') ? $_SESSION['user']['extensions'][0]['destination'] : escape($_REQUEST['eavesdrop_dest'])) . "\">\n";
		echo "				<img src='resources/images/eavesdrop.png' style='width: 12px; height: 12px; border: none; margin: 0px 5px; cursor: help;' title='" . $text['description-eavesdrop_destination'] . "' align='absmiddle'>\n";
		echo "				<select class='formfld' style='margin-right: 5px;' align='absmiddle' onchange=\"document.getElementById('eavesdrop_dest').value = this.options[this.selectedIndex].value; refresh_start();\" onfocus='refresh_stop();'>\n";
		if (is_array($_SESSION['user']['extensions'])) {
			foreach ($_SESSION['user']['extensions'] as $user_extension) {
				echo "				<option value='" . escape($user_extension) . "' " . (($_REQUEST['eavesdrop_dest'] == $user_extension) ? "selected" : null) . ">" . escape($user_extension) . "</option>\n";
			}
		}

		echo "				</select>\n";
	} else if (sizeof($_SESSION['user']['extensions']) == 1) {
		echo "				<input type='hidden' id='eavesdrop_dest' value=\"" . escape($_SESSION['user']['extension'][0]['destination']) . "\">\n";
	}
	echo "				</td>\n";
}
echo "				</tr>\n";
echo "			</table>\n";

echo "		</td>\n";
echo "	</tr>\n";
echo "</table>\n";
echo "<br>\n";	
if (permission_exists('operator_panel_groups')) {
echo "			<table cellpadding='0' cellspacing='0' border='0'>\n";
echo "				<tr>\n";
	echo "	<tr>\n";
	echo "				<td valign='top' nowrap='nowrap'>\n";
	echo "					<input type='hidden' id='extension_filter' value=\"" . escape($_REQUEST['extension_filter']) . "\">\n";
	echo "					<input type='hidden' id='name_filter' value=\"" . strtolower(escape($_REQUEST['name_filter'])) . "\">\n";
	echo "					<input type='text' class='formfld' placeholder='Filter Extension' value=\"" . escape($_REQUEST['extension_filter']) . "\" onkeyup=\"document.getElementById('extension_filter').value = this.value; refresh_start();\" onfocus='refresh_stop();'>\n";
	echo "					<input type='text' class='formfld' placeholder='Filter Name' value=\"" . strtolower(escape($_REQUEST['name_filter'])) . "\" onkeyup=\"document.getElementById('name_filter').value = this.value; refresh_start();\" onfocus='refresh_stop();'>\n";
	echo "					<input type='button' class='btn' title=\"Clear\" value=\"Clear\" onclick=\"document.getElementById('extension_filter').value = ''; document.getElementById('name_filter').value = '';\" " . $onhover_pause_refresh . ">\n";
	echo "		<span id='refresh_state'>" . button::create(['type' => 'button', 'title' => $text['label-refresh_pause'], 'icon' => 'sync-alt fa-spin', 'onclick' => 'refresh_stop()']) . "</span>";
	echo "				</td>\n";
	echo "	</tr>\n";
	echo "</table>\n";
} else {

echo "			<table cellpadding='0' cellspacing='0' border='0'>\n";
echo "				<tr>\n";
	echo "	<tr>\n";
	echo "				<td valign='top' nowrap='nowrap'>\n";
	echo "		<span id='refresh_state'>" . button::create(['type' => 'button', 'title' => $text['label-refresh_pause'], 'icon' => 'sync-alt fa-spin', 'onclick' => 'refresh_stop()']) . "</span>";
	echo "				</td>\n";
	echo "	</tr>\n";
	echo "</table>\n";
}
echo "</table>\n";
echo "<br>\n";

//define the arrays to ensure no errors are omitted below with the sizeof operators
$user_extensions = array();
$grouped_extensions = array();
$other_extensions = array();

//loop through the array
if (is_array($activity)) {
	foreach ($activity as $extension => $ext) {
		unset($block);

		//filter by group, if defined
		if ($_REQUEST['group'] != '' && substr_count($ext['call_group'], $_REQUEST['group']) == 0 && !in_array($extension, $_SESSION['user']['extensions'])) { continue; }

		//filter by extension or name, if defined
		if ($_REQUEST['extension_filter'] != '' && substr_count($ext['extension'], $_REQUEST['extension_filter']) == 0 && !in_array($extension, $_SESSION['user']['extensions'])) { continue; }
		if ($_REQUEST['name_filter'] != '' && substr_count($ext['filter_name'], $_REQUEST['name_filter']) == 0 && !in_array($extension, $_SESSION['user']['extensions'])) { continue; }

		//check if feature code being called
		$format_number = (substr($ext['dest'], 0, 1) == '*') ? false : true;

		//determine extension state, direction icon, and displayed name/number for caller/callee
		if ($ext['state'] == 'CS_EXECUTE') {
			if (($ext['callstate'] == 'RINGING' || $ext['callstate'] == 'EARLY' || $ext['callstate'] == 'RING_WAIT') && $ext['direction'] == 'inbound') {
				$ext_state = 'ringing';
			}
			else if ($ext['callstate'] == 'ACTIVE' && $ext['direction'] == 'outbound') {
				$ext_state = 'active';
			}
			else if ($ext['callstate'] == 'HELD' && $ext['direction'] == 'outbound') {
				$ext_state = 'held';
			}
			else if ($ext['callstate'] == 'RING_WAIT' && $ext['direction'] == 'outbound') {
				$ext_state = 'ringing';
			}
			else if ($ext['callstate'] == 'ACTIVE' && $ext['direction'] == 'inbound') {
				$ext_state = 'active';
			}
			else if ($ext['callstate'] == 'HELD' && $ext['direction'] == 'inbound') {
				$ext_state = 'held';
			}
			if (!$format_number) {
				$call_name = 'System';
				$call_number = $ext['dest'];
			}
			else {
				$call_name = $activity[$ext['dest']]['effective_caller_id_name'];
				$call_number = format_phone($ext['dest']);
			}
			$dir_icon = 'outbound';
		}
		else if ($ext['state'] == 'CS_HIBERNATE') {
			if ($ext['callstate'] == 'ACTIVE') {
				$ext_state = 'active';
				if ($ext['direction'] == 'inbound') {
					$call_name = $activity[$ext['dest']]['effective_caller_id_name'];
					$call_number = format_phone($ext['dest']);
					$dir_icon = 'outbound';
				}
				else if ($ext['direction'] == 'outbound') {
					$call_name = $activity[$ext['cid_num']]['effective_caller_id_name'];
					$call_number = format_phone($ext['cid_num']);
					$dir_icon = 'inbound';
				}
			}
		}
		else if ($ext['state'] == 'CS_EXCHANGE_MEDIA' && $ext['callstate'] == 'ACTIVE' && $ext['direction'] == 'inbound') {
			//valet park
			$ext_state = 'active';
					$call_name = $activity[$ext['dest']]['effective_caller_id_name'];
					$call_number = format_phone($ext['dest']);
		}
		else if ($ext['state'] == 'CS_SOFT_EXECUTE' && $ext['callstate'] == 'ACTIVE' && $ext['direction'] == 'outbound') {
			//valet park
			$ext_state = 'active';
					$call_name = $activity[$ext['dest']]['effective_caller_id_name'];
					$call_number = format_phone($ext['dest']);
		}
		else if ($ext['state'] == 'CS_CONSUME_MEDIA' || $ext['state'] == 'CS_EXCHANGE_MEDIA') {
			if ($ext['state'] == 'CS_CONSUME_MEDIA' && $ext['callstate'] == 'RINGING' && $ext['direction'] == 'outbound') {
				$ext_state = 'ringing';
			}
			else if ($ext['state'] == 'CS_EXCHANGE_MEDIA' && $ext['callstate'] == 'ACTIVE' && $ext['direction'] == 'outbound') {
				$ext_state = 'active';
			}
			else if ($ext['state'] == 'CS_EXCHANGE_MEDIA' && $ext['callstate'] == 'ACTIVE' && $ext['direction'] == 'outbound') {
				$ext_state = 'active';
			}
			else if ($ext['state'] == 'CS_CONSUME_MEDIA' && $ext['callstate'] == 'HELD' && $ext['direction'] == 'outbound') {
				$ext_state = 'held';
			}
			else if ($ext['state'] == 'CS_EXCHANGE_MEDIA' && $ext['callstate'] == 'HELD' && $ext['direction'] == 'outbound') {
				$ext_state = 'held';
			}
			$dir_icon = 'inbound';
			$call_name = $activity[$ext['cid_num']]['effective_caller_id_name'];
			$call_number = format_phone($ext['cid_num']);
		}
		else {
			unset($ext_state, $dir_icon, $call_name, $call_number);
		}

		

		//determin extension register status
		$extension_number = $extension.'@'.$_SESSION['domain_name'];
		$found_count = 0;
		if (is_array($registrations)) {
			foreach ($registrations as $array) {
				if ($extension_number == $array['user']) {
					$found_count++;
					$ext_info = 'Ramal Online';
					$status_color_ext = 'green';
				} else {
				$ext_info = 'Ramal Offline';
				$status_color_ext = 'red';
				
				}	
				//$extension_status = $array['status'];
			}
		}
		if ($found_count > 0) {	
		if ($fp) {
							$extension_info = event_socket_request($fp, 'api sofia_username_of '.$user_extension_profile.'/' . $extension . '@' . $_SESSION['domain_name']);
							if (isset($extension_info)) {
								if ($extension_info != $ext['extension']) {
									$ext_info = 'Ramal Offline';
									$status_color_ext = 'red';
								} else {
									$ext_info = 'Ramal Online';
									$status_color_ext = 'green';
								}
							}
				
						}
			//determine block style by state (if any) and register status
			$style = ($ext_state != '') ? "op_ext op_state_".$ext_state : "op_ext";
		} 
		else {
			$style = "op_ext";	
				$ext_info = 'Ramal Offline';
			    $status_color_ext = 'red';
		}
		unset($extension_number, $found_count, $array);

		//determine the call identifier passed on drop
		if ($ext['uuid'] == $ext['call_uuid'] && $ext['variable_bridge_uuid'] == '') { // transfer an outbound internal call
			$call_identifier = $activity[$call_number]['uuid'];
		}
		else if (($ext['variable_call_direction'] == 'outbound' || $ext['variable_call_direction'] == 'local') && $ext['variable_bridge_uuid'] != '') { // transfer an outbound external call
			$call_identifier = $ext['variable_bridge_uuid'];
		}
		else {
			if( $ext['call_uuid'] ) {
				$call_identifier = $ext['call_uuid']; // transfer all other call types
			}
			else {
				$call_identifier = $ext['uuid']; // e.g. voice menus
			}
		}

		//determine extension draggable state
		if (permission_exists('operator_panel_manage')) {
			if (!in_array($extension, $_SESSION['user']['extensions'])) {
				//other extension
				if ($ext_state == "ringing") {
					if ($_GET['vd_ext_from'] == '' && $dir_icon == 'inbound') {
						$draggable = true; // selectable - is ringing and not outbound so can transfer away the call (can set as vd_ext_from)
					}
					else {
						$draggable = true; // unselectable - is ringing so can't send a call to the ext (can't set as vd_ext_to)
					}
				}
				else if ($ext_state == 'active') {
					$draggable = true; // unselectable - on a call already so can't transfer or send a call to the ext (can't set as vd_ext_from or vd_ext_to)
				}
				else { // idle
					if ($_GET['vd_ext_from'] == '') {
						$draggable = true; // unselectable - is idle, but can't initiate a call from the ext as is not assigned to user (can't set as vd_ext_from)
					}
					else {
						$draggable = true; // selectable - is idle, so can transfer a call in to ext (can set as vd_ext_to).
					}
				}
			}
			else {
				//user extension
				if ($ext['uuid'] != '' && $ext['uuid'] == $ext['call_uuid'] && $ext['variable_bridge_uuid'] == '') {
					$draggable = true;
				}
				else if ($ext_state == 'ringing' && $ext['variable_call_direction'] == 'local') {
					$draggable = true;
				}
				else if ($ext_state != '' && !$format_number) {
					$draggable = true;
				}
				else {
					$draggable = true;
				}
			}
		}
		else {
			$draggable = true;
		}

		//determine extension (user) status
		$ext_status = (in_array($extension, $_SESSION['user']['extensions'])) ? $ext_user_status[$_SESSION['user_uuid']] : $ext_user_status[$ext['user_uuid']];
		switch ($ext_status) {
			case "Available" :
				$status_icon = "available";
				$status_hover = $text['label-status_available'];
			$status_color = 'green';
				break;
			case "Available (On Demand)" :
				$status_icon = "available_on_demand";
				$status_hover = $text['label-status_available_on_demand'];
			$status_color = 'green';
				break;
			case "On Break" :
				$status_icon = "on_break";
				$status_hover = $text['label-status_on_break'];
			$status_color = 'indigo';
				break;
			case "Do Not Disturb" :
				$status_icon = "do_not_disturb";
				$status_hover = $text['label-status_do_not_disturb'];
			$status_color = 'red';
			break;
		case "On Meeting":
			$status_icon = "meeting";
			$status_hover = $text['label-meeting'];
			$status_color = 'blue';
			break;
		case "Lunch":
			$status_icon = "lunch";
			$status_hover = $text['label-lunch'];
			$status_color = 'indigo';
			break;
		case "Bathroom":
			$status_icon = "bathroom";
			$status_hover = $text['label-bathroom'];
			$status_color = 'red';
			break;
		case "Snack":
			$status_icon = "snack";
			$status_hover = $text['label-snack'];
			$status_color = 'blue';
			break;
		case "Feedback":
			$status_icon = "feedback";
			$status_hover = $text['label-feedback'];
			$status_color = 'orange';
			break;
		case "Training":
			$status_icon = "training";
			$status_hover = $text['label-training'];
			$status_color = 'green';
			break;
		default:
			$status_icon = "logged_out";
			$status_hover = $text['label-status_logged_out_or_unknown'];
			$status_color = 'gray';
		}

		//build the list of extensions
		$block .= "<div id='".escape($extension)."' class='".$style."' ".(($_GET['vd_ext_from'] == $extension || $_GET['vd_ext_to'] == $extension) ? "style='border-style: dotted;'" : null)." ".(($ext_state != 'active' && $ext_state != 'ringing') ? "ondrop='drop(event, this.id);' ondragover='allowDrop(event, this.id);' ondragleave='discardDrop(event, this.id);'" : null).">"; // DRAG TO
		$block .= "<table class='".$style."'>\n";
		$block .= "	<tr>\n";
		$block .= "		<td class='op_ext_icon'>\n";
		$block .= "			<span name='".escape($extension)."'>\n"; // DRAG FROM
		$block .= 				"<img id='".escape($call_identifier)."' class='op_ext_icon' src='resources/images/status_".$status_icon.".png' title='".$status_hover."' ".(($draggable) ? "draggable='true' ondragstart=\"drag(event, this.parentNode.getAttribute('name'));\" onclick=\"virtual_drag('".escape($call_identifier)."', '".escape($extension)."');\"" : "onfocus='this.blur();' draggable='false' style='cursor: not-allowed;'").">\n";
		$block .= 			"</span>\n";
		$block .= "		</td>\n";
		$block .= "		<td class='op_ext_info ".$style."'>\n";
		if ($dir_icon != '') {
			$block .= "			<img src='resources/images/".$dir_icon.".png' align='right' style='margin-top: 3px; margin-right: 1px; width: 12px; height: 12px; cursor: help;' draggable='false' alt=\"".$text['label-call_direction']."\" title=\"".$text['label-call_direction']."\">\n";
		}
		$block .= "			<span class='op_user_info'>\n";
		if ($ext['effective_caller_id_name'] != '' && escape($ext['effective_caller_id_name']) != $extension) {
			$block .= "			<strong class='strong'>".escape($ext['effective_caller_id_name'])."</strong> (".escape($extension).")\n";
			$block .= "			<br>Status Agente: <strong class='strong' style='color: " . $status_color . ";'>" . escape($status_hover) . "</strong>";
			$block .= "			<br>Status Ramal: <strong class='strong' style='color: " . $status_color_ext . ";'>" . escape($ext_info) . "</strong>";
		}
		else {
			$block .= "			<strong class='strong'>".escape($extension)."</strong>\n";
		}
		$block .= "			</span><br>\n";
		if ($ext_state != '') {
			$block .= "		<span class='op_caller_info'>\n";
			$block .= "			<table align='right'><tr><td style='text-align: right;'>\n";
			$block .= "				<span class='op_call_info'>".escape($ext['call_length'])."</span><br>\n";
			$block .= "				<span class='call_control'>\n";
			//record
			if (permission_exists('operator_panel_record') && $ext_state == 'active') {
				$call_identifier_record = $ext['call_uuid'];
				$rec_file = $_SESSION['switch']['recordings']['dir']."/".$_SESSION['domain_name']."/archive/".date("Y")."/".date("M")."/".date("d")."/".escape($call_identifier_record)."mp3";
				if (file_exists($rec_file)) {
					$block .= 		"<img src='resources/images/recording.png' style='width: 12px; height: 12px; border: none; margin: 4px 0px 0px 5px; cursor: help;' title=\"".$text['label-recording']."\" ".$onhover_pause_refresh.">\n";
				}
				else {
					$block .= 		"<img src='resources/images/record.png' style='width: 12px; height: 12px; border: none; margin: 4px 0px 0px 5px; cursor: pointer;' title=\"".$text['label-record']."\" onclick=\"record_call('".$call_identifier_record."');\" ".$onhover_pause_refresh.">\n";
				}
			}
			//eavesdrop
			if (permission_exists('operator_panel_eavesdrop') && $ext_state == 'active' && sizeof($_SESSION['user']['extensions']) > 0 && !in_array($extension, $_SESSION['user']['extensions'])) {
				$block .= 			"<img src='resources/images/eavesdrop.png' style='width: 12px; height: 12px; border: none; margin: 4px 0px 0px 5px; cursor: pointer;' title='".$text['label-eavesdrop']."' onclick=\"eavesdrop_call('".escape($ext['destination'])."','".escape($call_identifier)."');\" ".$onhover_pause_refresh.">\n";
			}
			//hangup
			if (permission_exists('operator_panel_hangup') || in_array($extension, $_SESSION['user']['extensions'])) {
				if ($ext['variable_bridge_uuid'] == '' && $ext_state == 'ringing') {
					$call_identifier_hangup_uuid = $ext['uuid'];
				}
				else if ($dir_icon == 'outbound') {
					$call_identifier_hangup_uuid = $ext['uuid'];
				}
				else {
					$call_identifier_hangup_uuid = $call_identifier;
				}
				$block .= 			"<img src='resources/images/kill.png' style='width: 12px; height: 12px; border: none; margin: 4px 0px 0px 5px; cursor: pointer;' title='".$text['label-hangup']."' onclick=\"hangup_call('".escape($call_identifier_hangup_uuid)."');\" ".$onhover_pause_refresh.">\n";
			}
			$block .=				"</span>\n";
			//transfer
			if (in_array($extension, $_SESSION['user']['extensions']) && $ext_state == 'active') {
				$block .= 			"<img id='destination_control_".escape($extension)."_transfer' class='destination_control' src='resources/images/keypad_transfer.png' style='width: 12px; height: 12px; border: none; margin: 4px 0px 0px 5px; cursor: pointer;' onclick=\"toggle_destination('".escape($extension)."', 'transfer');\" ".$onhover_pause_refresh.">\n";
			}
			$block .= "			</td></tr></table>\n";
			if (permission_exists('operator_panel_call_details')) {
				$block .= "			<span id='op_caller_details_".escape($extension)."'><strong>".escape($call_name)."</strong><br>".escape($call_number)."</span>\n";
			}
			$block .= "		</span>\n";
			//transfer
			if (in_array($extension, $_SESSION['user']['extensions']) && $ext_state == 'active') {
				$call_identifier_transfer = $ext['variable_bridge_uuid'];
				$block .= "		<form id='frm_destination_".escape($extension)."_transfer' onsubmit=\"go_destination('".escape($extension)."', document.getElementById('destination_".escape($extension)."_transfer').value, 'transfer', '".escape($call_identifier_transfer)."'); return false;\">\n";
				$block .= "			<input type='text' class='formfld' id='destination_".escape($extension)."_transfer' style='width: 100px; min-width: 100px; max-width: 100px; margin-top: 3px; text-align: center; display: none;' onblur=\"toggle_destination('".escape($extension)."', 'transfer');\">\n";
				$block .= "		</form>\n";
			}
		}
		else {
			//call
			if (in_array($extension, $_SESSION['user']['extensions'])) {
				$block .= "		<img id='destination_control_".escape($extension)."_call' class='destination_control' src='resources/images/keypad_call.png' style='width: 12px; height: 12px; border: none; margin-top: 26px; margin-right: 1px; cursor: pointer;' align='right' onclick=\"toggle_destination('".escape($extension)."', 'call');\" ".$onhover_pause_refresh.">\n";
				$block .= "		<form id='frm_destination_".escape($extension)."_call' onsubmit=\"go_destination('".escape($extension)."', document.getElementById('destination_".escape($extension)."_call').value, 'call'); return false;\">\n";
				$block .= "			<input type='text' class='formfld' id='destination_".escape($extension)."_call' style='width: 100px; min-width: 100px; max-width: 100px; margin-top: 10px; text-align: center; display: none;' onblur=\"toggle_destination('".escape($extension)."', 'call');\">\n";
				$block .= "		</form>\n";
			}
		}
		$block .= "		</td>\n";
		$block .= "	</tr>\n";
		$block .= "</table>\n";

		if (if_group("superadmin") && isset($_GET['debug'])) {
			$block .= "<span style='font-size: 10px;'>\n";
			$block .= "From ID<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: maroon'>".escape($extension)."</strong><br>\n";
			$block .= "uuid<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: ".($call_identifier == $ext['uuid'] ? 'blue' : 'black').";'>".escape($ext['uuid'])."</strong><br>\n";
			$block .= "call_uuid<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: ".($call_identifier == $ext['call_uuid'] ? 'blue' : 'black').";'>".escape($ext['call_uuid'])."</strong><br>\n";
			$block .= "variable_bridge_uuid<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: ".($call_identifier == $ext['variable_bridge_uuid'] ? 'blue' : 'black').";'>".escape($ext['variable_bridge_uuid'])."</strong><br>\n";
			$block .= "direction<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['direction'])."</strong><br>\n";
			$block .= "variable_call_direction<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['variable_call_direction'])."</strong><br>\n";
			$block .= "state<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['state'])."</strong><br>\n";
			$block .= "cid_num<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['cid_num'])."</strong><br>\n";
			$block .= "dest<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['dest'])."</strong><br>\n";
			$block .= "context<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['context'])."</strong><br>\n";
			$block .= "presence_id<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['presence_id'])."</strong><br>\n";
			$block .= "callstate<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['callstate'])."</strong><br>\n";
			$block .= "</span>\n";
		}
		$block .= "</div>\n";

		if (in_array($extension, $_SESSION['user']['extensions'])) {
			$user_extensions[] = $block;
		} elseif (!empty($ext['call_group']) && filter_var($_SESSION['operator_panel']['group_extensions']['boolean'], FILTER_VALIDATE_BOOLEAN)) {
			$grouped_extensions[$ext['call_group']][] = $block;
		} else {
			$other_extensions[] = $block;
		}
	}
}

if (sizeof($user_extensions) > 0) {
	echo "<table width='100%'><tr><td>\n";
	if (is_array($user_extensions)) {
		foreach ($user_extensions as $ext_block) {
			echo $ext_block;
		}
	}
	
	foreach($valet_array as $row) {
			$extension = $row['extension'];
			$ext_state = 'active';
			$style = "op_ext op_valet_park_active";
			$draggable = false;
	
			//build the list of park extensions
			$valet_block .= "<div id='".escape($extension)."' class='".$style."' ".(($_GET['vd_ext_from'] == $extension || $_GET['vd_ext_to'] == $extension) ? "style='border-style: dotted;'" : null)." ondrop='drop(event, this.id);' ondragover='allowDrop(event, this.id);' ondragleave='discardDrop(event, this.id);'>\n"; // DRAG TO
			$valet_block .= "<table class='".$style."'>\n";
			$valet_block .= "	<tr>\n";
			$valet_block .= "		<td class='op_ext_icon'>\n";
			$valet_block .= "			<span name='".escape($extension)."'>"; // DRAG FROM
			$valet_block .= 				"<img id='".escape($call_identifier)."' class='op_ext_icon' src='resources/images/status_".$status_icon.".png' title='".$status_hover."' ".(($draggable) ? "draggable='true' ondragstart=\"drag(event, this.parentNode.getAttribute('name'));\" onclick=\"virtual_drag('".escape($call_identifier)."', '".escape($extension)."');\"" : "onfocus='this.blur();' draggable='false' style='cursor: not-allowed;'").">\n";
			$valet_block .= 			"</span>\n";
			$valet_block .= "		</td>\n";
			$valet_block .= "		<td class='op_ext_info ".$style."'>\n";
			if ($dir_icon != '') {
				$valet_block .= "			<img src='resources/images/".$dir_icon.".png' align='right' style='margin-top: 3px; margin-right: 1px; width: 12px; height: 12px; cursor: help;' draggable='false' alt=\"".$text['label-call_direction']."\" title=\"".$text['label-call_direction']."\">\n";
			}
			$valet_block .= "			<span class='op_user_info'>\n";
			//$valet_block .= "			<strong class='strong'>".escape($extension)."</strong>";
			$valet_block .= "			<strong class='strong'>Park </strong> (".escape($extension).")\n";
			$valet_block .= "			</span><br>\n";
			//if ($ext_state != '') {
				$valet_block .= "		<span class='op_caller_info'>\n";
				$valet_block .= "			<table align='right'><tr><td style='text-align: right;'>\n";
				$valet_block .= "				<span class='op_call_info'>".escape($ext['call_length'])."</span><br>\n";
				$valet_block .= "				<span class='call_control'>\n";
	
				$call_identifier_record = $ext['call_uuid'];
	
				$valet_block .=				"</span>\n";
				//transfer
				//if (in_array($extension, $_SESSION['user']['extensions']) && $ext_state == 'active') {
				//	$valet_block .= 			"<img id='destination_control_".escape($extension)."_transfer' class='destination_control' src='resources/images/keypad_transfer.png' style='width: 12px; height: 12px; border: none; margin: 4px 0px 0px 5px; cursor: pointer;' onclick=\"toggle_destination('".escape($extension)."', 'transfer');\" ".$onhover_pause_refresh.">";
				//}
				$valet_block .= "			</td></tr></table>\n";
				if (permission_exists('operator_panel_call_details')) {
					$valet_block .= "			<span id='op_caller_details_".escape($extension)."'><strong>".escape($row['caller_id_name'])."</strong><br>".escape($row['caller_id_number'])."</span>\n";
				}
				$valet_block .= "		</span>\n";
				//transfer
				//if (in_array($extension, $_SESSION['user']['extensions']) && $ext_state == 'active') {
					$call_identifier_transfer = $ext['variable_bridge_uuid'];
					$valet_block .= "		<form id='frm_destination_".escape($extension)."_transfer' onsubmit=\"go_destination('".escape($extension)."', document.getElementById('destination_".escape($extension)."_transfer').value, 'transfer', '".escape($call_identifier_transfer)."'); return false;\">\n";
					$valet_block .= "			<input type='text' class='formfld' id='destination_".escape($extension)."_transfer' style='width: 100px; min-width: 100px; max-width: 100px; margin-top: 3px; text-align: center; display: none;' onblur=\"toggle_destination('".escape($extension)."', 'transfer');\">\n";
					$valet_block .= "		</form>\n";
			
		//	}
			//else {
			//	//call
				if (in_array($extension, $_SESSION['user']['extensions'])) {
					$valet_block .= "		<img id='destination_control_".escape($extension)."_call' class='destination_control' src='resources/images/keypad_call.png' style='width: 12px; height: 12px; border: none; margin-top: 26px; margin-right: 1px; cursor: pointer;' align='right' onclick=\"toggle_destination('".escape($extension)."', 'call');\" ".$onhover_pause_refresh.">";
					$valet_block .= "		<form id='frm_destination_".escape($extension)."_call' onsubmit=\"go_destination('".escape($extension)."', document.getElementById('destination_".escape($extension)."_call').value, 'call'); return false;\">";
					$valet_block .= "			<input type='text' class='formfld' id='destination_".escape($extension)."_call' style='width: 100px; min-width: 100px; max-width: 100px; margin-top: 10px; text-align: center; display: none;' onblur=\"toggle_destination('".escape($extension)."', 'call');\">";
					$valet_block .= "		</form>\n";
				}
			//}
			$valet_block .= "		</td>\n";
			$valet_block .= "	</tr>\n";
			$valet_block .= "</table>\n";
	
			if (if_group("superadmin") && isset($_GET['debug'])) {
				$valet_block .= "<span style='font-size: 10px;'>\n";
				$valet_block .= "From ID<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: maroon'>".escape($extension)."</strong><br>\n";
				$valet_block .= "uuid<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: ".($call_identifier == $ext['uuid'] ? 'blue' : 'black').";'>".escape($ext['uuid'])."</strong><br>\n";
				$valet_block .= "call_uuid<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: ".($call_identifier == $ext['call_uuid'] ? 'blue' : 'black').";'>".escape($ext['call_uuid'])."</strong><br>\n";
				$valet_block .= "variable_bridge_uuid<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: ".($call_identifier == $ext['variable_bridge_uuid'] ? 'blue' : 'black').";'>".escape($ext['variable_bridge_uuid'])."</strong><br>\n";
				$valet_block .= "direction<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['direction'])."</strong><br>\n";
				$valet_block .= "variable_call_direction<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['variable_call_direction'])."</strong><br>\n";
				$valet_block .= "state<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['state'])."</strong><br>\n";
				$valet_block .= "cid_num<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['cid_num'])."</strong><br>\n";
				$valet_block .= "dest<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['dest'])."</strong><br>\n";
				$valet_block .= "context<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['context'])."</strong><br>\n";
				$valet_block .= "presence_id<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['presence_id'])."</strong><br>\n";
				$valet_block .= "callstate<br>&nbsp;&nbsp;&nbsp;&nbsp;<strong style='color: black;'>".escape($ext['callstate'])."</strong><br>\n";
				$valet_block .= "</span>\n";
			}
			$valet_block .= "</div>\n";
			echo $valet_block;
			unset($valet_block);
		}
	     //   if (isset($num_rows)) {
		    if ($num_rows > 0) {
//		    echo "<div valign='top' class='heading' style='float: right;'><strong>".$text['label-recent_calls']."</strong></div>\n";
			echo "<div class='op_ext hud_heading' style='text-align: center;'>".$text['label-recent_calls']."\n";
			echo "<table class='tr_hover' cellpadding='0' cellspacing='0' border='0'>\n";
			echo "<tr>\n";
			echo "<th class='hud_heading' width='50%'>".$textcalls['label-direction']."</th>\n";			
			echo "<th class='hud_heading' style='text-align: center;' width='50%'>".$textcalls['label-cid_number']."</th>\n";
			echo "<th class='hud_heading' style='text-align: center;'>".$textcalls['label-date_time']."</th>\n";
			echo "</tr>\n";
			//if (isset($num_rows)) {
			//if ($num_rows > 0) {
				$theme_cdr_images_exist = (
					file_exists($theme_image_path."icon_cdr_inbound_answered.png") &&
					file_exists($theme_image_path."icon_cdr_inbound_voicemail.png") &&
					file_exists($theme_image_path."icon_cdr_inbound_cancelled.png") &&
					file_exists($theme_image_path."icon_cdr_inbound_failed.png") &&
					file_exists($theme_image_path."icon_cdr_outbound_answered.png") &&
					file_exists($theme_image_path."icon_cdr_outbound_cancelled.png") &&
					file_exists($theme_image_path."icon_cdr_outbound_failed.png") &&
					file_exists($theme_image_path."icon_cdr_local_answered.png") &&
					file_exists($theme_image_path."icon_cdr_local_voicemail.png") &&
					file_exists($theme_image_path."icon_cdr_local_cancelled.png") &&
					file_exists($theme_image_path."icon_cdr_local_failed.png")
					) ? false : true;
		
				foreach($result as $index => $row) {
					if ($index + 1 > $recent_limit) { break; } //only show limit
					$tmp_year = date("Y", strtotime($row['start_stamp']));
					$tmp_month = date("M", strtotime($row['start_stamp']));
					$tmp_day = date("d", strtotime($row['start_stamp']));
					$tmp_start_epoch = ($_SESSION['domain']['time_format']['text'] == '12h') ? date("n/j g:ia", $row['start_epoch']) : date("n/j H:i", $row['start_epoch']);
		
					//determine name
						$cdr_name = ($row['direction'] == 'inbound' || ($row['direction'] == 'local' && is_array($assigned_extensions) && in_array($row['destination_number'], $assigned_extensions))) ? $row['caller_id_name'] : $row['destination_number'];
					//determine number to display
						if ($row['direction'] == 'inbound' || ($row['direction'] == 'local' && is_array($assigned_extensions) && in_array($row['destination_number'], $assigned_extensions))) {
							$cdr_number = (is_numeric($row['caller_id_number'])) ? format_phone($row['caller_id_number']) : $row['caller_id_number'];
							$dest = $row['caller_id_number'];
						}
						else if ($row['direction'] == 'outbound' || ($row['direction'] == 'local' && is_array($assigned_extensions) && in_array($row['caller_id_number'], $assigned_extensions))) {
							$cdr_number = (is_numeric($row['destination_number'])) ? format_phone($row['destination_number']) : $row['destination_number'];
							$dest = $row['destination_number'];
						}
					//set click-to-call variables
					/*
					send_cmd('/app/click_to_call/click_to_call.php?src_cid_name=011956812949&src_cid_number=011956812949&dest_cid_name=9&dest_cid_number=9&src=9&dest=011956812949&rec=false&ringback=us-ring&auto_answer=true');
					
					*/
						if (permission_exists('click_to_call_call')) {
							$tr_link = "onclick=\"send_cmd('".PROJECT_PATH."/app/click_to_call/click_to_call.php".
								"?src_cid_name=".urlencode($cdr_name).
								"&src_cid_number=".urlencode($cdr_number).
								"&dest_cid_name=".urlencode($user_extension_number).
								"&dest_cid_number=".urlencode($user_extension_number).
								"&src=".urlencode($user_extension_number).
								"&dest=".urlencode($dest).
								"&rec=".(isset($_SESSION['click_to_call']['record']['boolean'])?$_SESSION['click_to_call']['record']['boolean']:"true").
								"&ringback=".(isset($_SESSION['click_to_call']['ringback']['text'])?$_SESSION['click_to_call']['ringback']['text']:"pt-ring").
								"&auto_answer=".(isset($_SESSION['click_to_call']['auto_answer']['boolean'])?$_SESSION['click_to_call']['auto_answer']['boolean']:"true").
								"');\" ".
								"style='cursor: pointer;'";
						}
					echo "<tr ".$tr_link.">\n";
					//determine call result and appropriate icon
						echo "<td class='op_ext_info' style='cursor: help; padding: 0 0 0 6px;'>\n";
						if ($theme_cdr_images_exist) {
							if ($row['direction'] == 'inbound' || $row['direction'] == 'local') {
								if ($row['answer_stamp'] != '' && $row['bridge_uuid'] != '') { $call_result = 'answered'; }
								else if ($row['answer_stamp'] != '' && $row['bridge_uuid'] == '') { $call_result = 'voicemail'; }
								else if ($row['answer_stamp'] == '' && $row['bridge_uuid'] == '' && $row['sip_hangup_disposition'] != 'send_refuse') { $call_result = 'cancelled'; }
								else { $call_result = 'failed'; }
							}
							else if ($row['direction'] == 'outbound') {
								if ($row['answer_stamp'] != '' && $row['bridge_uuid'] != '') { $call_result = 'answered'; }
								else if ($row['answer_stamp'] == '' && $row['bridge_uuid'] != '') { $call_result = 'cancelled'; }
								else { $call_result = 'failed'; }
							}
							if (isset($row['direction'])) {
								echo "<img src='".PROJECT_PATH."/themes/".$_SESSION['domain']['template']['name']."/images/icon_cdr_".$row['direction']."_".$call_result.".png' width='12' style='border: none;' title='".$textcalls['label-'.$row['direction']].": ".$textcalls['label-'.$call_result]."'>".$textcalls['label-'.$row['direction']]."\n";
							}
						}
						echo "</td>\n";
						echo "<td valign='top' class='op_ext_info hud_text' nowrap='nowrap'><a href='javascript:void(0);' ".(($cdr_name != '') ? "title=\"".$cdr_name."\"" : null).">".$cdr_number."</a></td>\n";
						echo "<td valign='top' class='op_ext_info hud_text' nowrap='nowrap'>".$tmp_start_epoch."</td>\n";
					echo "</tr>\n";
		
					unset($cdr_name, $cdr_number);
					$c = ($c) ? 0 : 1;
				}
			//}
			unset($sql, $parameters, $result, $num_rows, $index, $row);
			echo "</table>\n";
			$n++;
			echo "</div>\n";
			}
	//if (isset($num_contact_rows)) {
			    if ($num_contact_rows > 0) {

			    echo "<div class='op_ext hud_heading' style='width: 50%; text-align: center;'>".$textcalls['label-contacts']."\n";
			    echo "<table width='100%'>\n";
//				echo "<table class='tr_hover' cellpadding='0' cellspacing='0' border='0'>\n";
				echo "<tr>\n";
				echo "<th class='hud_heading' style='text-align: center; width: 30%;'>".$textcalls['label-name']."</th>\n";			
				echo "<th class='hud_heading' style='text-align: center; width: 30%;'>".$textcalls['label-number']."</th>\n";
				echo "<th class='hud_heading' style='text-align: center; width: 80%;'>".$textcalls['label-email_address']."</th>\n";
				echo "</tr>\n";
				if (isset($num_contact_rows)) {
				//if ($num_rows > 0) {
					
			
					foreach($result_contacts as $index_contacts => $row_contacts) {
						
						echo "<tr>\n";
						//determine call result and appropriate icon
							//echo "<td class='op_ext_info' style='cursor: help; padding: 0 0 0 6px;'>\n";
							echo "	<td>".escape($row_contacts['contact_name'])."</td>\n";
							//echo "</td>\n";
							echo "	<td>".escape($row_contacts['outbound_caller_id_number'])."</td>\n";
							echo "	<td>".escape($row_contacts['email_address'])."</td>\n";
							
						echo "</tr>\n";
			
						//unset($cdr_name, $cdr_number);
						$c = ($c) ? 0 : 1;
					}
				}
				unset($sql_contacts, $parameters_contacts, $result_contacts, $num_contact_rows, $index_contacts, $row_contacts);
				echo "</table>\n";
				$n++;
				echo "</div>\n";
				}
	    
	  //  echo "</td></tr></table><br>\n";
	echo "</td></tr></table><br>\n";
}

//loop throug each group
if (permission_exists('operator_panel_groups')) {
if (sizeof($grouped_extensions) > 0) {
	//alphabetical order
	ksort($grouped_extensions);
	
	//loop through the groups
	foreach ($grouped_extensions as $group => $extensions) {
		echo "<div class=\"heading\"><strong>".ucwords(escape($group))."</strong></div>\n";
		echo "<br><br>\n";
		echo "<table width='100%'><tr><td>\n";
		foreach ($extensions as $ext_block) {
			echo $ext_block;
		}
		echo "</td></tr></table><br>\n";
	}
}
}
//show the other extensions
if (sizeof($other_extensions) > 0) {
	echo "<div class=\"heading\"><strong>".$text['label-other_extensions']."</strong></div>\n";
	echo "<br><br>\n";
	echo "<table width='100%'><tr><td>\n";
	foreach ($other_extensions as $ext_block) {
		echo $ext_block;
	}
	echo "</td></tr></table>\n";
}

//no extensions found
if (sizeof($other_extensions) + sizeof($grouped_extensions) < 1) {
	echo $text['label-no_extensions_found'];
}

echo "<br><br>\n";


if (if_group("superadmin") && isset($_GET['debug'])) {
	echo '$activity<br>';
	echo "<textarea style='width: 100%; height: 600px; overflow: scroll;' onfocus='refresh_stop();' onblur='refresh_start();'>";
	print_r($activity);	
	echo "</textarea>";
	echo "<br><br>";

	echo '$_SESSION<br>';
	echo "<textarea style='width: 100%; height: 600px; overflow: scroll;' onfocus='refresh_stop();' onblur='refresh_start();'>";
	print_r($_SESSION);
	echo "</textarea>";
}


?>

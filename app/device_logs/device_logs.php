<?php
/*
	Copyright (c) 2019-2023 Daniel Paixao <daniel@flux.net.br>
	
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions
	are met:

		1. Redistributions of source code must retain the above copyright
		notice, this list of conditions and the following disclaimer.
	
		2. Redistributions in binary form must reproduce the above copyright
		notice, this list of conditions and the following disclaimer in the
		documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
	ARE DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE
	FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
	DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
	OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
	HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
	LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
	OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
	SUCH DAMAGE.
*/

//includes files
	require_once dirname(__DIR__, 2) . "/resources/require.php";
	require_once "resources/check_auth.php";
	require_once "resources/paging.php";

//check permissions
	if (permission_exists('device_log_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//set additional variables
	$search = $_GET["search"] ?? '';
	$show = $_GET["show"] ?? '';

//set from session variables
	$list_row_edit_button = !empty($_SESSION['theme']['list_row_edit_button']['boolean']) ? $_SESSION['theme']['list_row_edit_button']['boolean'] : 'false';

//get the http post data
	if (!empty($_POST['device_logs']) && is_array($_POST['device_logs'])) {
		$action = $_POST['action'];
		$search = $_POST['search'];
		$device_logs = $_POST['device_logs'];
	}

//process the http post data by action
	if (!empty($action) && is_array($device_logs) && @sizeof($device_logs) != 0) {
		switch ($action) {
			case 'copy':
				if (permission_exists('device_log_add')) {
					$obj = new device_logs;
					$obj->copy($device_logs);
				}
				break;
			case 'toggle':
				if (permission_exists('device_log_edit')) {
					$obj = new device_logs;
					$obj->toggle($device_logs);
				}
				break;
			case 'delete':
				if (permission_exists('device_log_delete')) {
					$obj = new device_logs;
					$obj->delete($device_logs);
				}
				break;
		}

		header('Location: device_logs.php'.($search != '' ? '?search='.urlencode($search) : null));
		exit;
	}

//get order and order by
	$order_by = $_GET["order_by"] ?? '';
	$order = $_GET["order"] ?? '';

//set the time zone
	if (isset($_SESSION['domain']['time_zone']['name'])) {
		$time_zone = $_SESSION['domain']['time_zone']['name'];
	}
	else {
		$time_zone = date_default_timezone_get();
	}

//add the search string
	if (isset($_GET["search"]) && $_GET["search"] != '') {
		$search =  strtolower($_GET["search"]);
	}

//get the count
	$sql = "select count(device_log_uuid) \n";
	$sql .= "from v_device_logs \n";
	if ($show == "all" && permission_exists('device_log_all')) {
		$sql .= "where true \n";
	}
	else {
		$sql .= "where (domain_uuid = :domain_uuid or domain_uuid is null) ";
		$parameters['domain_uuid'] = $domain_uuid;
	}
	if (isset($search)) {
		$sql .= "and (";
		$sql .= "	lower(device_address) like :search ";
		$sql .= "	or lower(request_scheme) like :search ";
		$sql .= "	or lower(http_host) like :search ";
		$sql .= "	or lower(server_port) like :search ";
		$sql .= "	or lower(server_protocol) like :search ";
		$sql .= "	or lower(query_string) like :search ";
		$sql .= "	or lower(remote_address) like :search ";
		$sql .= "	or lower(http_user_agent) like :search ";
		$sql .= "	or lower(http_status) like :search ";
		$sql .= "	or lower(http_status_code) like :search ";
		$sql .= ") ";
		$parameters['search'] = '%'.$search.'%';
	}
	$database = new database;
	$num_rows = $database->select($sql, $parameters, 'column');

//prepare to page the results
	$rows_per_page = (!empty($_SESSION['domain']['paging']['numeric'])) ? $_SESSION['domain']['paging']['numeric'] : 50;
	$param = $search ? "&search=".$search : null;
	$param .= ($show == 'all' && permission_exists('device_log_all')) ? "&show=all" : null;
	$page = isset($_GET['page']) ? $_GET['page'] : 0;
	list($paging_controls, $rows_per_page) = paging($num_rows, $param, $rows_per_page);
	list($paging_controls_mini, $rows_per_page) = paging($num_rows, $param, $rows_per_page, true);
	$offset = $rows_per_page * $page;

//get the list
	$sql = "select \n";
	$sql .= "d.domain_uuid, \n";
	$sql .= "device_log_uuid, \n";
	$sql .= "device_uuid, \n";
	$sql .= "d.domain_name, \n";
	$sql .= "timestamp, \n";
	$sql .= "to_char(timezone(:time_zone, timestamp), 'DD Mon YYYY') as date_formatted, \n";
	$sql .= "to_char(timezone(:time_zone, timestamp), 'HH12:MI:SS am') as time_formatted, \n";
	$sql .= "device_address, \n";
	$sql .= "request_scheme, \n";
	$sql .= "http_host, \n";
	$sql .= "server_port, \n";
	$sql .= "server_protocol, \n";
	$sql .= "query_string, \n";
	$sql .= "remote_address, \n";
	$sql .= "http_user_agent, \n";
	$sql .= "http_status, \n";
	$sql .= "http_status_code, \n";
	$sql .= "http_content_body \n";
	$sql .= "from v_device_logs as l, v_domains as d \n";
	if ($show == "all" && permission_exists('device_log_all')) {
		$sql .= "where true \n";
	}
	else {
		$sql .= "where (l.domain_uuid = :domain_uuid or l.domain_uuid is null) \n";
		$parameters['domain_uuid'] = $domain_uuid;
	}
	$sql .= "and l.domain_uuid = d.domain_uuid \n";
	if (isset($search)) {
		$sql .= "and ( \n";
		$sql .= "	lower(device_address) like :search \n";
		$sql .= "	or lower(request_scheme) like :search \n";
		$sql .= "	or lower(http_host) like :search \n";
		$sql .= "	or lower(server_port) like :search \n";
		$sql .= "	or lower(server_protocol) like :search \n";
		$sql .= "	or lower(query_string) like :search \n";
		$sql .= "	or lower(remote_address) like :search \n";
		$sql .= "	or lower(http_user_agent) like :search \n";
		$sql .= "	or lower(http_status) like :search \n";
		$sql .= "	or lower(http_status_code) like :search \n";
		$sql .= ") ";
		$parameters['search'] = '%'.$search.'%';
	}
	$parameters['time_zone'] = $time_zone;
	$sql .= order_by($order_by, $order, 'timestamp', 'desc');
	$sql .= limit_offset($rows_per_page, $offset);
	$database = new database;
	$device_logs = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//include the header
	$document['title'] = $text['title-device_logs'];
	require_once "resources/header.php";

//show the content
	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>".$text['title-device_logs']." (".$num_rows.")</b></div>\n";
	echo "	<div class='actions'>\n";
	if (permission_exists('device_log_add')) {
		echo button::create(['type'=>'button','label'=>$text['button-add'],'icon'=>$_SESSION['theme']['button_icon_add'],'id'=>'btn_add','link'=>'device_log_edit.php']);
	}
	if (permission_exists('device_log_delete') && $device_logs) {
		echo button::create(['type'=>'button','label'=>$text['button-delete'],'icon'=>$_SESSION['theme']['button_icon_delete'],'name'=>'btn_delete','onclick'=>"modal_open('modal-delete','btn_delete');"]);
	}
	echo 		"<form id='form_search' class='inline' method='get'>\n";
	if (permission_exists('device_log_all')) {
		if ($show == 'all') {
			echo "		<input type='hidden' name='show' value='all'>\n";
		}
		else {
			echo button::create(['type'=>'button','label'=>$text['button-show_all'],'icon'=>$_SESSION['theme']['button_icon_all'],'link'=>'?show=all']);
		}
	}
	echo 		"<input type='text' class='txt list-search' name='search' id='search' value=\"".escape($search)."\" placeholder=\"".$text['label-search']."\" onkeydown='list_search_reset();'>";
	echo button::create(['label'=>$text['button-search'],'icon'=>$_SESSION['theme']['button_icon_search'],'type'=>'submit','id'=>'btn_search','style'=>($search != '' ? 'display: none;' : null)]);
	echo button::create(['label'=>$text['button-reset'],'icon'=>$_SESSION['theme']['button_icon_reset'],'type'=>'button','id'=>'btn_reset','link'=>'device_logs.php','style'=>($search == '' ? 'display: none;' : null)]);
	if (!empty($paging_controls_mini)) {
		echo 	"<span style='margin-left: 15px;'>".$paging_controls_mini."</span>\n";
	}
	echo "		</form>\n";
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	if (permission_exists('device_log_add') && $device_logs) {
		echo modal::create(['id'=>'modal-copy','type'=>'copy','actions'=>button::create(['type'=>'button','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_copy','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('copy'); list_form_submit('form_list');"])]);
	}
	if (permission_exists('device_log_edit') && $device_logs) {
		echo modal::create(['id'=>'modal-toggle','type'=>'toggle','actions'=>button::create(['type'=>'button','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_toggle','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('toggle'); list_form_submit('form_list');"])]);
	}
	if (permission_exists('device_log_delete') && $device_logs) {
		echo modal::create(['id'=>'modal-delete','type'=>'delete','actions'=>button::create(['type'=>'button','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_delete','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('delete'); list_form_submit('form_list');"])]);
	}

	echo $text['description-device_logs']."\n";
	echo "<br /><br />\n";

	echo "<form id='form_list' method='post'>\n";
	echo "<input type='hidden' id='action' name='action' value=''>\n";
	echo "<input type='hidden' name='search' value=\"".escape($search)."\">\n";

	echo "<table class='list'>\n";
	echo "<tr class='list-header'>\n";
	if (permission_exists('device_log_add') || permission_exists('device_log_edit') || permission_exists('device_log_delete')) {
		echo "	<th class='checkbox'>\n";
		echo "		<input type='checkbox' id='checkbox_all' name='checkbox_all' onclick='list_all_toggle();' ".(!empty($device_logs) ?: "style='visibility: hidden;'").">\n";
		echo "	</th>\n";
	}
	if ($show == 'all' && permission_exists('device_log_all')) {
		echo th_order_by('domain_name', $text['label-domain'], $order_by, $order);
	}
	echo "<th class='left'>".$text['label-date']."</th>\n";
	echo "<th class='left hide-md-dn'>".$text['label-time']."</th>\n";
	echo th_order_by('device_address', $text['label-device_address'], $order_by, $order);
	echo "<th class='left hide-sm-dn'>".$text['label-request_scheme']."</th>\n";
	echo "<th class='left hide-md-dn'>".$text['label-http_host']."</th>\n";
	echo "<th class='left hide-md-dn'>".$text['label-server_port']."</th>\n";
	echo "<th class='left hide-md-dn'>".$text['label-server_protocol']."</th>\n";
	echo "<th class='left hide-md-dn'>".$text['label-query_string']."</th>\n";
	echo th_order_by('remote_address', $text['label-remote_address'], $order_by, $order);
	echo "<th class='left hide-md-dn'>".$text['label-http_user_agent']."</th>\n";
	echo "<th class='left hide-md-dn'>".$text['label-http_status']."</th>\n";
	echo "<th class='left hide-md-dn'>".$text['label-http_status_code']."</th>\n";
	if (permission_exists('device_log_edit') && $list_row_edit_button == 'true') {
		echo "	<td class='action-button'>&nbsp;</td>\n";
	}
	echo "</tr>\n";

	if (is_array($device_logs) && @sizeof($device_logs) != 0) {
		$x = 0;
		foreach ($device_logs as $row) {
			if (permission_exists('device_log_edit')) {
				$list_row_url = "device_log_edit.php?id=".urlencode($row['device_log_uuid']);
			}
			echo "<tr class='list-row' href='".$list_row_url."'>\n";
			if (permission_exists('device_log_add') || permission_exists('device_log_edit') || permission_exists('device_log_delete')) {
				echo "	<td class='checkbox'>\n";
				echo "		<input type='checkbox' name='device_logs[$x][checked]' id='checkbox_".$x."' value='true' onclick=\"if (!this.checked) { document.getElementById('checkbox_all').checked = false; }\">\n";
				echo "		<input type='hidden' name='device_logs[$x][uuid]' value='".escape($row['device_log_uuid'])."' />\n";
				echo "	</td>\n";
			}
			if ($show == 'all' && permission_exists('device_log_all')) {
				echo "	<td>".escape($_SESSION['domains'][$row['domain_uuid']]['domain_name'])."</td>\n";
			}
			echo "	<td>".escape($row['date_formatted'])."</td>\n";
			echo "	<td class='left hide-md-dn'>".escape($row['time_formatted'])."</td>\n";
			echo "	<td>".escape($row['device_address'])."</td>\n";
			echo "	<td class='left hide-sm-dn'>".escape($row['request_scheme'])."</td>\n";
			echo "	<td class='left hide-md-dn'>".escape($row['http_host'])."</td>\n";
			echo "	<td class='left hide-md-dn'>".escape($row['server_port'])."</td>\n";
			echo "	<td class='left hide-md-dn'>".escape($row['server_protocol'])."</td>\n";
			echo "	<td class='left hide-md-dn'>".escape($row['query_string'])."</td>\n";
			echo "	<td>".escape($row['remote_address'])."</td>\n";
			echo "	<td class='left hide-md-dn'>".escape($row['http_user_agent'])."</td>\n";
			echo "	<td class='left hide-md-dn'>".escape($row['http_status'])."</td>\n";
			echo "	<td class='left hide-md-dn'>".escape($row['http_status_code'])."</td>\n";
			if (permission_exists('device_log_edit') && $list_row_edit_button == 'true') {
				echo "	<td class='action-button'>\n";
				echo button::create(['type'=>'button','title'=>$text['button-edit'],'icon'=>$_SESSION['theme']['button_icon_edit'],'link'=>$list_row_url]);
				echo "	</td>\n";
			}
			echo "</tr>\n";
			$x++;
		}
	}

	echo "</table>\n";
	echo "<br />\n";
	echo "<div align='center'>".$paging_controls."</div>\n";
	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";
	echo "</form>\n";

//include the footer
	require_once "resources/footer.php";

?>

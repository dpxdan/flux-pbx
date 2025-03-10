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
	require_once "resources/paging.php";

//check permissions
	if (permission_exists('restapi_endpoint_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get the http post data
	if (is_array($_POST['rest_api_endpoint'])) {
		$action = $_POST['action'];
		$search = $_POST['search'];
		$rest_api_endpoint = $_POST['rest_api_endpoint'];
	}

//process the http post data by action
	if ($action != '' && is_array($rest_api_endpoint) && @sizeof($rest_api_endpoint) != 0) {
		switch ($action) {
			case 'copy':
				if (permission_exists('restapi_endpoint_add')) {
					$obj = new rest_api;
					$obj->copy($rest_api_endpoint);
				}
				break;
			case 'toggle':
				if (permission_exists('restapi_endpoint_edit')) {
					$obj = new rest_api;
					$obj->toggle($rest_api_endpoint);
				}
				break;
			case 'delete':
				if (permission_exists('restapi_endpoint_delete')) {
					$obj = new rest_api;
					$obj->delete($rest_api_endpoint);
				}
				break;
		}

		header('Location: rest_api_endpoints.php'.($search != '' ? '?search='.urlencode($search) : null));
		exit;
	}

//get order and order by
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];

//add the search string
	$search = strtolower($_GET["search"]);
	if (strlen($search) > 0) {
		$sql_search = " (";
		$sql_search .= "	lower(api_endpoint_category) like :search ";
		$sql_search .= "	or lower(api_endpoint_uri) like :search ";
		$sql_search .= "	or lower(api_endpoint_redirect) like :search ";
		$sql_search .= "	or lower(api_endpoint_carrier) like :search ";
		$sql_search .= "	or lower(api_endpoint_enabled) like :search ";
		$sql_search .= "	or lower(api_endpoint_description) like :search ";
		$sql_search .= ") ";
		$parameters['search'] = '%'.$search.'%';
	}

//get the count
	$sql = "select count(restapi_endpoint_uuid) from v_restapi_endpoints ";
	if ($_GET['show'] == "all" && permission_exists('restapi_endpoint_all')) {
		if (isset($sql_search)) {
			$sql .= "where ".$sql_search;
		}
	}
	else {
		$sql .= "where (domain_uuid = :domain_uuid or domain_uuid is null) ";
		if (isset($sql_search)) {
			$sql .= "and ".$sql_search;
		}
		$parameters['domain_uuid'] = $domain_uuid;
	}
	$database = new database;
	$num_rows = $database->select($sql, $parameters, 'column');

//prepare to page the results
	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	$param = $search ? "&search=".$search : null;
	$param = ($_GET['show'] == 'all' && permission_exists('restapi_endpoint_all')) ? "&show=all" : null;
	$page = is_numeric($_GET['page']) ? $_GET['page'] : 0;
	list($paging_controls, $rows_per_page) = paging($num_rows, $param, $rows_per_page);
	list($paging_controls_mini, $rows_per_page) = paging($num_rows, $param, $rows_per_page, true);
	$offset = $rows_per_page * $page;

//get the list
	$sql = str_replace('count(restapi_endpoint_uuid)', '*', $sql);
	$sql .= order_by($order_by, $order, 'api_endpoint_name', 'asc');
	$sql .= limit_offset($rows_per_page, $offset);
	$database = new database;
	$rest_api_endpoint = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//include the header
	$document['title'] = $text['title-restapis_endpoints'];
	require_once "resources/header.php";

//show the content
	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>".$text['title-restapis_endpoints']." (".$num_rows.")</b></div>\n";
	echo "	<div class='actions'>\n";
	if (permission_exists('restapi_endpoint_add')) {
		echo button::create(['type'=>'button','label'=>$text['button-add'],'icon'=>$_SESSION['theme']['button_icon_add'],'link'=>'rest_api_endpoints_edit.php']);
		echo button::create(['type'=>'button','label'=>$text['button-restapi'],'icon'=>'th','link'=>'rest_api.php']);
	}
	if (permission_exists('restapi_endpoint_add') && $rest_api_endpoint) {
		echo button::create(['type'=>'button','label'=>$text['button-copy'],'icon'=>$_SESSION['theme']['button_icon_copy'],'onclick'=>"if (confirm('".$text['confirm-copy']."')) { list_action_set('copy'); list_form_submit('form_list'); } else { this.blur(); return false; }"]);
	}
	if (permission_exists('restapi_endpoint_edit') && $rest_api_endpoint) {
		echo button::create(['type'=>'button','label'=>$text['button-toggle'],'icon'=>$_SESSION['theme']['button_icon_toggle'],'onclick'=>"if (confirm('".$text['confirm-toggle']."')) { list_action_set('toggle'); list_form_submit('form_list'); } else { this.blur(); return false; }"]);
	}
	if (permission_exists('restapi_endpoint_delete') && $rest_api_endpoint) {
		echo button::create(['type'=>'button','label'=>$text['button-delete'],'icon'=>$_SESSION['theme']['button_icon_delete'],'onclick'=>"if (confirm('".$text['confirm-delete']."')) { list_action_set('delete'); list_form_submit('form_list'); } else { this.blur(); return false; }"]);
	}
	echo 		"<form id='form_search' class='inline' method='get'>\n";
	if (permission_exists('restapi_endpoint_all')) {
		if ($_GET['show'] == 'all') {
			echo "		<input type='hidden' name='show' value='all'>\n";
		}
		else {
			echo button::create(['type'=>'button','label'=>$text['button-show_all'],'icon'=>$_SESSION['theme']['button_icon_all'],'link'=>'?show=all']);
		}
	}
	echo 		"<input type='text' class='txt list-search' name='search' id='search' value=\"".escape($search)."\" placeholder=\"".$text['label-search']."\" onkeydown='list_search_reset();'>";
	echo button::create(['label'=>$text['button-search'],'icon'=>$_SESSION['theme']['button_icon_search'],'type'=>'submit','id'=>'btn_search','style'=>($search != '' ? 'display: none;' : null)]);
	echo button::create(['label'=>$text['button-reset'],'icon'=>$_SESSION['theme']['button_icon_reset'],'type'=>'button','id'=>'btn_reset','link'=>'rest_api.php','style'=>($search == '' ? 'display: none;' : null)]);
	if ($paging_controls_mini != '') {
		echo 	"<span style='margin-left: 15px;'>".$paging_controls_mini."</span>\n";
	}
	echo "		</form>\n";
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	echo $text['title_description-restapi_endpoints']."\n";
	echo "<br /><br />\n";

	echo "<form id='form_list' method='post'>\n";
	echo "<input type='hidden' id='action' name='action' value=''>\n";
	echo "<input type='hidden' name='search' value=\"".escape($search)."\">\n";

	echo "<table class='list'>\n";
	echo "<tr class='list-header'>\n";
	if (permission_exists('restapi_endpoint_add') || permission_exists('restapi_endpoint_edit') || permission_exists('restapi_endpoint_delete')) {
		echo "	<th class='checkbox'>\n";
		echo "		<input type='checkbox' id='checkbox_all' name='checkbox_all' onclick='list_all_toggle();' ".($rest_api_endpoint ?: "style='visibility: hidden;'").">\n";
		echo "	</th>\n";
	}
	if ($_GET['show'] == 'all' && permission_exists('restapi_endpoint_all')) {
		echo th_order_by('domain_name', $text['label-domain'], $order_by, $order);
	}
	echo th_order_by('api_endpoint_name', $text['label-restapi_name'], $order_by, $order);
//	echo th_order_by('api_endpoint_category', $text['label-restapi_category'], $order_by, $order);
	echo th_order_by('api_endpoint_method', $text['label-restapi_method'], $order_by, $order);
	echo th_order_by('api_endpoint_uri', $text['label-restapi_uri'], $order_by, $order);
	echo th_order_by('api_endpoint_carrier', $text['label-restapi_carrier'], $order_by, $order);
	echo th_order_by('api_endpoint_redirect', $text['label-restapi_redirect'], $order_by, $order);
	echo th_order_by('api_endpoint_enabled', $text['label-restapi_enabled'], $order_by, $order, null, "class='center'");
	echo "	<th class='hide-sm-dn'>".$text['label-restapi_description']."</th>\n";
	if (permission_exists('restapi_endpoint_edit') && $_SESSION['theme']['list_row_edit_button']['boolean'] == 'true') {
		echo "	<td class='action-button'>&nbsp;</td>\n";
	}
	echo "</tr>\n";

	if (is_array($rest_api_endpoint) && @sizeof($rest_api_endpoint) != 0) {
		$x = 0;
		foreach ($rest_api_endpoint as $row) {
			if (permission_exists('restapi_endpoint_edit')) {
				$list_row_url = "rest_api_endpoints_edit.php?id=".urlencode($row['restapi_endpoint_uuid']);
			}
			echo "<tr class='list-row' href='".$list_row_url."'>\n";
			if (permission_exists('restapi_endpoint_add') || permission_exists('restapi_endpoint_edit') || permission_exists('restapi_endpoint_delete')) {
				echo "	<td class='checkbox'>\n";
				echo "		<input type='checkbox' name='rest_api[$x][checked]' id='checkbox_".$x."' value='true' onclick=\"if (!this.checked) { document.getElementById('checkbox_all').checked = false; }\">\n";
				echo "		<input type='hidden' name='rest_api[$x][uuid]' value='".escape($row['restapi_endpoint_uuid'])."' />\n";
				echo "	</td>\n";
			}
			if ($_GET['show'] == 'all' && permission_exists('restapi_endpoint_all')) {
				echo "	<td>".escape($_SESSION['domains'][$row['domain_uuid']]['domain_name'])."</td>\n";
			}
			echo "	<td>\n";
			if (permission_exists('restapi_endpoint_edit')) {
				echo "	<a href='".$list_row_url."' title=\"".$text['button-edit']."\">".escape($row['api_endpoint_name'])."</a>\n";
			}
			else {
				echo "	".escape($row['api_endpoint_name']);
			}
			echo "	</td>\n";
	//		echo "	<td>\n";
//			if (permission_exists('restapi_endpoint_edit')) {
//				echo "	<a href='".$list_row_url."' title=\"".$text['button-edit']."\">".escape($row['api_endpoint_category'])."</a>\n";
//			}
	//		else {
//				echo "	".escape($row['api_endpoint_category']);
//			}
		//	echo "	</td>\n";
			echo "	<td>\n";
			if (permission_exists('restapi_endpoint_edit')) {
				echo "	<a href='".$list_row_url."' title=\"".$text['button-edit']."\">".escape($row['api_endpoint_method'])."</a>\n";
			}
			else {
				echo "	".escape($row['api_endpoint_method']);
			}
			echo "	</td>\n";
			echo "	<td>".escape($row['api_endpoint_uri'])."</td>\n";
			echo "	<td>".escape($row['api_endpoint_carrier'])."</td>\n";
			echo "	<td>".escape($row['api_endpoint_redirect'])."</td>\n";

			if (permission_exists('restapi_endpoint_edit')) {
				echo "	<td class='no-link center'>\n";
				echo button::create(['type'=>'submit','class'=>'link','label'=>$text['label-'.$row['api_endpoint_enabled']],'title'=>$text['button-toggle'],'onclick'=>"list_self_check('checkbox_".$x."'); list_action_set('toggle'); list_form_submit('form_list')"]);
			}
			else {
				echo "	<td class='center'>\n";
				echo $text['label-'.$row['api_endpoint_enabled']];
			}
			echo "	</td>\n";
			echo "	<td class='description overflow hide-sm-dn'>".escape($row['api_endpoint_description'])."</td>\n";
			if (permission_exists('restapi_endpoint_edit') && $_SESSION['theme']['list_row_edit_button']['boolean'] == 'true') {
				echo "	<td class='action-button'>\n";
				echo button::create(['type'=>'button','title'=>$text['button-edit'],'icon'=>$_SESSION['theme']['button_icon_edit'],'link'=>$list_row_url]);
				echo "	</td>\n";
			}
			echo "</tr>\n";
			$x++;
		}
		unset($rest_api_endpoint);
	}

	echo "</table>\n";
	echo "<br />\n";
	echo "<div align='center'>".$paging_controls."</div>\n";
	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";
	echo "</form>\n";

//include the footer
	require_once "resources/footer.php";

?>

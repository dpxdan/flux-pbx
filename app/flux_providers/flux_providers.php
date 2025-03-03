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
	Portions created by the Initial Developer are Copyright (C) 2010-2019
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
	require_once "resources/paging.php";

//check permissions
	if (permission_exists('flux_provider_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get posted data
	if (is_array($_POST['flux_providers'])) {
		$action = $_POST['action'];
		$search = $_POST['search'];
		$flux_providers = $_POST['flux_providers'];
	}

//process the http post data by action
	if ($action != '' && is_array($flux_providers) && @sizeof($flux_providers) != 0) {
		switch ($action) {
			case 'copy':
				$obj = new flux_providers;
				$obj->copy($flux_providers);
				break;
			case 'toggle':
				$obj = new flux_providers;
				$obj->toggle($flux_providers);
				break;
			case 'delete':
				$obj = new flux_providers;
				$obj->delete($flux_providers);
				break;
		}

		header('Location: flux_providers.php'.($search != '' ? '?search='.urlencode($search) : null));
		exit;
	}

//get order and order by
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];

//add the search term
	if (isset($_GET["search"])) {
		$search = strtolower($_GET["search"]);
	}

//get total domain ring group count
	$sql = "select count(*) from v_flux_providers ";
	$sql .= "where domain_uuid = :domain_uuid ";
	$parameters['domain_uuid'] = $domain_uuid;
	$database = new database;
	$total_ring_groups = $database->select($sql, $parameters, 'column');
	$num_rows = $total_ring_groups;

//get filtered ring group count
	if ($search) {
		$sql = "select count(*) from v_flux_providers where true ";
		if ($_GET['show'] != "all" || !permission_exists('flux_provider_all')) {
			$sql .= "and domain_uuid = :domain_uuid ";
			$parameters['domain_uuid'] = $domain_uuid;
		}
		if (isset($search)) {
			$sql .= "and (";
			$sql .= "lower(flux_provider_name) like :search ";
			$sql .= "or lower(flux_provider_api_url) like :search ";
			$sql .= "or lower(flux_provider_description) like :search ";
			$sql .= "or lower(flux_provider_enabled) like :search ";
			$sql .= "or lower(flux_provider_auth_type) like :search ";
			$sql .= ") ";
			$parameters['search'] = '%'.$search.'%';
		}
		$database = new database;
		$num_rows = $database->select($sql, $parameters, 'column');
	}
	unset($sql, $parameters);

//prepare to page the results
	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	$param = $search ? "&search=".$search : null;
	$param = ($_GET['show'] == "all" && permission_exists('flux_provider_all')) ? "&show=all" : null;
	$page = is_numeric($_GET['page']) ? $_GET['page'] : 0;
	list($paging_controls, $rows_per_page) = paging($num_rows, $param, $rows_per_page);
	list($paging_controls_mini, $rows_per_page) = paging($num_rows, $param, $rows_per_page, true);
	$offset = $rows_per_page * $page;

//get the list
	$sql = "select * from v_flux_providers where true ";
	if ($_GET['show'] != "all" || !permission_exists('flux_provider_all')) {
		$sql .= "and domain_uuid = :domain_uuid ";
		$parameters['domain_uuid'] = $domain_uuid;
	}
	if (isset($search)) {
		$sql .= "and (";
		$sql .= "lower(flux_provider_name) like :search ";
		$sql .= "or lower(flux_provider_api_url) like :search ";
		$sql .= "or lower(flux_provider_description) like :search ";
		$sql .= "or lower(flux_provider_enabled) like :search ";
		$sql .= "or lower(flux_provider_auth_type) like :search ";
		$sql .= ") ";
		$parameters['search'] = '%'.$search.'%';
	}
	$sql .= ($order_by) ? order_by($order_by, $order) : "order by flux_provider_name asc, flux_provider_auth_type asc ";
	$sql .= limit_offset($rows_per_page, $offset);
	$flux_providers = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//additional includes
	$document['title'] = $text['title-flux_providers'];
	require_once "resources/header.php";

//show the content
	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>".$text['title-flux_providers']." (".$num_rows.")</b></div>\n";
	echo "	<div class='actions'>\n";
	if (permission_exists('flux_provider_add') && (!is_numeric($_SESSION['limit']['flux_providers']['numeric']) || ($total_ring_groups < $_SESSION['limit']['flux_providers']['numeric']))) {
		echo button::create(['type'=>'button','label'=>$text['button-add'],'icon'=>$_SESSION['theme']['button_icon_add'],'id'=>'btn_add','link'=>'flux_provider_edit.php']);
	}
	if (permission_exists('flux_provider_add') && $flux_providers && (!is_numeric($_SESSION['limit']['flux_providers']['numeric']) || ($total_ring_groups < $_SESSION['limit']['flux_providers']['numeric']))) {
		echo button::create(['type'=>'button','label'=>$text['button-copy'],'icon'=>$_SESSION['theme']['button_icon_copy'],'name'=>'btn_copy','onclick'=>"modal_open('modal-copy','btn_copy');"]);
	}
	if (permission_exists('flux_provider_edit') && $flux_providers) {
		echo button::create(['type'=>'button','label'=>$text['button-toggle'],'icon'=>$_SESSION['theme']['button_icon_toggle'],'name'=>'btn_toggle','onclick'=>"modal_open('modal-toggle','btn_toggle');"]);
	}
		if (permission_exists('flux_provider_user_view')) {
		echo button::create(['type'=>'button','label'=>$text['button-users'],'icon'=>'users','link'=>'flux_providers_users.php']);
	}
	if (permission_exists('flux_provider_delete') && $flux_providers) {
		echo button::create(['type'=>'button','label'=>$text['button-delete'],'icon'=>$_SESSION['theme']['button_icon_delete'],'name'=>'btn_delete','onclick'=>"modal_open('modal-delete','btn_delete');"]);
	}
	echo 		"<form id='form_search' class='inline' method='get'>\n";
	if (permission_exists('flux_provider_all')) {
		if ($_GET['show'] == 'all') {
			echo "		<input type='hidden' name='show' value='all'>";
		}
		else {
			echo button::create(['type'=>'button','label'=>$text['button-show_all'],'icon'=>$_SESSION['theme']['button_icon_all'],'link'=>'?show=all']);
		}
	}
	echo 		"<input type='text' class='txt list-search' name='search' id='search' value=\"".escape($search)."\" placeholder=\"".$text['label-search']."\" onkeydown=''>";
	echo button::create(['label'=>$text['button-search'],'icon'=>$_SESSION['theme']['button_icon_search'],'type'=>'submit','id'=>'btn_search']);
	//echo button::create(['label'=>$text['button-reset'],'icon'=>$_SESSION['theme']['button_icon_reset'],'type'=>'button','id'=>'btn_reset','link'=>'flux_providers.php','style'=>($search == '' ? 'display: none;' : null)]);
	if ($paging_controls_mini != '') {
		echo 	"<span style='margin-left: 15px;'>".$paging_controls_mini."</span>";
	}
	echo "		</form>\n";
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	if (permission_exists('flux_provider_add') && $flux_providers && (!is_numeric($_SESSION['limit']['flux_providers']['numeric']) || ($total_ring_groups < $_SESSION['limit']['flux_providers']['numeric']))) {
		echo modal::create(['id'=>'modal-copy','type'=>'copy','actions'=>button::create(['type'=>'button','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_copy','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('copy'); list_form_submit('form_list');"])]);
	}
	if (permission_exists('flux_provider_edit') && $flux_providers) {
		echo modal::create(['id'=>'modal-toggle','type'=>'toggle','actions'=>button::create(['type'=>'button','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_toggle','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('toggle'); list_form_submit('form_list');"])]);
	}
	if (permission_exists('flux_provider_delete') && $flux_providers) {
		echo modal::create(['id'=>'modal-delete','type'=>'delete','actions'=>button::create(['type'=>'button','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_delete','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('delete'); list_form_submit('form_list');"])]);
	}

	echo $text['description']."\n";
	echo "<br /><br />\n";

	echo "<form id='form_list' method='post'>\n";
	echo "<input type='hidden' id='action' name='action' value=''>\n";
	echo "<input type='hidden' name='search' value=\"".escape($search)."\">\n";

	echo "<table class='list'>\n";
	echo "<tr class='list-header'>\n";
	if (permission_exists('flux_provider_add') || permission_exists('flux_provider_edit') || permission_exists('flux_provider_delete')) {
		echo "	<th class='checkbox'>\n";
		echo "		<input type='checkbox' id='checkbox_all' name='checkbox_all' onclick='list_all_toggle();' ".($flux_providers ?: "style='visibility: hidden;'").">\n";
		echo "	</th>\n";
	}
	if ($_GET['show'] == "all" && permission_exists('flux_provider_all')) {
		echo th_order_by('domain_name', $text['label-domain'], $order_by, $order);
	}
	echo th_order_by('flux_provider_name', $text['label-provider_name'], $order_by, $order);
	echo th_order_by('flux_provider_api_url', $text['label-provider_api_url'], $order_by, $order);
	echo th_order_by('flux_provider_auth_type', $text['label-provider_auth_type'], $order_by, $order);
	echo th_order_by('flux_provider_auth_method', $text['label-provider_auth_method'], $order_by, $order);
	echo th_order_by('flux_provider_enabled', $text['label-enabled'], $order_by, $order, null, "class='center'");
	echo th_order_by('flux_provider_description', $text['header-description'], $order_by, $order, null, "class='hide-sm-dn'");
	if (permission_exists('flux_provider_edit') && $_SESSION['theme']['list_row_edit_button']['boolean'] == 'true') {
		echo "	<td class='action-button'>&nbsp;</td>\n";
	}
	echo "</tr>\n";

	if (is_array($flux_providers) && @sizeof($flux_providers) != 0) {
		$x = 0;
		foreach ($flux_providers as $row) {
			if (permission_exists('flux_provider_edit')) {
				$list_row_url = "flux_provider_edit.php?id=".urlencode($row['flux_provider_uuid']);
			}
			echo "<tr class='list-row' href='".$list_row_url."'>\n";
			if (permission_exists('flux_provider_add') || permission_exists('flux_provider_edit') || permission_exists('flux_provider_delete')) {
				echo "	<td class='checkbox'>\n";
				echo "		<input type='checkbox' name='flux_providers[$x][checked]' id='checkbox_".$x."' value='true' onclick=\"if (!this.checked) { document.getElementById('checkbox_all').checked = false; }\">\n";
				echo "		<input type='hidden' name='flux_providers[$x][uuid]' value='".escape($row['flux_provider_uuid'])."' />\n";
				echo "	</td>\n";
			}
			if ($_GET['show'] == "all" && permission_exists('flux_provider_all')) {
				echo "	<td>".escape($_SESSION['domains'][$row['domain_uuid']]['domain_name'])."</td>\n";
			}
			echo "	<td>";
			if (permission_exists('flux_provider_edit')) {
				echo "<a href='".$list_row_url."' title=\"".$text['button-edit']."\">".escape($row['flux_provider_name'])."</a>";
			}
			else {
				echo escape($row['flux_provider_name']);
			}
			echo "	</td>\n";
			echo "	<td>".escape($row['flux_provider_api_url'])."&nbsp;</td>\n";
			echo "	<td>".escape($row['flux_provider_auth_type'])."&nbsp;</td>\n";
      echo "	<td>".escape($row['flux_provider_auth_method'])."&nbsp;</td>\n";
			if (permission_exists('flux_provider_edit')) {
				echo "	<td class='no-link center'>";
				echo button::create(['type'=>'submit','class'=>'link','label'=>$text['label-'.$row['flux_provider_enabled']],'title'=>$text['button-toggle'],'onclick'=>"list_self_check('checkbox_".$x."'); list_action_set('toggle'); list_form_submit('form_list')"]);
			}
			else {
				echo "	<td class='center'>";
				echo $text['label-'.$row['flux_provider_enabled']];
			}
			echo "	</td>\n";
			echo "	<td class='description overflow hide-sm-dn'>".escape($row['flux_provider_description'])."&nbsp;</td>\n";
			if (permission_exists('flux_provider_edit') && $_SESSION['theme']['list_row_edit_button']['boolean'] == 'true') {
				echo "	<td class='action-button'>";
				echo button::create(['type'=>'button','title'=>$text['button-edit'],'icon'=>$_SESSION['theme']['button_icon_edit'],'link'=>$list_row_url]);
				echo "	</td>\n";
			}
			echo "</tr>\n";
			$x++;
		}
		unset($flux_providers);
	}

	echo "</table>\n";
	echo "<br />\n";
	echo "<div align='center'>".$paging_controls."</div>\n";

	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";

	echo "</form>\n";

//include the footer
	require_once "resources/footer.php";

?>

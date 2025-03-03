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
	Portions created by the Initial Developer are Copyright (C) 2016
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
	if (permission_exists('flux_provider_user_view')) {
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
	if (is_array($_POST['flux_providers_users'])) {
		$action = $_POST['action'];
		$search = $_POST['search'];
		$flux_providers_users = $_POST['flux_providers_users'];
	}

//process the http post data by action
	if ($action != '' && is_array($flux_providers_users) && @sizeof($flux_providers_users) != 0) {
		switch ($action) {
			case 'copy':
				$obj = new flux_providers;
				$obj->copy($flux_providers_users);
				break;
			case 'toggle':
				$obj = new flux_providers;
				$obj->toggle($flux_providers_users);
				break;
			case 'delete':
				$obj = new flux_providers;
				$obj->delete($flux_providers_users);
				break;
		}

		header('Location: flux_providers.php'.($search != '' ? '?search='.urlencode($search) : null));
		exit;
	}

//get variables used to control the order
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];

//add the search term
	$search = strtolower($_GET["search"]);
	if (strlen($search) > 0) {
		$sql_where = "where (";
		$sql_where .= "lower(username) like :search ";
		$sql_where .= "or lower(flux_provider_user_enabled) like :search ";
		$sql_where .= "or lower(flux_provider_user_description) like :search ";
		$sql_where .= ")";
		$parameters['search'] = '%'.$search.'%';
	}

//prepare to page the results
	$sql = "select count(*) from view_flux_providers ";
	$sql .= $sql_where;
	$database = new database;
	$num_rows = $database->select($sql, $parameters, 'column');

//prepare to page the results
	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	$param = "&search=".$search;
	$page = $_GET['page'];
	if (strlen($page) == 0) { $page = 0; $_GET['page'] = 0; }
	list($paging_controls, $rows_per_page) = paging($num_rows, $param, $rows_per_page);
	list($paging_controls_mini, $rows_per_page) = paging($num_rows, $param, $rows_per_page, true);
	$offset = $rows_per_page * $page;

//get the list
	$sql = str_replace('count(*)', '*', $sql);
	$sql .= order_by($order_by, $order, 'username', 'asc');
	$sql .= limit_offset($rows_per_page, $offset);
	$database = new database;
	$result = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//include the header
	$document['title'] = $text['title-flux_providers_users'];
	require_once "resources/header.php";

//show the content
	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>".$text['title-flux_providers_users']." (".$num_rows.")</b></div>\n";
	echo "	<div class='actions'>\n";
	echo button::create(['type'=>'button','label'=>$text['button-back'],'icon'=>$_SESSION['theme']['button_icon_back'],'id'=>'btn_back','style'=>'margin-right: 15px;','link'=>'flux_providers.php']);
	if (permission_exists('flux_provider_view')) {
		echo button::create(['type'=>'button','label'=>$text['button-providers'],'icon'=>'fax','link'=>'flux_providers.php']);
	}
	
	if (permission_exists('flux_provider_user_add')) {
		echo button::create(['type'=>'button','label'=>$text['button-add'],'icon'=>$_SESSION['theme']['button_icon_add'],'id'=>'btn_add','link'=>'flux_provider_user_edit.php']);
	}
	if (permission_exists('flux_provider_user_edit') && $result) {
		echo button::create(['type'=>'button','label'=>$text['button-toggle'],'icon'=>$_SESSION['theme']['button_icon_toggle'],'id'=>'btn_toggle','name'=>'btn_toggle','style'=>'display: none;','onclick'=>"modal_open('modal-toggle','btn_toggle');"]);
	}
	if (permission_exists('flux_provider_user_delete') && $result) {
		echo button::create(['type'=>'button','label'=>$text['button-delete'],'icon'=>$_SESSION['theme']['button_icon_delete'],'id'=>'btn_delete','name'=>'btn_delete','style'=>'display: none;','onclick'=>"modal_open('modal-delete','btn_delete');"]);
	}
	echo 		"<form id='form_search' class='inline' method='get'>\n";
	echo 		"<input type='text' class='txt list-search' name='search' id='search' value=\"".escape($search)."\" placeholder=\"".$text['label-search']."\" onkeydown='list_search_reset();'>";
	echo button::create(['label'=>$text['button-search'],'icon'=>$_SESSION['theme']['button_icon_search'],'type'=>'submit','id'=>'btn_search','style'=>($search != '' ? 'display: none;' : null)]);
	echo button::create(['label'=>$text['button-reset'],'icon'=>$_SESSION['theme']['button_icon_reset'],'type'=>'button','id'=>'btn_reset','link'=>'flux_providers_users.php','style'=>($search == '' ? 'display: none;' : null)]);
	if ($paging_controls_mini != '') {
		echo 	"<span style='margin-left: 15px;'>".$paging_controls_mini."</span>";
	}
	echo "		</form>\n";
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	if (permission_exists('flux_provider_user_edit') && $result) {
		echo modal::create(['id'=>'modal-toggle','type'=>'toggle','actions'=>button::create(['type'=>'button','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_toggle','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('toggle'); list_form_submit('form_list');"])]);
	}
	if (permission_exists('flux_provider_user_delete') && $result) {
		echo modal::create(['id'=>'modal-delete','type'=>'delete','actions'=>button::create(['type'=>'button','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_delete','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('delete'); list_form_submit('form_list');"])]);
	}

	echo $text['description_users']."\n";
	echo "<br /><br />\n";

	echo "<form id='form_list' method='post'>\n";
	echo "<input type='hidden' id='action' name='action' value=''>\n";
	echo "<input type='hidden' name='search' value=\"".escape($search)."\">\n";


	echo "<table class='list'>\n";
	echo "<tr class='list-header'>\n";
	if (permission_exists('flux_provider_user_edit') || permission_exists('flux_provider_user_delete')) {
		echo "	<th class='checkbox'>\n";
		echo "		<input type='checkbox' id='checkbox_all' name='checkbox_all' onclick='list_all_toggle(); checkbox_on_change(this);' ".($result ?: "style='visibility: hidden;'").">\n";
		echo "	</th>\n";
	}
	echo th_order_by('username', $text['label-username'], $order_by, $order);
	echo th_order_by('flux_provider_name', $text['label-provider_name'], $order_by, $order);
	echo th_order_by('flux_provider_auth_method', $text['label-provider_auth_method'], $order_by, $order);
	echo th_order_by('flux_provider_user_enabled', $text['label-enabled'], $order_by, $order, null, "class='center'");
	echo th_order_by('flux_provider_user_description', $text['label-description'], $order_by, $order, null, "class='hide-xs'");
	if (permission_exists('flux_provider_user_edit') && $_SESSION['theme']['list_row_edit_button']['boolean'] == 'true') {
		echo "	<td class='action-button'>&nbsp;</td>\n";
	}
	echo "</tr>\n";

	if (is_array($result) && @sizeof($result) != 0) {
		$x = 0;
		foreach($result as $row) {
			if (permission_exists('flux_provider_user_edit')) {
				$list_row_url = "flux_provider_user_edit.php?id=".urlencode($row['flux_provider_user_uuid']);
			}
			echo "<tr class='list-row' href='".$list_row_url."'>\n";
			if (permission_exists('flux_provider_user_edit') || permission_exists('flux_provider_user_delete')) {
				echo "	<td class='checkbox'>\n";
				echo "		<input type='checkbox' name='vendors[$x][checked]' id='checkbox_".$x."' value='true' onclick=\"checkbox_on_change(this); if (!this.checked) { document.getElementById('checkbox_all').checked = false; }\">\n";
				echo "		<input type='hidden' name='vendors[$x][uuid]' value='".escape($row['flux_provider_user_uuid'])."' />\n";
				echo "	</td>\n";
			}
			echo "	<td><a href='".$list_row_url."'>".escape($row['username'])."</a>&nbsp;</td>\n";
			echo "	<td>".escape($row['flux_provider_name'])."&nbsp;</td>\n";
			echo "	<td>".escape($row['flux_provider_auth_method'])."&nbsp;</td>\n";
			
			if (permission_exists('flux_provider_user_edit')) {
				echo "	<td class='no-link center'>";
				echo button::create(['type'=>'submit','class'=>'link','label'=>$text['label-'.$row['flux_provider_user_enabled']],'title'=>$text['button-toggle'],'onclick'=>"list_self_check('checkbox_".$x."'); list_action_set('toggle'); list_form_submit('form_list')"]);
			}
			else {
				echo "	<td class='center'>";
				echo $text['label-'.$row['flux_provider_user_enabled']];
			}
			
			echo "	<td class='description overflow hide-xs'>".escape($row['flux_provider_user_description'])."&nbsp;</td>\n";
			if (permission_exists('flux_provider_user_edit') && $_SESSION['theme']['list_row_edit_button']['boolean'] == 'true') {
				echo "	<td class='action-button'>";
				echo button::create(['type'=>'button','title'=>$text['button-edit'],'icon'=>$_SESSION['theme']['button_icon_edit'],'link'=>$list_row_url]);
				echo "	</td>\n";
			}
			echo "</tr>\n";
			$x++;
		}
	}
	unset($result);

	echo "</table>\n";
	echo "<br />\n";
	echo "<div align='center'>".$paging_controls."</div>\n";

	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";

	echo "</form>\n";

//include the footer
	require_once "resources/footer.php";

?>

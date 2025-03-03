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
require_once "resources/paging.php";


//check permissions
if (permission_exists('contact_view')) {
	//access granted
} else {
	echo "access denied";
	exit;
}


//add multi-lingual support
$language = new text;
$text = $language->get();
$textcalls = $language->get($_SESSION['domain']['language']['code'], 'core/user_settings');


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
	
	
	if (isset($num_contact_rows)) {
				   // if ($num_rows > 0) {
				   
				 /*  user_uuid,
				   domain_name,
				   username,
				   contact_uuid,
				   extension,
				   extension_uuid,
				   email_address*/
				    echo "<div id='contacts' class='container table-responsive' style='width: auto; text-align: center;'>".$textcalls['label-contacts']."\n";
					echo "<table id='example' class='table align-middle mb-0 table-light table-hover display responsive nowrap' cellpadding='0' cellspacing='0' border='0'>\n";
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
	?>
	<script src="/resources/datatables/datatables/jquery.dataTables.min.js"></script>
	<script src="/resources/datatables/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
	<script src="/resources/datatables/datatables-responsive/js/dataTables.responsive.js"></script>
	<script src="/resources/datatables/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
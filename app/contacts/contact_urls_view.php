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

//includes files
	require_once dirname(__DIR__, 2) . "/resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('contact_url_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//get the contact list
	$sql = "select * from v_contact_urls ";
	$sql .= "where domain_uuid = :domain_uuid ";
	$sql .= "and contact_uuid = :contact_uuid ";
	$sql .= "order by url_primary desc, url_label asc ";
	$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
	$parameters['contact_uuid'] = $contact_uuid ?? '';
	$database = new database;
	$contact_urls = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);

//show if exists
	if (!empty($contact_urls)) {

		//show the content
			echo "<div class='grid' style='grid-template-columns: 70px auto;'>\n";
			$x = 0;
			foreach ($contact_urls as $row) {
				echo "<div class='box contact-details-label'>".escape($row['url_label'])."</div>\n";
// 				($row['url_primary'] ? "&nbsp;<i class='fas fa-star fa-xs' style='float: right; margin-top: 0.5em; margin-right: -0.5em;' title=\"".$text['label-primary']."\"></i>" : null)."</td>\n";
				echo "<div class='box'><a href='".escape($row['url_address'])."' target='_blank'>".escape(str_replace(['http://','https://'], '', $row['url_address']))."</a></div>\n";
				$x++;
			}
			echo "</div>\n";
			unset($contact_urls);

	}

?>

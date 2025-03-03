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
	Portions created by the Initial Developer are Copyright (C) 20018-2021
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Daniel Paixao <daniel@flux.net.br>
*/

//check the domain cidr range 
	if (isset($_SESSION['domain']["cidr"]) && !defined('STDIN')) {
		$found = false;
		if (!empty($_SESSION['domain']["cidr"])) {
			foreach($_SESSION['domain']["cidr"] as $cidr) {
				if (check_cidr($cidr, $_SERVER['REMOTE_ADDR'])) {
					$found = true;
					break;
				}
			}
			unset($cidr);
		}
		if (!$found) {
			echo "access denied";
			exit;
		}
	}
 
 ?>

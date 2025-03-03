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
	Portions created by the Initial Developer are Copyright (C) 2008-2012
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Lucas Santos <daniel@flux.net.br>
*/

//session handling
	//start the session
		if (function_exists('session_start')) { 
			if (!isset($_SESSION)) {
				session_start();
				$_SESSION['session']['user_session_id'] = session_id();
				$_SESSION['session']['user_referer'] = $_SERVER['HTTP_REFERER'];
			}
		}
	//regenerate sessions to avoid session id attacks such as session fixation
		if (array_key_exists('security',$_SESSION) && $_SESSION['security']['session_rotate']['boolean'] == "true") {
			$_SESSION['session']['last_activity'] = time();
			if (!isset($_SESSION['session']['created'])) {
				$_SESSION['session']['created'] = time();
			} else if (time() - $_SESSION['session']['created'] > 1200) {
				// session started more than 2 hours ago
				session_regenerate_id(true);    // rotate the session id
				$_SESSION['session']['user_session_id'] = session_id();
				$_SESSION['session']['created'] = time();  // update creation time
				$_SESSION['session']['user_referer'] = $_SERVER['HTTP_REFERER'];
			}
		}

?>

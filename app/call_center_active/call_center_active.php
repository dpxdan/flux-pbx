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
	Portions created by the Initial Developer are Copyright (C) 2008-2023
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Daniel Paixao <daniel@flux.net.br>
*/

//includes files
	require_once dirname(__DIR__, 2) . "/resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('call_center_active_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get the queue_name and set it as a variable
	$queue_name = $_GET['queue_name'];
	$name = $_GET['name'] ?? null;

//get a new session array
	unset($_SESSION['queues']);
	unset($_SESSION['agents']);

//get the header
	$document['title'] = $text['title-call_center_queue_activity'];
	require_once "resources/header.php";

//add the ajax
	?><script type="text/javascript">
	function loadXmlHttp(url, id) {
		var f = this;
		f.xmlHttp = null;
		/*@cc_on @*/ // used here and below, limits try/catch to those IE browsers that both benefit from and support it
		/*@if(@_jscript_version >= 5) // prevents errors in old browsers that barf on try/catch & problems in IE if Active X disabled
		try {f.ie = window.ActiveXObject}catch(e){f.ie = false;}
		@end @*/
		if (window.XMLHttpRequest&&!f.ie||/^http/.test(window.location.href))
			f.xmlHttp = new XMLHttpRequest(); // Firefox, Opera 8.0+, Safari, others, IE 7+ when live - this is the standard method
		else if (/(object)|(function)/.test(typeof createRequest))
			f.xmlHttp = createRequest(); // ICEBrowser, perhaps others
		else {
			f.xmlHttp = null;
			 // Internet Explorer 5 to 6, includes IE 7+ when local //
			/*@cc_on @*/
			/*@if(@_jscript_version >= 5)
			try{f.xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");}
			catch (e){try{f.xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");}catch(e){f.xmlHttp=null;}}
			@end @*/
		}
		if(f.xmlHttp != null) {
			f.el = document.getElementById(id);
			f.xmlHttp.open("GET",url,true);
			f.xmlHttp.onreadystatechange = function(){f.stateChanged();};
			f.xmlHttp.send(null);
		}
	}

	loadXmlHttp.prototype.stateChanged=function () {
		var url = new URL(this.xmlHttp.responseURL);

		//logged out stop the refresh
		if (/login\.php$/.test(url.pathname)) {
			url.searchParams.set('path', '<?php echo $_SERVER['REQUEST_URI']; ?>');
			window.location.href = url.href;
			return;
		}

		if (this.xmlHttp.readyState == 4 && (this.xmlHttp.status == 200 || !/^http/.test(window.location.href))) {
			//this.el.innerHTML = this.xmlHttp.responseText;
			document.getElementById('ajax_response').innerHTML = this.xmlHttp.responseText;
		}

		//link table rows (except the last - the list_control_icons cell) on a table with a class of 'tr_hover', according to the href attribute of the <tr> tag
		$('.tr_hover tr,.list tr').each(function(i,e) {
			$(e).children('td:not(.list_control_icon,.list_control_icons,.tr_link_void,.list-row > .no-link,.list-row > .checkbox,.list-row > .button,.list-row > .action-button)').on('click', function() {
				var href = $(this).closest('tr').attr('href');
				var target = $(this).closest('tr').attr('target');
				if (href) {
					if (target) { window.open(href, target); }
					else { window.location = href; }
				}
			});
		});
	}

	var requestTime = function() {
		var url = 'call_center_active_inc.php?queue_name=<?php echo escape($queue_name); ?>&name=<?php echo urlencode(escape($name)); ?>';
		new loadXmlHttp(url, 'ajax_response');
		<?php

		//determine refresh rate
		$refresh_default = 1500; //milliseconds
		$refresh = is_numeric($_SESSION['call_center']['refresh']['numeric']) ? $_SESSION['call_center']['refresh']['numeric'] : $refresh_default;
		if ($refresh >= 0.5 && $refresh <= 120) { //convert seconds to milliseconds
			$refresh = $refresh * 1000;
		}
		else if ($refresh < 0.5 || ($refresh > 120 && $refresh < 500)) {
			$refresh = $refresh_default; //use default
		}
		else {
			//>= 500, must be milliseconds
		}

		//set the value for the refresh
		echo "setInterval(function(){new loadXmlHttp(url, 'ajax_reponse');}, ".$refresh.");";

		?>
	}

	if (window.addEventListener) {
		window.addEventListener('load', requestTime, false);
	}
	else if (window.attachEvent) {
		window.attachEvent('onload', requestTime);
	}

	function send_command(url) {
		if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp=new XMLHttpRequest();
		}
		else {// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.open("GET", url, false);
		xmlhttp.send(null);
		//document.getElementById('cmd_response').innerHTML=xmlhttp.responseText;
	}

	</script>

<?php

//show the response
	echo "<div id='ajax_response'></div>\n";
	echo "<br><br>";

//include the footer
	require_once "resources/footer.php";

?>

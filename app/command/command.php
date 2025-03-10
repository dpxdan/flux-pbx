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

//set the include path
	$conf = glob("{/usr/local/etc,/etc}/fluxpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

//includes files
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//permissions
	if (permission_exists('command_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

// load editor preferences/defaults
	$setting_size = !empty($_SESSION["editor"]["font_size"]["text"]) ? $_SESSION["editor"]["font_size"]["text"] : '12px';
	$setting_theme = !empty($_SESSION["editor"]["theme"]["text"]) ? $_SESSION["editor"]["theme"]["text"] : 'cobalt';
	$setting_invisibles = isset($_SESSION["editor"]["invisibles"]["boolean"]) && $_SESSION["editor"]["invisibles"]["boolean"] != '' ? $_SESSION["editor"]["invisibles"]["boolean"] : 'false';
	$setting_indenting = isset($_SESSION["editor"]["indent_guides"]["boolean"]) && $_SESSION["editor"]["indent_guides"]["boolean"] != '' ? $_SESSION["editor"]["indent_guides"]["boolean"] : 'false';
	$setting_numbering = isset($_SESSION["editor"]["line_numbers"]["boolean"]) && $_SESSION["editor"]["line_numbers"]["boolean"] != '' ? $_SESSION["editor"]["line_numbers"]["boolean"] : 'true';

//get the html values and set them as variables
	$handler = !empty($_REQUEST["handler"]) ? trim($_REQUEST["handler"]) : ((permission_exists('exec_switch')) ? 'switch' : null);
	$code = trim($_POST["code"] ?? '');
	$command = trim($_POST["command"] ?? '');

//check the captcha
	$command_authorized = false;
	if (strlen($code) > 0) {
		if (strtolower($_SESSION['captcha']) == strtolower($code)) {
			$command_authorized = true;
		}
	}

//set editor moder
	switch ($handler) {
		case 'php': $mode = 'php'; break;
		case 'sql': $mode = 'sql'; break;
		default: $mode = 'text';
	}

//show the header
	require_once "resources/header.php";
	$document['title'] = $text['title-command'];

//scripts and styles
	?>
	<script language="JavaScript" type="text/javascript">
		function submit_check() {
			document.getElementById('command').value = editor.getSession().getValue();
			if (document.getElementById('mode').value == 'sql') {
				$('#frm').prop('target', 'iframe').prop('action', 'sql_query_result.php?code='+ document.getElementById('code').value);
				$('#sql_response').show();
			}
			else {
				if (document.getElementById('command').value == '') {
					focus_editor();
					return false;
				}
				$('#frm').prop('target', '').prop('action', '');
			}
			return true;
		}

		function toggle_option(opt) {
			switch (opt) {
				case 'numbering': 	toggle_option_do('showLineNumbers'); toggle_option_do('fadeFoldWidgets'); break;
				case 'invisibles':	toggle_option_do('showInvisibles'); break;
				case 'indenting':	toggle_option_do('displayIndentGuides'); break;
			}
			focus_editor();
		}

		function toggle_option_do(opt_name) {
			var opt_val = editor.getOption(opt_name);
			editor.setOption(opt_name, ((opt_val) ? false : true));
		}

		function insert_clip(before, after) {
			var selected_text = editor.session.getTextRange(editor.getSelectionRange());
			editor.insert(before + selected_text + after);
			focus_editor();
		}

		function focus_editor() {
			editor.focus();
		}

		function set_handler(handler) {
			switch (handler) {
				<?php if (permission_exists('exec_switch')) { ?>
					case 'switch':
						document.getElementById('description').innerHTML = "<?php echo $text['description-switch'];?>";
						editor.getSession().setMode('ace/mode/text');
						$('#mode option[value=text]').prop('selected',true);
						<?php if (permission_exists('exec_sql')) { ?>
							$('.sql_controls').hide();
							document.getElementById('sql_type').selectedIndex = 0;
							document.getElementById('table_name').selectedIndex = 0;
							$('#iframe').prop('src','');
							$('#sql_response').hide();
						<?php } ?>
						$('#response').show();
						break;
				<?php } ?>
				<?php if (permission_exists('command_php')) { ?>
					case 'php':
						document.getElementById('description').innerHTML = "<?php echo $text['description-php'];?>";
						editor.getSession().setMode({path:'ace/mode/php', inline:true}); //highlight without opening tag
						$('#mode option[value=php]').prop('selected',true);
						<?php if (permission_exists('exec_sql')) { ?>
							$('.sql_controls').hide();
							document.getElementById('sql_type').selectedIndex = 0;
							document.getElementById('table_name').selectedIndex = 0;
							$('#iframe').prop('src','');
							$('#sql_response').hide();
						<?php } ?>
						$('#response').show();
						break;
				<?php } ?>
				<?php if (permission_exists('command_shell')) { ?>
					case 'shell':
						document.getElementById('description').innerHTML = "<?php echo $text['description-shell'];?>";
						editor.getSession().setMode('ace/mode/text');
						$('#mode option[value=text]').prop('selected',true);
						$('#response').show();
						break;
				<?php } ?>
				default:
					break;
			}
			focus_editor();
		}

		function reset_editor() {
			editor.getSession().setValue('');
			$('#command').val('');
			$('#response').hide();
			focus_editor();
		}
	</script>
	<style>
		img.control {
			cursor: pointer;
			width: auto;
			height: 23px;
			border: none;
			opacity: 0.5;
			}

		img.control:hover {
			opacity: 1.0;
			}

		div#editor {
			box-shadow: 0 3px 10px #333;
			text-align: left;
			width: 100%;
			height: calc(100% - 30px);
			font-size: 12px;
			}
	</style>

<?php

//generate the captcha image
	$_SESSION['captcha'] = generate_password(7, 2);
	$captcha = new captcha;
	$captcha->code = $_SESSION['captcha'];
	$image_base64 = $captcha->image_base64();

//show the header
	echo "<form method='post' name='frm' id='frm' action='exec.php' style='margin: 0;' onsubmit='return submit_check();'>\n";
	echo "<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
	echo "	<tr>";
	echo "		<td valign='top' align='left' width='50%'>";
	echo "			<b>".$text['title-command']."</b>\n";
	echo "		</td>";
	echo "		<td valign='top' align='right' nowrap='nowrap'>";

	//add the captcha
	echo "				<img src=\"data:image/png;base64, ".$image_base64."\" /><input type='text' class='txt' style='width: 150px; margin-left: 15px;' name='code' id='code' value=''>\n";
	echo "				&nbsp; &nbsp; &nbsp;\n";

	if (permission_exists('command_switch') || permission_exists('command_php') || 
permission_exists('command_shell')) {
		echo "				<select name='handler' id='handler' class='formfld' style='width:100px;' onchange=\"handler=this.value;set_handler(this.value);\">\n";
		if (permission_exists('command_switch')) { echo "<option value='switch' ".(($handler == 
'switch') ? "selected='selected'" : null).">".$text['label-switch']."</option>\n"; }
		if (permission_exists('command_php')) { echo "<option value='php' ".(($handler == 'php') ? 
"selected='selected'" : null).">".$text['label-php']."</option>\n"; }
		if (permission_exists('command_shell')) { echo "<option value='shell' ".(($handler == 'shell') ? 
"selected='selected'" : null).">".$text['label-shell']."</option>\n"; }
		echo "				</select>\n";
	}

	echo "					<input type='button' class='btn' style='margin-top: 0px;' title=\"".$text['button-execute']." [Ctrl+Enter]\" value=\"    ".$text['button-execute']."    \" onclick=\"$('form#frm').submit();\">";
	echo "					<input type='button' class='btn' style='margin-top: 0px;' title=\"\" value=\"    ".$text['button-reset']."    \" onclick=\"reset_editor();\">";

	echo "		</td>";
	echo "	</tr>";
	echo "	<tr><td colspan='2'>\n";
	echo 			$text['description-command']."\n";
	echo "	</tr>\n";
	echo "</table>";
	echo "<br>";

//html form
	echo "<input type='hidden' name='id' value='".escape($_REQUEST['id'])."'>\n"; //sql db id
	echo "<textarea name='command' id='command' style='display: none;'></textarea>";
	echo "<table cellpadding='0' cellspacing='0' border='0' style='width: 100%;'>\n";
	echo "	<tr>";
	echo "		<td style='width: 280px;' valign='top' nowrap>";

	echo "			<table cellpadding='0' cellspacing='0' border='0' width='100%' height='100%'>";
	if (permission_exists('edit_view') && file_exists($_SERVER["PROJECT_ROOT"]."/app/edit/")) {
		echo "			<tr>";
		echo "				<td valign='top' height='100%'>";
		echo "					<iframe id='clip_list' src='".PROJECT_PATH."/app/edit/clip_list.php' style='border: none; border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; height: calc(100% - 2px); width: calc(100% - 15px);'></iframe>\n";
		echo "				</td>";
		echo "			</tr>";
	}
	echo "			</table>";

	echo "		</td>";
	echo "		<td valign='top' style='height: 400px;'>"
	?>
	<table cellpadding='0' cellspacing='0' border='0' style='width: 100%;'>
		<tr>
			<td valign='middle' style='padding: 0 6px;' width='100%'><span id='description'><?php echo $text['description-'.$handler]; ?></span></td>
			<td valign='middle' style='padding: 0;'><img src='resources/images/blank.gif' style='width: 1px; height: 30px; border: none;'></td>
			<td valign='middle' style='padding-left: 6px;'><img src='resources/images/icon_numbering.png' title='Toggle Line Numbers' class='control' onclick="toggle_option('numbering');"></td>
			<td valign='middle' style='padding-left: 6px;'><img src='resources/images/icon_invisibles.png' title='Toggle Invisibles' class='control' onclick="toggle_option('invisibles');"></td>
			<td valign='middle' style='padding-left: 6px;'><img src='resources/images/icon_indenting.png' title='Toggle Indent Guides' class='control' onclick="toggle_option('indenting');"></td>
			<!--<td valign='middle' style='padding-left: 6px;'><img src='resources/images/icon_replace.png' title='Show Find/Replace [Ctrl+H]' class='control' onclick="editor.execCommand('replace');"></td>-->
			<td valign='middle' style='padding-left: 6px;'><img src='resources/images/icon_goto.png' title='Show Go To Line' class='control' onclick="editor.execCommand('gotoline');"></td>
			<td valign='middle' style='padding-left: 10px;'>
				<select id='mode' style='height: 23px;' onchange="editor.getSession().setMode((this.options[this.selectedIndex].value == 'php') ? {path:'ace/mode/php', inline:true} : 'ace/mode/' + this.options[this.selectedIndex].value); focus_editor();">
					<?php
					$modes['php'] = 'PHP';
					$modes['css'] = 'CSS';
					$modes['html'] = 'HTML';
					$modes['javascript'] = 'JS';
					$modes['json'] = 'JSON';
					$modes['ini'] = 'Conf';
					$modes['lua'] = 'Lua';
					$modes['text'] = 'Text';
					$modes['xml'] = 'XML';
					foreach ($modes as $value => $label) {
						$selected = $value == $mode ? 'selected' : null;
						echo "<option value='".$value."' ".$selected.">".escape($label)."</option>\n";
					}
					?>
				</select>
			</td>
			<td valign='middle' style='padding-left: 4px;'>
				<select id='size' style='height: 23px;' onchange="document.getElementById('editor').style.fontSize = this.options[this.selectedIndex].value; focus_editor();">
					<?php
					$sizes = explode(',','9px,10px,11px,12px,14px,16px,18px,20px');
					if (!in_array($setting_size, $sizes)) {
						echo "<option value='".$setting_size."'>".escape($setting_size)."</option>\n";
						echo "<option value='' disabled='disabled'></option>\n";
					}
					foreach ($sizes as $size) {
						$selected = ($size == $setting_size) ? 'selected' : null;
						echo "<option value='".$size."' ".$selected.">".escape($size)."</option>\n";
					}
					?>
				</select>
			</td>
			<td valign='middle' style='padding-left: 4px; padding-right: 0px;'>
				<select id='theme' style='height: 23px;' onchange="editor.setTheme('ace/theme/' + this.options[this.selectedIndex].value); focus_editor();">
					<?php
					$themes['Light']['chrome']= 'Chrome';
					$themes['Light']['clouds']= 'Clouds';
					$themes['Light']['crimson_editor']= 'Crimson Editor';
					$themes['Light']['dawn']= 'Dawn';
					$themes['Light']['dreamweaver']= 'Dreamweaver';
					$themes['Light']['eclipse']= 'Eclipse';
					$themes['Light']['github']= 'GitHub';
					$themes['Light']['iplastic']= 'IPlastic';
					$themes['Light']['solarized_light']= 'Solarized Light';
					$themes['Light']['textmate']= 'TextMate';
					$themes['Light']['tomorrow']= 'Tomorrow';
					$themes['Light']['xcode']= 'XCode';
					$themes['Light']['kuroir']= 'Kuroir';
					$themes['Light']['katzenmilch']= 'KatzenMilch';
					$themes['Light']['sqlserver']= 'SQL Server';
					$themes['Dark']['ambiance']= 'Ambiance';
					$themes['Dark']['chaos']= 'Chaos';
					$themes['Dark']['clouds_midnight']= 'Clouds Midnight';
					$themes['Dark']['cobalt']= 'Cobalt';
					$themes['Dark']['idle_fingers']= 'idle Fingers';
					$themes['Dark']['kr_theme']= 'krTheme';
					$themes['Dark']['merbivore']= 'Merbivore';
					$themes['Dark']['merbivore_soft']= 'Merbivore Soft';
					$themes['Dark']['mono_industrial']= 'Mono Industrial';
					$themes['Dark']['monokai']= 'Monokai';
					$themes['Dark']['pastel_on_dark']= 'Pastel on dark';
					$themes['Dark']['solarized_dark']= 'Solarized Dark';
					$themes['Dark']['terminal']= 'Terminal';
					$themes['Dark']['tomorrow_night']= 'Tomorrow Night';
					$themes['Dark']['tomorrow_night_blue']= 'Tomorrow Night Blue';
					$themes['Dark']['tomorrow_night_bright']= 'Tomorrow Night Bright';
					$themes['Dark']['tomorrow_night_eighties']= 'Tomorrow Night 80s';
					$themes['Dark']['twilight']= 'Twilight';
					$themes['Dark']['vibrant_ink']= 'Vibrant Ink';
					foreach ($themes as $optgroup => $theme) {
						echo "<optgroup label='".$optgroup."'>\n";
						foreach ($theme as $value => $label) {
							$selected = strtolower($label) == strtolower($setting_theme) ? 'selected' : null;
							echo "<option value='".$value."' ".$selected.">".escape($label)."</option>\n";
						}
						echo "</optgroup>\n";
					}
					?>
				</select>
			</td>
		</tr>
	</table>
	<div id='editor'><?php echo $command; ?></div>

	<?php
	echo "		</td>";
	echo "	</tr>\n";
	echo "</table>";
	echo "</form>";
	echo "<br /><br />";
	?>

	<script type="text/javascript" src="<?php echo PROJECT_PATH; ?>/resources/ace/ace.js" charset="utf-8"></script>
	<script type="text/javascript">
		//load ace editor
			var editor = ace.edit("editor");
			editor.setOptions({
				mode: 'ace/mode/<?php echo $mode;?>',
				theme: 'ace/theme/'+document.getElementById('theme').options[document.getElementById('theme').selectedIndex].value,
				selectionStyle: 'text',
				cursorStyle: 'smooth',
				showInvisibles: <?php echo $setting_invisibles;?>,
				displayIndentGuides: <?php echo $setting_indenting;?>,
				showLineNumbers: <?php echo $setting_numbering;?>,
				showGutter: true,
				scrollPastEnd: true,
				fadeFoldWidgets: <?php echo $setting_numbering;?>,
				showPrintMargin: false,
				highlightGutterLine: false,
				useSoftTabs: false
				});
			<?php if ($mode == 'php') { ?>
				editor.getSession().setMode({path:'ace/mode/php', inline:true});
			<?php } ?>
			document.getElementById('editor').style.fontSize='<?php echo escape($setting_size);?>';
			focus_editor();

		//keyboard shortcut to execute command
			<?php key_press('ctrl+enter', 'down', 'window', null, null, "$('form#frm').submit();", false); ?>

		//remove certain keyboard shortcuts
			editor.commands.bindKey("Ctrl-T", null); //disable transpose letters - prefer new browser tab
			editor.commands.bindKey("Ctrl-F", null); //disable find - control broken with bootstrap
			editor.commands.bindKey("Ctrl-H", null); //disable replace - control broken with bootstrap
	</script>

<?php

//show the result
	if (is_array($_POST)) {
		if ($command != '') {
			$result = '';
			switch ($handler) {
				case 'shell':
					if (permission_exists('command_shell') && $command_authorized) {
						$result = shell_exec($command . " 2>&1");
					}
					break;
				case 'php':
					if (permission_exists('command_php') && $command_authorized) {
						ob_start();
						eval($command);
						$result = ob_get_contents();
						ob_end_clean();
					}
					break;
				case 'switch':
					if (permission_exists('command_switch') && $command_authorized) {
						$fp = event_socket_create($_SESSION['event_socket_ip_address'], $_SESSION['event_socket_port'], $_SESSION['event_socket_password']);
						if ($fp) { 
							$result = event_socket_request($fp, 'api '.$command);
						}
					}
					break;
			}
			if ($result != '') {
				echo "<span id='response'>";
				echo "<b>".$text['label-response']."</b>\n";
				echo "<br /><br />\n";
				echo ($handler == 'switch') ? "<textarea style='width: 100%; height: 450px; font-family: monospace; padding: 15px;' wrap='off'>".$result."</textarea>\n" : "<pre>".escape($result)."</pre>";
				echo "</span>";
			}
		}
	}

//show the footer
	require_once "resources/footer.php";

?>

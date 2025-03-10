<?php

	//application details
		$apps[$x]['name'] = "Conferences Active";
		$apps[$x]['uuid'] = "c168c943-833a-c29c-7ef9-d1ee78810b71";
		$apps[$x]['category'] = "Switch";;
		$apps[$x]['subcategory'] = "";
		$apps[$x]['version'] = "1.0";
		$apps[$x]['license'] = "Mozilla Public License 1.1";
		$apps[$x]['url'] = "https://sbcdev4.flux.net.br";
		$apps[$x]['description']['en-us'] = "AJAX tool to view and manage all active callers in a conference room.";
		$apps[$x]['description']['en-gb'] = "AJAX tool to view and manage all active callers in a conference room.";
		$apps[$x]['description']['ar-eg'] = "";
		$apps[$x]['description']['de-at'] = "AJAX Wekzeug um alle aktive Anrufer in einem Konferenzraum anzuzeigen und zu verwalten.";
		$apps[$x]['description']['de-ch'] = "";
		$apps[$x]['description']['de-de'] = "AJAX Wekzeug um alle aktive Anrufer in einem Konferenzraum anzuzeigen und zu verwalten.";
		$apps[$x]['description']['es-cl'] = "Herramienta AJAX para ver y administrar todas las llamadas activas en una sala de conferencia.";
		$apps[$x]['description']['es-mx'] = "";
		$apps[$x]['description']['fr-ca'] = "Outil en AJAX pour voir et gerer toutes les conferences actives aux chambres.";
		$apps[$x]['description']['fr-fr'] = "Outil en AJAX pour voir et gérer toutes les conférences actives.";
		$apps[$x]['description']['he-il'] = "";
		$apps[$x]['description']['it-it'] = "";
		$apps[$x]['description']['nl-nl'] = "AJAX gereedschap om in een gespreksruimte de aktieve gebruikers te bekijken en beheren";
		$apps[$x]['description']['pl-pl'] = "";
		$apps[$x]['description']['pt-br'] = "";
		$apps[$x]['description']['pt-pt'] = "A ferramenta AJAX permite visualizar e gerir todas as chamadas ativas numa sala de conferências.";
		$apps[$x]['description']['ro-ro'] = "";
		$apps[$x]['description']['ru-ru'] = "AJAX утилита для просмотра и управления всеми активными участниками в конференции.";
		$apps[$x]['description']['sv-se'] = "";
		$apps[$x]['description']['uk-ua'] = "";

	//permission details
		$y=0;
		$apps[$x]['permissions'][$y]['name'] = "conference_active_view";
		$apps[$x]['permissions'][$y]['menu']['uuid'] = "2d857bbb-43b9-b8f7-a138-642868e0453a";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "conference_interactive_view";
		$apps[$x]['permissions'][$y]['groups'][] = "user";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "conference_interactive_lock";
		$apps[$x]['permissions'][$y]['groups'][] = "user";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "conference_interactive_kick";
		$apps[$x]['permissions'][$y]['groups'][] = "user";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "conference_interactive_energy";
		//$apps[$x]['permissions'][$y]['groups'][] = "user";
		//$apps[$x]['permissions'][$y]['groups'][] = "admin";
		//$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "conference_interactive_volume";
		//$apps[$x]['permissions'][$y]['groups'][] = "user";
		//$apps[$x]['permissions'][$y]['groups'][] = "admin";
		//$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "conference_interactive_gain";
		//$apps[$x]['permissions'][$y]['groups'][] = "user";
		//$apps[$x]['permissions'][$y]['groups'][] = "admin";
		//$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "conference_interactive_mute";
		$apps[$x]['permissions'][$y]['groups'][] = "user";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "conference_interactive_deaf";
		$apps[$x]['permissions'][$y]['groups'][] = "user";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "conference_interactive_video";
		$apps[$x]['permissions'][$y]['groups'][] = "user";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";

?>
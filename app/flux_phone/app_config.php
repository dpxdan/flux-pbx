<?php

	//application details
		$apps[$x]['name'] = "Flux Phone";
		$apps[$x]['uuid'] = "29ad51b0-6ab0-4d65-9394-629d1a34580b";
		$apps[$x]['category'] = "Switch";;
		$apps[$x]['subcategory'] = "";
		$apps[$x]['version'] = "1.0";
		$apps[$x]['license'] = "Mozilla Public License 1.1";
		$apps[$x]['url'] = "https://flux.net.br";
		$apps[$x]['description']['en-us'] = "Flux Phone.";
		$apps[$x]['description']['en-gb'] = "Flux Phone.";
		$apps[$x]['description']['ar-eg'] = "";
		$apps[$x]['description']['de-at'] = "Zeigt die Switch-Logs an.";
		$apps[$x]['description']['de-ch'] = "";
		$apps[$x]['description']['de-de'] = "Zeigt die Switch-Logs an.";
		$apps[$x]['description']['es-cl'] = "Muestra los registros del switch";
		$apps[$x]['description']['es-mx'] = "";
		$apps[$x]['description']['fr-ca'] = "";
		$apps[$x]['description']['fr-fr'] = "Ramal Flux.";
		$apps[$x]['description']['he-il'] = "";
		$apps[$x]['description']['it-it'] = "";
		$apps[$x]['description']['nl-nl'] = "Centale log tonen.";
		$apps[$x]['description']['pl-pl'] = "";
		$apps[$x]['description']['pt-br'] = "Ramal Flux.";
		$apps[$x]['description']['pt-pt'] = "Ramal Flux.";
		$apps[$x]['description']['ro-ro'] = "";
		$apps[$x]['description']['ru-ru'] = "";
		$apps[$x]['description']['sv-se'] = "";
		$apps[$x]['description']['uk-ua'] = "";

	//permission details
		$y=0;
		$apps[$x]['permissions'][$y]['name'] = "flux_phone_view";
		$apps[$x]['permissions'][$y]['menu']['uuid'] = "781ebbec-a55a-9d60-f7bb-506d0b6056b4";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
		$apps[$x]['permissions'][$y]['groups'][] = "user";
		$apps[$x]['permissions'][$y]['groups'][] = "agent";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "flux_phone_edit";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		
				
		//default settings details
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = "dbbadd02-f95d-480b-85d5-2a41cacacaca";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "flux_phone";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "wss_proxy";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "192.168.1.130";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "false";
		$apps[$x]['default_settings'][$y]['default_setting_description'] = "Ip Address or DNS name of WSS proxy (server)";
		$y++;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = "dbbadd02-f95d-480b-85d5-2a41cacacacb";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "flux_phone";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "wss_port";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "7443";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "false";
		$apps[$x]['default_settings'][$y]['default_setting_description'] = "Port of WSS proxy (server)";

?>
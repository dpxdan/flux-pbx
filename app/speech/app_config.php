<?php

	//application details
		$apps[$x]['name'] = 'Speech';
		$apps[$x]['uuid'] = 'dff26a0d-9439-4743-b787-765f1dd5ebfb';
		$apps[$x]['category'] = 'API';
		$apps[$x]['subcategory'] = '';
		$apps[$x]['version'] = '1.0';
		$apps[$x]['license'] = 'Mozilla Public License 1.1';
		$apps[$x]['url'] = 'https://flux.net.br';
		$apps[$x]['description']['en-us'] = 'Artificial Intelligence';

	//default settings
		$y=0;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = "c4751875-011d-4181-ba9a-8978ffd7497d";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "speech";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "enabled";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "boolean";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "true";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "false";
		$apps[$x]['default_settings'][$y]['default_setting_description'] = "Text to Speech API enabled.";
		$y++;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = "e7a77c36-92d1-4fb6-9db3-4f62866dbaf2";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "speech";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "engine";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "openai";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "false";
		$apps[$x]['default_settings'][$y]['default_setting_description'] = "Text to Speech API engine.";
		$y++;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = "eced068b-db30-4257-aa7c-6e2659271e4b";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "speech";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "api_key";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "false";
		$apps[$x]['default_settings'][$y]['default_setting_description'] = "Text to Speech API Key";
		$y++;

?>
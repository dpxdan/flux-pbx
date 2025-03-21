<?php

	//application details
		$apps[$x]['name'] = "Dialplan Manager";
		$apps[$x]['uuid'] = "742714e5-8cdf-32fd-462c-cbe7e3d655db";
		$apps[$x]['category'] = "Switch";
		$apps[$x]['subcategory'] = "";
		$apps[$x]['version'] = "1.0";
		$apps[$x]['license'] = "Mozilla Public License 1.1";
		$apps[$x]['url'] = "https://sbcdev4.flux.net.br";
		$apps[$x]['description']['en-us'] = "The dialplan is used to setup call destinations based on conditions and context. You can use the dialplan to send calls to gateways, auto attendants, external numbers, to scripts, or any destination.";
		$apps[$x]['description']['en-gb'] = "The dialplan is used to setup call destinations based on conditions and context. You can use the dialplan to send calls to gateways, auto attendants, external numbers, to scripts, or any destination.";
		$apps[$x]['description']['ar-eg'] = "";
		$apps[$x]['description']['de-at'] = "Der Wählplan wird verwendet um Anruf-Ziele anhand von Bedingungen und Kontext fest zu legen. Sie können den Wählplan verwenden, um Anrufe an Gateways, Automatische Zentralen, externe Rufnummern, Skripte oder andere Ziele weiterzuleiten.";
		$apps[$x]['description']['de-ch'] = "";
		$apps[$x]['description']['de-de'] = "Der Wählplan wird verwendet um Anruf-Ziele anhand von Bedingungen und Kontext fest zu legen. Sie können den Wählplan verwenden, um Anrufe an Gateways, Automatische Zentralen, externe Rufnummern, Skripte oder andere Ziele weiterzuleiten.";
		$apps[$x]['description']['es-cl'] = "El plan de marcado es usado para configurar los destinos de llamadas basados en condiciones y contexto. Usted puede usar los planes de marcado para enviar hacia pasarelas, IVR, números externos, scripts o cualquier otro destino.";
		$apps[$x]['description']['es-mx'] = "El plan de marcado es usado para configurar los destinos de llamadas basados en condiciones y contexto. Usted puede usar los planes de marcado para enviar hacia pasarelas, IVR, números externos, scripts o cualquier otro destino.";
		$apps[$x]['description']['fr-ca'] = "";
		$apps[$x]['description']['fr-fr'] = "Les dialplans son utilisés pour configurer les destinations d'appels selon des conditions et contextes.  Vous pouvez en utiliser pour diriger les appels vers les passerelles, IVR, numéross exterieurs ou tout autre destination.";
		$apps[$x]['description']['he-il'] = "";
		$apps[$x]['description']['it-it'] = "";
		$apps[$x]['description']['nl-nl'] = "Het kiesplan wordt gebruikt om oproep bestemmingen die aan de voorwaarden en context voldoet te kiezen. het kiespaln kan worden gebruikt om oproepen naar gateways, antwoordapparaten, externe nummers, scripts of enig andere bestemming te kiezen.";
		$apps[$x]['description']['pl-pl'] = "";
		$apps[$x]['description']['pt-br'] = "";
		$apps[$x]['description']['pt-pt'] = "O dialplan é usado para configurar destinos de chamada com base nas condições e contexto. Você pode usar o dialplan para enviar chamadas para gateways, atendedores automáticos, números externos, para scripts, ou qualquer destino.";
		$apps[$x]['description']['ro-ro'] = "";
		$apps[$x]['description']['ru-ru'] = "";
		$apps[$x]['description']['sv-se'] = "";
		$apps[$x]['description']['uk-ua'] = "";

	//destination details
		$y = 0;
		$apps[$x]['destinations'][$y]['type'] = "sql";
		$apps[$x]['destinations'][$y]['label'] = "dialplans";
		$apps[$x]['destinations'][$y]['name'] = "dialplans";
		$apps[$x]['destinations'][$y]['sql'] = "select d.domain_name, e.dialplan_name, e.dialplan_number as destination, e.dialplan_description as description from v_dialplans as e, v_domains as d ";
		$apps[$x]['destinations'][$y]['where'] = "where e.domain_uuid = '\${domain_uuid}' and e.domain_uuid = d.domain_uuid and e.dialplan_destination = 'true' and e.dialplan_number <> '' and e.dialplan_enabled = 'true' ";
		$apps[$x]['destinations'][$y]['order_by'] = "dialplan_order asc";
		$apps[$x]['destinations'][$y]['field']['domain_name'] = "domain_name";
		$apps[$x]['destinations'][$y]['field']['dialplan_name'] = "dialplan_name";
		$apps[$x]['destinations'][$y]['field']['destination'] = "destination";
		$apps[$x]['destinations'][$y]['field']['description'] = "description";
		//$apps[$x]['destinations'][$y]['select_value']['user_contact'] = "sofia/internal/sip:\$1\${regex(\${sofia_contact(\${destination}@\${domain_name})}|^[^@]+(.*)|%1)}";
		$apps[$x]['destinations'][$y]['select_value']['dialplan'] = "transfer:\${destination} XML \${domain_name}";
		$apps[$x]['destinations'][$y]['select_value']['ivr'] = "menu-exec-app:transfer \${destination} XML \${domain_name}";
		$apps[$x]['destinations'][$y]['select_label'] = "\${dialplan_name} \${description}";
		$y++;

	//default settings
		$y = 0;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = "d3c5a59d-e6c0-4ded-ac79-9a7fcd9e1269";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "dialplan";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "destination";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "destination_number";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
		$apps[$x]['default_settings'][$y]['default_setting_description'] = "Options: destination_number, \${sip_to_user}, \${sip_req_user}";
		$y++;

	//permission details
		$y=0;
		$apps[$x]['permissions'][$y]['name'] = "dialplan_view";
		$apps[$x]['permissions'][$y]['menu']['uuid'] = "b94e8bd9-9eb5-e427-9c26-ff7a6c21552a";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "dialplan_add";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "dialplan_edit";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "dialplan_delete";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "dialplan_global";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "dialplan_xml";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "dialplan_context";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "dialplan_detail_view";
		$apps[$x]['permissions'][$y]['menu']['uuid'] = "b94e8bd9-9eb5-e427-9c26-ff7a6c21552a";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "dialplan_detail_add";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "dialplan_detail_edit";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "dialplan_detail_delete";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "dialplan_domain";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "dialplan_all";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "dialplan_destinations";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";

	//cache details
		$apps[$x]['cache']['key'] = "dialplan.\${dialplan_context}";

	//schema details
		$y=0;
		$apps[$x]['db'][$y]['table']['name'] = "v_dialplans";
		$apps[$x]['db'][$y]['table']['parent'] = "";
		$z=0;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "domain_uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "foreign";
		$apps[$x]['db'][$y]['fields'][$z]['key']['reference']['table'] = "v_domains";
		$apps[$x]['db'][$y]['fields'][$z]['key']['reference']['field'] = "domain_uuid";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "primary";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "app_uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "hostname";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_context";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['search'] = 'true';
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_name";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['search'] = 'true';
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_number";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['search'] = 'true';
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_destination";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_continue";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['search'] = 'true';
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_xml";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_order";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "numeric";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_enabled";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_description";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['search'] = 'true';
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "insert_date";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = 'timestamptz';
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = 'date';
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = 'date';
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "insert_user";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "update_date";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = 'timestamptz';
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = 'date';
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = 'date';
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "update_user";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";

		$y++;
		$apps[$x]['db'][$y]['table']['name'] = "v_dialplan_details";
		$apps[$x]['db'][$y]['table']['parent'] = "v_dialplans";
		$z=0;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "domain_uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "foreign";
		$apps[$x]['db'][$y]['fields'][$z]['key']['reference']['table'] = "v_domains";
		$apps[$x]['db'][$y]['fields'][$z]['key']['reference']['field'] = "domain_uuid";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "foreign";
		$apps[$x]['db'][$y]['fields'][$z]['key']['reference']['table'] = "v_dialplans";
		$apps[$x]['db'][$y]['fields'][$z]['key']['reference']['field'] = "dialplan_uuid";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_detail_uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "primary";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_detail_tag";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_detail_type";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_detail_data";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_detail_break";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_detail_inline";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_detail_group";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "numeric";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_detail_order";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "numeric";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "dialplan_detail_enabled";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "boolean";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "insert_date";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = 'timestamptz';
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = 'date';
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = 'date';
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "insert_user";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "update_date";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = 'timestamptz';
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = 'date';
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = 'date';
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "update_user";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";

?>

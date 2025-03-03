<?php

	//application details
		$apps[$x]['name'] = "SIP Trunks";
		$apps[$x]['uuid'] = "9ebfede4-4091-426e-bf9e-521cd4ecd3d1";
		$apps[$x]['category'] = "Switch";
		$apps[$x]['subcategory'] = "SIP Trunks";
		$apps[$x]['version'] = "1.0";
		$apps[$x]['license'] = "BSD";
		$apps[$x]['url'] = "https://flux.net.br";
		$apps[$x]['description']['en-us'] = "Send calls to registered endpoint with phone number that was called.";
		$apps[$x]['description']['es-cl'] = "";
		$apps[$x]['description']['es-mx'] = "";
		$apps[$x]['description']['de-de'] = "";
		$apps[$x]['description']['de-ch'] = "";
		$apps[$x]['description']['de-at'] = "";
		$apps[$x]['description']['fr-fr'] = "";
		$apps[$x]['description']['fr-ca'] = "";
		$apps[$x]['description']['fr-ch'] = "";
		$apps[$x]['description']['pt-pt'] = "";
		$apps[$x]['description']['pt-br'] = "";

	//destination details
		$y = 0;
		$apps[$x]['destinations'][$y]['type'] = "sql";
		$apps[$x]['destinations'][$y]['label'] = "sip_trunks";
		$apps[$x]['destinations'][$y]['name'] = "sip_trunks";
		$apps[$x]['destinations'][$y]['sql'] = "select d.domain_name, e.extension, e.number_alias, e.description from v_extensions as e, v_domains as d ";
		$apps[$x]['destinations'][$y]['where'] = "where e.domain_uuid = '\${domain_uuid}' and e.domain_uuid = d.domain_uuid and e.enabled = 'true' ";
		$apps[$x]['destinations'][$y]['order_by'] = "extension asc";
		$apps[$x]['destinations'][$y]['field']['domain_name'] = "domain_name";
		$apps[$x]['destinations'][$y]['field']['destination'] = "number_alias,extension";
		$apps[$x]['destinations'][$y]['field']['description'] = "description";
		$apps[$x]['destinations'][$y]['select_value']['user_contact'] = "sofia/internal/sip:\$1\@\${regex(\${sofia_contact(\${destination}@\${domain_name})}|^[^@]*@(.*)$|%1)}";
		$apps[$x]['destinations'][$y]['select_value']['dialplan'] = "bridge:sofia/internal/sip:\$1@\${regex(\${sofia_contact(\${destination}@\${domain_name})}|^[^@]*@(.*)$|%1)}";
		$apps[$x]['destinations'][$y]['select_value']['ivr'] = "menu-exec-app:bridge sofia/internal/sip:\\$1@\${regex(\${sofia_contact(\${destination}@\${domain_name})}|^[^@]*@(.*)$|%1)}";
		$apps[$x]['destinations'][$y]['select_label'] = "\${destination} \${description}";
		$y++;

	//permission details
		$y=0;
		$apps[$x]['permissions'][$y]['name'] = "sip_trunk_destinations";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";

?>

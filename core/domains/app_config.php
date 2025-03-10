<?php

	//application details
		$apps[$x]['name'] = "Domains";
		$apps[$x]['uuid'] = "8b91605b-f6d2-42e6-a56d-5d1ded01bb44";
		$apps[$x]['category'] = "Core";
		$apps[$x]['subcategory'] = "";
		$apps[$x]['version'] = "1.0";
		$apps[$x]['license'] = "Mozilla Public License 1.1";
		$apps[$x]['url'] = "https://flux.net.br";
		$apps[$x]['description']['en-us'] = "Manage a single domain or multiple domains for multi-tenant.";
		$apps[$x]['description']['en-gb'] = "Manage a single domain or multiple domains for multi-tenant.";
		$apps[$x]['description']['ar-eg'] = "إدارة مجال واحد أو مجالات متعددة للمستأجرين المتعددين.";
		$apps[$x]['description']['de-at'] = "Verwalte eine einzelne Domäne oder mehrere Domänen für Multi-Mandanten.";
		$apps[$x]['description']['de-ch'] = "Verwalten Sie eine einzelne Domäne oder mehrere Domänen für mehrere Mandanten.";
		$apps[$x]['description']['de-de'] = "Verwalte eine einzelne Domäne oder mehrere Domänen für Multi-Mandanten.";
		$apps[$x]['description']['es-cl'] = "Administre un único dominio o múltiples dominios";
		$apps[$x]['description']['es-mx'] = "Administre un solo dominio o múltiples dominios para múltiples inquilinos.";
		$apps[$x]['description']['fr-ca'] = "Gérez un domaine unique ou plusieurs domaines pour le multi-locataire.";
		$apps[$x]['description']['fr-fr'] = "Gestion d'un domaine ou plusieurs dans le cas d'un système multi-parties.";
		$apps[$x]['description']['he-il'] = "נהל דומיין בודד או מספר דומיינים עבור ריבוי דיירים.";
		$apps[$x]['description']['it-it'] = "Gestisci un singolo dominio o più domini per multi-tenant.";
		$apps[$x]['description']['nl-nl'] = "Beheer een enkel domein of meerdere domeinen voor meerdere huurders.";
		$apps[$x]['description']['pl-pl'] = "Zarządzaj pojedynczą domeną lub wieloma domenami dla wielu dzierżawców.";
		$apps[$x]['description']['pt-br'] = "Gerencie um único ou múltiplos domínios para multi-locatários";
		$apps[$x]['description']['pt-pt'] = "Gerir um único domínio ou vários domínios para multi-tenant.";
		$apps[$x]['description']['ro-ro'] = "Gestionați un singur domeniu sau mai multe domenii pentru multi-locatari.";
		$apps[$x]['description']['ru-ru'] = "Управление одним доменом или несколькими доменами для нескольких пользователей";
		$apps[$x]['description']['sv-se'] = "Hantera en enda domän eller flera domäner för flera hyresgäster.";
		$apps[$x]['description']['uk-ua'] = "Керуйте одним або декількома доменами для кількох орендарів.";

	//permission details
		$y=0;
		$apps[$x]['permissions'][$y]['name'] = "domain_view";
		$apps[$x]['permissions'][$y]['menu']['uuid'] = "4fa7e90b-6d6c-12d4-712f-62857402b801";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "domain_add";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "domain_edit";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "domain_delete";
		//$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = 'domain_all';
		$apps[$x]['permissions'][$y]['groups'][] = 'superadmin';
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "domain_select";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;

	//default settings
		$y=0;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = "0bc44a93-b87c-414f-8584-f890dd06d28c";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "domain";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "country_code";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "numeric";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
		$apps[$x]['default_settings'][$y]['default_setting_description'] = "";

	//schema details
		$y=0;
		$apps[$x]['db'][$y]['table']['name'] = "v_domains";
		$apps[$x]['db'][$y]['table']['parent'] = "";
		$z=0;	
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "domain_uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "primary";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "domain_parent_uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "foreign";
		$apps[$x]['db'][$y]['fields'][$z]['key']['reference']['table'] = "v_domains";
		$apps[$x]['db'][$y]['fields'][$z]['key']['reference']['field'] = "domain_uuid";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "domain_name";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['search'] = "true";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "Enter the domain name.";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "domain_enabled";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "boolean";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['toggle'] = ['true','false'];
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "Set the status of the domain.";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "domain_description";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['search'] = "true";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "Enter the description.";
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

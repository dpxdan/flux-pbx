<?php

	//application details
		$apps[$x]['name'] = "Command";
		$apps[$x]['uuid'] = "1dd98ca6-95f1-e728-7e8f-137fe18dc23c";
		$apps[$x]['category'] = "System";
		$apps[$x]['subcategory'] = "Command";
		$apps[$x]['version'] = "1.1";
		$apps[$x]['license'] = "Mozilla Public License 1.1";
		$apps[$x]['url'] = "https://flux.net.br";
		$apps[$x]['description']['en-us'] = "Provides a conventient way to execute system, PHP, switch and SQL commands.";
		$apps[$x]['description']['ar-eg'] = "";
		$apps[$x]['description']['de-at'] = "Bietet eine praktische Möglichkeit system, PHP, Switch und SQL Befehle aus zu führen.";
		$apps[$x]['description']['de-ch'] = "";
		$apps[$x]['description']['de-de'] = "Bietet eine praktische Möglichkeit system, PHP, Switch und SQL Befehle aus zu führen.";
		$apps[$x]['description']['es-cl'] = "Provee un modo conveniente de ejecutar comandos de sistema, PHP o del switch.";
		$apps[$x]['description']['es-mx'] = "Provee un modo conveniente de ejecutar comandos de sistema, PHP o del switch.";
		$apps[$x]['description']['fr-ca'] = "Il offre un mode d'exécuter des commandes du système, PHP ou switch.";
		$apps[$x]['description']['fr-fr'] = "Offre un mode pour exécuter des commandes système, PHP ou switch.";
		$apps[$x]['description']['he-il'] = "";
		$apps[$x]['description']['it-it'] = "";
		$apps[$x]['description']['nl-nl'] = "Voorzie in een makelijke maniet om systeem, PHP, centrale en SQL commando's uit te voeren.";
		$apps[$x]['description']['pl-pl'] = "";
		$apps[$x]['description']['pt-br'] = "";
		$apps[$x]['description']['pt-pt'] = "Ofereçe uma forma conveniente para executar comandos de sistema, PHP e switch.";
		$apps[$x]['description']['ro-ro'] = "";
		$apps[$x]['description']['ru-ru'] = "";
		$apps[$x]['description']['sv-se'] = "";
		$apps[$x]['description']['uk-ua'] = "";

	//permission details
		$y=0;
		$apps[$x]['permissions'][$y]['name'] = "command_view";
		$apps[$x]['permissions'][$y]['menu']['uuid'] = "06493580-9131-ce57-23cd-d42d69dd8526";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "command_shell";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "command_php";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "command_switch";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;

?>

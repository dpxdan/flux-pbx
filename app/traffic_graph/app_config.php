<?php

	//application details
		$apps[$x]['name'] = "Traffic Graph";
		$apps[$x]['uuid'] = "99932b6e-6560-a472-25dd-22e196262187";		
		$apps[$x]['category'] = "System";
		$apps[$x]['subcategory'] = "";
		$apps[$x]['version'] = "1.0";
		$apps[$x]['license'] = "Mozilla Public License 1.1";
		$apps[$x]['url'] = "https://flux.net.br";
		$apps[$x]['description']['en-us'] = "Uses SVG to show the network traffic.";
		$apps[$x]['description']['en-gb'] = "Uses SVG to show the network traffic.";
		$apps[$x]['description']['ar-eg'] = "";
		$apps[$x]['description']['de-at'] = "Originate calls on Xpro with a URL.";
		$apps[$x]['description']['de-ch'] = "";
		$apps[$x]['description']['de-de'] = "Originate calls on Xpro with a URL.";
		$apps[$x]['description']['es-cl'] = "Originate calls on Xpro with a URL.";
		$apps[$x]['description']['es-mx'] = "";
		$apps[$x]['description']['fr-ca'] = "Originate calls on Xpro with a URL";
		$apps[$x]['description']['fr-fr'] = "Appeler à partir d'une URL";
		$apps[$x]['description']['he-il'] = "";
		$apps[$x]['description']['it-it'] = "";
		$apps[$x]['description']['nl-nl'] = "Start oproepen met een URL";
		$apps[$x]['description']['pl-pl'] = "";
		$apps[$x]['description']['pt-br'] = "Utiliza SVG para mostrar o tráfego de rede";
		$apps[$x]['description']['pt-pt'] = "Utiliza SVG para mostrar o tráfego de rede.";
		$apps[$x]['description']['ro-ro'] = "";
		$apps[$x]['description']['ru-ru'] = "Создание исходящих вызовов с помощью вызова URL";
		$apps[$x]['description']['sv-se'] = "";
		$apps[$x]['description']['uk-ua'] = "";
		
	

	//permission details
	    $y = 0;
		$apps[$x]['permissions'][$y]['name'] = "traffic_graph_view";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = 'traffic_graph_all';
		$apps[$x]['permissions'][$y]['groups'][] = 'superadmin';
		$y++;


?>
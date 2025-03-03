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
Portions created by the Initial Developer are Copyright (C) 2008-2020
the Initial Developer. All Rights Reserved.

Contributor(s):
Daniel Paixao <daniel@flux.net.br>
 */
 

$conf = glob("{/usr/local/etc,/etc}/fluxpbx/config.conf", GLOB_BRACE);
set_include_path(parse_ini_file($conf[0])['document.root']);

require_once "resources/require.php";
require_once "resources/check_auth.php";
//require_once "resources/paging.php";


//check permissions
if (permission_exists('flux_phone_view')) {
	//access granted
} else {
	echo "access denied";
	exit;
}

//add multi-lingual support
$language = new text;
$text = $language->get();
$textuser = $language->get($_SESSION['domain']['language']['code'], 'core/users');
$textcalls = $language->get($_SESSION['domain']['language']['code'], 'core/user_settings');

if (is_uuid($_GET['id'])) {
	$extension_uuid = $_GET['id'];
	

}

else {

	//normal user
	$sql = "select e.* ";
	$sql .= "from v_extensions as e, ";
	$sql .= "v_extension_users as eu ";
	$sql .= "where e.extension_uuid = eu.extension_uuid ";
	$sql .= "and eu.user_uuid = :user_uuid ";
	$sql .= "and e.domain_uuid = :domain_uuid ";
	$sql .= "and e.enabled = 'true' ";
	$sql .= "order by e.extension asc limit 1";
	$parameters['user_uuid'] = $_SESSION['user']['user_uuid'];

$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
$database = new database;
$extensions = $database->select($sql, $parameters, 'all');
unset($sql, $parameters);

}

date_default_timezone_set('America/Sao_Paulo');
function time_diff_conv($start, $s)
{
    $string="";
    $t = array( //suffixes
        'd' => 86400,
        'h' => 3600,
        'm' => 60,
    );
    $s = abs($s - $start);
    foreach($t as $key => &$val) {
        $$key = floor($s/$val);
        $s -= ($$key*$val);
        $string .= ($$key==0) ? '' : $$key . "$key ";
    }
    return $string . $s. 's';
}

$date = date('h:i');

if (!isset($extension_uuid)) {

if (is_array($extensions) && @sizeof($extensions) != 0) {
	foreach ($extensions as $row) {
		$extension_uuid = $row['extension_uuid'];
	}
}
}
$fp = event_socket_create($_SESSION['event_socket_ip_address'], $_SESSION['event_socket_port'], $_SESSION['event_socket_password']);

//retrieve outbound caller id from the (source) extension
$sql = "select * from view_fluxphone_api where domain_uuid = :domain_uuid and extension_uuid = :extension_uuid ";
$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
$parameters['extension_uuid'] = $extension_uuid;
$database = new database;
$result = $database->select($sql, $parameters, 'all');
foreach ($result as &$row) {
	$domain_name = $row['domain_name'];
	$extension = $row['extension'];
	$extension_login = $row['extension_login'];
	$extension_password = $row['extension_password'];
	$extension_context = $row['extension_context'];
	$outbound_caller_id_name = $row['outbound_caller_id_name'];
	$outbound_caller_id_number = $row['outbound_caller_id_number'];
	$call_group = $row['call_group'];
	$extension_enabled = $row['extension_enabled'];
	$user_uuid = $row['user_uuid'];
	$user_login = $row['user_login'];
	$user_password = $row['user_password'];
	$user_email = $row['user_email'];
	$user_status = $row['user_status'];
	$api_key = $row['api_key'];
	break; //limit to 1 row
}


$sqlws = "select domain_setting_value as wss_port ";
$sqlws .= "FROM v_domain_settings ";
$sqlws .= "WHERE domain_setting_category = 'flux_phone' ";
$sqlws .= "AND domain_setting_subcategory = 'wss_port' ";
$sqlws .= "AND domain_uuid = '" . $_SESSION["domain_uuid"] . "' LIMIT 1";

$prep_statementws = $db->prepare($sqlws);

if ($prep_statementws) {
	$prep_statementws->execute();
	$rowws = $prep_statementws->fetch(PDO::FETCH_ASSOC);
	$wss_port = $rowws['wss_port'];

}


$agent_uuid = $_SESSION['user']['call_center_agent_uuid'];
if (!isset($agent_uuid)) {


$sqlagent = "select call_center_agent_uuid as agent_uuid ";
$sqlagent .= "FROM v_call_center_agents ";
$sqlagent .= "WHERE domain_uuid = '" . $_SESSION["domain_uuid"] . "' ";
$sqlagent .= "AND user_uuid = '" . $_SESSION['user']['user_uuid'] . "' LIMIT 1";

$prep_statementagent = $db->prepare($sqlagent);

if ($prep_statementagent) {
	$prep_statementagent->execute();
	$rowsagent = $prep_statementagent->fetch(PDO::FETCH_ASSOC);
	$agent_uuid = $rowsagent['agent_uuid'];

}

}


  //create assigned extensions array
  	if (is_array($_SESSION['user']['extension'])) {
  		foreach ($_SESSION['user']['extension'] as $assigned_extension) {
  			$assigned_extensions[$assigned_extension['extension_uuid']] = $extension_uuid;
  		}
  	}


//get the recent calls from call detail records
	$sqlcdr = "
		select
			direction,
			start_stamp,
			start_epoch,
			caller_id_name,
			caller_id_number,
			destination_number,
			answer_stamp,
			bridge_uuid,
			sip_hangup_disposition
		from
			v_xml_cdr
		where
			domain_uuid = :domain_uuid ";
			if (is_array($assigned_extensions) && sizeof($assigned_extensions) != 0) {
				$x = 0;
				foreach ($assigned_extensions as $assigned_extension_uuid => $assigned_extension) {
					$sql_where_array[] = "extension_uuid = :extension_uuid_".$x;
					$sql_where_array[] = "caller_id_number = :caller_id_number_".$x;
					$sql_where_array[] = "destination_number = :destination_number_1_".$x;
					$sql_where_array[] = "destination_number = :destination_number_2_".$x;
					$parameterscdr['extension_uuid_'.$x] = $assigned_extension_uuid;
					$parameterscdr['caller_id_number_'.$x] = $assigned_extension;
					$parameterscdr['destination_number_1_'.$x] = $assigned_extension;
					$parameterscdr['destination_number_2_'.$x] = '*99'.$assigned_extension;
					$x++;
				}
				if (is_array($sql_where_array) && sizeof($sql_where_array) != 0) {
					$sqlcdr .= "and (".implode(' or ', $sql_where_array).") ";
				}
				unset($sql_where_array);
			}
			$sqlcdr .= "
			and start_epoch > ".(time() - 86400)."
		order by
			start_epoch desc";
	$parameterscdr['domain_uuid'] = $_SESSION['domain_uuid'];
	$database = new database;
	$resultcdr = $database->select($sqlcdr, $parameterscdr, 'all');
	$num_rows_cdr = is_array($resultcdr) ? sizeof($resultcdr) : 0;

if ($user_status == 'Available') {
$command = "api callcenter_config agent set status ".$agent_uuid." 'Available'";
$command2 = "api callcenter_config agent set state ".$agent_uuid." 'Waiting'";
$_SESSION['user_agent_status'] = 'Available';

if ($fp) {
$switch_result = event_socket_request($fp, $command);
$switch_result2 = event_socket_request($fp, $command2);
}

//update the status
if (permission_exists("operator_panel_manage")) {
//add the user_edit permission
$p = new permissions;
$p->add("user_edit", "temp");

//update the database user_status
$array['users'][0]['user_uuid'] = $_SESSION['user']['user_uuid'];
$array['users'][0]['domain_uuid'] = $_SESSION['user']['domain_uuid'];
$array['users'][0]['user_status'] = $user_status;
$database = new database;
$database->app_name = 'operator_panel';
$database->app_uuid = 'dd3d173a-5d51-4231-ab22-b18c5b712bb2';
$database->save($array);

//remove the temporary permission
$p->delete("user_edit", "temp");
unset($array);
}

}



echo "<html> \n";
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');


echo "<head> \n";
echo "    <meta charset=\"utf-8\"> \n";
echo "    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> \n";
echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"> \n";
echo "    <meta http-equiv=\"expires\" content=\"Sun, 01 Jan 2014 00:00:00 GMT\"/> \n";
echo "    <meta http-equiv=\"pragma\" content=\"no-cache\" /> \n";
echo "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"> \n";
echo "    <meta name=\"description\" content=\"" . $text['head_description'] . "\"> \n";
echo "    <title>Flux Telecom - Unindo pessoas e negócios</title> \n";
echo "    <link rel=\"icon\" href=\"/themes/default/favicon.ico\"> \n";
echo "<link rel='stylesheet' type='text/css' href='/resources/fontawesome6/css/all.min.css'>\n";
echo "<link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/icon?family=Material+Icons+Round'>\n";
echo "<link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=Exo'>\n";

echo "<link rel='stylesheet' type='text/css' href='/resources/datatables/datatables-bs4/css/dataTables.bootstrap4.min.css'>\n";
echo "<link rel='stylesheet' type='text/css' href='/resources/datatables/datatables-responsive/css/responsive.bootstrap4.min.css'>\n";

echo "<link rel='stylesheet' type='text/css' href='/app/flux_phone/prod/assets/css/mdb.min.css'>\n";
echo "<link rel='stylesheet' type='text/css' href='/app/flux_phone/prod/assets/css/style_v1.css'>\n";

echo "</head> \n";
echo "\n";
echo "<body id='sipClient'> \n";
$_SESSION['phone_open'] = true;
$status_phone = $_SESSION['phone_open'];

//javascript function: send_cmd
	echo "<script type=\"text/javascript\">\n";
	echo "	function send_cmd(url) {\n";
	echo "		if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari\n";
	echo "			xmlhttp=new XMLHttpRequest();\n";
	echo "		}\n";
	echo "		else {// code for IE6, IE5\n";
	echo "			xmlhttp=new ActiveXObject(\"Microsoft.XMLHTTP\");\n";
	echo "		}\n";
	echo "		xmlhttp.open(\"GET\",url,true);\n";
	echo "		xmlhttp.send(null);\n";
	echo "		document.getElementById('cmd_reponse_agent').innerHTML=xmlhttp.responseText;\n";
	echo "	}\n";
	echo "</script>\n";
	
?>
<div id="app"></div>
<video id="remoteVideo" hidden="hidden"></video>
<video id="localVideo" hidden="hidden" muted="muted"></video>
<div class="container">
<!-- Tabs navs -->
<ul class="nav nav-pills nav-justified mb-3" id="ex-pages" role="tablist">
  <li class="nav-item" role="presentation">
    <a class="nav-link active" id="ex-pages-tab-1" data-mdb-toggle="pill" href="#ex-pages-tabs-1" role="tab"
      aria-controls="ex-pages-tabs-1" aria-selected="true"><i class="fas fa-phone fa-fw me-2"></i></a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link" id="ex-pages-tab-2" data-mdb-toggle="pill" href="#ex-pages-tabs-2" role="tab"
      aria-controls="ex-pages-tabs-2" aria-selected="false"><i class="fas fa-history fa-fw me-2"></i></a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link" id="ex-pages-tab-3" data-mdb-toggle="pill" href="#ex-pages-tabs-3" role="tab"
      aria-controls="ex-pages-tabs-3" aria-selected="false"><i class="fas fa-book fa-fw me-2"></i></a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link" id="ex-pages-tab-4" data-mdb-toggle="pill" href="#ex-pages-tabs-4" role="tab"
      aria-controls="ex-pages-tabs-4" aria-selected="false"><i class="fas fa-users-cog fa-fw me-2"></i></a>
  </li>
</ul>
<!-- Tabs content -->
<div class="tab-content" id="ex-pages-content">
     <div class="tab-pane fade show active" id="ex-pages-tabs-1" role="tabpanel" aria-labelledby="ex-pages-tab-1">
       <div class="clearfix sipStatus">
                   <div id="txtRegStatus" class="pull-left"></div>
                   <div id="txtCallStatus" class="pull-right">&nbsp;</div>
                 </div>
       <div class="form-group" id="phoneUI">
                      <div class="input-group">
                      <input type="text" name="number" id="numDisplay" class="form-control text-center input-sm" value="" placeholder="Insira o número..." autocomplete="off" />
                      </div>
                      <div class="container input-group" style="top: 10px;">
                     <div class="container sip-dialpad" id="sip-dialpad">
                     <button type="button" class="btn btn-default digit" data-digit="1">1<span>&nbsp;</span></button>
                     <button type="button" class="btn btn-default digit" data-digit="2">2<span>ABC</span></button>
                     <button type="button" class="btn btn-default digit" data-digit="3">3<span>DEF</span></button>
                     <button type="button" class="btn btn-default digit" data-digit="4">4<span>GHI</span></button>
                     <button type="button" class="btn btn-default digit" data-digit="5">5<span>JKL</span></button>
                     <button type="button" class="btn btn-default digit" data-digit="6">6<span>MNO</span></button>
                     <button type="button" class="btn btn-default digit" data-digit="7">7<span>PQRS</span></button>
                     <button type="button" class="btn btn-default digit" data-digit="8">8<span>TUV</span></button>
                     <button type="button" class="btn btn-default digit" data-digit="9">9<span>WXYZ</span></button>
                     <button type="button" class="btn btn-default digit" data-digit="*">*<span>&nbsp;</span></button>
                     <button type="button" class="btn btn-default digit" data-digit="0">0<span>+</span></button>
                     <button type="button" class="btn btn-default digit" data-digit="#">#<span>&nbsp;</span></button>
                     <div class="clearfix">&nbsp;</div>
                     <button class="btn btn-info btn-block btnCall" title="Discar">
                     <i class="fa fa-play"></i> Discar
                     </button>
                   </div>
                      </div>
           </div>
     </div>
     <div class="tab-pane fade" id="ex-pages-tabs-2" role="tabpanel" aria-labelledby="ex-pages-tab-2">
       <div id="sip-log" class="panel panel-default">
       <div id="logPanel" class="panel-heading">
         <h6 class="text-muted panel-title"><span id="asknotificationpermission" class="asknotificationpermission pull-left"><i class="fa fa-envelope-square text-muted asknotificationpermission" title="Habilitar Notificações" style="cursor: pointer;"></i></span>&nbsp;Chamadas Recentes<span id="sipLogClear" class="sipLogClear pull-right"><i class="fa fa-trash text-muted sipLogClear" title="Limpar Histórico" style="cursor: pointer;"></i></span></h6>
       </div>
       <div id="sip-logitems" class="list-group" style="text-align: left; font-size: 12px;">
         <p class="text-muted text-center">Nenhuma chamada recente.</p>
       </div>
     </div>
     </div>
     <div class="tab-pane fade" id="ex-pages-tabs-3" role="tabpanel" aria-labelledby="ex-pages-tab-3">
      <?php
       
       
       	$sql_contacts = "
       		select
       			user_uuid,
       			domain_name,
       			username,
       			contact_organization,
       			contact_name,
       			contact_uuid,
       			extension,
       			extension_uuid,
       			email_address,
       			outbound_caller_id_number
       		from
       			view_api_contacts
       		where
       			domain_uuid = :domain_uuid and user_uuid <> :user_uuid ";
       			
       			$sql_contacts .= "order by username desc";
       	$parameters_contacts['domain_uuid'] = $_SESSION['domain_uuid'];
       	$parameters_contacts['user_uuid'] = $_SESSION['user_uuid'];
       	$database = new database;
       	$result_contacts = $database->select($sql_contacts, $parameters_contacts, 'all');
       	//view_array($result, true);
       	$num_contact_rows = is_array($result_contacts) ? sizeof($result_contacts) : 0;
       	
       	
       	if (isset($num_contact_rows)) {
       				   // if ($num_rows > 0) {
       				   
       				 /*  user_uuid,
       				   domain_name,
       				   username,
       				   contact_uuid,
       				   extension,
       				   
       				   
       echo "<table id='example' class='table'>";
       echo "<thead class='text-primary'>";
       echo "<tr>";
       echo "<th><small>ID</small></th>";
       echo "<th><small>Comentário</small></th>";
       echo "<th><small>Data de Criação</small></th>";
       echo "<th><small>Usuário</small></th>";
       
       echo "</thead>";
       echo "<tbody>";
       echo "<tr>";while($row=mysqli_fetch_assoc($resultfat)){
       $cliente_id = $row['cliente_id'];
       
       echo "<td><small>" . $row['id'] . "</small></td>";
       echo "<td><small>" . $row['comentario'] . "</small></td>";
       echo "<td><small>" . $row['data_comentario'] . "</small></td>";
       echo "<td><small>" . $row['usuario'] . "</small></td>";
       
       
       
       echo "</tr>";}
       
       echo "</tbody>";
       echo "</table>";
       				   
       				   extension_uuid,
       				   email_address*/
       				   
       					    echo "<div class='container table table-responsive'>\n";
       					    echo "<table id='example' class='display responsive nowrap' style='width:100%'>";
       				    					//echo "<table id='example' class='table align-middle mb-0 table-light table-hover table-borderless display responsive nowrap'>\n";
       				    					echo "<thead class='text-primary'>";       				    		
       				    					echo "<tr>\n";
       				    					echo "<th scope='col'>".$textcalls['label-name']."</th>\n";
       				    					echo "<th scope='col'>".$textcalls['label-number']."</th>\n";	
       				    					echo "<th scope='col'>".$textuser['label-email']."</th>\n";
//       				    					echo "<th scope='col'>".$text['label-organization']."</th>\n";	
       				    					echo "</tr>\n";
       				    					echo "</thead>";
       				    					echo "<tbody>";
       				    					if (isset($num_contact_rows)) {
       				    					//if ($num_rows > 0) {       				    						       				    				
       				    						foreach($result_contacts as $index_contacts => $row_contacts) {
       				    							
       				    							echo "<tr>\n";
       				    								//echo "<td class='op_ext_info' style='cursor: help; padding: 0 0 0 6px;'>\n";
       				    								echo "	<td><a href='/app/contacts/contact_edit.php?id=".$row_contacts['contact_uuid']."' target='_blank'>".escape($row_contacts['contact_name'])." ".escape($row_contacts['contact_organization'])."</a></td>\n";
       				    								echo "	<td>\n";
       				    								echo "<a href=\"javascript:void(0)\" onclick=\"send_cmd('".PROJECT_PATH."/app/click_to_call/click_to_call.php?src_cid_name=".urlencode($row_contacts['outbound_caller_id_number'])."&src_cid_number=".urlencode($row_contacts['outbound_caller_id_number'])."&dest_cid_name=".urlencode($_SESSION['user']['extension'][0]['outbound_caller_id_name'])."&dest_cid_number=".urlencode($_SESSION['user']['extension'][0]['outbound_caller_id_number'])."&src=".urlencode($_SESSION['user']['extension'][0]['user'])."&dest=".urlencode($row_contacts['outbound_caller_id_number'])."&rec=true&ringback=pt-ring&auto_answer=false');\"><i class='fas fa-phone-alt' style='margin-right: 5px;'></i></a>".escape($row_contacts['outbound_caller_id_number'])."</td>\n";
       				    					echo "	<td>".escape($row_contacts['email_address'])."\n";
       				    								echo "<a href='mailto:".escape($row_contacts['email_address'])."'><i class='fas fa-envelope' style='margin-right: 5px;'></i></a></td>\n";
       				    							echo "</tr>\n";
       				    				
       				    							//unset($cdr_name, $cdr_number);
       				    							$c = ($c) ? 0 : 1;
       				    						}
       				    					}
       				    					unset($sql_contacts, $parameters_contacts, $result_contacts, $num_contact_rows, $index_contacts, $row_contacts);
       				    					echo "</tbody>";
       				    					echo "</table>\n";
       				    					$n++;
       				    					echo "</div>\n";
       					}
       ?>
     </div>
  <div class="tab-pane fade" id="ex-pages-tabs-4" role="tabpanel" aria-labelledby="ex-pages-tab-4">
  <ul class="list-group list-group-light">
    <li class="list-group-item d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
       <i class="fas fa-volume-up fa-fw me-2" title="Volume"></i>
        <div class="ms-3">
          <div class="range">
            <input type="range" class="form-range" id="sldVolume" />
          </div>
        </div>
      </div>
    </li>
    <li class="list-group-item d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <i class="fas fa-microphone fa-fw me-2" title="Devices"></i>
        <div class="ms-3">
         <div id="listmic"></div>
        </div>
      </div>
    </li>
   <!-- <li class="list-group-item d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <i class="fas fa-phone-volume fa-fw me-2" title="Dial"></i>
        <div class="ms-3">
         <div id="dialClick"></div>
        </div>
      </div>
    </li>
    <li class="list-group-item d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <i class="fas fa-headset fa-fw me-2" title="Status"></i>
        <div class="ms-3">
          <?php if (is_array($result)) {
    echo "<form class='form-inline agent-form' id='agent-form'>\n";
//               echo "<form class='form-inline agent-form' id='agent-form'>\n";
//         echo "<div class='input-group'>\n";
     
     
//     echo "	<input type='hidden' id='user_status' name='user_status' value=\"" . escape($_REQUEST['select_user_status']) . "\">\n";
     //$cmd = "'" . PROJECT_PATH . "/app/basic_operator_panel/index.php?cmd=status+'+this.value";
     echo "		<select class='form-select form-select-sm' id='select_user_status' name='select_user_status'>\n";
     echo "			<option value=''></option>\n";
     echo "			<option value='Available' " . (($user_status == "Available") ? "selected='selected'" : null) . ">" . $textuser['option-available'] . "</option>\n";
     echo "			<option value='Available (On Demand)' " . (($user_status == "Available (On Demand)") ? "selected='selected'" : null) . ">" . $textuser['option-available_on_demand'] . "</option>\n";
     echo "			<option value='Logged Out' " . (($user_status == "Logged Out") ? "selected='selected'" : null) . ">" . $textuser['option-logged_out'] . "</option>\n";
     echo "			<option value='On Break' " . (($user_status == "On Break") ? "selected='selected'" : null) . ">" . $textuser['option-on_break'] . "</option>\n";
     echo "			<option value='Do Not Disturb' " . (($user_status == "Do Not Disturb") ? "selected='selected'" : null) . ">" . $textuser['option-do_not_disturb'] . "</option>\n";
     echo "			<option value='On Meeting' " . (($user_status == "On Meeting") ? "selected='selected'" : null) . ">" . $textuser['option-meeting'] . "</option>\n";
     echo "			<option value='Lunch' " . (($user_status == "Lunch") ? "selected='selected'" : null) . ">" . $textuser['option-lunch'] . "</option>\n";
     echo "			<option value='Snack' " . (($user_status == "Snack") ? "selected='selected'" : null) . ">" . $textuser['option-snack'] . "</option>\n";
     echo "			<option value='Bathroom' " . (($user_status == "Bathroom") ? "selected='selected'" : null) . ">" . $textuser['option-bathroom'] . "</option>\n";
     
     echo "			<option value='Training' " . (($user_status == "Training") ? "selected='selected'" : null) . ">" . $textuser['option-training'] . "</option>\n";
     
     echo "			<option value='Feedback' " . (($user_status == "Feedback") ? "selected='selected'" : null) . ">" . $textuser['option-feedback'] . "</option>\n";
     echo "		</select>\n";
//     echo "<button class='btn btn-rounded btn-sm btn-secondary agentSubmit' type='submit'>".$text['button-save']."</button>\n";
     echo "		</form>\n";
                                             }
                                             ?>
        </div>
      </div>
    </li> -->
  </ul>
  <!--<label class="form-label" for="sldVolume"><?php echo $text['label-volume'];?></label>
  <div class="range">
    <input type="range" class="form-range" id="sldVolume" />
  </div>
  <button class="btn btn-primary increase-volume">+ ring volume</button>
  <button class="btn btn-primary decrease-volume">- ring volume</button>
  <div style="font-size: 12px; margin-bottom: 4px; width: 100%; background-color: #333;" id="listmic"> 
  </div> -->

<!-- Tabs content -->
</div>
</div>
</div>
<script type="text/html" id="template-addon-phone">


</script>
<script type="text/html" id="template-transfer">
<div class="modal fade sizefull" id="mdlTransfer" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
   <div class="modal-dialog modal-md">
     <div class="modal-content">
     <div class="modal-header">
       <h6 class="modal-title" id="mdlTransfer">Transferência</h6>
       <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close">
        </button>
     </div>
       <div class="modal-body">
         <form class="form-inline transfer-form" id="transfer-form">
           <div class="form-group">
           <input type="text" id="numTransfer" class="form-control" name="transfer"/>
         </div>
       <div class="modal-footer">
       <button class="btn btn-sm btn-secondary" type="submit">Transferir</button>
       
       <button id="transferCancel" class="btn btn-sm btn-danger transferCancel" data-mdb-dismiss="modal" style="display: none;" type="button"><?php echo $text['button-cancel'];?></button>
       <button id="warm" class="btn btn-sm btn-primary warm" type="button">Discar</button>
       <button id="tpark" class="btn btn-sm btn-info tpark">Estacionar</button>
       <button id="complete" class="btn btn-sm btn-success complete" type="button">Completar</button>
     </div>
         </form>

       </div>
     </div>
   </div>
 </div>
</script>
<script type="text/html" id="template-accepted">

</script>
<script type="text/html" id="template-error">
  <div class="modal fade" id="mdlError" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="exampleModalLabel">Transferir</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
        <div class="modal-body">
          <form class="form-inline transfer-form">
            <div class="form-group">
            <input type="text" class="form-control" name="transfer"/>
          </div>
          <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><?php echo $text['label-cancel'];?></button>
        <button type="button" class="btn btn-sm btn-success complete">Transferir</button>
      </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</script>
<script type="text/html" id="template-agent">
 <div class="modal fade" id="mdlAgent" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="mdlAgent"><?php echo "		" . $textuser['description-status'] . "<br />\n";?></h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
        <div class="op_ext modal-body">
          <?php if (is_array($result)) {
          echo "<form class='form-inline agent-form' id='agent-form'>\n";
         echo "<div class='input-group'>\n";
                         echo "	<input type='hidden' id='user_status' name='user_status' value=\"" . escape($_REQUEST['select_user_status']) . "\">\n";
                         //$cmd = "'" . PROJECT_PATH . "/app/basic_operator_panel/index.php?cmd=status+'+this.value";
                         echo "		<select id='select_user_status' name='select_user_status' class='formfld' style=''>\n";
                         echo "			<option value=''></option>\n";
                         echo "			<option value='Available' " . (($user_status == "Available") ? "selected='selected'" : null) . ">" . $textuser['option-available'] . "</option>\n";
                         echo "			<option value='Available (On Demand)' " . (($user_status == "Available (On Demand)") ? "selected='selected'" : null) . ">" . $textuser['option-available_on_demand'] . "</option>\n";
                         echo "			<option value='Logged Out' " . (($user_status == "Logged Out") ? "selected='selected'" : null) . ">" . $textuser['option-logged_out'] . "</option>\n";
                         echo "			<option value='On Break' " . (($user_status == "On Break") ? "selected='selected'" : null) . ">" . $textuser['option-on_break'] . "</option>\n";
                         echo "			<option value='Do Not Disturb' " . (($user_status == "Do Not Disturb") ? "selected='selected'" : null) . ">" . $textuser['option-do_not_disturb'] . "</option>\n";
                         echo "			<option value='On Meeting' " . (($user_status == "On Meeting") ? "selected='selected'" : null) . ">" . $textuser['option-meeting'] . "</option>\n";
                         echo "			<option value='Lunch' " . (($user_status == "Lunch") ? "selected='selected'" : null) . ">" . $textuser['option-lunch'] . "</option>\n";
                         echo "			<option value='Snack' " . (($user_status == "Snack") ? "selected='selected'" : null) . ">" . $textuser['option-snack'] . "</option>\n";
                         echo "			<option value='Bathroom' " . (($user_status == "Bathroom") ? "selected='selected'" : null) . ">" . $textuser['option-bathroom'] . "</option>\n";
                         
                         echo "			<option value='Training' " . (($user_status == "Training") ? "selected='selected'" : null) . ">" . $textuser['option-training'] . "</option>\n";
                         
                         echo "			<option value='Feedback' " . (($user_status == "Feedback") ? "selected='selected'" : null) . ">" . $textuser['option-feedback'] . "</option>\n";
                         
                         echo "		</select>\n";
                           }
                           ?>
                           </div>
          <div class="modal-footer">
                 <button class="btn btn-sm btn-secondary agentSubmit" type="submit"><?php echo $text['button-save'];?></button>
                 <button id="agentCancel" class="btn btn-sm btn-danger agentCancel" data-dismiss="modal" type="button"><?php echo $text['button-cancel'];?></button>
               </div>
                   </form>
        </div>
      </div>
    </div>
  </div>
</script>
<script type="text/html" id="template-incoming">
   <div class="modal fade" tabindex="-1" id="mdlIncoming" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="mdlIncoming">Chamada de Entrada</h6>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form class="form-inline forward-form">
            <div class="wrap-input100 validate-input m-b-16 form-group" data-validate="">
             <label>Encaminhar Chamada:</label>
               <input type="text" name="forward" id="forward" class="input100 form-control text-center input-sm" placeholder="" autocomplete="off" />
              <span class="focus-input100"></span>
            </div>
            <div class="container-login100-form-btn p-t-15">
              <button class="btn btn-primary" type="submit">
              Enviar
              </button>
            </div>
                    </form>
                </div>
                <div class="modal-footer before-answer">
                    <button class="btn btn-success answer">Atender</button>
                    <button class="btn btn-danger decline">Rejeitar</button>
                    <button class="btn btn-info toPark">Estacionar</button>
                   <button class="btn btn-warning toVoicemail">Caixa Postal</button>
                </div>
                <div class="modal-footer answered" style="display: none">Conectando...</div>
            </div>
        </div>
    </div>
</script>
<div id='cmd_reponse_agent' style='display: none;'></div>
<audio id="ringtone" src="/app/flux_phone/assets/audio/ogg/ringtone_in.ogg" loop></audio>
<audio id="dtmfTone" src="/app/flux_phone/assets/audio/mp3/dtmf.mp3"></audio>
<audio id="audioRemote"></audio>
<script src="/app/flux_phone/assets/js/jquery-3.3.1.slim.min.js"></script>
<script>
  $(function () {
  $('#example').DataTable( {
    
     "language": {
	   "zeroRecords": "Nenhum registro encontrado",
	   "lengthMenu": 'Exibir <select>'+
		 '<option value="10">10</option>'+
		 '<option value="20">20</option>'+
		 '<option value="30">30</option>'+
		 '<option value="40">40</option>'+
		 '<option value="50">50</option>'+
		 '<option value="100">100</option>'+
		 '<option value="-1">Todos</option>'+
		 '</select> registros'
	 },
	 "responsive": true,
	 "autoWidth": true,
	 "paging": true,
	 "lengthChange": true,
	 "ordering": true,
	 "order": [[0,'asc']],
	 "searching": true
   });
  });
</script>
<script src="/resources/datatables/datatables/jquery.dataTables.min.js"></script>
<script src="/resources/datatables/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<!--<script src="/resources/datatables/datatables-responsive/js/responsive.bootstrap4.min.js"></script>-->
<script src="/resources/datatables/datatables-responsive/js/dataTables.responsive.min.js"></script>

<script type="text/javascript" src="/app/flux_phone/prod/assets/js/mdb.min.js"></script>
<script src="/app/flux_phone/prod/assets/js/core/popper.min.js"></script>
<script src="/app/flux_phone/prod/assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="/app/flux_phone/prod/assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="/app/flux_phone/prod/assets/js/plugins/bootstrap-notify.js"></script>
<script type="text/javascript" src="/app/flux_phone/assets/js/moment.min.js"></script>
<script type="text/javascript" src="/app/flux_phone/assets/js/pt-br.js"></script>

<script type="text/javascript" src="/app/flux_phone/assets/js/fluxPhone.js"></script>
<script type="text/javascript" src="/app/flux_phone/assets/js/flux.js"></script>
<script type="text/javascript" src="/app/flux_phone/assets/js/flux-web-phone.js"></script>


<script type="text/javascript">

var ramal = '<?php echo $extension; ?>';
var pass = '<?php echo $extension_password; ?>';
var nome = '<?php echo $outbound_caller_id_name; ?>';
var user_email = '<?php echo $user_email; ?>';
var domain = '<?php echo $domain_name; ?>';
var server = '<?php echo $extension_context; ?>';
var appKey = '<?php echo $api_key; ?>';
var userKey = '<?php echo $user_uuid; ?>';
var appSecret = '<?php echo $user_password; ?>';
var call_group = '<?php echo $call_group; ?>';
var username = '<?php echo $extension_login; ?>';
var login = '<?php echo $user_login; ?>';
var fromNumber = '<?php echo $outbound_caller_id_number; ?>';
var wssport = '<?php echo $wss_port; ?>';
var wsserver = '<?php echo $extension_context; ?>:'+wssport+'';
var userStatus = '<?php echo $user_status; ?>';
//var blocked = '<?php echo $device_key_label; ?>';
var phoneStatus = '<?php echo $extension_enabled; ?>';
var agent_uuid = '<?php echo $agent_uuid; ?>';
var user = {
    'User' : ramal,
    'Pass' : pass,
    'Agent': agent_uuid,
    'Realm'   : domain,
    'Domain'  : domain,
    'Status'  : userStatus,
    'Email'   : user_email,
    'appUser'  : userKey,
    'appAgent'  : agent_uuid,
    'stunServers' : ['stun:stun.l.google.com:19302'],
    'turnServers' : [{urls:'turn:sbcdev4.flux.net.br:9579', username : 'fluxDev' , credential: 'FluxDev4SBC201'}],
    'Display' : nome,
    'WSServer'  : 'wss://'+wsserver+'',
    'Loglevel' : 3
};
var sip = {
    'username' : ramal,
    'ext' : ramal,
//    'blocked' : blocked,
    'appAgent': agent_uuid,
    'user_status': userStatus,
    'login' : login,
    'password' : pass,
    'transport' : 'WSS',
    'domain'   : domain,
    'appKey' : appKey,
    'appUser' : userKey,
    'appSecret' : appKey,
    'call_group' : call_group,
    'fromNumber' : fromNumber,
    'username' : username,
    'outboundProxy'  : wsserver,
    'regId' : appKey,
    'transport'      : 'WSS',
    'authorizationId' : ramal,
    'wsServers' : wsserver,
    'iceCheckingTimeout': 500,
    'loglevel' : 3,
    'displayName' : nome,
	  'hackIpInContact': true,
	  'hackWssInTransport': true,
	  'hackViaTcp': false
};
var sipInfo = [sip];
var sipdata = {
'sipInfo' : sipInfo
};
var callcenter = [user];
var cData = {
'cData' : callcenter
};
var regData = {'regData' : user};
var sipData = {'sipData' : sip};
var data = [sipdata,regData];
//var regBlock = {'regBlock' : blocked};
localStorage.setItem('SIPCreds', JSON.stringify(user));
localStorage.setItem('User', JSON.stringify(data));
localStorage.setItem('regData', JSON.stringify(regData));
localStorage.setItem('sipData', JSON.stringify(sipData));
localStorage.setItem('cData', JSON.stringify(cData));

//localStorage.setItem('Block', JSON.stringify(regBlock));
console.log('USER STATUS: '+userStatus);
console.log('PHONE STATUS: '+phoneStatus);
console.log('AGENT UUID: '+agent_uuid);


//console.log('AGENT Block: '+blocked);
//localStorage.setItem('Block', blocked );
</script>
<script type="text/javascript" src="/app/flux_phone/assets/js/fluxphone.js"></script>



<script>
$(document).ready(function() {
//$('#example').DataTable();
     $(document).on('submit', '#transfer-form', function() {
      return false;
     });
     $(document).on('submit', '#agent-form', function() {
           return false;
          });

});
</script>


<?php
echo "</body> \n";
echo "</html> \n";
//require_once "resources/footer.php";
?>

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
//includes
$conf = glob("{/usr/local/etc,/etc}/fluxpbx/config.conf", GLOB_BRACE);
set_include_path(parse_ini_file($conf[0])['document.root']);

require_once "resources/require.php";
require_once "resources/check_auth.php";

//check permissions
if (permission_exists('fluxphone_view')) {
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
$sqlws .= "WHERE domain_setting_category = 'fluxphone' ";
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

$sql3 = "SELECT distinct d.device_mac_address, extension,d.device_template,display_name,effective_caller_id_name,outbound_caller_id_number FROM v_extension_users, v_extensions, v_users,v_device_lines AS l, v_devices AS d WHERE ((l.user_id = extension) AND (v_users.user_uuid = v_extension_users.user_uuid) AND (v_extensions.extension_uuid = v_extension_users.extension_uuid)  AND (v_extensions.domain_uuid = '" . $_SESSION["domain_uuid"] . "') AND (l.user_id=extension) AND (l.device_uuid = d.device_uuid) AND (v_users.user_uuid = '" . $_SESSION['user_uuid'] . "') AND (d.domain_uuid = '" . $_SESSION["domain_uuid"] . "')) ORDER BY extension, d.device_mac_address asc";
$database3 = new database;
$rows3 = $database3->select($sql3, NULL, 'all');

header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

if (strlen($rows3[0]['device_mac_address'])) {

	if (!strlen($rows3[1]['device_mac_address'])) {
		//user has one and only one device, go for it directly
		$wanted_device = $rows3[0]['device_mac_address'];
	}
}


header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

//show the content
//echo "<head> \n";
//echo "    <meta charset=\"utf-8\"> \n";
//echo "    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> \n";
//echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"> \n";
//echo "    <meta http-equiv=\"expires\" content=\"Sun, 01 Jan 2014 00:00:00 GMT\"/> \n";
//echo "    <meta http-equiv=\"pragma\" content=\"no-cache\" /> \n";
//echo "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"> \n";
//echo "    <meta name=\"description\" content=\"" . $text['head_description'] . "\"> \n";
echo "    <link rel=\"icon\" href=\"/app/flux_phone/assets/img/favicon.ico\"> \n";
//echo "    <title>flux - Pabx Cloud com ligações ilimitadas para todo Brasil</title> \n";*/
/*echo "    <link href=\"/app/flux_phone/phone/scripts/flux/bootstrap/dist/css/bootstrap.min.css\" rel=\"stylesheet\"> \n";
echo "    <link href=\"/app/flux_phone/phone/scripts/flux/fonts/font-awesome-4.7.0/css/font-awesome.min.css\" rel=\"stylesheet\"> \n";
echo "    <link href=\"/app/flux_phone/phone/scripts/flux/fonts/Linearicons-Free-v1.0.0/icon-font.min.css\" rel=\"stylesheet\"> \n";
echo "    <link href=\"/app/flux_phone/phone/scripts/flux/vendor/animate/animate.css\" rel=\"stylesheet\"> \n";
echo "    <link href=\"/app/flux_phone/phone/scripts/flux/vendor/css-hamburgers/hamburgers.min.css\" rel=\"stylesheet\"> \n";
echo "    <link href=\"/app/flux_phone/phone/scripts/flux/vendor/select2/select2.min.css\" rel=\"stylesheet\"> \n";

echo "    <link href=\"/app/flux_phone/phone/app.css\" rel=\"stylesheet\"> \n";*/
echo "    <link href=\"/app/flux_phone/assets/css/bootstrap.min.css\" rel=\"stylesheet\"> \n";
echo "    <link href=\"/resources/fontawesome6/css/all.min.css\" rel=\"stylesheet\"> \n";
echo "    <link href=\"/app/flux_phone/prod/assets/css/mdb.min.css\" rel=\"stylesheet\"/>\n";
echo "    <link href=\"/app/flux_phone/prod/assets/css/style_v1.css\" rel=\"stylesheet\"/>\n";
//echo "</head> \n";
echo "\n";
//echo "<body id='sip-phone'> \n";

?>
<!--
<div class="pos-f-t">
    <div class="collapse" id="navbarToggleExternalContent">
      <div class="bg-light pb-1 pt-1 mb-1" style="background-color: #e3f2fd;">
        <div id="sip-log" class="panel panel-default hide">
          <div class="panel-heading">
            <h6 class="text-muted panel-title"><span id="asknotificationpermission" class="asknotificationpermission pull-left"><i class="fa fa-envelope-square text-muted asknotificationpermission" id="vmailcount" title="Habilitar Notificações" style="cursor: pointer;"></i></span> Chamadas Recentes <span class="pull-right"><i class="fa fa-trash text-muted sipLogClear" title="Limpar Histórico" style="cursor: pointer;"></i></span></h6>
          </div>
          <div id="sip-logitems" class="list-group" style="text-align: left; font-size: 12px;">
            <p class="text-muted text-center">Nenhuma chamada recente.</p>
          </div>
        </div>
      </div>
    </div>
    <nav class="navbar navbar-light pb-1 pt-1 mb-1" style="background-color: #e3f2fd;">
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarToggleExternalContent" aria-controls="navbarToggleExternalContent" aria-expanded="false" aria-label="Alterna navegação" style="font-size: 14px !important;">
      <span class="navbar-toggler-icon"></span>
      </button>
    </nav>
  </div>

-->

<div id="app"></div>
<video id="remoteVideo" hidden="hidden"></video>
<video id="localVideo" hidden="hidden" muted="muted"></video>
<script type="text/html" id="template-addon-phone">
<link href="/app/flux_phone/assets/css/flux_dev.css" rel="stylesheet">
<link href="/app/flux_phone/prod/assets/css/style_v1.css" rel="stylesheet">
<ul class="nav nav-pills nav-justified mb-1" id="ex1" role="tablist">
 <li class="nav-item" role="presentation">
   <a
     class="nav-link active"
     id="tab-phone"
     data-mdb-toggle="pill"
     href="#pills-phone"
     role="tab"
     aria-controls="pills-phone"
     aria-selected="true"
     ><span><i class="fas fa-phone"></i></span></a
   >
 </li>
 <li class="nav-item me-3 me-lg-1" role="presentation">
       <a
         class="nav-link"
         id="tab-history"
         data-mdb-toggle="pill"
         href="#pills-history"
         role="tab"
         aria-controls="pills-history"
         aria-selected="false"
         ><i class="fa-sharp fa-solid fa-clock-rotate-left"></i></a
       >
     </li>

     <li class="nav-item" role="presentation">
       <a
         class="nav-link"
         id="tab-agent"
         data-mdb-toggle="pill"
         href="#pills-agent"
         role="tab"
         aria-controls="pills-agent"
         aria-selected="false"
         ><span><i class="fas fa-user"></i></span></a
       >
     </li>
   </ul>
   <div class="tab-content">
   <div class="tab-pane fade show active" id="pills-phone" role="tabpanel" aria-labelledby="tab-phone">
<div  id="sipPhone" class="container-login100">
<div class="containerNav">
<div class="clearfix sipStatus">
    <div id="txtCallStatus" class="txtCallStatus pull-right"></div>
    <div id="txtRegStatus" class="txtRegStatus pull-left"></div>
  </div>
<div class="form-group" id="phoneUI">
        <div class="input-group">
      <input type="text" name="number" id="numDisplay" class="form-control text-center input-sm numDisplay" style="border: none;font-size: 16px;width:100%;" value="" placeholder="Insira o número..." autocomplete="off" />
        </div>
        <div class="input-group">
        <div id="sip-dialpad" class="sip-dialpad">
        <div class="row">
          <div class="digit" id="one" data-digit="1">1</div>
          <div class="digit" id="two" data-digit="2">
            2
            <div class="sub">ABC</div>
          </div>
          <div class="digit" id="three" data-digit="3">
            3
            <div class="sub">DEF</div>
          </div>
        </div>
        <div class="row">
          <div class="digit" id="four" data-digit="4">
            4
            <div class="sub">GHI</div>
          </div>
          <div class="digit" id="five" data-digit="5">
            5
            <div class="sub">JKL</div>
          </div>
          <div class="digit" data-digit="6">
            6
            <div class="sub">MNO</div>
          </div>
        </div>
        <div class="row">
          <div class="digit" data-digit="7">
            7
            <div class="sub">PQRS</div>
          </div>
          <div class="digit" data-digit="8">
            8
            <div class="sub">TUV</div>
          </div>
          <div class="digit" data-digit="9">
            9
            <div class="sub">WXYZ</div>
          </div>
        </div>
        <div class="row">
          <div class="digit" data-digit="*">*
          </div>
          <div class="digit" data-digit="0">0
          </div>
          <div class="digit" data-digit="#">#
          </div>
        </div>
        <div class="botrow">
        <div id="dnd" class="btnDnd">
            <i class="fa fa-ban" aria-hidden="true" title="Não perturbe"></i>
          </div>
          <div id="call" class="btnCall">
            <i class="fa fa-phone" aria-hidden="true" title="Discar"></i>
          </div>
          <div id="logout" class="btnLogout">
          <i class="fa fa-sign-out" aria-hidden="true" title="Sair"></i>
          </div>
        </div>
        </div>
        </div>
    </div>
</div>
</div>
</div>
   <div class="tab-pane fade" id="pills-history" role="tabpanel" aria-labelledby="tab-history">
           <div id="sip-log" class="panel panel-default">
                        <div id="logPanel" class="panel-heading">
                          <h6 class="text-muted panel-title"><span id="asknotificationpermission" class="asknotificationpermission pull-left"><i class="fa fa-envelope-square text-muted asknotificationpermission" title="Habilitar Notificações" style="cursor: pointer;"></i></span>&nbsp;Chamadas Recentes<span id="sipLogClear" class="sipLogClear pull-right"><i class="fa fa-trash text-muted sipLogClear" title="Limpar Histórico" style="cursor: pointer;"></i></span></h6>
                        </div>
                        <div id="sip-logitems" class="list-group" style="text-align: left; font-size: 12px;">
                          <p class="text-muted text-center">Nenhuma chamada recente.</p>
                        </div>
                      </div>
           </div>
</div>

</script>
<script type="text/html" id="template-transfer">
  <div class="modal fade sizefull" id="mdlTransfer" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="mdlTransfer">Transferência</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
        <div class="modal-body">
          <form class="form-inline transfer-form" id="my-form">
            <div class="form-group">
            <input type="text" id="numTransfer" class="form-control" name="transfer"/>
          </div>
        <div class="modal-footer">
        <button class="btn btn-sm btn-secondary" type="submit">Transferir</button>
        <button id="transferCancel" class="btn btn-sm btn-danger transferCancel" data-dismiss="modal" style="display: none;" type="button">Cancelar</button>
        <button id="warm" class="btn btn-sm btn-primary warm" type="button">Discar</button>
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
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
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
        <h6 class="modal-title" id="mdlAgent">Transferir</h6>
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
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-sm btn-success complete">Transferir</button>
      </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</script>
<script type="text/html" id="template-incoming">
     <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Chamada de Entrada</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
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
                   <button class="btn btn-warning toVoicemail">Caixa Postal</button>
                </div>
                <div class="modal-footer answered" style="display: none">Conectando...</div>
            </div>
        </div>
    </div>

</script>

<audio id="ringtone" src="/app/flux_phone/assets/audio/mp3/ringtone_in.mp3" loop></audio>
<audio id="dtmfTone" src="/app/flux_phone/prod/audio/mp3/dtmf.mp3"></audio>
<audio id="audioRemote"></audio>
<script src="/app/flux_phone/assets/js/jquery-3.3.1.slim.min.js"></script>
<!--<script src="scripts/flux/js/popper.min.js"></script>-->
<!--<script src="https://unpkg.com/@popperjs/core@2"></script>-->
<script type="text/javascript" src="/app/flux_phone/prod/assets/js/mdb.min.js"></script>
<script src="/app/flux_phone/prod/assets/js/core/popper.min.js"></script>
<script src="/app/flux_phone/prod/assets/js/core/bootstrap.min.js"></script>
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
    'turnServers' : [{urls:'turn:45.166.193.41:5349', username : 'fluxDev' , credential: 'fluxDevTurn2010'}],
    'Display' : ramal,
    'WSServer'  : 'wss://'+wsserver+'',
    'Loglevel' : 1
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
	'hackWssInTransport': false,
	'hackViaTcp': false
};
var sipInfo = [sip];
var sipdata = {
'sipInfo' : sipInfo
};
var regData = {'regData' : user};
var sipData = {'sipData' : sip};
var data = [sipdata,regData];
//var regBlock = {'regBlock' : blocked};
localStorage.setItem('SIPCreds', JSON.stringify(user));
localStorage.setItem('User', JSON.stringify(data));
localStorage.setItem('regData', JSON.stringify(regData));
localStorage.setItem('sipData', JSON.stringify(sipData));

//localStorage.setItem('Block', JSON.stringify(regBlock));
console.log('USER STATUS: '+userStatus);
console.log('PHONE STATUS: '+phoneStatus);
console.log('AGENT UUID: '+agent_uuid);
//console.log('AGENT Block: '+blocked);
//localStorage.setItem('Block', blocked );
</script>
<script type='text/javascript' src='/app/flux_phone/assets/js/fluxphone.js'></script>
<!--<script type="text/javascript" src="scripts/flux/widget/app.js"></script>-->
<script>
$(document).ready(function() {
//audioElement.setAttribute('src', './scripts/flux/vendor/webphone/mp3/ring.mp3');
//used by call control and ajax refresh functions
    $(document).on('submit', '#transfer-form', function() {
      return false;
     });
     $(document).on('submit', '#agent-form', function() {
           return false;
          });
});
</script>



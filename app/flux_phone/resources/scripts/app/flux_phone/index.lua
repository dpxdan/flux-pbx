--prepare the api object
api = freeswitch.API();

loglevel = "debug"
session:setAutoHangup(false);

------------------------------------------------------------------------
------------------------------------------------------------------------
------------------------------------------------------------------------


--connect to the database
Database = require "resources.functions.database";
dbh = Database.new('system')


--get the variables
uuid = session:getVariable("uuid");
domain_name = session:getVariable("domain_name");
direction = session:getVariable("direction");

if (direction == nil) then
direction = session:getVariable("call_direction");
end

destination_number = session:getVariable("destination_number");
caller_id_number = session:getVariable("caller_id_number");
caller_id_number = session:getVariable("caller_id_number");
profile_name = session:getVariable("sofia_profile_name");
call_center_agent_uuid = session:getVariable("variable_call_center_agent_uuid");

if (call_center_agent_uuid ~= nil) then
session:execute("callcenter_track","callcenter_agent_uuid="..call_center_agent_uuid.."");

if(destination_number == nil) then destination_number = '0' end
if(caller_id_number == nil) then caller_id_number = '0' end

fluxphone_destination_user_agent = api:execute("sofia_presence_data", "user_agent "..profile_name.."/"..destination_number.."@"..domain_name);
fluxphone_caller_user_agent = api:execute("sofia_presence_data", "user_agent "..profile_name.."/"..caller_id_number.."@"..domain_name);

fluxphone_bind = session:getVariable("fluxphone_bind");
if(fluxphone_bind == nil) then fluxphone_bind = "false" end

fluxphone_ringback = session:getVariable("pt-ring");
if(fluxphone_ringback == nil) then fluxphone_ringback = "%(2000,4000,440,480)" end

freeswitch.consoleLog(loglevel, uuid .. " ------------ BEGIN ----------------------------------------------------------\n")

freeswitch.consoleLog(loglevel, uuid .. " domain_name: " .. domain_name .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " destination_number: " .. destination_number .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " caller_id_number: " .. caller_id_number .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " fluxphone_destination_user_agent: " .. fluxphone_destination_user_agent .. "\n");
--if (call_center_agent_uuid ~= nil) then
freeswitch.consoleLog(loglevel, uuid .. " call_center_agent_uuid: " .. call_center_agent_uuid .. "\n");
--end
freeswitch.consoleLog(loglevel, uuid .. " fluxphone_caller_user_agent: " .. fluxphone_caller_user_agent .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " fluxphone_bind: " .. fluxphone_bind .. "\n");

if(fluxphone_bind == "false") then

	session:execute("export","fluxphone_bind=true");
	
	fluxphone_is_caller = string.find(fluxphone_caller_user_agent, "FluxPhone/6.3.1");
	if(fluxphone_is_caller) then
		fluxphone_is_caller = "true"
		session:execute("export","ignore_early_media=true");
		session:execute("export","fluxphone_is_caller=true");
		session:setVariable("ringback", fluxphone_ringback);
		session:setVariable("instant_ringback", "true");
		session:answer()
		api:execute("msleep", "1000");
	
	
	session:execute("export","fluxphone_is_both=true");
	session:execute("bind_digit_action","fluxphone_local,*299,exec:execute_extension,fluxphone_hold XML ${context},aleg,bleg");
	session:execute("bind_digit_action","fluxphone_local,*399,exec:execute_extension,fluxphone_hold XML ${context},peer,peer");
	session:execute("bind_digit_action","fluxphone_local,*499,exec:execute_extension,fluxphone_dx XML ${context},aleg,bleg");
	session:execute("bind_digit_action","fluxphone_local,*599,exec:execute_extension,fluxphone_dx XML ${context},peer,peer");
	session:execute("bind_digit_action","fluxphone_local,*699,exec:execute_extension,fluxphone_att_xfer XML ${context},aleg,bleg");
	session:execute("bind_digit_action","fluxphone_local,*799,exec:execute_extension,fluxphone_att_xfer XML ${context},peer,peer");
	session:execute("digit_action_set_realm","fluxphone_local");


else
fluxphone_is_caller = "false"
--cc_status=api:execute("uuid_getvar", uuid.." cc_cancel_reason")
--freeswitch.consoleLog("notice", "FluxPBX: Queue Call Ended "..cc_status.."\n")
end

end
freeswitch.consoleLog(loglevel, uuid .. " ------------ END   ----------------------------------------------------------\n")


------------------------------------------------------------------------
------------------------------------------------------------------------
------------------------------------------------------------------------


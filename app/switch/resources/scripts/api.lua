--include config.lua
	require "resources.functions.config";

--connect to the database
	local Database = require "resources.functions.database";
	dbh = Database.new('system');

--include json library
	local json
	if (debug["sql"]) then
		json = require "resources.functions.lunajson"
	end

--define the trim function
	require "resources.functions.trim"

	api = freeswitch.API();

--get the variables
   -- random_number = math.random(1000,99999999);
 --   id_crm_call = random_number;
	id_crm_call = session:getVariable("id_crm_call");
	destination = session:getVariable("caller_id_number");
	destination_number = session:getVariable("destination_number");
	caller = session:getVariable("caller_id_number");
	uuid = session:getVariable("uuid");
	external_call_id = session:getVariable("external_call_id");
	token = "5e69a5798a1df55fe182c2a5aac211ff";
	cmd = "uuid_setvar "..uuid.." id_crm_call "..id_crm_call;
	freeswitch.consoleLog("NOTICE", "[ID] cmd: "..cmd.."\n");
	results = trim(api:executeString(cmd));

	cmd = "uuid_setvar "..uuid.." external_call_id "..external_call_id;
	freeswitch.consoleLog("NOTICE", "[ID] cmd: "..cmd.."\n");
	results = trim(api:executeString(cmd));


	freeswitch.consoleLog("set", "accountcode: " .. caller .. "\n");
	freeswitch.consoleLog("notice", "id_crm_call: " .. id_crm_call .. "\n");
	freeswitch.consoleLog("notice", "external_call_id: " .. external_call_id .. "\n");
	freeswitch.consoleLog("notice", "outbound_number: " .. destination .. "\n");
	freeswitch.consoleLog("notice", "token: " .. token .. "\n");
	freeswitch.consoleLog("notice", "accountcode: " .. caller .. "\n");



--local handle = io.popen("/var/www/html/fluxpbx/app/flux_xpro/xpro_start '" ..id_crm_call.. "' '" ..destination.. "' '" ..token.. "' '"..caller.."'")
--local result = handle:read("*a")
--handle:close()
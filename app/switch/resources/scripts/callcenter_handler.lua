--local s = event:serialize("xml")
--local name = event:getHeader("Event-Name")
--freeswitch.consoleLog("NOTICE", "Got event! " .. name)
--freeswitch.consoleLog("NOTICE", "Serial!\n" .. s)

local call_uuid = event:getHeader("Caller-Unique-ID");
--local channel_timestamp = event:getHeader("Event-Date-Timestamp");
local channel_timestamp = os.time();
local action = event:getHeader("CC-Action")
local caller_id_number = event:getHeader("CC-Member-CID-Number")
local uuid = event:getHeader("CC-Member-Session-UUID")
local agentUuid = event:getHeader("CC-Agent-UUID")
local agent = event:getHeader("CC-Agent")



if action == "agent-status-change" then
   local agent = event:getHeader("CC-Agent")
   local agent_status = event:getHeader("CC-Agent-Status")
   local date_event = event:getHeader("Event-Date-Local")
   local api = freeswitch.API()
    freeswitch.consoleLog("NOTICE", "Got agent_status! " .. agent_status)
    freeswitch.consoleLog("NOTICE", "agent!\n" .. agent)
end


-- lua.conf.xml
-- <hook event="CUSTOM" subclass="callcenter::info" script="callcenter_handler.lua"/>

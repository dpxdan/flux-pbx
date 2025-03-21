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
	Portions created by the Initial Developer are Copyright (C) 2008-2017
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Daniel Paixao <daniel@flux.net.br>
	KonradSC <konrd@yahoo.com>
*/

//define the blf_notify class
	class call_center_notify {

		public $debug;
		public $domain_name;
		public $agent_name;
		public $answer_state;
		public $agent_uuid;

		//feature_event method
		public function send_call_center_notify() {

				$esl = event_socket::create();
				if ($esl->is_connected()) {
					//send the event
						$event = "sendevent PRESENCE_IN\n";
						$event .= "proto: agent\n";
						$event .= "event_type: presence\n";
						$event .= "alt_event_type: dialog\n";
						$event .= "Presence-Call-Direction: outbound\n";
						$event .= "state: Active (1 waiting)\n";
						$event .= "from: agent+".$this->agent_name."@".$this->domain_name."\n";
						$event .= "login: agent+".$this->agent_name."@".$this->domain_name."\n";
						$event .= "unique-id: ".$this->agent_uuid."\n";
						$event .= "answer-state: ".$this->answer_state."\n";
						event_socket::command($event);
						//echo $event."<br />";
				}
		} //function

	} //class

?>

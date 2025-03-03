(function(window) {
/*

https://nomedoservico.com.br/ligacao?numero=[telefone]&nome=[nomeContato]&observacao=contato do nectar id:[idCliente], oportunidade:[idOportunidade]

usuario
https://login.devtest.ringcentral.com/?responseType=code&clientId=WEGDirXaSG2UOt6dUV0w-w&brandId=1210&state=MzEzMDQ4MTU6OTY0NTIzNTox.d5a35b07bc30df4d0e139ebde467a9cc0be5c41bc2dad025904e0855085ee0e4&localeId=en_US&endpointId=&session=2264996040897453789&display=page&prompt=login%20sso&scope=&appUrlScheme=https%3A%2F%2Fringcentralvoip.amocrm.com%2Fringcentral%2Foauth&ui_options=&code_challenge=&code_challenge_method=&hideNavigationBar=true&v=23.2.3#/enterCredential
userLogin

*/

	var curday = function(sp) {
		today = new Date();
		var dd = today.getDate();
		const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
			"Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
		];

		const d = new Date();
		var mm = monthNames[d.getMonth()];
		var yyyy = today.getFullYear();

		if (dd < 10) dd = '0' + dd;
		if (mm < 10) mm = '0' + mm;
		return (yyyy + sp + mm + sp + dd);
	};
	var path = curday('/');
	let nectarWebphone = window.nectarWebphone;
	let urlServidor = 'https://sbcdev4.flux.net.br/';
	let callServidor = 'https://sbcdev4.flux.net.br/';
	//https://api.flux.cloud/flux.php?destination=011956812949&key=IUEv37td4TkgKRJesddYCBAvcQdcBzPK
	let hashUser = "QDIZRNnGNBR5Ls5ELjgY21MCQRcxtRVL";

	let endpointChamada = `${urlServidor}api/calls/`;
	let enviaChamada = `${callServidor}`;
	let endpointBuscarLigacao = `${urlServidor}recordings/`;


	let myHeaders = new Headers();
	let myNewHeaders = new Headers();
	myHeaders.append("Content-Type", "application/json");
	myNewHeaders.append("Content-Type", "application/x-www-form-urlencoded");



	// REALIZANDO UMA LIGACAO
	let checkCall = null;
	let idLigacao = null;
	let _doCall = (params) => {
		let numero = params.numero;
		let ramal = params.ramalUsuario;
		let name = params.usuario;
		if (!numero) {
			alert("Numero não foi encontrado");
		} else if (!ramal) {
			alert("O ramal do usuário não foi configurado");
		}
		//nosso servico de ligacao nao pode receber o prefixo do pais
		if (numero.startsWith("+55")) {
			numero = numero.substring(3, numero.length);
		} else if (numero.startsWith("55")) {
			numero = numero.substring(2, numero.length);
		}
		if (idLigacao) {
			alert('Uma ligação já esta em andamento');
			return false;
		}
		idLigacao = true;
		
		let urlencoded = new URLSearchParams();
urlencoded.append("api_key", `${hashUser}`);
urlencoded.append("src", `${ramal}`);
urlencoded.append("dest", `${numero}`);
urlencoded.append("src_cid_name", `${name}`);

//usuario
//extensionNumber
//userLogin
//phoneNumber
//ramalUsuario

		//let url = `${enviaChamada}exec.php?cmd=originate&source=${ramal}&destination=${numero}&key=${hashUser}`;
		let url = `${enviaChamada}app/click_to_call/click_to_call.php?src=${ramal}&dest=${numero}&src_cid_name='${name}'&key=${hashUser}`;
		let cfg = {
			method: 'POST',
			headers: myNewHeaders,
			body: urlencoded
		};
		fetch(url, cfg)
			.then(response => {
				return response.text()
				.then(function(text) {
					let resposta = text ? JSON.parse(text) : {};
					if (response.status !== 200) {
						throw Error(resposta.mensagem);
					}
					return Promise.resolve(resposta);
				});
			})
			.then(resposta => {
				idLigacao = resposta.id;
				statusChamada = resposta.statusChamada;
				console.log("STATUS CHAMADA:" + statusChamada);
				nectarWebphone.notify("call:preparing");
				nectarWebphone.notify("call:id", {
					id: idLigacao
				});
				if (statusChamada === "RING_WAIT") {
				nectarWebphone.notify("call:start");
			    }
				if (statusChamada === "ACTIVE") {
				nectarWebphone.notify("call:answered");
			    }
				// QUANDO A LIGACAO E FEITA, FICAMOS MONITORANDO O STATUS DELA ATE QUE SEJA ENCERRADA
				checkCall = setInterval(() => {
					_getCall();
				}, 3000);
			})
			.catch(resposta => {
				idLigacao = null;
				handleError(resposta, true);
			});
	};

	//MONITORA O STATUS DA LIGACAO
	let loadinCall = false;
	let _getCall = function() {
		let raw = JSON.stringify({
			"id": `${idLigacao}`
		});
		myHeaders.append("Authorization", "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJUSEVfSVNTVUVSIiwiYXVkIjoiVEhFX0FVRElFTkNFIiwiaWF0IjoxNjIwMzU0OTgwLCJuYmYiOjE2MjAzNTQ5ODMsImV4cCI6MzAyMjIwMzU0OTgwLCJkYXRhIjp7ImlkIjo0LCJmaXJzdG5hbWUiOiJEYW5pZWwiLCJsYXN0bmFtZSI6IlBhaXhhbyIsImVtYWlsIjoiZGFuaWVsQGl1bmdvLmNsb3VkIn19.nmUUOyn55ooqz4mmv711mQh6GoGcqMY15gWCpHcrZz0");
		let url = `${callServidor}check_nectar_call.php`;
		let cfg = {
			method: 'POST',
			headers: myHeaders,
			body: raw
		};

		fetch(url, cfg)
			.then(function(response) {
				return response.text()
				.then(function(text) {
					let resposta = text ? JSON.parse(text) : {};
					if (response.status !== 200) {
						throw Error(resposta.mensagem);
					}
					return Promise.resolve(resposta);
				});
			})
			.then(function(resposta) {
				console.log(resposta);
				statusChamada = resposta.statusChamada;
				console.log("LOG FLUX STATUS GET CALL:" + statusChamada);

				_validadeCall(statusChamada);
				loadinCall = false;
			})
			.catch((error) => {
				loadinCall = false;
				handleError(error, true);
			});
	};
	let lastStatus = null;
	
	
	let _validadeCall = (callInfo) => {
	
		if (callInfo) {
			let status = callInfo;
			console.log("LOG FLUX CALLINFO CALL STATUS: " + status);

			let finalizar = false;
			if (lastStatus != null && lastStatus === status) {
				return false;
			}
			lastStatus = callInfo;
			switch (lastStatus) {

				case "RING_WAIT":
				    console.log("LOG FLUX CALL RINGING: " + lastStatus);
					nectarWebphone.notify("call:start");
					break;
				case "RINGING":
				    console.log("LOG FLUX CALL RINGING: " + lastStatus);
					nectarWebphone.notify("call:start");
					break;
				case "ACTIVE":
				    console.log("LOG FLUX CALL ANSWERED: " + lastStatus);
					nectarWebphone.notify("call:answered");
					break;
				case "END":
				    console.log("LOG FLUX CALL END: " + lastStatus);
					var call = {
						url: `${endpointBuscarLigacao}${path}/${idLigacao}.wav`
					};
					console.log("LOG FLUX URL RECORD " + call.url);
					nectarWebphone.notify("call:end", call);
					finalizar = true;
					break;
				case "nao atendida":
					nectarWebphone.notify("call:not_answered");
					finalizar = true;
					break;
				case "telefone ocupado":
					nectarWebphone.notify("call:not_answered");
					finalizar = true;
					break;
				case "a ligacao falhou":
					nectarWebphone.notify("call:erro");
					handleError(status, false);
					finalizar = true;
					break;
			}
			if (finalizar) {
				cancelInterval();
			}
		}
	};


	let handleError = (msg, supress) => {
		if (typeof msg === 'object' && msg.message) {
			msg = msg.message;
			supress = false;
		}
		if (msg) {
			if (!supress) {
				alert(msg);
			}
			console.error(msg);
		}
		nectarWebphone.notify("erro");
		cancelInterval();
	};
	let cancelInterval = function() {
		if (checkCall) {
			clearInterval(checkCall);
			checkCall = null;
			idLigacao = null;
		}
	};

	let _endCall = async() => {
		if (!idLigacao) {
			return false;
		}
		let endingCall = false;

        let urlencoded = new URLSearchParams();
urlencoded.append("cmd", "uuid_kill");
urlencoded.append("call_id", `${idLigacao}`);


		let url = `${enviaChamada}exec.php?cmd=uuid_kill&call_id=${idLigacao}&key=${hashUser}`;
	
		//let url = `${endpointChamada}chamada?id=${idLigacao}&chave_api=${hashUser}`;
		let cfg = {
			method: 'POST',
			headers: myNewHeaders,
			body: urlencoded
			
		};

		await fetch(url, cfg)
			.then(function(response) {
				return response.text().then(function(text) {
					let resposta = text ? JSON.parse(text) : {};
					if (response.status !== 200) {
						throw Error(resposta.mensagem);
					}
					return Promise.resolve(resposta);
				});
			})
			.then(function(resposta) {
				console.log(resposta);
				endingCall = false;
			})
			.catch((error) => {
				endingCall = false;
				handleError(error, true);
			});

	};

	let events = nectarWebphone.getEvents();
	events.register("call:new", _doCall);
	events.register("call:end", _endCall);


})(window, undefined);
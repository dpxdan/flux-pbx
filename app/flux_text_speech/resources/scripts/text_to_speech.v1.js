//const args = require('yargs').argv;
//

//console.log(args.0);
'use strict';

const args = require('yargs').argv;
const fs = require('fs');

//console.log('Name: ' + args.name);
//console.log('Text: ' + args.textivr);

//console.log(process.argv.slice(2));
//const argv = require('yargs');


const TextToSpeechV1 = require('ibm-watson/text-to-speech/v1');
const DiscoveryV1 = require('ibm-watson/discovery/v1');
const { IamAuthenticator } = require('ibm-watson/auth');




const textToSpeech = new TextToSpeechV1({
  authenticator: new IamAuthenticator({
    apikey: 'smc7hBZbnQusYKDG2ak93zwc_K96rEeQ5FlsDjBDKzsM',
  }),
  serviceUrl: 'https://api.us-south.text-to-speech.watson.cloud.ibm.com/instances/fef21bde-7b5c-4914-86b6-332139199d3f',
 disableSslVerification: true,
});


const name = process.argv.slice(2);
const textivr = process.argv.slice(3);

const synthesizeParams = {
  text: ''+args.textivr+'',
  accept: 'audio/wav',
  voice: 'pt-BR_IsabelaV3Voice',
};


textToSpeech.synthesize(synthesizeParams)
  .then(response => {
    // The following line is necessary only for
    // wav formats; otherwise, `response.result`
    // can be directly piped to a file.
    return textToSpeech.repairWavHeaderStream(response.result);
  })
  .then(buffer => {
    fs.writeFileSync('/var/www/html/fluxpbx/app/flux_text_speech/recordings/'+ args.name +'/audio/'+args.name+'.wav', buffer);
  })
  .catch(err => {
    console.log('error:', err);
  });

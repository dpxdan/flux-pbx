'use strict';

//const SpeechToTextV1 = require('ibm-watson/speech-to-text/v1');
//const args = require('minimist')(process.argv.slice(1));
const args = require('yargs').argv;
const fs = require('fs');

//const speechToText = new SpeechToTextV1({
  // See: https://github.com/watson-developer-cloud/node-sdk#authentication
//});

/*
    This code will print the entire response to the console when it
    receives the 'data' event. Some applications will want to write
    out only the transcribed text, to the console or to a file.
    To do this, remove `objectMode: true` from the `params` object.
    Then, uncomment the block of code at Line 30.
*/
const SpeechToTextV1 = require('ibm-watson/speech-to-text/v1');
const { IamAuthenticator } = require('ibm-watson/auth');

const speechToText = new SpeechToTextV1({
  authenticator: new IamAuthenticator({
    apikey: '0leOj3ZRp3v1vbZ9py001VeDZt1k7MG_7XR7Bp_d1O5R',
  }),
  url: 'https://api.us-south.speech-to-text.watson.cloud.ibm.com',
 disableSslVerification: true,
});

const params = {
  contentType: 'audio/wav',
  objectMode: true,
  model: 'pt-BR_NarrowbandModel',
};

// create the stream
const recognizeStream = speechToText.recognizeUsingWebSocket(params);

// pipe in some audio
//fs.createReadStream(__dirname + '/resources/speech.wav').pipe(recognizeStream);
console.log('File: ' + args.file);
console.log('Number: ' + args.number);
const argv = require('yargs')
   // .command('upload', 'upload a file', (yargs) => {}, (argv) => {
       fs.createReadStream('/usr/share/freeswitch/app/flux-customer/resources/records/' + args.number + '/' + args.file).pipe(recognizeStream);


recognizeStream.on('data', function (event) {
  onEvent('Data:', event);
});
recognizeStream.on('error', function (event) {
  onEvent('Error:', event);
});
recognizeStream.on('close', function (event) {
  onEvent('Close:', event);
});

// Displays events on the console.
function onEvent(name, event) {
  console.log(name, JSON.stringify(event, null, 2));
}

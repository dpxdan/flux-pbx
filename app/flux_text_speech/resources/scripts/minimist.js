'use strict';

const argv = require('yargs')
    .command('upload', 'upload a file', (yargs) => {}, (argv) => {
        console.log('Uploading your file now...');

        // Do stuff here
    }).argv;
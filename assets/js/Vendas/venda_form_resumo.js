'use strict';

import $ from "jquery";

import 'print-js';

import hotkeys from 'hotkeys-js';


$(document).ready(function () {


    let k = hotkeys.noConflict();
    k('ctrl+1', function (event, handler) {
        event.preventDefault();
        $('#aDados')[0].click();
    });
    k('ctrl+2', function (event, handler) {
        event.preventDefault();
        $('#aItens')[0].click();
    });
    k('ctrl+3', function (event, handler) {
        event.preventDefault();
        $('#aPagto')[0].click();
    });
    k('ctrl+4', function (event, handler) {
        event.preventDefault();
        $('#aResumo')[0].click();
    });


});


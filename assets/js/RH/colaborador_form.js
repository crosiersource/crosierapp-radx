/* eslint-disable */


import $ from 'jquery';

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import 'lightbox2/dist/css/lightbox.css';
import 'lightbox2';
import 'blueimp-file-upload';

Routing.setRoutingData(routes);

$(document).ready(function () {

    let $imageFile = $('#colaborador_imageFile');

    $imageFile.on('change', function () {
        //get the file name
        let fileName = $(this).val().split('\\').pop();
        //replace the "Choose a file" label
        $(this).next('.custom-file-label').html(fileName);
    });



});

'use strict';

import $ from "jquery";

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';


$(document).ready(function () {

    let $grupo = $('#grupo');
    let $filterGrupoItem = $('#filter_grupoItem');

    $grupo.on('select2:select', function () {
        window.location = Routing.generate('grupoItem_listMovs', {'grupo': $grupo.val()});
    });

    $filterGrupoItem.on('select2:select', function () {
        window.location = Routing.generate('grupoItem_listMovs', {'grupoItem': $filterGrupoItem.val()});
    });


});


Routing.setRoutingData(routes);

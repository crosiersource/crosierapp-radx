'use strict';


import Numeral from 'numeral';
import 'numeral/locales/pt-br.js';


import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

Numeral.locale('pt-br');

Routing.setRoutingData(routes);

$(document).ready(function () {

    let $subtotal = $('#pv_subtotal');
    let $descontos = $('#pv_descontos');
    let $total = $('#pv_total');

    function calcular() {
        let subtotal = parseFloat(Numeral($subtotal.val()).value());
        let descontos = parseFloat(Numeral($descontos.val()).value());
        let total = parseFloat((subtotal - descontos).toFixed(2));
        $total.val(Numeral(total).format('0.0,[00]'));
    }

    $descontos.on('keyup', function (e) {
        calcular();
    });

});
/* eslint-disable */

import $ from "jquery";

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import 'jquery-maskmoney/dist/jquery.maskMoney.js';

import 'select2/dist/css/select2.css';
import 'select2';
import 'select2/dist/js/i18n/pt-BR.js';
import 'select2-bootstrap-theme/dist/select2-bootstrap.css';
$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");

import Moment from 'moment';


Routing.setRoutingData(routes);


$(document).ready(function () {

    let $movimentacao_id = $('#movimentacao_id');

    let $documentoNum = $('#movimentacao_documentoNum');

    let $modo = $('#movimentacao_modo');

    let $sacado = $('#movimentacao_sacado');
    let $cedente = $('#movimentacao_cedente');
    let $carteira = $('#movimentacao_carteira');

    let $dtMoviment = $("#movimentacao_dtMoviment");

    let $valor = $("#movimentacao_valor");
    let $descontos = $("#movimentacao_descontos");
    let $acrescimos = $("#movimentacao_acrescimos");
    let $valorTotal = $("#movimentacao_valorTotal");

    function resValorTotal() {
        let valor =  Number($valor.val().replace('.','').replace(',','.'));
        let descontos = Number($descontos.val().replace('.','').replace(',','.'));
        let acrescimos = Number($acrescimos.val().replace('.','').replace(',','.'));
        let valorTotal = (valor - descontos + acrescimos).toFixed(2).replace('.',',');
        $valorTotal.val(valorTotal).maskMoney('mask');
    }



    $valor.on('blur', function () {
        resValorTotal()
    });
    $descontos.on('blur', function () {
        resValorTotal()
    });
    $acrescimos.on('blur', function () {
        resValorTotal()
    });

    $dtMoviment.on('focus', function () {
        if ($dtMoviment.val() === '') {
            $dtMoviment.val(Moment().format('DD/MM/YYYY'));
        }
    });

    $('.dtVenctoEfetiva').on('focus', function () {
        let $dtVenctoEfetiva = $(this);
        let dtVencto = $('#' + $(this).data('dtvencto'));
        if (dtVencto.val() != $dtVenctoEfetiva.data('dtvenctoval')) {
            let route = '/base/diaUtil/findDiaUtil/?financeiro=true&dt=' + encodeURIComponent(dtVencto.val()) ;

            $.ajax(
                route,
                {
                    crossDomain: true,
                    dataType: "json",
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    xhrFields: {
                        withCredentials: true
                    }
                }
            ).done(
                function (data) {
                    let dtF = Moment(data['diaUtil']).format('DD/MM/YYYY')
                    $dtVenctoEfetiva.val(dtF);
                    $dtVenctoEfetiva.data('dtvenctoval', dtVencto.val());
                }
            ).fail(function (jqXHR, textStatus, errorThrown) {
                if (jqXHR) {
                    console.dir(jqXHR);
                }
                if (textStatus) {
                    console.dir(textStatus);
                }
            });
        }
    });


    // -----------------


    $modo.select2('focus');


})
;

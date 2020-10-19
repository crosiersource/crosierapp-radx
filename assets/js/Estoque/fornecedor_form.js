'use strict';

import $ from "jquery";

import Numeral from 'numeral';

import 'numeral/locales/pt-br.js';

import 'select2/dist/css/select2.css';
import select2 from 'select2';
import 'select2/dist/js/i18n/pt-BR.js';
import 'select2-bootstrap-theme/dist/select2-bootstrap.css';
import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';


$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");

Numeral.locale('pt-br');

Routing.setRoutingData(routes);


$(document).ready(function () {


    let $endereco_i = $('#endereco_i');
    let $endereco_tipo = $('#endereco_tipo');
    let $endereco_logradouro = $('#endereco_logradouro');
    let $endereco_numero = $('#endereco_numero');
    let $endereco_complemento = $('#endereco_complemento');
    let $endereco_bairro = $('#endereco_bairro');
    let $endereco_cidade = $('#endereco_cidade');
    let $endereco_estado = $('#endereco_estado');
    let $endereco_cep = $('#endereco_cep');

    let $btnSalvarEndereco = $('#btnSalvarEndereco');


    $('.btnEditEndereco').click(function () {
        let dados = $(this).data();

        $endereco_i.val('');
        $endereco_tipo.val('').trigger('change');
        $endereco_logradouro.val('');
        $endereco_numero.val('');
        $endereco_complemento.val('');
        $endereco_cep.val('');
        $endereco_bairro.val('');
        $endereco_cidade.val('');
        $endereco_estado.val('').trigger('change');

        $endereco_i.val(dados.i);

        $endereco_tipo.val(dados.tipo.split(',')).trigger('change');
        $endereco_logradouro.val(dados.logradouro);
        $endereco_numero.val(dados.numero);
        $endereco_complemento.val(dados.complemento);
        $endereco_cep.val(dados.cep);
        $endereco_bairro.val(dados.bairro);
        $endereco_cidade.val(dados.cidade);
        $endereco_estado.val(dados.estado).trigger('change');

    });

    $endereco_estado.select2({
        allowClear: true,
        width: '100%',
        dropdownAutoWidth: true
    });

    $endereco_tipo.select2({
        width: '100%',
        dropdownAutoWidth: true,
        placeholder: '...',
        allowClear: true,
        tags: true,
        data: $endereco_tipo.data('options'),
        createTag: function (params) {
            let termStr = $endereco_tipo.hasClass('notuppercase') ? params.term : params.term.toUpperCase();
            return {
                id: termStr,
                text: termStr,
                newOption: true
            }
        },
        templateResult: function (data) {
            let termStr = $endereco_tipo.hasClass('notuppercase') ? data.text : data.text.toUpperCase();
            let $result = $("<span></span>");
            $result.text(termStr);
            return $result;
        }
    });


    $('.campo-endereco').on('keypress', function (e) {
        if (e.which == 13) {
            $btnSalvarEndereco.click();
        }
    });


});


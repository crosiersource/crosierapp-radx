'use strict';

import $ from "jquery";

import Numeral from 'numeral';

import 'print-js';

import 'numeral/locales/pt-br.js';

import 'select2/dist/css/select2.css';
import 'select2';
import 'select2/dist/js/i18n/pt-BR.js';
import 'select2-bootstrap-theme/dist/select2-bootstrap.css';
import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
import hotkeys from 'hotkeys-js';

$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");

Numeral.locale('pt-br');

Routing.setRoutingData(routes);


$(document).ready(function () {


    let $cliente = $('#clienteId');
    let $cliente_documento = $('#venda_jsonData_cliente_documento');
    let $cliente_nome = $('#cliente_nome');
    let $cliente_fone = $('#venda_jsonData_cliente_fone');
    let $cliente_email = $('#venda_jsonData_cliente_email');

    let $spanClienteNome = $('#spanClienteNome');

    // cache para não buscar toda hora
    $cliente_documento.data('val', $cliente_documento.val());

    $cliente_documento.on('blur', function () {
        if ($cliente_documento.val() !== $cliente_documento.data('val')) {
            $cliente_documento.data('val', $cliente_documento.val());
            $.ajax({
                url: Routing.generate('crm_cliente_findClienteByDocumento') + '?term=' + $cliente_documento.val(),
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    if (res?.results) {
                        //     $cliente_nome.empty().trigger("change");
                        //     $cliente_nome.append(new Option(res?.results[0]?.text, res?.results[0]?.text, false, false)).trigger('change');
                        $cliente_nome.val(res?.results[0]?.text);
                        $cliente_fone.val(res?.results[0]?.json_data?.fone1);
                        $cliente_email.val(res?.results[0]?.json_data?.email);
                        $cliente.val(res?.results[0]?.id)
                    } else {
                        $cliente_nome.val('');
                        $cliente_fone.val('');
                        $cliente_email.val('');
                        $cliente.val('')
                    }
                }
            });
        }
    });


    // $cliente_nome.select2({
    //     minimumInputLength: 4,
    //     width: '100%',
    //     dropdownAutoWidth: true,
    //     placeholder: '...',
    //     allowClear: true,
    //     tags: true,
    //     createTag: function (params) {
    //         let term = $.trim(params.term);
    //
    //         if (term === '') {
    //             return null;
    //         }
    //
    //         return {
    //             id: term,
    //             text: term
    //         }
    //     },
    //     ajax: {
    //         delay: 750,
    //         url: Routing.generate('ven_venda_findClienteByStr'),
    //         dataType: 'json',
    //         processResults: function (data) {
    //             let mapped = $.map(data.results, function (obj) {
    //                 obj.id = obj.text;
    //                 return obj;
    //             });
    //             return {
    //                 results: mapped
    //             };
    //         },
    //     }
    // }).on('select2:select', function () {
    //     let o = $cliente_nome.select2('data')[0];
    //
    //     if (o?.documento) {
    //         // só retorna documento se achou o cliente na base
    //         $cliente_documento.val(o?.documento);
    //         $cliente.val(o?.id);
    //     } else {
    //         // Para deixar sempre em UPPERCASE
    //         $cliente_nome.empty().trigger("change");
    //         $cliente_nome.append(new Option(o.text.toUpperCase(), o.text.toUpperCase(), false, false)).trigger('change');
    //         if (!$cliente.val()) {
    //             $cliente_documento.val('');
    //             $cliente.val('');
    //         }
    //     }
    //     $cliente_fone.val(o?.json_data?.fone1 ?? '');
    //     $cliente_email.val(o?.json_data?.email ?? '');
    //
    //     CrosierMasks.maskDecs();
    // });
    //
    // if ($cliente_nome.data('val')) {
    //     let val = $cliente_nome.data('val');
    //     $cliente_nome.append(new Option(val, val, false, false)).trigger('change');
    // }

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


    /**
     * RTA: como o venda_jsonData_cliente_nome é adicionando manualmente sem o form do symfony (para poder ser um
     * select2js), e mais tarde é necessário adicionar os demais campos json_data com o {{ form_widget(form) }}
     * então o Symfony vai adicionar outro venda_jsonData_cliente_nome, porém do tipo input text. Removemos ele
     * do DOM para não ser submetido.
     *
     * Atenção que ainda tem outro input text com name=venda_jsonData_cliente_nome que é utilizado quando é um novo CPF/CNPJ sendo cadastrado.     *
     *
     */
    $('input[type=text]#venda_jsonData_cliente_nome').remove();

});

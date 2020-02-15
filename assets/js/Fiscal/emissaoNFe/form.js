'use strict';

import $ from 'jquery';

import routes from '../../../static/fos_js_routes.json';
import Routing from '../../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import toastrr from "toastr";
import Moment from 'moment';

Routing.setRoutingData(routes);


$(document).ready(function () {


    let $tipo = $("input[name*='nota_fiscal[tipoNotaFiscal]']");

    let $dtSaiEnt = $('#nota_fiscal_dtSaiEnt');

    let $documentoDestinatario = $('#nota_fiscal_documentoDestinatario');
    let $xNomeDestinatario = $('#nota_fiscal_xNomeDestinatario');

    let $inscricaoEstadualDestinatario = $('#nota_fiscal_inscricaoEstadualDestinatario');
    let $foneDestinatario = $('#nota_fiscal_foneDestinatario');
    let $emailDestinatario = $('#nota_fiscal_emailDestinatario');
    let $cepDestinatario = $('#nota_fiscal_cepDestinatario');
    let $logradouroDestinatario = $('#nota_fiscal_logradouroDestinatario');
    let $numeroDestinatario = $('#nota_fiscal_numeroDestinatario');
    let $bairroDestinatario = $('#nota_fiscal_bairroDestinatario');
    let $cidadeDestinatario = $('#nota_fiscal_cidadeDestinatario');
    let $estadoDestinatario = $('#nota_fiscal_estadoDestinatario');

    let $transpDocumento = $('#nota_fiscal_transpDocumento');
    let $transpNome = $('#nota_fiscal_transpNome');
    let $transpEndereco = $('#nota_fiscal_transpEndereco');
    let $transpCidade = $('#nota_fiscal_transpCidade');
    let $transpEstado = $('#nota_fiscal_transpEstado');

    /**
     *
     */
    function handleFields() {

        if ($documentoDestinatario.val()) {
            $documentoDestinatario.data('documento', $documentoDestinatario.val().replace(/[^\d]+/g, ''));
        }
        

        if ($transpDocumento.val()) {
            $transpDocumento.data('documento', $transpDocumento.val().replace(/[^\d]+/g, ''));
        }

        let tipoVal = $("input[name*='nota_fiscal[tipoNotaFiscal]']:checked").val();
        if (tipoVal !== 'NFE') {
            $inscricaoEstadualDestinatario.parent().parent().css('display', 'none');
            $foneDestinatario.parent().parent().css('display', 'none');
            $emailDestinatario.parent().parent().css('display', 'none');
            $cepDestinatario.parent().parent().css('display', 'none');
            $logradouroDestinatario.parent().parent().css('display', 'none');
            $numeroDestinatario.parent().parent().css('display', 'none');
            $bairroDestinatario.parent().parent().css('display', 'none');
            $cidadeDestinatario.parent().parent().css('display', 'none');
            $estadoDestinatario.parent().parent().css('display', 'none');
        } else {
            $inscricaoEstadualDestinatario.parent().parent().css('display', '');
            $foneDestinatario.parent().parent().css('display', '');
            $emailDestinatario.parent().parent().css('display', '');
            $cepDestinatario.parent().parent().css('display', '');
            $logradouroDestinatario.parent().parent().css('display', '');
            $numeroDestinatario.parent().parent().css('display', '');
            $bairroDestinatario.parent().parent().css('display', '');
            $cidadeDestinatario.parent().parent().css('display', '');
            $estadoDestinatario.parent().parent().css('display', '');
        }
        $documentoDestinatario.focus();
    }


    $('.btnCopiarNfItem').click(function () {
        let nfid = $(this).data('nfid');
        $.ajax({
            url: Routing.generate('fis_emissaonfe_copiarNotaFiscalItem', {'notaFiscalItem': $(this).data('nfitemid')}),
            type: 'get',
            dataType: 'json',
            success: function (res) {
                if (res.result === 'OK') {
                    window.location = Routing.generate('fis_emissaonfe_form', {'id': nfid}) + '#itens';
                } else {
                    toastrr.error('Erro ao copiar item');
                }
            }
        });
    });

    $dtSaiEnt.focus(function () {
        if (!$(this).val()) {
            $(this).val(Moment().format('DD/MM/YYYY HH:mm:ss'));
        }
    });


    $tipo.change(function () {
        handleFields();
    });

    handleFields();


    window.consultarCNPJDestinatario = function () {

        let documentoVal = $documentoDestinatario.val().replace(/[^\d]+/g, '');
        if (!documentoVal || !$estadoDestinatario.val()) {
            toastrr.error('É necessário informar o CNPJ e o UF');
            return;
        }
        $.ajax({
            url: Routing.generate('fis_emissaonfe_consultarCNPJ') + '/?cnpj=' + documentoVal + '&uf=' + $estadoDestinatario.val(),
            type: 'get',
            dataType: 'json',
            success: function (res) {
                if (res.result === 'OK') {
                    $xNomeDestinatario.val(res.dados.razaoSocial);
                    $inscricaoEstadualDestinatario.val(res.dados.IE);
                    $cepDestinatario.val(res.dados.CEP);
                    $logradouroDestinatario.val(res.dados.logradouro);
                    $numeroDestinatario.val(res.dados.numero);
                    $bairroDestinatario.val(res.dados.bairro);
                    $cidadeDestinatario.val(res.dados.cidade);
                    $estadoDestinatario.val(res.dados.UF).change();
                    CrosierMasks.maskAll();
                } else {
                    toastrr.error(res.msg ? res.msg : 'Erro ao consultar CNPJ');
                }
            }
        });
    };


    window.consultarTranspDocumento = function () {
        let documentoVal = $transpDocumento.val().replace(/[^\d]+/g, '');
        if (!documentoVal || !$transpEstado.val()) {
            toastrr.error('É necessário informar o CNPJ e o UF');
            return;
        }
        $.ajax({
            url: Routing.generate('fis_emissaonfe_consultarCNPJ') + '/?cnpj=' + documentoVal + '&uf=' + $transpEstado.val(),
            type: 'get',
            dataType: 'json',
            success: function (res) {
                if (res.result === 'OK') {
                    $transpNome.val(res.dados.razaoSocial);
                    $transpEndereco.val(res.dados.logradouro + ', ' + res.dados.numero + ' - ' + res.dados.bairro);
                    $transpCidade.val(res.dados.cidade);
                    $transpEstado.val(res.dados.UF).change();
                    CrosierMasks.maskAll();
                } else {
                    toastrr.error(res.msg ? res.msg : 'Erro ao consultar CNPJ');
                }
            }
        });
    };

});



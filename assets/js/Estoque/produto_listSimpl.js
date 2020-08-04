'use strict';

import $ from "jquery";

import 'daterangepicker';

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import 'datatables';
import 'datatables.net-bs4/css/dataTables.bootstrap4.css';
import 'datatables/media/css/jquery.dataTables.css';
import Moment from 'moment';

import 'select2/dist/css/select2.css';
import 'select2';
import 'select2/dist/js/i18n/pt-BR.js';
import 'select2-bootstrap-theme/dist/select2-bootstrap.css';

$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");

Routing.setRoutingData(routes);

$(document).ready(function () {

    let $filter_dtIntegrEcommerce = $('#filter_dtIntegrEcommerce');

    let $filter_depto = $('#filter_depto');
    let $filter_grupo = $('#filter_grupo');
    let $filter_subgrupo = $('#filter_subgrupo');

    let $filter_montadora = $('#filter_montadora');
    let $filter_ano = $('#filter_ano');
    let $filter_modelo = $('#filter_modelo');

    let $filter_order = $('#filter_order');


    let $form = $('#form_produto_list');

    let $datatable = $('#produto_list');

    // declaro antes para poder sobreescrever ali com o extent, no caso de querer mudar alguma coisa (ex.: movimentacaoRecorrentesList.js)
    let defaultParams = {
        paging: false,
        serverSide: false,
        stateSave: true,
        searching: false,
        language: {
            "url": "/build/static/datatables-Portuguese-Brasil.json"
        }
    };

    let datatable = $datatable.DataTable(defaultParams);

    datatable.on('init.dt', function(e, settings, json){
        datatable.on('order.dt', function () {

            // Atenção: as colunas devem estar na mesma ordem que o array no Controller
            let order = datatable.order();
            $filter_order.val(JSON.stringify(order));

            $form.submit();
        });
    });




    $filter_dtIntegrEcommerce.daterangepicker(
        {
            autoUpdateInput: false,
            opens: 'left',
            autoApply: true,
            locale: {
                "format": "DD/MM/YYYY",
                "separator": " - ",
                "applyLabel": "Aplicar",
                "cancelLabel": "Cancelar",
                "fromLabel": "De",
                "toLabel": "Até",
                "customRangeLabel": "Custom",
                "daysOfWeek": [
                    "Dom",
                    "Seg",
                    "Ter",
                    "Qua",
                    "Qui",
                    "Sex",
                    "Sáb"
                ],
                "monthNames": [
                    "Janeiro",
                    "Fevereiro",
                    "Março",
                    "Abril",
                    "Maio",
                    "Junho",
                    "Julho",
                    "Agosto",
                    "Setembro",
                    "Outubro",
                    "Novembro",
                    "Dezembro"
                ],
                "firstDay": 0
            },
            ranges: {
                'Hoje': [Moment(), Moment()],
                'Ontem': [Moment().subtract(1, 'days'), Moment().subtract(1, 'days')],
                'Últimos 7 dias': [Moment().subtract(6, 'days'), Moment()],
                'Últimos 30 dias': [Moment().subtract(29, 'days'), Moment()],
                'Este mês': [Moment().startOf('month'), Moment().endOf('month')],
                'Mês passado': [Moment().subtract(1, 'month').startOf('month'), Moment().subtract(1, 'month').endOf('month')]
            },
            "alwaysShowCalendars": true
        }
    ).on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    }).on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
    });


    /**
     *
     */
    function build_filterDepto() {
        $filter_depto.select2({
                width: '100%',
                data: $filter_depto.data('options')
            }
        ).on('select2:select', function () {
            build_filterGrupo(true);
        });
    }

    /**
     *
     */
    function build_filterGrupo(onSelect = false) {
        $filter_grupo.empty().trigger("change");
        if ($filter_depto.select2('data')[0]['id'] > 0) {
            if (onSelect) {
                $filter_depto.select2('data')[0].grupos.forEach((v, i) => v.selected = false);
            }
            $filter_grupo.select2({
                    width: '100%',
                    data: $filter_depto.select2('data')[0].grupos
                }
            ).on('select2:select', function () {
                build_filterSubgrupo(true)
            });


        } else {
            $filter_grupo.select2({
                    width: '100%',
                    data: [{"id": "", "text": "Selecione..."}]
                }
            );
        }
        build_filterSubgrupo();
    }

    /**
     *
     */
    function build_filterSubgrupo(onSelect = false) {
        $filter_subgrupo.empty().trigger("change");
        if ($filter_grupo.select2('data')[0]['id'] > 0) {
            if (onSelect) {
                $filter_grupo.select2('data')[0].subgrupos.forEach((v, i) => v.selected = false);
            }
            $filter_subgrupo.select2({
                    width: '100%',
                    data: $filter_grupo.select2('data')[0].subgrupos
                }
            );
        } else {
            $filter_subgrupo.select2({
                    width: '100%',
                    data: [{"id": "", "text": "Selecione..."}]
                }
            );
        }
    }


    build_filterDepto();
    build_filterGrupo();
    build_filterSubgrupo();


    /**
     *
     */
    function build_filterMontadora() {
        $filter_montadora.select2({
                width: '100%',
                data: $filter_montadora.data('options')
            }
        ).on('select2:select', function () {
            build_filterAno(true);
        });
    }

    /**
     *
     */
    function build_filterAno(onSelect = false) {
        $filter_ano.empty().trigger("change");
        if ($filter_montadora.select2('data')[0]['id']) {
            if (onSelect) {
                $filter_montadora.select2('data')[0].anos.forEach((v, i) => v.selected = false);
            }
            $filter_ano.select2({
                    width: '100%',
                    data: $filter_montadora.select2('data')[0].anos
                }
            ).on('select2:select', function () {
                build_filterModelo(true)
            });
        } else {
            $filter_ano.select2({
                    width: '100%',
                    data: [{"id": "", "text": "Selecione..."}]
                }
            );
        }
        build_filterModelo();
    }

    /**
     *
     */
    function build_filterModelo(onSelect = false) {
        $filter_modelo.empty().trigger("change");
        if ($filter_ano.select2('data')[0]['id']) {
            if (onSelect) {
                $filter_ano.select2('data')[0].modelos.forEach((v, i) => v.selected = false);
            }
            $filter_modelo.select2({
                    width: '100%',
                    data: $filter_ano.select2('data')[0].modelos
                }
            );
        } else {
            $filter_modelo.select2({
                    width: '100%',
                    data: [{"id": "", "text": "Selecione..."}]
                }
            );
        }
    }


    build_filterMontadora();
    build_filterAno();
    build_filterModelo();


});
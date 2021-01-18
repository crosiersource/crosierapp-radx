/* eslint-disable */

let listId = "#distDFeList";

import Moment from 'moment';

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

Routing.setRoutingData(routes);

function getDatatablesColumns() {
    return [
        {
            name: 'e.id',
            data: 'e.id',
            title: 'ID',
            render: function (data, type, row) {
                return (data + "").padStart(8, "0");
            },
        },
        {
            name: 'e.tipoDistDFe',
            data: 'e.tipoDistDFe',
            title: 'Tipo',
        },
        {
            name: 'e.nsu',
            data: 'e.nsu',
            title: 'NSU'
        },
        {
            name: 'e.chave',
            data: 'e.chave',
            title: 'Chave'
        },
        {
            name: 'e.notaFiscalId',
            data: 'e',
            title: 'NF',
            render: function (data, type, row) {
                if (!data.notaFiscalId) {
                    return '';
                }

                let url;

                let params = data.tipoDistDFe.includes('EVENTO') ?
                    {'id': data.notaFiscalId, 'eventoId': data.eventoId} :
                    {'id': data.notaFiscalId};

                if (data.proprio) {
                    url = Routing.generate('emissaonfe_form', params);
                } else {
                    if (data.tipoDistDFe == 'RESNFE' || data.tipoDistDFe == 'RESEVENTO') {
                        url = Routing.generate('nfesFornecedores_formResumo', params);
                    } else {
                        url = Routing.generate('nfesFornecedores_form', params);
                    }
                }
                let tipoBtn = data.proprio ? 'btn-primary' : 'btn-warning';

                url += data.tipoDistDFe.includes('EVENTO') ? '#EVENTOS' : '';
                return '<button type="button"' +
                    'class="btn ' + tipoBtn + ' btn-sm" onclick="window.open(\'' + url + '\', \'_blank\')">' +
                    '<i class="fas fa-file-code"></i> Abrir NF</button>';
            },
        },
        {
            name: 'e.status',
            data: 'e.status',
            title: 'Status'
        },
        {
            name: 'e.updated',
            data: 'e',
            title: '',
            render: function (data, type, row) {
                let colHtml = "";

                let downloadXMLurl = Routing.generate('distDFe_downloadXML', {'distDFe': data.id});
                colHtml += '<button type="button" title="Ver XML do DistDFe" ' +
                    'class="btn btn-primary btn-sm" onclick="window.open(\'' + downloadXMLurl + '\', \'_blank\')">' +
                    '<i class="fas fa-file-code"></i></button> ';

                let reprocessar_url = Routing.generate('distDFe_reprocessarDistDFe', {'distDFe': data.id});
                colHtml += '<button type="button" title="Reprocessar o DistDFe" data-target="#confirmationModal" data-toggle="modal" ' +
                    'class="btn btn-primary btn-sm" data-url="' + reprocessar_url + '">' +
                    '<i class="fas fa-cog"></i></button> ';


                colHtml += '<br/><span class="badge badge-pill badge-info">' + Moment(data.updated).format('DD/MM/YYYY HH:mm:ss') + '</span> ';


                return colHtml;
            },
            className: 'text-right'
        }
    ];
}

DatatablesJs.makeDatatableJs(listId, getDatatablesColumns());
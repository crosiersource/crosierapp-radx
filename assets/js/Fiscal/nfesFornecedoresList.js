'use strict';

import Moment from 'moment';

import Numeral from 'numeral';
import 'numeral/locales/pt-br.js';

Numeral.locale('pt-br');

let listId = "#nfesFornecedoresList";

function getDatatablesColumns() {
    return [
        {
            name: 'e.id',
            data: 'e.id',
            title: 'Id'
        },
        {
            name: 'e.numero',
            data: 'e',
            title: 'Número/Série',
            render: function (data, type, row) {

                let html = '';
                html += '<div class="float-left">';
                html += "<b>" + String(data.numero ? data.numero : '0').padStart(6, "0") + "</b>/" + String(data.serie ? data.serie : 0).padStart(3, "0");
                html += '</div><div class="float-right">';

                if (data.nsu) {
                    html += '<span class="badge badge-pill badge-secondary">' + data.nsu + '</span>';
                }
                html += '</div>';
                return html;


                return
            }
        },
        {
            name: 'e.documento',
            data: 'e',
            title: 'Emitente',
            render: function (data, type, row) {
                let html = '';
                html += '<div class="float-left">';
                if (data && data.documentoEmitente && data.xNomeEmitente) {
                    html += '<span class="' + (data.documentoEmitente.length === 14 ? 'cnpj' : 'cpf') + '">' + data.documentoEmitente + '</span><br />' + data.xNomeEmitente;
                }
                html += '</div><div class="float-right">';

                if (data.resumo) {
                    html += '<span class="badge badge-pill badge-secondary">Resumo</span>';
                } else {
                    html += '<span class="badge badge-pill badge-success">Completa</span>';
                }
                html += '</div>';
                return html;
            }
        },
        {
            name: 'e.dtEmissao',
            data: 'e.dtEmissao',
            title: 'Dt Emissão',
            render: function (data, type, row) {
                return data ? Moment(data, Moment.ISO_8601, true).format('DD/MM/YYYY HH:mm:ss') : null;
            },
            className: 'text-center'

        },
        {
            name: 'e.valorTotal',
            data: 'e',
            title: 'Valor',
            render: function (data, type, row) {
                let val = parseFloat(data.valorTotal);
                return Numeral(val).format('$ 0.0,[00]');
            },
            className: 'text-right'
        },
        {
            name: 'e.id',
            data: 'e',
            title: '',
            render: function (data, type, row) {
                let colHtml = "";

                let downloadXMLurl = Routing.generate('nfesFornecedores_downloadXML', {'nf': data.id});

                let manifestarCienciaUrl = Routing.generate('nfesFornecedores_manifestar', {'nf': data.id});

                let pdfUrl = Routing.generate('fis_emissaonfe_imprimir', {'notaFiscal': data.id});

                let faturaUrl = Routing.generate('fin_movimentacao_pesquisaList', {'filter': {'notafiscal_id': data.id}});

                let fatura_id = data.jsonData?.fatura?.fatura_id ?? null;

                if (fatura_id) {
                    colHtml += '<a role="button" title="Visualizar Fatura" class="btn btn-sm btn-outline-warning mr-1"' +
                        ' href="' + faturaUrl + '">' +
                        ' <i class="fas fa-money-check-alt"></i></a>';
                }

                if (data.resumo && !data.manifestDest) {
                    colHtml += '<button type="button" title="Manifestar Ciência da Operação"' +
                        'class="btn btn-outline-primary btn-sm" ' +
                        'data-url="' + manifestarCienciaUrl + '" ' +
                        'data-target="#confirmationModal" data-toggle="modal">' +
                        '<i class="fas fa-eye"></i></button> ';
                }

                colHtml += '<button type="button" title="Download do XML"' +
                    'class="btn btn-primary btn-sm" onclick="window.open(\'' + downloadXMLurl + '\', \'_blank\')">' +
                    '<i class="fas fa-file-code"></i></button> ';

                colHtml += '<a role="button" value="Imprimir" title="Ver PDF" class="btn btn-sm btn-outline-success mr-1" ' +
                    'href="' + pdfUrl + '" target="_blank"> ' +
                    '<i class="fas fa-file-pdf" aria-hidden="true"></i> ' +
                    '</a>';

                let routeEdit = null;
                if (data.resumo) {
                    routeEdit = Routing.generate('nfesFornecedores_formResumo', {id: data.id});
                } else {
                    routeEdit = Routing.generate('nfesFornecedores_form', {id: data.id});
                }

                colHtml += DatatablesJs.makeEditButton(routeEdit);
                if ($(listId).data('routedelete')) {
                    let deleteUrl = Routing.generate($(listId).data('routedelete'), {id: data.id});
                    let csrfTokenDelete = $(listId).data('crsf-token-delete');
                    colHtml += DatatablesJs.makeDeleteButton(deleteUrl, csrfTokenDelete);
                }
                colHtml += '<br /><span class="badge badge-pill badge-info">' + Moment(data.updated).format('DD/MM/YYYY HH:mm:ss') + '</span> ';
                return colHtml;
            },
            className: 'text-right'
        }
    ];
}

DatatablesJs.makeDatatableJs(listId, getDatatablesColumns());
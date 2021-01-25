/* eslint-disable */

import Moment from 'moment';

import Numeral from 'numeral';
import 'numeral/locales/pt-br.js';

Numeral.locale('pt-br');

let listId = "#emissaoNFeList";

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
                return "<b>" +
                    String(data.numero ? data.numero : '0').padStart(6, "0") + "</b>/" +
                    String(data.serie).padStart(3, "0") + "<br>" + data.documentoEmitente.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g,"\$1.\$2.\$3\/\$4\-\$5");;
            }
        },
        {
            name: 'e.xNomeDestinatario',
            data: 'e',
            title: 'Destinatário',
            render: function (data, type, row) {
                return '<span class="' + (data.documentoDestinatario && data.documentoDestinatario.length === 14 ? 'cnpj' : 'cpf') + '">' + data.documentoDestinatario + '</span><br /><b>' + data.xNomeDestinatario + '</b>';
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
            name: 'e.cStat',
            data: 'e',
            title: 'Status',
            render: function (data, type, row) {

                let r = '';
                if (data.cStat) {
                    r += data.cStat + ' - ' + data.xMotivo;
                }
                if (data.cStatLote && data.cStatLote != data.cStat) {
                    if (data.cStat) r += '<br>';
                    r += data.cStatLote + ' - ' + data.xMotivoLote;
                }

                return r;
            },
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

                if ($(listId).data('routeedit')) {
                    let routeedit = Routing.generate($(listId).data('routeedit'), {id: data.id});
                    colHtml += DatatablesJs.makeEditButton(routeedit);
                }
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
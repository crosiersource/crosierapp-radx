'use strict';

import Moment from 'moment';

import Numeral from 'numeral';
import 'numeral/locales/pt-br.js';

import $ from "jquery";

Numeral.locale('pt-br');

let listId = "#produto_list";

function getDatatablesColumns() {
    return [
        {
            name: 'e.id',
            data: 'e.id',
            title: 'Código'
        },
        {
            name: 'e.titulo',
            data: 'e',
            title: 'Título',
            render: function (data, type, row) {
                let s = '<div class="float-left">' + data.nome + '</div>';
                if (data.imagem1) {
                    s += '<div class="float-right"><img src="' + crosierAppVendestUrl + '/images/produtos/' + data.depto.id + '/' + data.grupo.id + '/' + data.subgrupo.id + '/' + data.imagem1 + '" width="50px"/></div>';
                }
                return s;
            },
        },
        {
            name: 'e.nomeDepto',
            data: 'e.nomeDepto',
            title: 'Depto',
            render: function (data, type, row) {
                return data;
            }
        },
        {
            name: 'e.status',
            data: 'e.status',
            title: 'Status',
            render: function (data, type, row) {
                return data;
            }
        },
        {
            name: 'e.porcentPreench',
            data: 'e',
            title: 'Status Cad',
            render: function (data, type, row) {
                return Numeral(parseFloat(data.porcentPreench) * 100).format('0,0') + '%';
            }
        },
        {
            name: 'e.updated',
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


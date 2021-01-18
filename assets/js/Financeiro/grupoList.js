/* eslint-disable */


import routes from '../../static/fos_js_routes.json';

import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';

let listId = "#grupoList";

// Gambi pra passar o json certo
Routing.setRoutingData(routes);

function getDatatablesColumns() {
    return [
        {
            name: 'e.id',
            data: 'e.id',
            title: 'Id'
        },
        {
            name: 'e.descricao',
            data: 'e.descricao',
            title: 'Descrição'
        },
        {
            name: 'e.ativo',
            data: 'e.ativo',
            title: 'Ativo',
            render: function (data, type, row) {
                return data ? 'SIM' : 'NÃO';
            },
            className: 'text-center'

        },
        {
            name: 'e.id',
            data: 'e',
            title: '',
            render: function (data, type, row) {
                let colHtml = "";
                let routeItem = Routing.generate('grupoItem_list', {'pai': data.id});
                colHtml +=
                    '<a class="btn btn-sm btn-warning" href="' + routeItem + '" role="button" title="Itens">' +
                        '<i class="fas fa-list-alt" aria-hidden="true"></i>' +
                    '</a> ';


                if ($(listId).data('routeedit')) {
                    let routeedit = Routing.generate($(listId).data('routeedit'), {id: data.id});
                    colHtml += DatatablesJs.makeEditButton(routeedit);
                }
                if ($(listId).data('routedelete')) {
                    let deleteUrl = Routing.generate($(listId).data('routedelete'), {id: data.id});
                    let csrfTokenDelete = $(listId).data('crsf-token-delete');
                    colHtml += DatatablesJs.makeDeleteButton(deleteUrl, csrfTokenDelete);
                }
                return colHtml;
            },
            className: 'text-right'
        }
    ];
}

DatatablesJs.makeDatatableJs(listId, getDatatablesColumns());


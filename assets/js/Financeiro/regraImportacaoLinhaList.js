'use strict';


let listId = "#regraImportacaoLinhaList";


function getDatatablesColumns() {
    return [
        {
            name: 'e.id',
            data: 'e.id',
            title: 'Id'
        },
        {
            name: 'e.regraRegexJava',
            data: 'e.regraRegexJava',
            title: 'Regex',
            render: function (data, type, row) {
                return Utils.escapeHtml(data);
            }
        },
        {
            name: 'carteira.descricaoMontada',
            data: 'e.carteira',
            title: 'Carteira',
            render: function (data, type, row) {
                return data ? data.descricaoMontada : '';
            }
        },
        {
            name: 'e.tipoLancto',
            data: 'e.tipoLancto.descricao',
            title: 'Tipo Lancto'
        },
        {
            name: 'e.status',
            data: 'e.status',
            title: 'Status'
        },
        {
            name: 'e.modo',
            data: 'e.modo.descricaoMontada',
            title: 'Modo'
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
                return colHtml;
            },
            className: 'text-right'
        }
    ];
}

DatatablesJs.makeDatatableJs(listId, getDatatablesColumns());
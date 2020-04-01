'use strict';


import Numeral from 'numeral';
import 'numeral/locales/pt-br.js';


import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
import $ from "jquery";

Numeral.locale('pt-br');

Routing.setRoutingData(routes);

$(document).ready(function () {

    let $produto = $('#produto');
    let $descricao = $('#pedido_compra_item_descricao');


    if ($produto.data('val')) {
        $.ajax({
                type: 'GET',
                url: '/est/produto/findById/' + $produto.data('val'),
                async: true,
                contentType: "application/json",
                dataType: 'json'
            }
        ).done(function (results) {
            $descricao.val(results.produto.nome);

            $produto.empty().trigger("change");
            let d =
                [{
                    'id': results.produto.id,
                    'text': results.produto.nome,
                    'selected': true
                }]
            ;
            $('#produto').select2({
                data: d
            });
            console.log('stou auq: ' + $produto.data('val'));
            console.dir(d);
            $produto.val($produto.data('val')).trigger('change');

        });
    } else {
        $produto = $produto.select2({
            width: '100%',
            dropdownAutoWidth: true,
            minimumInputLength: 2,
            placeholder: '...',
            allowClear: true,
            ajax: {
                delay: 750,
                url: function (params) {
                    return Routing.generate('est_produto_findProdutoByIdOuNome');
                },
                dataType: 'json',
                cache: true
            }
        }).on('select2:select', function () {
            $descricao.val($produto.select2('data')[0].text);
        });
    }


});
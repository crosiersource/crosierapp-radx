'use strict';

import Sortable from 'sortablejs';
import $ from "jquery";

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import toastrr from "toastr";

Routing.setRoutingData(routes);


$(document).ready(function () {

    let sortable = Sortable.create(simpleList,
        {
            animation: 150,
            onEnd: function (/**Event*/evt) {
                let jsonSortable = JSON.stringify(sortable.toArray());

                $.ajax({
                    url: Routing.generate('categoria_saveOrdem'),
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'jsonSortable': jsonSortable
                    },
                    success: function (res) {
                        if (res.result === 'OK') {
                            toastrr.info('Itens ordenados');
                        } else {
                            toastrr.error('Erro ao ordenar itens');
                        }
                    }
                });

            }
        }
    );




});

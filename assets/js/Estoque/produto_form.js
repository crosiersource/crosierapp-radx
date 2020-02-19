'use strict';

import $ from 'jquery';

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import Sortable from 'sortablejs';
import 'summernote/dist/summernote-bs4.js';
import 'summernote/dist/summernote-bs4.css';
import toastrr from "toastr";

import 'lightbox2/dist/css/lightbox.css';
import 'lightbox2';

Routing.setRoutingData(routes);

import Numeral from 'numeral';
import 'numeral/locales/pt-br.js';
Numeral.locale('pt-br');


$(document).ready(function () {

    let $depto = $('#produto_depto');
    let $grupo = $('#produto_grupo');
    let $subgrupo = $('#produto_subgrupo');

    $('.summernote').summernote();

    $depto.on('select2:select select2:clear', function () {
        buildGrupo()
    });

    function buildGrupo() {
        if ($depto.val()) {
            $.ajax({
                    dataType: "json",
                    url: Routing.generate('est_grupo_select2json') + '/' + $depto.val(),
                    type: 'GET'
                }
            ).done(function (data) {
                let results = data.results;
                // o valor por ter vindo pelo value ou pelo data-val (ou por nenhum)
                let val = $grupo.val() ? $grupo.val() : $grupo.data('val');
                $grupo.empty().trigger("change");

                results.unshift({"id": "", "text": ""});

                $grupo.select2({
                        placeholder: "...",
                        data: results,
                        width: '100%'
                    }
                );
                // Se veio o valor do PHP...
                if (val) {
                    $grupo.val(val).trigger('change');
                }
            });
        } else {
            $grupo.empty().trigger("change");
            $grupo.select2();
        }
    }


    $grupo.on('select2:select select2:clear', function () {
        buildSubgrupo()
    });

    function buildSubgrupo() {
        if ($grupo.val()) {
            $.ajax({
                    dataType: "json",
                    url: Routing.generate('est_subgrupo_select2json') + '/' + $grupo.val(),
                    type: 'GET'
                }
            ).done(function (data) {
                let results = data.results;
                // o valor por ter vindo pelo value ou pelo data-val (ou por nenhum)
                let val = $subgrupo.val() ? $subgrupo.val() : $subgrupo.data('val');
                $subgrupo.empty().trigger("change");

                results.unshift({"id": "", "text": ""});

                $subgrupo.select2({
                        placeholder: "...",
                        data: results,
                        width: '100%'
                    }
                );
                // Se veio o valor do PHP...
                if (val) {
                    $subgrupo.val(val).trigger('change');
                }
            });
        } else {
            $subgrupo.empty().trigger("change");
            $subgrupo.select2();
        }
    }

    buildGrupo();
    buildSubgrupo();


    $('.btnComposicaoEdit').on('click', function (e) {

        // produtoComposicao_produtoFilho
        let produtoComposicao = $(this).data('json');
        let $produtoComposicao_produtoFilho = $('#produtoComposicao_produtoFilho');
        let text = produtoComposicao.produtoFilho.titulo ?
            produtoComposicao.produtoFilho.titulo + ' (' + produtoComposicao.produtoFilho.id + ')' :
            produtoComposicao.produtoFilho.nome + ' (' + produtoComposicao.produtoFilho.id + ')';
        $produtoComposicao_produtoFilho.append(new Option(text, produtoComposicao.produtoFilho.id, true, true)).trigger('change');

        $('#produtoComposicao_id').val(produtoComposicao.id);
        $('#produtoComposicao_qtde').val(produtoComposicao.qtde);
        $('#produtoComposicao_precoComposicao').val( Numeral(parseFloat(produtoComposicao.precoComposicao)).format('0.0,[00]') );

        document.body.scrollTop = 0; // For Safari
        document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
    });


    $('.btnImagemEdit').on('click', function (e) {
        let produtoImagem = $(this).data('json');
        $('#produto_imagem_id').val(produtoImagem.id);
        $('#produto_imagem_descricao').val(produtoImagem.descricao);
        document.body.scrollTop = 0; // For Safari
        document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
    });

    if ($('#ulFotosSortable').length) {
        Sortable.create(ulFotosSortable,
            {
                animation: 150,
                onEnd:
                    function (/**Event*/evt) {
                        let ids = '';
                        $('#ulFotosSortable > li').each(function () {
                            ids += $(this).data('id') + ',';
                        });

                        $.ajax({
                                dataType: "json",
                                data: {'ids': ids},
                                url: Routing.generate('est_produto_formImagemSaveOrdem'),
                                type: 'POST'
                            }
                        ).done(function (data) {
                            if (data.result === 'OK') {
                                toastrr.success('Fotos ordenadas com sucesso');

                                $.each(data.ids, function (id, ordem) {
                                    $('#ulFotosSortable > li[data-id="' + id + '"] > div > span.ordem').html(ordem);
                                });

                            } else {
                                toastrr.error('Erro ao ordenar itens');
                            }
                        });
                    }

            });
    }


    if ($('#tbodySortableComposicao').length) {
        Sortable.create(tbodySortableComposicao,
            {
                animation: 150,
                onEnd:
                    function (/**Event*/evt) {
                        let ids = '';
                        $('#tbodySortableComposicao > tr').each(function () {
                            ids += $(this).data('id') + ',';
                        });

                        $.ajax({
                                dataType: "json",
                                data: {'ids': ids},
                                url: Routing.generate('est_produto_formComposicaoSaveOrdem'),
                                type: 'POST'
                            }
                        ).done(function (data) {
                            if (data.result === 'OK') {
                                toastrr.success('Itens ordenados com sucesso');

                                $.each(data.ids, function (id, ordem) {
                                    $('#tbodySortableComposicao > tr[data-id="' + id + '"] > td[id="ordem"]').html(ordem);
                                });

                            } else {
                                toastrr.error('Erro ao ordenar itens');
                            }
                        });
                    }
            });
    }

    $('#produto_imagem_imageFile').on('change',function(){
        //get the file name
        let fileName = $(this).val().split('\\').pop();
        //replace the "Choose a file" label
        $(this).next('.custom-file-label').html(fileName);
    })


});

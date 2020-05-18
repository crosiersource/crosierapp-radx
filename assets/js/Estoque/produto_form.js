'use strict';

import $ from 'jquery';

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import Sortable from 'sortablejs';

import 'bootstrap';
import 'popper.js';
import 'summernote/dist/summernote-bs4.js';
import 'summernote/dist/summernote-bs4.css';

import 'select2/dist/css/select2.css';
import 'select2';
import 'select2/dist/js/i18n/pt-BR.js';
import 'select2-bootstrap-theme/dist/select2-bootstrap.css';


import toastrr from "toastr";

import 'lightbox2/dist/css/lightbox.css';
import 'lightbox2';
import 'blueimp-file-upload';

import Numeral from 'numeral';
import 'numeral/locales/pt-br.js';

Routing.setRoutingData(routes);

Numeral.locale('pt-br');


$(document).ready(function () {

    $.fn.select2.defaults.set("theme", "bootstrap");
    $.fn.select2.defaults.set("language", "pt-BR");

    let $depto = $('#produto_depto');
    let $grupo = $('#produto_grupo');
    let $subgrupo = $('#produto_subgrupo');
    let $btnImagemEdit = $('.btnImagemEdit');

    let $produtoId = $('#produto_id');
    let $produtoComposicaoId = $('#produtoComposicao_id');
    let $produtoComposicaoProdutoFilho = $('#produtoComposicao_produtoFilho');
    let $produtoComposicaoQtde = $('#produtoComposicao_qtde');
    let $produtoComposicaoPrecoComposicao = $('#produtoComposicao_precoComposicao');


    let $imageFile = $('#produto_imagem_imageFile');

    $imageFile.on('change', function () {
        //get the file name
        let fileName = $(this).val().split('\\').pop();
        //replace the "Choose a file" label
        $(this).next('.custom-file-label').html(fileName);
    });

    $imageFile.fileupload({
        dataType: 'json',
        singleFileUploads: false,
        add: function (e, data) {
            data.submit();
        },
        success: function (result, textStatus, jqXHR) {
            $('#filesUl').html(result.filesUl);
            createUlFotosSortable();
            toastrr.success('Imagem salva com sucesso');
        },
        fail: function (result, textStatus, jqXHR) {
            toastrr.error('Erro ao salvar imagem');
        },
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                $('<p/>').text(file.name).appendTo(document.body);
            });
        }
    });

    function submitImagemDescricao(produtoImagemId) {
        let dados = {
            'produtoImagemId': produtoImagemId,
            'descricao': $('#produto_imagem_descricao_' + produtoImagemId).val()
        };
        $.ajax({
                dataType: "json",
                data: dados,
                url: Routing.generate('est_produto_formImagemSalvarDescricao') + '/',
                type: 'POST'
            }
        ).done(function (data) {
            $('#filesUl').html(data.filesUl);
            toastrr.success('Descrição salva com sucesso');
        });
    }

    $('.produto_imagem_descricao').keypress(function (event) {
        if ((event.keyCode ? event.keyCode : event.which) === 13) {
            let produtoImagemId = $(this).data('produtoimagemid');
            submitImagemDescricao(produtoImagemId);
            event.preventDefault();
            return false;
        }
    });

    $btnImagemEdit.on('click', function (e) {
        let produtoImagemId = $(this).data('produtoimagemid');
        submitImagemDescricao(produtoImagemId);
    });


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
                        width: '100%',
                        dropdownAutoWidth: true,
                        placeholder: "...",
                        data: results
                    }
                );
                // Se veio o valor do PHP...
                if (val) {
                    $grupo.val(val).trigger('change');
                }
            });
        } else {
            $grupo.empty().trigger("change");
            $grupo.select2({
                width: '100%',
                dropdownAutoWidth: true,
                placeholder: "..."
            });
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
                        width: '100%',
                        dropdownAutoWidth: true,
                        placeholder: "...",
                        data: results
                    }
                );
                // Se veio o valor do PHP...
                if (val) {
                    $subgrupo.val(val).trigger('change');
                }
            });
        } else {
            $subgrupo.empty().trigger("change");
            $subgrupo.select2({
                width: '100%',
                dropdownAutoWidth: true,
                placeholder: "..."
            });
        }
    }

    buildGrupo();
    buildSubgrupo();


    function createUlFotosSortable() {
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
                                    $('#ulFotosSortable > li[data-id="' + id + '"] > div > div > label > span.ordem').html(ordem);
                                });

                            } else {
                                toastrr.error('Erro ao ordenar itens');
                            }
                        });
                    }

            });
    }

    if ($('#ulFotosSortable').length) {
        createUlFotosSortable();
    }

    function createSortableComposicao() {
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
    }


    function submitComposicao() {

        if (!$produtoComposicaoProdutoFilho.val() || !$produtoComposicaoQtde.val() || !$produtoComposicaoPrecoComposicao.val()) {
            toastrr.error('"É necessário informar o "Item", a "Qtde" e o "Preço (Compo)"');
            return;
        }

        let composicao = {
            "produtoComposicao": {
                "id": $produtoComposicaoId.val(),
                "produtoFilho": $produtoComposicaoProdutoFilho.val(),
                "qtde": $produtoComposicaoQtde.val(),
                "precoComposicao": $produtoComposicaoPrecoComposicao.val()
            }
        };

        $.ajax({
                dataType: "json",
                data: composicao,
                url: Routing.generate('est_produto_formComposicao') + '/' + $produtoId.val(),
                type: 'POST'
            }
        ).done(function (data) {
            if (data.result === 'OK') {
                $('#divTbComposicao').html(data.divTbComposicao);
                $produtoComposicaoId.val('');
                $produtoComposicaoProdutoFilho.val('').trigger('change');
                $produtoComposicaoQtde.val('');
                $produtoComposicaoPrecoComposicao.val('');
                $produtoComposicaoProdutoFilho.select2('focus');
                initForm();
                toastrr.success('Item salvo com sucesso');
            } else {
                toastrr.error(data.msg ? data.msg : 'Erro ao salvar item da composição');
            }

        });
    }

    $('#btnSalvarItemComposicao').on('click', function (e) {
        submitComposicao();
    });


    function initForm() {
        $('.btnComposicaoEdit').on('click', function (e) {

            // produtoComposicao_produtoFilho
            let produtoComposicao = $(this).data('json');

            let text = produtoComposicao.produtoFilho.titulo ?
                produtoComposicao.produtoFilho.titulo + ' (' + produtoComposicao.produtoFilho.id + ')' :
                produtoComposicao.produtoFilho.nome + ' (' + produtoComposicao.produtoFilho.id + ')';
            $produtoComposicaoProdutoFilho.append(new Option(text, produtoComposicao.produtoFilho.id, true, true)).trigger('change');

            $produtoComposicaoId.val(produtoComposicao.id);
            $produtoComposicaoQtde.val(produtoComposicao.qtde);
            $produtoComposicaoPrecoComposicao.val(Numeral(parseFloat(produtoComposicao.precoComposicao)).format('0.0,[00]'));

            document.body.scrollTop = 0; // For Safari
            document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
        });

        createSortableComposicao();
    }


    initForm();


});

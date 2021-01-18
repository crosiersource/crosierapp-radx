/* eslint-disable */


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
import Moment from "moment";

$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");


Routing.setRoutingData(routes);

Numeral.locale('pt-br');


$(document).ready(function () {

    let $depto = $('#produto_depto');
    let $grupo = $('#produto_grupo');
    let $subgrupo = $('#produto_subgrupo');
    let $btnImagemEdit = $('.btnImagemEdit');

    let $produtoId = $('#produto_id');

    let $produtoComposicaoId = $('#produtoComposicao_id');
    let $produtoComposicaoProdutoFilho = $('#produtoComposicao_produtoFilho');
    let $produtoComposicaoQtde = $('#produtoComposicao_qtde');
    let $produtoComposicaoPrecoComposicao = $('#produtoComposicao_precoComposicao');
    let $produtoComposicaoPrecoTabela = $('#produtoComposicao_precoTabela');


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

    $depto.select2({
        width: '100%',
        dropdownAutoWidth: true,
        placeholder: "..."
    });

    $depto.on('select2:select select2:clear', function () {
        $grupo.data('val', '');
        $grupo.val('');
        buildGrupo();
        $subgrupo.data('val', '');
        $subgrupo.val('');
        buildSubgrupo();
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
                $grupo.val(val).trigger('change');
            });
        } else {
            $grupo.empty().val('').trigger("change");
            $grupo.select2({
                width: '100%',
                dropdownAutoWidth: true,
                placeholder: "..."
            });
        }
    }


    $grupo.on('select2:select select2:clear', function () {
        $subgrupo.data('val', '');
        $subgrupo.val('');
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
            $subgrupo.empty().val('').trigger("change");
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


    $produtoComposicaoProdutoFilho.select2({
        minimumInputLength: 4,
        width: '100%',
        dropdownAutoWidth: true,
        placeholder: '...',
        allowClear: true,
        ajax: {
            delay: 750,
            url: Routing.generate('est_produto_findProdutoParaComposicao'),
            dataType: 'json',
            cache: true,
        },
    }).on('select2:select', function () {
        let o = $produtoComposicaoProdutoFilho.select2('data')[0];

        let precoTabela = parseFloat(o.preco_tabela).toFixed(2).replace('.', ',');
        $produtoComposicaoPrecoTabela.val(precoTabela);

    });


    function editComposicao() {
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


    let $produtoPreco_id = $('#produtoPreco_id');

    let $produtoPreco_lista = $('#produtoPreco_lista').select2({
        width: '100%',
        dropdownAutoWidth: true,
        placeholder: '...',
        allowClear: true,
        data: $('#produtoPreco_lista').data('options')
    });

    let $produtoPreco_unidade = $('#produtoPreco_unidade').select2({
        width: '100%',
        dropdownAutoWidth: true,
        placeholder: '...',
        allowClear: true,
        data: $('#produtoPreco_unidade').data('options')
    });

    let $produtoPreco_precoCusto = $('#produtoPreco_precoCusto');
    let $produtoPreco_coeficiente = $('#produtoPreco_coeficiente');
    let $produtoPreco_margem = $('#produtoPreco_margem');
    let $produtoPreco_custoOperacional = $('#produtoPreco_custoOperacional');
    let $produtoPreco_custoFinanceiro = $('#produtoPreco_custoFinanceiro');
    let $produtoPreco_prazo = $('#produtoPreco_prazo');
    let $produtoPreco_precoPrazo = $('#produtoPreco_precoPrazo');
    let $produtoPreco_precoVista = $('#produtoPreco_precoVista');
    let $produtoPreco_precoPromo = $('#produtoPreco_precoPromo');
    let $produtoPreco_atual = $('#produtoPreco_atual');
    let $produtoPreco_dtCusto = $('#produtoPreco_dtCusto');
    let $produtoPreco_dtPrecoVenda = $('#produtoPreco_dtPrecoVenda');


    function editPreco() {
        $('.btnPrecoEdit').on('click', function (e) {

            // produtoPreco_produtoFilho
            let produtoPreco = $(this).data('json');

            $produtoPreco_id.val(produtoPreco.id);
            $produtoPreco_lista.val(produtoPreco.lista.id).trigger('change');
            $produtoPreco_unidade.val(produtoPreco.unidade.id).trigger('change');
            $produtoPreco_precoCusto.val(parseFloat(produtoPreco.precoCusto).toFixed(2).replace('.', ','));

            $produtoPreco_coeficiente.val(parseFloat(produtoPreco.coeficiente).toFixed(3).replace('.', ','));
            $produtoPreco_margem.val(parseFloat(produtoPreco.margem).toFixed(3).replace('.', ','));
            $produtoPreco_custoOperacional.val(parseFloat(produtoPreco.custoOperacional).toFixed(3).replace('.', ','));
            $produtoPreco_custoFinanceiro.val(parseFloat(produtoPreco.custoFinanceiro).toFixed(3).replace('.', ','));
            $produtoPreco_prazo.val(produtoPreco.prazo);
            $produtoPreco_precoPrazo.val(parseFloat(produtoPreco.precoPrazo).toFixed(2).replace('.', ','));
            $produtoPreco_precoVista.val(parseFloat(produtoPreco.precoVista).toFixed(2).replace('.', ','));
            $produtoPreco_precoPromo.val(parseFloat(produtoPreco.precoPromo).toFixed(2).replace('.', ','));
            $produtoPreco_atual.prop('checked', produtoPreco.atual == true);

            $produtoPreco_dtCusto.val(Moment(produtoPreco.dtCusto.substr(0, 10)).format('DD/MM/YYYY'));
            $produtoPreco_dtPrecoVenda.val(Moment(produtoPreco.dtPrecoVenda.substr(0, 10)).format('DD/MM/YYYY'));

            CrosierMasks.maskDecs();

            document.body.scrollTop = 0; // For Safari
            document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
        });
    }

    function submitPreco() {

        if (!$produtoPreco_lista.val()) {
            toastrr.error('"É necessário informar "Lista"');
            return;
        }
        if (!$produtoPreco_unidade.val()) {
            toastrr.error('"É necessário informar "Unidade"');
            return;
        }
        if (!$produtoPreco_precoCusto.val()) {
            toastrr.error('"É necessário informar "Preço de Custo"');
            return;
        }


        let preco = {
            "produtoPreco": {
                "id": $produtoPreco_id.val(),
                "lista": $produtoPreco_lista.val(),
                "unidade": $produtoPreco_unidade.val(),
                "precoCusto": $produtoPreco_precoCusto.val(),
                "coeficiente": $produtoPreco_coeficiente.val(),
                "margem": $produtoPreco_margem.val(),
                "custoOperacional": $produtoPreco_custoOperacional.val(),
                "custoFinanceiro": $produtoPreco_custoFinanceiro.val(),
                "prazo": $produtoPreco_prazo.val(),
                "precoPrazo": $produtoPreco_precoPrazo.val(),
                "precoVista": $produtoPreco_precoVista.val(),
                "precoPromo": $produtoPreco_precoPromo.val(),
                "dtCusto": $produtoPreco_dtCusto.val(),
                "dtPrecoVenda": $produtoPreco_dtPrecoVenda.val(),
                "atual": $produtoPreco_atual.val(),
            }
        };

        $.ajax({
                dataType: "json",
                data: preco,
                url: Routing.generate('est_produto_formPreco') + '/' + $produtoId.val(),
                type: 'POST'
            }
        ).done(function (data) {
            if (data.result === 'OK') {
                $('#divTbPrecos').html(data.divTbPrecos);

                $produtoPreco_id.val('');
                $produtoPreco_lista.val('').trigger('change');
                $produtoPreco_unidade.val('').trigger('change');
                $produtoPreco_precoCusto.val('');
                $produtoPreco_coeficiente.val('');
                $produtoPreco_margem.val('');
                $produtoPreco_custoOperacional.val('');
                $produtoPreco_custoFinanceiro.val('');
                $produtoPreco_prazo.val('');
                $produtoPreco_precoPrazo.val('');
                $produtoPreco_precoVista.val('');
                $produtoPreco_precoPromo.val('');
                $produtoPreco_dtPrecoVenda.val('');
                $produtoPreco_dtCusto.val('');

                initForm();
                toastrr.success('Preço salvo com sucesso');
            } else {
                toastrr.error(data.msg ? data.msg : 'Erro ao salvar preço');
            }

        });
    }

    $('#btnSalvarPreco').on('click', function (e) {
        submitPreco();
    });


    function initForm() {
        editComposicao();
        editPreco();

        createSortableComposicao();
    }


    initForm();


});

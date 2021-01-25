/* eslint-disable */

import $ from "jquery";


$(document).ready(function () {

    $('#btn_ante').on('click', function() {
        $('#dtVenda_i').val($(this).data('ante-periodoi'));
        $('#dtVenda_f').val($(this).data('ante-periodof'));
        $('#formPesquisar').submit();
    });

    $('#btn_prox').on('click', function() {
        $('#dtVenda_i').val($(this).data('prox-periodoi'));
        $('#dtVenda_f').val($(this).data('prox-periodof'));
        $('#formPesquisar').submit();
    });

});
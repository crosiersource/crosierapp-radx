/* eslint-disable */

import $ from "jquery";

import Moment from "moment";

import "daterangepicker";

import "bootstrap";

import Numeral from "numeral";

import "print-js";

import "numeral/locales/pt-br.js";

import "select2/dist/css/select2.css";
import "select2";
import "select2/dist/js/i18n/pt-BR.js";
import "select2-bootstrap-theme/dist/select2-bootstrap.css";
import hotkeys from "hotkeys-js";
import routes from "../../static/fos_js_routes.json";
import Routing from "../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js";

Moment.locale("pt-BR");

$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");

Numeral.locale("pt-br");

Routing.setRoutingData(routes);

$(document).ready(function () {
  const $cliente = $("#clienteId");
  const $cliente_documento = $("#venda_jsonData_cliente_documento");
  const $cliente_nome = $("#cliente_nome");
  const $s2PesquisarCliente = $("#s2PesquisarCliente");
  const $cliente_fone = $("#venda_jsonData_cliente_fone");
  const $cliente_email = $("#venda_jsonData_cliente_email");

  const $dtVenda = $("#venda_dtVenda");

  const $pesquisarClienteModal = $("#pesquisarClienteModal");
  const $btnCancelarPesquisa = $("#btnCancelarPesquisa");

  // cache para não buscar toda hora
  $cliente_documento.data("val", $cliente_documento.val());

  $cliente_documento.on("blur", function () {
    if ($cliente_documento.val() !== $cliente_documento.data("val")) {
      $cliente_documento.data("val", $cliente_documento.val());
      $.ajax({
        url: `${Routing.generate(
          "crm_cliente_findClienteByDocumento"
        )}?term=${$cliente_documento.val()}`,
        type: "post",
        dataType: "json",
        success(res) {
          if (res?.results) {
            //     $cliente_nome.empty().trigger("change");
            //     $cliente_nome.append(new Option(res?.results[0]?.text, res?.results[0]?.text, false, false)).trigger('change');
            $cliente_nome.val(res?.results[0]?.text);
            $cliente_fone.val(res?.results[0]?.json_data?.fone1);
            $cliente_email.val(res?.results[0]?.json_data?.email);
            $cliente.val(res?.results[0]?.id);
          } else {
            $cliente_nome.val("");
            $cliente_fone.val("");
            $cliente_email.val("");
            $cliente.val("");
          }
        },
      });
    }
  });

  $s2PesquisarCliente
    .select2({
      minimumInputLength: 3,
      width: "100%",
      dropdownAutoWidth: true,
      placeholder: "...",
      allowClear: true,
      dropdownParent: $pesquisarClienteModal,
      ajax: {
        delay: 750,
        url: Routing.generate("ven_venda_findClienteByStr"),
        dataType: "json",
        processResults(data) {
          const mapped = $.map(data.results, function (obj) {
            obj.id = obj.text;
            return obj;
          });
          return {
            results: mapped,
          };
        },
      },
    })
    .on("select2:select", function () {
      const o = $s2PesquisarCliente.select2("data")[0];

      if (o?.documento) {
        // só retorna documento se achou o cliente na base
        $cliente_documento.val(o?.documento);
        $cliente_nome.val(o?.text);
        $cliente.val(o?.id);
        $cliente_fone.val(o?.json_data?.fone1 ?? "");
        $cliente_email.val(o?.json_data?.email ?? "");
        CrosierMasks.maskDecs();
        $pesquisarClienteModal.modal("hide");
        $btnCancelarPesquisa.click();
      }
    });

  const k = hotkeys.noConflict();
  k("ctrl+1", function (event, handler) {
    event.preventDefault();
    $("#aDados")[0].click();
  });
  k("ctrl+2", function (event, handler) {
    event.preventDefault();
    $("#aItens")[0].click();
  });
  k("ctrl+3", function (event, handler) {
    event.preventDefault();
    $("#aPagto")[0].click();
  });
  k("ctrl+4", function (event, handler) {
    event.preventDefault();
    $("#aResumo")[0].click();
  });
  k("ctrl+p", function (event, handler) {
    event.preventDefault();
    // $('#btnPesquisarCliente')[0].click();
    $pesquisarClienteModal.modal("show");
  });

  $pesquisarClienteModal.on("shown.bs.modal", function (e) {
    $s2PesquisarCliente.select2("open");
  });

  $dtVenda.daterangepicker({
    timePicker: false,
    autoUpdateInput: true,
    autoApply: true,
    singleDatePicker: true,
    showDropdowns: true,
    timePicker24Hour: true,
    locale: {
      format: "DD/MM/YYYY HH:mm",
      separator: " - ",
      applyLabel: "Aplicar",
      cancelLabel: "Cancelar",
      fromLabel: "De",
      toLabel: "Até",
      customRangeLabel: "Custom",
      daysOfWeek: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"],
      monthNames: [
        "Janeiro",
        "Fevereiro",
        "Março",
        "Abril",
        "Maio",
        "Junho",
        "Julho",
        "Agosto",
        "Setembro",
        "Outubro",
        "Novembro",
        "Dezembro",
      ],
      firstDay: 0,
    },
  });

  /**
   * RTA: como o venda_jsonData_cliente_nome é adicionando manualmente sem o form do symfony (para poder ser um
   * select2js), e mais tarde é necessário adicionar os demais campos json_data com o {{ form_widget(form) }}
   * então o Symfony vai adicionar outro venda_jsonData_cliente_nome, porém do tipo input text. Removemos ele
   * do DOM para não ser submetido.
   *
   * Atenção que ainda tem outro input text com name=venda_jsonData_cliente_nome que é utilizado quando é um novo CPF/CNPJ sendo cadastrado.
   *
   */
  $("input[type=text]#venda_jsonData_cliente_nome").remove();

  $cliente_documento.focus();
});

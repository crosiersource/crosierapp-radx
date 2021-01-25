/* eslint-disable */

import $ from "jquery";

import "daterangepicker";

import Moment from "moment";

import routes from "../../static/fos_js_routes.json";
import Routing from "../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js";

import "datatables";
import "datatables.net-bs4/css/dataTables.bootstrap4.css";
import "datatables/media/css/jquery.dataTables.css";

Routing.setRoutingData(routes);

$(document).ready(function () {
  const $filter_dtsVenda = $("#filter_dtsVenda");

  const $form_vendasPorDia_list = $("#form_vendasPorDia_list");

  $filter_dtsVenda
    .daterangepicker(
      {
        opens: "left",
        autoApply: true,
        locale: {
          format: "DD/MM/YYYY",
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
        ranges: {
          Hoje: [Moment(), Moment()],
          Ontem: [Moment().subtract(1, "days"), Moment().subtract(1, "days")],
          "Últimos 7 dias": [Moment().subtract(6, "days"), Moment()],
          "Últimos 30 dias": [Moment().subtract(29, "days"), Moment()],
          "Este mês": [Moment().startOf("month"), Moment().endOf("month")],
          "Mês passado": [
            Moment().subtract(1, "month").startOf("month"),
            Moment().subtract(1, "month").endOf("month"),
          ],
        },
        alwaysShowCalendars: true,
      },
      function (start, end, label) {
        $form_vendasPorDia_list.submit();
      }
    )
    .on("apply.daterangepicker", function (ev, picker) {
      $form_vendasPorDia_list.submit();
    });
});

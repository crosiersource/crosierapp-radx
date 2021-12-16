/* eslint-disable */
const Encore = require('@symfony/webpack-encore');
const path = require('path');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

// noinspection NpmUsedModulesInstalled
const webpack = require('webpack');

const CopyWebpackPlugin = require('copy-webpack-plugin');

// noinspection JSValidateTypes
Encore
  .setOutputPath('public/build/')
  .setPublicPath('/build')
  .autoProvidejQuery()
  .addPlugin(new CopyWebpackPlugin({
    patterns: [
      {from: "./assets/static", to: "static"},
    ],
  }))


  // --------------------------------------------
  .addEntry('Estoque/produto_list', './assets/js/Estoque/produto_list.js')
  .addEntry('Estoque/produto_listSimpl', './assets/js/Estoque/produto_listSimpl.js')
  .addEntry('Estoque/produto_form', './assets/js/Estoque/produto_form.js')
  .addEntry('Estoque/pedidoCompra_form', './assets/js/Estoque/pedidoCompra_form.js')
  .addEntry('Estoque/pedidoCompraItem_form', './assets/js/Estoque/pedidoCompraItem_form.js')
  .addEntry('Estoque/pedidoCompra_list', './assets/js/Estoque/pedidoCompra_list.js')
  .addEntry('Estoque/romaneio_form', './assets/js/Estoque/romaneio_form.js')
  .addEntry('Estoque/romaneio_marcarProdutoForm', './assets/js/Estoque/romaneio_marcarProdutoForm.js')
  .addEntry('Estoque/unidade_form', './assets/js/Estoque/unidade_form.js')
  .addEntry('Estoque/unidade_list', './assets/js/Estoque/unidade_list.js')
  .addEntry('Estoque/entrada_form', './assets/js/Estoque/entrada_form.js')
  .addEntry('Estoque/entrada_list', './assets/js/Estoque/entrada_list.js')

  .addEntry('est/fornecedor/list', './assets/js/Estoque/Fornecedor/list.js')
  .addEntry('est/fornecedor/form', './assets/js/Estoque/Fornecedor/form.js')

  .addEntry('Fiscal/emissaoNFe/form', './assets/js/Fiscal/emissaoNFe/form.js')
  .addEntry('Fiscal/emissaoNFe/formItem', './assets/js/Fiscal/emissaoNFe/formItem.js')
  .addEntry('Fiscal/emissaoNFe/list', './assets/js/Fiscal/emissaoNFe/list.js')
  .addEntry('Fiscal/nfesFornecedoresList', './assets/js/Fiscal/nfesFornecedoresList.js')
  .addEntry('Fiscal/NotaFiscal/nfEntradaList', './assets/js/Fiscal/NotaFiscal/nfEntradaList.js')
  .addEntry('Fiscal/distDFeList', './assets/js/Fiscal/distDFeList.js')

  .addEntry('Vendas/vendasPorPeriodo', './assets/js/Vendas/vendasPorPeriodo.js')

  .addEntry('Financeiro/Carteira/list', './assets/js/Financeiro/Carteira/list.js')
  .addEntry('Financeiro/Carteira/form', './assets/js/Financeiro/Carteira/form.js')
  .addEntry('Financeiro/Carteira/caixaOperacaoForm', './assets/js/Financeiro/Carteira/caixaOperacaoForm.js')
  .addEntry('Financeiro/Banco/list', './assets/js/Financeiro/Banco/list.js')
  .addEntry('Financeiro/Banco/form', './assets/js/Financeiro/Banco/form.js')
  .addEntry('Financeiro/BandeiraCartao/list', './assets/js/Financeiro/BandeiraCartao/list.js')
  .addEntry('Financeiro/BandeiraCartao/form', './assets/js/Financeiro/BandeiraCartao/form.js')
  .addEntry('Financeiro/OperadoraCartao/list', './assets/js/Financeiro/OperadoraCartao/list.js')
  .addEntry('Financeiro/OperadoraCartao/form', './assets/js/Financeiro/OperadoraCartao/form.js')
  .addEntry('Financeiro/CentroCusto/list', './assets/js/Financeiro/CentroCusto/list.js')
  .addEntry('Financeiro/CentroCusto/form', './assets/js/Financeiro/CentroCusto/form.js')
  .addEntry('Financeiro/Modo/list', './assets/js/Financeiro/Modo/list.js')
  .addEntry('Financeiro/Modo/form', './assets/js/Financeiro/Modo/form.js')
  .addEntry('Financeiro/RegraImportacaoLinha/list', './assets/js/Financeiro/RegraImportacaoLinha/list.js')
  .addEntry('Financeiro/RegraImportacaoLinha/form', './assets/js/Financeiro/RegraImportacaoLinha/form.js')
  .addEntry('Financeiro/Categoria/form', './assets/js/Financeiro/Categoria/form.js')
  .addEntry('Financeiro/Movimentacao/form_aPagarReceber', './assets/js/Financeiro/Movimentacao/form_aPagarReceber.js')
  .addEntry('fin/movimentacao/list', './assets/js/Financeiro/Movimentacao/list.js')
  
  .addEntry('Financeiro/grupoList', './assets/js/Financeiro/grupoList.js')
  .addEntry('Financeiro/grupoItemList', './assets/js/Financeiro/grupoItemList.js')
  .addEntry('Financeiro/grupoItemListMovs', './assets/js/Financeiro/grupoItemListMovs.js')
  .addEntry('Financeiro/registroConferenciaList', './assets/js/Financeiro/registroConferenciaList.js')
  .addEntry('Financeiro/movimentacaoList', './assets/js/Financeiro/movimentacaoList.js')
  .addEntry('Financeiro/movimentacaoExtratoList', './assets/js/Financeiro/movimentacaoExtratoList.js')
  .addEntry('Financeiro/movimentacaoAPagarReceberList', './assets/js/Financeiro/movimentacaoAPagarReceberList.js')
  .addEntry('Financeiro/movimentacaoRecorrentesList', './assets/js/Financeiro/movimentacaoRecorrentesList.js')
  .addEntry('Financeiro/movimentacaoCaixaList', './assets/js/Financeiro/movimentacaoCaixaList.js')
  .addEntry('Financeiro/movimentacaoImport', './assets/js/Financeiro/movimentacaoImport.js')
  .addEntry('Financeiro/movimentacaoForm_geral', './assets/js/Financeiro/movimentacaoForm_geral.js')
  .addEntry('Financeiro/movimentacaoForm_transferenciaEntreCarteiras', './assets/js/Financeiro/movimentacaoForm_transferenciaEntreCarteiras.js')
  .addEntry('Financeiro/movimentacaoForm_caixa', './assets/js/Financeiro/movimentacaoForm_caixa.js')
  .addEntry('Financeiro/movimentacaoForm_chequeProprio', './assets/js/Financeiro/movimentacaoForm_chequeProprio.js')
  .addEntry('Financeiro/movimentacaoForm_chequeProprio_parcelamento', './assets/js/Financeiro/movimentacaoForm_chequeProprio_parcelamento.js')
  .addEntry('Financeiro/movimentacaoForm_aPagarReceber', './assets/js/Financeiro/movimentacaoForm_aPagarReceber.js')
  .addEntry('Financeiro/movimentacaoForm_alterarEmLote', './assets/js/Financeiro/movimentacaoForm_alterarEmLote.js')
  .addEntry('Financeiro/movimentacaoForm_grupo', './assets/js/Financeiro/movimentacaoForm_grupo.js')
  .addEntry('Financeiro/movimentacaoForm_recorrente', './assets/js/Financeiro/movimentacaoForm_recorrente.js')
  .addEntry('Financeiro/custoOperacional_relatorioMensal', './assets/js/Financeiro/custoOperacional_relatorioMensal.js')

  .addEntry('RH/colaborador_form', './assets/js/RH/colaborador_form.js')

  .addEntry('crm/cliente/list', './assets/js/CRM/Cliente/list.js')
  .addEntry('crm/cliente/form', './assets/js/CRM/Cliente/form.js')


  .addEntry('Vendas/ven_venda_listVendasPorDiaComEcommerce', './assets/js/Vendas/ven_venda_listVendasPorDiaComEcommerce.js')
  .addEntry('Vendas/venda_ecommerceForm', './assets/js/Vendas/venda_ecommerceForm.js')
  .addEntry('Vendas/venda_form_dados', './assets/js/Vendas/venda_form_dados.js')
  .addEntry('Vendas/venda_form_itens', './assets/js/Vendas/venda_form_itens.js')
  .addEntry('Vendas/venda_form_pagamento', './assets/js/Vendas/venda_form_pagamento.js')
  .addEntry('Vendas/venda_form_resumo', './assets/js/Vendas/venda_form_resumo.js')

  // --------------------------------------------

  .splitEntryChunks()

  // se deixar habilitado não funciona o datatables e o select2 (parece que começa a fazer 2 chamadas para montá-los no código)
  .disableSingleRuntimeChunk()

  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage';
    config.corejs = 3;
  })
  .configureBabel((config) => {
    config.plugins.push('@babel/plugin-proposal-class-properties');
  })
  .enableVueLoader(function (options) {
    options.loaders = {
      // vue: {loader: 'babel-loader'}
    };
  }, {version: 3})
  .addAliases({
    '@': path.resolve(__dirname, 'assets', 'js'),
    styles: path.resolve(__dirname, 'assets', 'scss'),
  })
  .enableEslintLoader({
    configFile: "./.eslintrc.js",
  })
  .configureCssLoader((config) => {
    if (!Encore.isProduction() && config.modules) {
      config.modules.localIdentName = '[name]_[local]_[hash:base64:5]';
    }
  })
  .enableSassLoader()
  .addLoader({
    test: /\.js$/,
    loader: 'babel-loader',
    options: {
      plugins: [require("@babel/plugin-proposal-optional-chaining")]
    },
    exclude: file => (
      /node_modules/.test(file) &&
      !/\.vue\.js/.test(file)
    )
  })
;

module.exports = Encore.getWebpackConfig();

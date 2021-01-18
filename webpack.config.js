/* eslint-disable */
const Encore = require("@symfony/webpack-encore");
const path = require("path");

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

const webpack = require("webpack");

const CopyWebpackPlugin = require("copy-webpack-plugin");

Encore
  // directory where compiled assets will be stored
  .setOutputPath("public/build/")
  // public path used by the web server to access the output path
  .setPublicPath("/build")
  .autoProvidejQuery()
  .addPlugin(
    new CopyWebpackPlugin({
      patterns: [{ from: "./assets/static", to: "static" }],
    })
  )

  // o summmernote tem esta dependência, mas não é necessária
  .addPlugin(new webpack.IgnorePlugin(/^codemirror$/))
  // only needed for CDN's or sub-directory deploy
  // .setManifestKeyPrefix('build/')

  /*
   * ENTRY CONFIG
   *
   * Add 1 entry for each "page" of your app
   * (including one that's included on every page - e.g. "app")
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
   */
  .addEntry('Estoque/produto_list', './assets/js/Estoque/produto_list.js')
  .addEntry('Estoque/produto_listSimpl', './assets/js/Estoque/produto_listSimpl.js')
  .addEntry('Estoque/produto_form', './assets/js/Estoque/produto_form.js')
  .addEntry('Estoque/pedidoCompra_form', './assets/js/Estoque/pedidoCompra_form.js')
  .addEntry('Estoque/pedidoCompraItem_form', './assets/js/Estoque/pedidoCompraItem_form.js')
  .addEntry('Estoque/pedidoCompra_list', './assets/js/Estoque/pedidoCompra_list.js')
  .addEntry('Estoque/fornecedor_list', './assets/js/Estoque/fornecedor_list.js')
  .addEntry('Estoque/romaneio_form', './assets/js/Estoque/romaneio_form.js')
  .addEntry('Estoque/romaneio_marcarProdutoForm', './assets/js/Estoque/romaneio_marcarProdutoForm.js')
  .addEntry('Estoque/fornecedor_form', './assets/js/Estoque/fornecedor_form.js')
  .addEntry('Estoque/unidade_form', './assets/js/Estoque/unidade_form.js')
  .addEntry('Estoque/unidade_list', './assets/js/Estoque/unidade_list.js')
  .addEntry('Estoque/entrada_form', './assets/js/Estoque/entrada_form.js')
  .addEntry('Estoque/entrada_list', './assets/js/Estoque/entrada_list.js')

  .addEntry('Fiscal/emissaoNFe/form', './assets/js/Fiscal/emissaoNFe/form.js')
  .addEntry('Fiscal/emissaoNFe/formItem', './assets/js/Fiscal/emissaoNFe/formItem.js')
  .addEntry('Fiscal/emissaoNFe/list', './assets/js/Fiscal/emissaoNFe/list.js')
  .addEntry('Fiscal/nfesFornecedoresList', './assets/js/Fiscal/nfesFornecedoresList.js')
  .addEntry('Fiscal/distDFeList', './assets/js/Fiscal/distDFeList.js')

  .addEntry('Vendas/vendasPorPeriodo', './assets/js/Vendas/vendasPorPeriodo.js')

  .addEntry('Financeiro/carteiraList', './assets/js/Financeiro/carteiraList.js')
  .addEntry('Financeiro/bancoList', './assets/js/Financeiro/bancoList.js')
  .addEntry('Financeiro/grupoList', './assets/js/Financeiro/grupoList.js')
  .addEntry('Financeiro/grupoItemList', './assets/js/Financeiro/grupoItemList.js')
  .addEntry('Financeiro/grupoItemListMovs', './assets/js/Financeiro/grupoItemListMovs.js')
  .addEntry('Financeiro/centroCustoList', './assets/js/Financeiro/centroCustoList.js')
  .addEntry('Financeiro/modoList', './assets/js/Financeiro/modoList.js')
  .addEntry('Financeiro/bandeiraCartaoList', './assets/js/Financeiro/bandeiraCartaoList.js')
  .addEntry('Financeiro/operadoraCartaoList', './assets/js/Financeiro/operadoraCartaoList.js')
  .addEntry('Financeiro/registroConferenciaList', './assets/js/Financeiro/registroConferenciaList.js')
  .addEntry('Financeiro/regraImportacaoLinhaList', './assets/js/Financeiro/regraImportacaoLinhaList.js')
  .addEntry('Financeiro/categoriaTreeList', './assets/js/Financeiro/categoriaTreeList.js')
  .addEntry('Financeiro/movimentacaoList', './assets/js/Financeiro/movimentacaoList.js')
  .addEntry('Financeiro/movimentacao_pesquisa_list', './assets/js/Financeiro/movimentacao_pesquisa_list.js')
  .addEntry('Financeiro/movimentacaoExtratoList', './assets/js/Financeiro/movimentacaoExtratoList.js')
  .addEntry('Financeiro/movimentacaoAPagarReceberList', './assets/js/Financeiro/movimentacaoAPagarReceberList.js')
  .addEntry('Financeiro/movimentacaoRecorrentesList', './assets/js/Financeiro/movimentacaoRecorrentesList.js')
  .addEntry('Financeiro/movimentacaoCaixaList', './assets/js/Financeiro/movimentacaoCaixaList.js')
  .addEntry('Financeiro/movimentacaoImport', './assets/js/Financeiro/movimentacaoImport.js')
  .addEntry('Financeiro/movimentacaoForm_geral', './assets/js/Financeiro/movimentacaoForm_geral.js')
  .addEntry('Financeiro/movimentacaoForm_transferenciaEntreCarteiras', './assets/js/Financeiro/movimentacaoForm_transferenciaEntreCarteiras.js')
  .addEntry('Financeiro/movimentacaoForm_caixa', './assets/js/Financeiro/movimentacaoForm_caixa.js')
  .addEntry('Financeiro/movimentacaoForm_caixa_transacaoCartao', './assets/js/Financeiro/movimentacaoForm_caixa_transacaoCartao.js')
  .addEntry('Financeiro/movimentacaoForm_chequeProprio', './assets/js/Financeiro/movimentacaoForm_chequeProprio.js')
  .addEntry('Financeiro/movimentacaoForm_chequeProprio_parcelamento', './assets/js/Financeiro/movimentacaoForm_chequeProprio_parcelamento.js')
  .addEntry('Financeiro/movimentacaoForm_aPagarReceber', './assets/js/Financeiro/movimentacaoForm_aPagarReceber.js')
  .addEntry('Financeiro/movimentacaoForm_pagto', './assets/js/Financeiro/movimentacaoForm_pagto.js')
  .addEntry('Financeiro/movimentacaoForm_grupo', './assets/js/Financeiro/movimentacaoForm_grupo.js')
  .addEntry('Financeiro/movimentacaoForm_recorrente', './assets/js/Financeiro/movimentacaoForm_recorrente.js')
  .addEntry('Financeiro/movimentacaoImportForm', './assets/js/Financeiro/movimentacaoImportForm.js')
  .addEntry('Financeiro/custoOperacional_relatorioMensal', './assets/js/Financeiro/custoOperacional_relatorioMensal.js')

  .addEntry('RH/colaborador_form', './assets/js/RH/colaborador_form.js')

  .addEntry('CRM/cliente_list', './assets/js/CRM/cliente_list.js')
  .addEntry('CRM/cliente_form', './assets/js/CRM/cliente_form.js')


  .addEntry('Vendas/ven_venda_listVendasPorDiaComEcommerce', './assets/js/Vendas/ven_venda_listVendasPorDiaComEcommerce.js')
  .addEntry('Vendas/venda_form_dados', './assets/js/Vendas/venda_form_dados.js')
  .addEntry('Vendas/venda_form_itens', './assets/js/Vendas/venda_form_itens.js')
  .addEntry('Vendas/venda_form_pagamento', './assets/js/Vendas/venda_form_pagamento.js')
  .addEntry('Vendas/venda_form_resumo', './assets/js/Vendas/venda_form_resumo.js')

  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()
  // .disableSingleRuntimeChunk()

  /*
   * FEATURE CONFIG
   *
   * Enable & configure other features below. For a full
   * list of features, see:
   * https://symfony.com/doc/current/frontend.html#adding-more-features
   */
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  // enables @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = "usage";
    config.corejs = 3;
  })
  .enableVueLoader(
    function (options) {
      options.loaders = {
        vue: { loader: "babel-loader" },
      };
    },
    {
      version: 3,
    }
  )
  .addAliases({
    "@": path.resolve(__dirname, "assets", "js"),
    styles: path.resolve(__dirname, "assets", "scss"),
  })
  .enableEslintLoader({
    configFile: "./.eslintrc.js",
  })
  .configureCssLoader((config) => {
    if (!Encore.isProduction() && config.modules) {
      config.modules.localIdentName = "[name]_[local]_[hash:base64:5]";
    }
  })
  .enableSassLoader()
  .addLoader({
    test: /\.js$/,
    loader: "babel-loader",
    options: {
      plugins: [require("@babel/plugin-proposal-optional-chaining")],
    },
    exclude: (file) => /node_modules/.test(file) && !/\.vue\.js/.test(file),
  });

// enables Sass/SCSS support
// .enableSassLoader()

// uncomment if you use TypeScript
// .enableTypeScriptLoader()

// uncomment to get integrity="..." attributes on your script & link tags
// requires WebpackEncoreBundle 1.4 or higher
// .enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
// .autoProvidejQuery()

// uncomment if you use API Platform Admin (composer req api-admin)
// .enableReactPreset()
// .addEntry('admin', './assets/js/admin.js')

module.exports = Encore.getWebpackConfig();

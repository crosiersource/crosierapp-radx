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


  .addEntry('Estoque/produto_form', './assets/js/Estoque/produto_form.js')
  .addEntry('Vendas/ven_venda_listVendasPorDiaComEcommerce', './assets/js/Vendas/ven_venda_listVendasPorDiaComEcommerce.js')
  .addEntry('Vendas/venda_ecommerceForm', './assets/js/Vendas/venda_ecommerceForm.js')

  .addEntry('Fiscal/emissaoNFe/form', './assets/js/Fiscal/emissaoNFe/form.js')
  .addEntry('Fiscal/emissaoNFe/formItem', './assets/js/Fiscal/emissaoNFe/formItem.js')
  .addEntry('Fiscal/emissaoNFe/list', './assets/js/Fiscal/emissaoNFe/list.js')
  
  .addEntry('Fiscal/distDFeList', './assets/js/Fiscal/distDFeList.js')

  
  // --------------------------------------------
  
  // Estoque
  
  .addEntry('crm/cliente/list', './assets/js/CRM/Cliente/list.js')
  .addEntry('crm/cliente/form', './assets/js/CRM/Cliente/form.js')
  
  .addEntry('est/fornecedor/list', './assets/js/Estoque/Fornecedor/list.js')
  .addEntry('est/fornecedor/form', './assets/js/Estoque/Fornecedor/form.js')

  .addEntry('est/produto/list', './assets/js/Estoque/Produto/list.js')
  .addEntry('est/produto/form', './assets/js/Estoque/Produto/form.js')
  .addEntry('est/produto/listComEcommerce', './assets/js/Estoque/Produto/list_comEcommerce.js')
  .addEntry('est/produto/formComEcommerce', './assets/js/Estoque/Produto/form_comEcommerce.js')

  .addEntry('est/unidade/list', './assets/js/Estoque/Unidade/list.js')
  .addEntry('est/unidade/form', './assets/js/Estoque/Unidade/form.js')

  .addEntry('est/listaPreco/list', './assets/js/Estoque/ListaPreco/list.js')
  .addEntry('est/listaPreco/form', './assets/js/Estoque/ListaPreco/form.js')
  
  
  // Financeiro
  
  .addEntry('fin/banco/list', './assets/js/Financeiro/Banco/list.js')
  .addEntry('fin/banco/form', './assets/js/Financeiro/Banco/form.js')
  .addEntry('fin/bandeiraCartao/list', './assets/js/Financeiro/BandeiraCartao/list.js')
  .addEntry('fin/bandeiraCartao/form', './assets/js/Financeiro/BandeiraCartao/form.js')
  .addEntry('fin/carteira/list', './assets/js/Financeiro/Carteira/list.js')
  .addEntry('fin/carteira/form', './assets/js/Financeiro/Carteira/form.js')
  .addEntry('fin/carteira/caixaOperacaoForm', './assets/js/Financeiro/Carteira/caixaOperacaoForm.js')
  .addEntry('fin/categoria/form', './assets/js/Financeiro/Categoria/form.js')
  .addEntry('fin/centroCusto/list', './assets/js/Financeiro/CentroCusto/list.js')
  .addEntry('fin/centroCusto/form', './assets/js/Financeiro/CentroCusto/form.js')
  .addEntry('fin/modo/list', './assets/js/Financeiro/Modo/list.js')
  .addEntry('fin/modo/form', './assets/js/Financeiro/Modo/form.js')
  .addEntry('fin/operadoraCartao/list', './assets/js/Financeiro/OperadoraCartao/list.js')
  .addEntry('fin/operadoraCartao/form', './assets/js/Financeiro/OperadoraCartao/form.js')
  .addEntry('fin/registroConferencia/list', './assets/js/Financeiro/RegistroConferencia/list.js')
  .addEntry('fin/registroConferencia/form', './assets/js/Financeiro/RegistroConferencia/form.js')
  .addEntry('fin/regraImportacaoLinha/list', './assets/js/Financeiro/RegraImportacaoLinha/list.js')
  .addEntry('fin/regraImportacaoLinha/form', './assets/js/Financeiro/RegraImportacaoLinha/form.js')
  
  .addEntry('fin/grupo/list', './assets/js/Financeiro/Grupo/list.js')
  .addEntry('fin/grupo/form', './assets/js/Financeiro/Grupo/form.js')
  
  .addEntry('fin/fornecedor/list', './assets/js/Estoque/Fornecedor/list.js')
  .addEntry('fin/fornecedor/form', './assets/js/Estoque/Fornecedor/form.js')
  
  .addEntry('fin/cliente/list', './assets/js/CRM/Cliente/list.js')
  .addEntry('fin/cliente/form', './assets/js/CRM/Cliente/form.js')

  .addEntry('fin/movimentacao/rapida', './assets/js/Financeiro/Movimentacao/form_rapida.js')
  .addEntry('fin/movimentacao/caixa', './assets/js/Financeiro/Movimentacao/form_caixa.js')
  .addEntry('fin/movimentacao/importador', './assets/js/Financeiro/Movimentacao/importador.js')
  .addEntry('fin/movimentacao/list', './assets/js/Financeiro/Movimentacao/list.js')
  .addEntry('fin/movimentacao/extrato', './assets/js/Financeiro/Movimentacao/extrato.js')
  .addEntry('fin/movimentacao/aPagarReceber/form', './assets/js/Financeiro/Movimentacao/form_aPagarReceber.js')
  .addEntry('fin/movimentacao/aPagarReceber/formParcelamento', './assets/js/Financeiro/Movimentacao/form_aPagarReceberParcelamento.js')
  .addEntry('fin/movimentacao/aPagarReceber/list', './assets/js/Financeiro/Movimentacao/list_aPagarReceber.js')
  .addEntry('fin/movimentacao/recorrente/form', './assets/js/Financeiro/Movimentacao/form_recorrente.js')
  .addEntry('fin/movimentacao/recorrente/list', './assets/js/Financeiro/Movimentacao/list_recorrente.js')
  .addEntry('fin/movimentacao/transfEntreCarteiras/form', './assets/js/Financeiro/Movimentacao/form_transfEntreCarteiras.js')
  
  .addEntry('fin/movimentacao/grupo/form', './assets/js/Financeiro/Movimentacao/form_emGrupo.js')
  .addEntry('fin/movimentacao/grupo/list', './assets/js/Financeiro/Movimentacao/list_emGrupo.js')
  
  .addEntry('fin/cadeia/exibirMovimentacoes', './assets/js/Financeiro/Movimentacao/cadeia_exibirMovimentacoes.js')
  


  .addEntry('fis/notaFiscal/recebidas/list', './assets/js/Fiscal/NotaFiscal/recebidasList.js')
  .addEntry('fis/notaFiscal/emitidas/list', './assets/js/Fiscal/NotaFiscal/emitidasList.js')
  .addEntry('fis/notaFiscal/distdfe/list', './assets/js/Fiscal/NotaFiscal/distDFesList.js')
  .addEntry('fis/selecaoContribuinte', './assets/js/Fiscal/NotaFiscal/selecaoContribuinte.js')
  
  
  .addEntry('ecommerce/clienteConfig/list', './assets/js/Ecommerce/ClienteConfig/list.js')
  .addEntry('ecommerce/clienteConfig/form', './assets/js/Ecommerce/ClienteConfig/form.js')
  
  .addEntry('ecommerce/trayVenda/list', './assets/js/Ecommerce/TrayVenda/list.js')
  .addEntry('ecommerce/mercadoLivrePergunta/list', './assets/js/Ecommerce/MercadoLivre/list_perguntas.js')
  // .addEntry('ecommerce/mercadoLivre/form_venda', './assets/js/Ecommerce/MercadoLivre/form_venda.js')
  
  .addEntry('ecommerce/dashboard/tray-e-ml', './assets/js/Ecommerce/dashboards/dashboard-tray-e-ml.js')


  .addEntry('ven/vendas/resultMensal', './assets/js/Vendas/resultMensal.js')
  
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

config = Encore.getWebpackConfig();
config.watchOptions = {
  aggregateTimeout: 1500,
};

module.exports = config;

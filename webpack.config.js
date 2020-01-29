var Encore = require('@symfony/webpack-encore');

const webpack = require('webpack');

const CopyWebpackPlugin = require('copy-webpack-plugin');

Encore
// directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    // fixes modules that expect jQuery to be global
    .autoProvidejQuery()
    .addPlugin(new CopyWebpackPlugin([
        // copies to {output}/static
        {from: './assets/static', to: 'static'}
    ]))
    // o summmernote tem esta dependência, mas não é necessária
    .addPlugin(new webpack.IgnorePlugin(/^codemirror$/))
    .enableSassLoader()
    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    // .createSharedEntry('bse_layout', './assets/js/bse/layout.js')
    .addEntry('Estoque/atributo_list', './assets/js/Estoque/atributo_list.js')
    .addEntry('Estoque/grupoAtributo_list', './assets/js/Estoque/grupoAtributo_list.js')
    .addEntry('Estoque/produto_list', './assets/js/Estoque/produto_list.js')
    .addEntry('Estoque/produto_form', './assets/js/Estoque/produto_form.js')

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
    .addEntry('Financeiro/movimentacaoForm_aPagarReceber_parcelamento', './assets/js/Financeiro/movimentacaoForm_aPagarReceber_parcelamento.js')
    .addEntry('Financeiro/movimentacaoForm_pagto', './assets/js/Financeiro/movimentacaoForm_pagto.js')
    .addEntry('Financeiro/movimentacaoForm_grupo', './assets/js/Financeiro/movimentacaoForm_grupo.js')
    .addEntry('Financeiro/movimentacaoForm_recorrente', './assets/js/Financeiro/movimentacaoForm_recorrente.js')
    .addEntry('Financeiro/movimentacaoImportForm', './assets/js/Financeiro/movimentacaoImportForm.js')



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
    .configureBabel(() => {
    }, {
        useBuiltIns: 'usage',
        corejs: 3
    })
    .enableSingleRuntimeChunk()
// enables Sass/SCSS support
//.enableSassLoader()

// uncomment if you use TypeScript
//.enableTypeScriptLoader()

// uncomment if you're having problems with a jQuery plugin
//.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();

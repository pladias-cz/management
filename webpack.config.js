const Encore = require('@symfony/webpack-encore');

const webpack = require('webpack');
const dotenv = require('dotenv');
const env = dotenv.config().parsed;

Encore
    .setOutputPath('htdocs/www/dist')
    .setPublicPath('/dist')
    .setManifestKeyPrefix('')
    .addEntry('app', './assets/application.js')
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader()
    .enablePostCssLoader()
    .configureBabel(()=> {}, {
        useBuiltIns: 'usage',
        corejs: 3
    })
    .splitEntryChunks()
    .enableSingleRuntimeChunk()

.addPlugin(new webpack.DefinePlugin({ //https://webpack.js.org/plugins/define-plugin/
    'process.env': JSON.stringify(env)
}));

module.exports = Encore.getWebpackConfig();

// webpack.config.js
var Encore = require('@symfony/webpack-encore');

Encore
    // the project directory where all compiled assets will be stored
    .setOutputPath('web/build/')

    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')

    // will create web/build/app.js and web/build/app.css
    .addEntry('app', './app/Resources/assets/js/app.js')
    .addEntry('app-designer', './app/Resources/assets/js/app-designer.js')
    .addEntry('app-model', './app/Resources/assets/js/app-model.js')
    .addEntry('app-covid', './app/Resources/assets/js/app-covid.js')
    // allow legacy applications to use $/jQuery as a global variable
    .autoProvidejQuery()

    // enable source maps during development
    .enableSourceMaps(!Encore.isProduction())

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // show OS notifications when builds finish/fail
    .enableBuildNotifications()

    // create hashed filenames (e.g. app.abc123.css)
    .enableVersioning()

    // allow sass/scss files to be processed
    // .enableSassLoader()

    .enableVueLoader()

    .configureBabel(function(babelConfig) {
        // add additional presets
        babelConfig.presets.push('stage-3');

        // no plugins are added by default, but you can add some
        // babelConfig.plugins.push('styled-jsx/babel');
    })

;

// export the final configuration
module.exports = Encore.getWebpackConfig();
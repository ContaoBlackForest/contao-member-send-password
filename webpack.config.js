const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const ManifestPlugin = require('webpack-manifest-plugin');
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const MinifyBundledPlugin = require('minify-bundled-webpack-plugin');

module.exports = function (env, config) {
    const plugins = function () {
        let commonPlugins = [
            new MiniCssExtractPlugin({
                filename: ('prod' === env) ? 'backend/ccs/[name].[hash].css' : 'backend/css/[name].css'
            }),
            new ManifestPlugin({
                map: (file) => {
                    const extension = path.extname(file.name).slice(1);
                    return {
                        ...file,
                        name: `backend/${extension}/${file.name}`
                    }
                }
            })
        ];

        if (true === config.clean) {
            commonPlugins = [
                ...commonPlugins,
                ...[
                    new CleanWebpackPlugin()
                ]
            ]
        }

        if ('prod' === env) {
            return [
                ...commonPlugins,
                ...[
                    new UglifyJsPlugin({
                        uglifyOptions: {
                            output: {
                                comments: false
                            }
                        }
                    }),
                    new OptimizeCSSAssetsPlugin(),
                    new MinifyBundledPlugin({ patterns: ['*.+(json|css|js)'] })
                ]
            ]
        }

        return commonPlugins
    };

    return {
        entry: {
            be_select_notification: './src/Resources/assets/backend/javascript/be_select_notification.js',
            be_send_notification: './src/Resources/assets/backend/javascript/be_send_notification.js'
        },
        output: {
            path: path.resolve(__dirname, 'src/Resources/public'),
            filename: ('prod' === env) ? 'backend/js/[name].[hash].js' : 'backend/js/[name].js',
            chunkFilename: ('prod' === env) ? 'backend/js/[id].[chunkhash].js' : 'backend/js/[id].js'
        },
        mode: 'development',

        module: {
            rules: [
                {
                    test: /\.(js|es6)$/,
                    exclude: /(node_modules)/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ['@babel/preset-env']
                        }
                    }
                },
                {
                    test: /\.(sa|sc|c)ss$/,
                    use: [
                        {
                            loader: MiniCssExtractPlugin.loader
                        },
                        {
                            loader: "css-loader",
                        },
                        {
                            loader: "postcss-loader"
                        },
                        {
                            loader: "sass-loader",
                            options: {
                                implementation: require("sass")
                            }
                        }
                    ]
                },
                {
                    test: /\.(png|jpe?g|gif|svg)$/,
                    use: [
                        {
                            loader: "file-loader",
                            options: {
                                outputPath: 'images',
                                name: ('prod' === env) ? '[name].[hash:20].[ext]' : '[name].[ext]'
                            }
                        }
                    ]
                },
                {
                    test: /\.(woff|woff2|ttf|otf|eot)$/,
                    use: [
                        {
                            loader: "file-loader",
                            options: {
                                outputPath: 'fonts',
                                name: ('prod' === env) ? '[name].[hash:20].[ext]' : '[name].[ext]'
                            }
                        }
                    ]
                }
            ]
        },

        plugins: plugins()
    }
};

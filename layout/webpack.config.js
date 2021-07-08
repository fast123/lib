const path = require("path");
const webpack = require("webpack");

module.exports = {
    plugins: [
        new webpack.ProvidePlugin({
            "jQuery": "jquery",
            "window.jQuery": "jquery",
            "jquery": "jquery",
            "window.jquery": "jquery",
            "$": "jquery",
            "window.$": "jquery"
        })
    ],

    externals: {
        // require("jquery") is external and available
        //  on the global var jQuery
        "jquery": "jQuery"
    },

    entry: {
        main: "./src/js/index.js",
    },

    output: {
        filename: "[name].js",
        chunkFilename: "[name].js",
        publicPath: "/"
    },

    optimization: {
        splitChunks: {
            cacheGroups: {
                vendor: {
                    test: /node_modules/,
                    chunks: "initial",
                    name: "vendor",
                    enforce: true
                }
            }
        }
    },

    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: require.resolve("babel-loader"),
                    query: {
                        presets: [
                            ["@babel/preset-env", { modules: false }]
                        ]
                    }
                }
            }
        ]
    },

    resolve: {
        alias: {
            "%modules%": path.resolve(__dirname, "src/blocks/modules")
        }
    }
};

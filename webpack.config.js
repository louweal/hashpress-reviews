const path = require("path");
const webpack = require("webpack");

module.exports = {
    entry: "./src/js/main.js",
    output: {
        path: path.resolve(__dirname),
        filename: "./dist/[name].bundle.js",
        publicPath: "/wp-content/plugins/hashpress-reviews/",
    },
    devServer: {
        static: {
            directory: path.join(__dirname, "/"),
        },
    },
    mode: "development",
    devtool: false,
    resolve: {
        extensions: [".js", ".jsx", ".json", ".mjs"],

        fallback: {
            path: false, //require.resolve('path-browserify'),
            os: false, //require.resolve('os-browserify/browser'),
            crypto: false, //require.resolve('crypto-browserify'),
            // stream: false, //require.resolve('stream-browserify'),
            // tls: false,
            // net: false,
            // zlib: false, //require.resolve('browserify-zlib'),
            // http: false, //require.resolve('stream-http'),
            // url: false, //require.resolve('url/'),
            // http2: false,
            // dns: false,
            // util: false, //require.resolve('util/'),
            // fs: false,
            buffer: require.resolve("buffer/"),
            process: false, //require.resolve('process/browser'),
        },
    },
    plugins: [
        // Work around for Buffer is undefined:
        // https://github.com/webpack/changelog-v5/issues/10
        new webpack.ProvidePlugin({
            Buffer: ["buffer", "Buffer"],
        }), //test
        // new webpack.ProvidePlugin({
        //     process: 'process/browser',
        // }),
    ],
    optimization: {
        splitChunks: {
            chunks: "all", // Split all chunks
            cacheGroups: {
                default: false, // Disable the default 'commons' chunk
                vendors: {
                    test: /[\\/]node_modules[\\/]/,
                    name: "vendors",
                    chunks: "all",
                },
            },
        },
    },
};

// Generated using webpack-cli https://github.com/webpack/webpack-cli

const path = require('path');
const TerserPlugin = require('terser-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const { VueLoaderPlugin } = require('vue-loader')

const isProduction = process.env.NODE_ENV == 'production';


const stylesHandler = isProduction ? MiniCssExtractPlugin.loader : 'style-loader';

const optimization =
	isProduction ?
		{
			minimize: true,
			minimizer: [new CssMinimizerPlugin(), new TerserPlugin()]
		}
		: {}


const config = {

	entry: './src/index.js',
	output: {
		path: path.resolve(__dirname, 'dist'),
	},
	devServer: {
		open: true,
		host: 'localhost',
	},
	// optimization: {
	// 	...optimization
	// },
	plugins: [
		// Add your plugins here
		// Learn more about plugins from https://webpack.js.org/configuration/plugins/
		// new VueLoaderPlugin()
	],
	module: {
		rules: [
			{
				test: /\.(js|jsx)$/i,
				loader: 'babel-loader',
			},
			{
				test: /\.css$/i,
				use: [stylesHandler, 'css-loader', 'postcss-loader'],
			},
			{
				test: /\.(eot|svg|ttf|woff|woff2|png|jpg|gif)$/i,
				type: 'asset',
			},
			// {
			// 	test: /\.vue$/,
			// 	loader: 'vue-loader',
			// 	options: {
			// 		reactivityTransform: true
			// 	}
			// },
			// Add your rules for custom modules here
			// Learn more about loaders from https://webpack.js.org/loaders/
		],
	},
	// resolve: {
	// 	alias: {
	// 		vue: "vue/dist/vue.esm-bundler.js",
	// 	},
	// },
};

module.exports = () => {
	if (isProduction) {
		config.mode = 'production';

		config.plugins.push(new MiniCssExtractPlugin());

	} else {
		config.mode = 'development';
		config.devtool = 'eval'
	}
	return config;
};

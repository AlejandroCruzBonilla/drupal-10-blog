// Generated using webpack-cli https://github.com/webpack/webpack-cli

const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const { VueLoaderPlugin } = require('vue-loader');

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
		clean: true
	},
	devServer: {
		open: true,
		host: 'localhost',
	},
	optimization: {
		...optimization
	},
	plugins: [
		// Add your plugins here
		// Learn more about plugins from https://webpack.js.org/configuration/plugins/
		new VueLoaderPlugin(),
	],
	module: {
		rules: [
			{
				test: /\.(js|jsx)$/i,
				loader: 'babel-loader',
			},
			{
				test: /\.css$/i,
				use: [
					stylesHandler,
					{
						loader: 'css-loader',
						options: {
							sourceMap: !isProduction,
						},
					},
					// 'postcss-loader'
				],
			},
			{
				test: /\.(eot|svg|ttf|woff|woff2|png|jpg|gif)$/i,
				type: 'asset',
			},
      {
        test: /\.vue$/,
        loader: 'vue-loader',
      },
			// Add your rules for custom modules here
			// Learn more about loaders from https://webpack.js.org/loaders/
		],
	},
	resolve: {
		alias: {
			vue: "vue/dist/vue.esm-bundler.js",
		},
	},
};

module.exports = () => {
	if (isProduction) {
		config.mode = 'production';

		config.plugins.push(
			new MiniCssExtractPlugin({ filename: 'main.css' })
		);


	} else {
		config.mode = 'development';
		config.devtool = 'eval';
	}
	return config;
};

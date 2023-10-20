// Generated using webpack-cli https://github.com/webpack/webpack-cli

import path  from 'path';
import Webpack  from 'webpack';
import MiniCssExtractPlugin  from 'mini-css-extract-plugin';
import TerserPlugin  from 'terser-webpack-plugin';
import CssMinimizerPlugin  from "css-minimizer-webpack-plugin";
import { VueLoaderPlugin }  from 'vue-loader';

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
		path: path.resolve('./dist'),
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
		new Webpack.DefinePlugin({
			__VUE_OPTIONS_API__: true,
			__VUE_PROD_DEVTOOLS__: !isProduction,
		}),
	],
	module: {
		
		rules: [
			{
				test: /\.(js|jsx)$/i,
				loader: 'babel-loader',
			},
			{
				test: /\.vue$/,
				loader: 'vue-loader',
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
					"postcss-loader",
				],
			},
			{
				test: /\.s[ac]ss$/i,
				use: [
					stylesHandler,
					"css-loader",
					"postcss-loader",
					"sass-loader"
				],
			},
			{
				test: /\.(eot|svg|ttf|woff|woff2|png|jpg|gif)$/i,
				type: 'asset',
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

export default() => {
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

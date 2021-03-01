const path = require('path');

const webpack = require('webpack');
const TerserWebPackPlugin = require('terser-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

const NODE_ENV = process.env.NODE_ENV || 'development';
const DEV_TOOL = NODE_ENV === 'development' ? 'source-map' : false;
const ASSETS_SRC = path.join(__dirname, 'src');

const PUBLIC_FOLDER = path.resolve(__dirname, '..', '..', '..', 'public_html');
const REACT_COMPONENTS = require('./src/react/index');

const outputFilename = ({ chunk: { name } }) => {
  if (name in REACT_COMPONENTS) {
    return `assets/react/${name}.js`;
  }

  return 'assets/[name]/app.js';
};

let optimization = {};

const plugins = [
  new webpack.ProgressPlugin(),
  new MiniCssExtractPlugin({
    filename: 'assets/[name]/app.css',
    chunkFilename: 'assets/[name]/app.css',
  }),
  new webpack.ProvidePlugin({
    $: 'jquery',
    jQuery: 'jquery',
    'global.$': 'jquery',
    'window.$': 'jquery',
    'global.jQuery': 'jquery',
    'window.jQuery': 'jquery',
  }),
];

if (NODE_ENV === 'production') {
  plugins.push(
    new CleanWebpackPlugin({
      cleanOnceBeforeBuildPatterns: ['static/*'],
    }),
  );

  optimization = {
    ...optimization,
    minimize: true,
    minimizer: [
      new CssMinimizerPlugin(),
      new TerserWebPackPlugin({
        parallel: true,
        extractComments: true,
      }),
    ],
  };
}

module.exports = {
  mode: NODE_ENV,
  devtool: DEV_TOOL,
  entry: {
    web: path.resolve(ASSETS_SRC, 'web', 'index.ts'),
    ...REACT_COMPONENTS,
  },
  output: {
    path: PUBLIC_FOLDER,
    filename: outputFilename,
    publicPath: '/',
  },
  plugins,
  optimization,
  module: {
    rules: [
      {
        test: /\.s?[ac]ss$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'sass-loader',
          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: {
                plugins: function () {
                  return [require('autoprefixer')];
                },
              },
            },
          },
        ],
      },
      {
        test: /\.(png|jpe?g|gif)$/,
        use: {
          loader: 'file-loader',
          options: {
            name: 'static/images/[name]-[hash:8].[ext]',
          },
        },
      },
      {
        test: /\.svg$/,
        use: {
          loader: 'file-loader',
          options: {
            name: 'static/svg/[name]-[hash:8].[ext]',
          },
        },
      },
      {
        test: /\.(ttf|eot|svg|gif|woff|woff2)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
        loader: 'file-loader',
        options: {
          name: 'static/fonts/[name]-[hash:8].[ext]',
        },
      },
      {
        test: /\.m?jsx?$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env', '@babel/preset-react'],
          },
        },
      },
      {
        test: /\.tsx?$/,
        use: 'ts-loader',
        exclude: /(node_modules|bower_components)/,
      },
    ],
  },
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM',
    jquery: 'jQuery',
  },
  resolve: {
    extensions: ['.ts', '.tsx', '.js', '.jsx', '.css', '.scss', '.sass'],
  },
};

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
  if (name in REACT_COMPONENTS) return `assets/react/${name}.js`;
  return 'assets/[name]/bundle.js';
};

const plugins = [
  new webpack.ProgressPlugin(),
  new MiniCssExtractPlugin({
    filename: 'assets/[name]/bundle.css',
    chunkFilename: 'assets/[name]/bundle.css',
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
      cleanOnceBeforeBuildPatterns: ['assets/static/*'],
    }),
  );
}

module.exports = {
  mode: NODE_ENV,
  devtool: DEV_TOOL,
  entry: {
    web: path.resolve(ASSETS_SRC, 'web', 'index.ts'),
    swagger: path.resolve(ASSETS_SRC, 'swagger', 'index.ts'),
    ...REACT_COMPONENTS,
  },
  output: {
    path: PUBLIC_FOLDER,
    filename: outputFilename,
  },
  // devServer: {
  //   hot: true,
  //   port: 9000,
  //   compress: true,
  //   progress: true,
  //   contentBase: PUBLIC_FOLDER,
  //   writeToDisk: true,
  //   watchContentBase: true,
  //   historyApiFallback: true,
  // },
  plugins,
  optimization: {
    minimize: true,
    minimizer: [new TerserWebPackPlugin(), new CssMinimizerPlugin()],
  },
  module: {
    rules: [
      {
        test: /\.s?[ac]ss$/,
        use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader', 'postcss-loader'],
      },
      {
        test: /\.(png|jpeg|jpg|svg|gif|webp)$/,
        use: {
          loader: 'file-loader',
          options: {
            name: 'assets/static/[contenthash].[ext]',
          },
        },
      },
      {
        test: /\.(ttf|eot|woff|woff2)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
        loader: 'file-loader',
        options: {
          name: 'assets/static/[contenthash].[ext]',
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
    fallback: {
      fs: false,
      path: false,
      browser: false,
      buffer: false,
      stream: false,
    },
  },
};

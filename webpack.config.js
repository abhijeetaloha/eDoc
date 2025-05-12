const path = require("path");

module.exports = {
  entry: "./jsPDF/src/toBundle.js",
  output: {
    filename: "jspdf.js",
    path: path.resolve(__dirname, "jsPDF/dist"),
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader",
          options: {
            presets: ["@babel/preset-env"],
          },
        },
      },
    ],
  },
};

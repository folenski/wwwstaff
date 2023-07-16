/**
 * Generate the development bundle for JavaScript and CSS files for the site.
 */

const esbuild = require("esbuild");
const sass = require("esbuild-sass-plugin");
const path = require("path");
const watch = require("node-watch");

const myjs = {
  entryPoints: [path.resolve(__dirname, "../src/app.ts")],
  bundle: true,
  outfile: path.resolve(__dirname, "../../public/app.js"),
  define: {
    "process.env.NODE_ENV": JSON.stringify("development"),
  },
  publicPath: "/js/",
  minify: false,
  sourcemap: false,
  assetNames: "assets/[name]-[hash]",
  loader: {
    ".svg": "file", // make image file use file-loader
  },
  target: "es2015",
};

const mycss = {
  entryPoints: [path.resolve(__dirname, "../sass/style.scss")],
  bundle: true,
  outfile: path.resolve(__dirname, "../../public/style.css"),
  minify: true,
  sourcemap: false,
  assetNames: "assets/[name]-[hash]",
  loader: {
    ".svg": "file", // make image file use file-loader
    ".jpg": "file",
    ".png": "file",
    ".woff": "file",
    ".ttf": "file",
    ".otf": "file",
  },
  target: "es2015",
  plugins: [
    sass.sassPlugin({
      type: "css",
      filter: /.(s[ac]ss|css)$/,
    }),
  ],
};

const run = async (start) => {
  await esbuild.build(myjs);
  await esbuild.build(mycss);

  console.log("Finished build in", new Date().getTime() - start, "ms");
  // refer to: https://esbuild.github.io/api/#incremental
  watch(
    path.resolve(__dirname, "../"),
    {
      recursive: true, // listens for changes in subdirectory as well
    },
    async () => {
      const start = new Date().getTime();
      await esbuild.build(myjs);
      await esbuild.build(mycss);
      console.log("Rebuilt in", new Date().getTime() - start, "ms");
    }
  );
};

run(new Date().getTime());

console.log("Development server started and watching directory: ./src ...");

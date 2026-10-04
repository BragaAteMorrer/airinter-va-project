const fs = require("fs");
const esbuild = require("esbuild");
const globalExternals = require("@fal-works/esbuild-plugin-global-externals");
const { sassPlugin } = require("esbuild-sass-plugin");

require("dotenv").config({ path: __dirname + "/.env" });

const watch = process.env.SERVING_MODE === "WATCH";
const config = {
  entryPoints: ["src/HermesEfb.tsx"],
  bundle: true,
  keepNames: true,
  outdir: "dist",
  target: "es2017",
  sourcemap: process.env.SOURCE_MAPS === "true",
  minify: process.env.MINIFY === "true",
  logLevel: "info",
  define: {
    BASE_URL: JSON.stringify("coui://html_ui/efb_ui/efb_apps/AirInterHermes")
  },
  plugins: [
    globalExternals.globalExternals({
      "@microsoft/msfs-sdk": { varName: "msfssdk", type: "cjs" }
    }),
    sassPlugin()
  ]
};

function copyAssets() {
  fs.rmSync("dist/Assets", { recursive: true, force: true });
  fs.cpSync("src/Assets", "dist/Assets", { recursive: true });
}

async function main() {
  if (watch) {
    const ctx = await esbuild.context(config);
    await ctx.watch();
    copyAssets();
    return;
  }

  await esbuild.build(config);
  copyAssets();
}

main().catch(error => {
  console.error(error);
  process.exitCode = 1;
});

"use strict";

import { paths } from "../gulpfile.babel";
import gulp from "gulp";
import babel from "gulp-babel";
import debug from "gulp-debug";

import webpack from "webpack";
import webpackStream from "webpack-stream";
import gulpif from "gulp-if";
import rename from "gulp-rename";
import browsersync from "browser-sync";
import yargs from "yargs";

const webpackConfig = require("../webpack.config.js"),
    argv = yargs.argv,
    production = !!argv.production;

webpackConfig.mode = production ? "production" : "development";
webpackConfig.devtool = production ? false : "source-map";

gulp.task("scripts", () => {
    return gulp.src(paths.scripts.src)
        .pipe(webpackStream(webpackConfig), webpack)
        .pipe(gulpif(production, rename({
            suffix: ".min"
        })))
        .pipe(gulp.dest(paths.scripts.dist))
        .pipe(debug({
            "title": "JS files"
        }))
        .on("end", browsersync.reload);
});


gulp.task("bx-scripts", () => {
    return gulp.src(paths["bx-scripts"]["src"])
        .pipe(babel())
        .pipe(gulp.dest(paths["bx-scripts"]["dist"]))
        .pipe(debug({
            "title": "BX JS files"
        }));
});

const gulp = require('gulp');
const autoprefixer = require('gulp-autoprefixer');
const browserSync = require('browser-sync').create();
const header = require('gulp-header');
const sass = require('gulp-sass');
const pkg = require('./package.json');

const SCSS_INPUT = 'static/scss/**/*.scss';

const STYLE_CSS_BANNER = `/*
 * Theme Name: <%= name %>
 * Theme URI: <%= homepage %>
 * Description: <%= description %>
 * Author: <%= author %>
 * Version: <%= version %>
 * License: <%= license %>
 * Text Domain: joist
*/`;

function cssDebug() {
    return gulp.src(SCSS_INPUT, { sourcemaps: true })
        .pipe(header(STYLE_CSS_BANNER, pkg))
        .pipe(sass({
            errLogToConsole: true,
            outputStyle: 'expanded',
        }).on('error', sass.logError))
        .pipe(autoprefixer())
        .pipe(gulp.dest('.', { sourcemaps: '.' }))
        .pipe(browserSync.reload({ stream: true }));
}

function cssProd() {
    return gulp.src(SCSS_INPUT, { sourcemaps: true })
        .pipe(sass({
            errLogToConsole: true,
            outputStyle: 'compressed',
        }).on('error', sass.logError))
        .pipe(autoprefixer())
        .pipe(gulp.dest('.', { sourcemaps: '.' }));
}

exports.css = gulp.parallel(cssDebug);
exports.build = gulp.parallel(cssProd);
exports.default = exports.watch = function() {
    browserSync.init({
        proxy: "pvn.test",
        open: false,
        port: 3001,
    });
    gulp.watch(SCSS_INPUT, cssDebug);
}

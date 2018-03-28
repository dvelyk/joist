const gulp = require('gulp');
const autoprefixer = require('gulp-autoprefixer');
const gulpif = require('gulp-if');
const header = require('gulp-header');
const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const pkg = require('./package.json');

const SCSS_INPUT = 'static/scss/**/*.scss';

const STYLE_CSS_BANNER = `/*
 * Theme Name: <%= name %>
 * Theme URI: <%= homepage %>
 * Description: <%= description %>
 * Author: <%= author %>
 * Version: <%= version %>
 * License: <%= license %>
 * Text Domain: <% print(name.toLowerCase()) %>
*/`;

const convertScssToCSS = ({ debug = false }) => {
    gulp.src(SCSS_INPUT)
        .pipe(gulpif(debug, header(STYLE_CSS_BANNER, pkg)))
        .pipe(gulpif(debug, sourcemaps.init()))
        .pipe(sass({
            errLogToConsole: true,
            outputStyle: debug ? 'expanded' : 'compressed',
        }).on('error', sass.logError))
        .pipe(autoprefixer())
        .pipe(gulpif(debug, sourcemaps.write('static/')))
        .pipe(gulpif(!debug, header(STYLE_CSS_BANNER, pkg)))
        .pipe(gulp.dest('.'));
}

gulp.task('css', () => {
    convertScssToCSS({ debug: true });
});

gulp.task('build', () => {
    convertScssToCSS({ debug: false });
});

gulp.task('default', ['css'], () => {
    gulp.watch(SCSS_INPUT, ['css']);
});

/**
 * @file
 * Contains an example build for .sfc files.
 */

const gulp = require('gulp');
const replace = require('gulp-replace');
const sass = require('node-sass');
const babel = require('@babel/core');

function defaultTask(cb) {
  gulp.src('./components_src/*')
    .pipe(replace(/(?<=<style[^>]*>)([\s\S]+?)(?=<\/style>)/gi, (match, p1, offset, string) => {
      return sass.renderSync({
        data: p1
      }).css;
    }))
    .pipe(replace(/(?<=<script[^>]*>)([\s\S]+?)(?=<\/script>)/gi, (match, p1, offset, string) => {
      return babel.transformSync(p1, {
        presets: ['@babel/env'],
        sourceType: 'script',
      }).code;
    }))
    .pipe(gulp.dest('./components'));
  cb();
}

function tailwindTask(cb) {
  const postcss = require('gulp-postcss');

  gulp.src('./css/tailwind.css')
    .pipe(postcss([
      require('tailwindcss'),
      require('autoprefixer'),
    ]))
    .pipe(gulp.dest('./css/dist'));
  cb();
}

exports.default = defaultTask;
exports.tailwind = tailwindTask;

exports.watch = () => {
  gulp.watch('./components_src/*', defaultTask);
  gulp.watch('./css/tailwind.css', tailwindTask);
};

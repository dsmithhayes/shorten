'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var mkdirp = require('mkdirp');
var fs = require('fs');
var concat = require('gulp-concat');

gulp.task('sass', function () {

    fs.stat('./public/css', function (err) {
        if (err) mkdirp('./public/css');
    });

    return gulp.src('./sass/**/*.scss')
        .pipe(concat('main.scss'))
        .pipe(sass({ outputStyle: 'compressed' }))
        .pipe(gulp.dest('./public/css'));
});

gulp.task('default', ['sass']);
'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var concat = require('gulp-concat');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');
var mkdirp = require('mkdirp');
var fs = require('fs');

var publicDir = [ 'css', 'js' ];

gulp.task('public', function () {
    var build = function (dir) {
        fs.stat('./public/' + dir, function (err) {
            if (err) mkdirp('./public/' + dir);
        });
    };

    publicDir.map(function(v) { build(v); });
});

gulp.task('sass', [ 'public' ], function () {
    return gulp.src('./sass/**/*.scss')
        .pipe(concat('main.scss'))
        .pipe(sass({ outputStyle: 'compressed' }))
        .pipe(gulp.dest('./public/css'));
});

gulp.task('js', [ 'public' ], function () {
    return gulp.src('./js/**/*.js')
        .pipe(concat('app.js'))
        .pipe(uglify())
        .pipe(gulp.dest('./public/js'));
});

gulp.task('jquery', [ 'public' ], function() {
    return gulp.src('./node_modules/jquery/dist/jquery.min.js')
        .pipe(rename('jquery.js'))
        .pipe(gulp.dest('./public/js'));
});

gulp.task('default', [ 'sass', 'js', 'jquery' ]);
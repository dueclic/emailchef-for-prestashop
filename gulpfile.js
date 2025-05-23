'use strict';

const gulp = require('gulp');
const scss = require('gulp-sass')(require('sass'));
const sourcemaps = require('gulp-sourcemaps');
const rename = require('gulp-rename');
const plumber = require('gulp-plumber');
const autoprefixer = require('gulp-autoprefixer');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const babel = require('gulp-babel');
const clean = require('gulp-clean');

gulp.task('styles', async () => {
    return gulp.src('./emailchef/views/scss/emailchef.scss')
        .pipe(plumber({
            errorHandler: function (err) {
                console.log(err);
                this.emit('end');
            }
        }))
        .pipe(sourcemaps.init({loadMaps: true}))
        .pipe(scss({outputStyle: 'compressed'}).on('error', scss.logError))
        .pipe(autoprefixer('last 2 versions'))
        .pipe(sourcemaps.write(undefined, {sourceRoot: null}))
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('./emailchef/views/css'));
});

gulp.task('scripts', async () => {

    let src = [
        './emailchef/views/js/emailchef.js'
    ];

    gulp.src(src)
        .pipe(concat('emailchef.min.js'))
        .pipe(babel({
            presets: ['@babel/preset-env']
        }))
        .pipe(uglify())
        .pipe(gulp.dest('./emailchef/views/bundle/emailchef'));
});


gulp.task('clean', function () {
    return gulp.src(['./emailchef/views/bundle/emailchef/*', './emailchef/views/css/*'], {read: false})
        .pipe(clean());
});

gulp.task('default', gulp.series('clean','styles', 'scripts'));

gulp.task('scss:watch', () => {
    gulp.watch('emailchef/views/scss/**/*.scss', gulp.series('styles'))
});

gulp.task('js:watch', () => {
    gulp.watch('./emailchef/views/js/**/*.js', gulp.series('scripts'))
});


gulp.task('watch', gulp.parallel('scss:watch', 'js:watch'));

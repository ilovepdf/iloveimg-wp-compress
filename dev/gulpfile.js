

var gulp            = require('gulp');
var browserSync     = require('browser-sync').create();
var sass            = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');
var gulpMerge = require('gulp-merge'),
  concat = require('gulp-concat'), 
  cleanCSS = require('gulp-clean-css'),
  minify = require('gulp-minify'),
  watch = require('gulp-watch'),
  livereload = require('gulp-livereload');

gulp.task('sass', function(){
    return gulp.src(['scss/*.scss'])
            .pipe(sass())
            .pipe(autoprefixer({
                browsers: ['last 2 versions'],
                cascade: false
            }))
            .pipe(concat('app.css'))
            .pipe(cleanCSS())
            .pipe(gulp.dest("../assets/css"))
            .pipe(livereload());
});

/*
gulp.task('js', function(){
    return gulp.src(['node_modules/bootstrap/dist/js/bootstrap.min.js'])
            .pipe(minify())
            .pipe(concat('app.js'))
            .pipe(gulp.dest("src/build"));
});
*/

gulp.task('watch', function() {
  livereload.listen();
  gulp.watch('scss/*.scss', gulp.series('sass'));
});
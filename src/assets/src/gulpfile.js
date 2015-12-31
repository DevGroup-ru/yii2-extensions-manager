const gulp = require('gulp');
const sass = require('gulp-sass');

gulp.task('styles', function() {
    return gulp.src('css/ext-manager.css')
      .pipe(sass().on('error', sass.logError))
      .pipe(gulp.dest('../dist/css'))
});

gulp.task('js', function() {
    return gulp.src('js/ext-manager.js')
        .pipe(gulp.dest('../dist/js'))
});

gulp.task('default', function() {
    gulp.start('styles', 'js');
});


const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const minify = require('gulp-uglify');

gulp.task('styles', () => {
  return gulp.src('sass/*.scss')
    .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    .pipe(gulp.dest('css'))
});

gulp.task('watch', () => {
  return gulp.watch('sass/*.scss', (done) => {
    gulp.series(['styles']) (done)
  })
});

gulp.task('minifyjs', () => {
  return gulp.src('js/*.js')
    .pipe(minify())
    .pipe(gulp.dest('js/jquery'))
});
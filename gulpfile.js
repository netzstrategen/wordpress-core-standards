const autoprefixer = require('gulp-autoprefixer');
const eol = require('gulp-eol');
const eslint = require('gulp-eslint');
const gulp = require('gulp');
const npmDist = require('gulp-npm-dist');
const filter = require('gulp-filter');
const concat = require('gulp-concat');
const runSequence = require('run-sequence');
const rename = require('gulp-rename');
const sass = require('gulp-sass');
const uglify = require('gulp-uglify');

gulp.task('styles', () => {
  gulp.src('assets/styles/**/*.scss')
  .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
  .pipe(autoprefixer({
    browsers: ['last 2 versions']
  }))
  .pipe(eol('\n'))
  .pipe(gulp.dest('./dist/styles'));
});

gulp.task('eslint', () => {
  return gulp.src(['**/*.js', '!node_modules/**', '!dist/**', '!*.min.js', '!gulpfile.js'])
    .pipe(eslint())
    .pipe(eslint.format());
});

gulp.task('libs', () => {
  gulp.src(npmDist(), { base:'./node_modules' })
    .pipe(filter(['**/*.min.js']))
    .pipe(concat('libs.js'))
    .pipe(gulp.dest('./dist/scripts/'));
});

gulp.task('scripts', ['eslint'], () => {
  gulp.src('assets/scripts/**/*.js')
  .pipe(uglify({
    output: { beautify: true }
  }))
  .pipe(eol('\n'))
  .pipe(gulp.dest('./dist/scripts'));
});

gulp.task('build', (callback) => {
  runSequence('styles', 'scripts', 'libs', callback);
});

gulp.task('watch', () => {
  gulp.watch('assets/styles/**/*.scss', ['styles']);
  gulp.watch('assets/scripts/**/*.js',['scripts']);
});

gulp.task('default', () => {
  gulp.start('build');
});

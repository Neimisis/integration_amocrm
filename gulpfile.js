const gulp = require("gulp");
const autoprefixer = require("gulp-autoprefixer");
const babel = require("gulp-babel");
const gulp_zip = require("gulp-zip");
const uglify = require("gulp-uglify-es").default;
const group_media = require("gulp-group-css-media-queries");
const clean_css = require("gulp-clean-css");
const del = require("del");

function css() {
  return gulp.src('./src/assets/css/app.css')
    .pipe(autoprefixer({
        overrideBrowserslist: ["last 5 versions"],
        cascade: true,
      })
    )
    .pipe(group_media())
    .pipe(clean_css({level:{1:{specialComments:0}}}))
    .pipe(gulp.dest("./build/assets/css/"));
}

function js() {
  return gulp.src(['./src/assets/js/app.js'])
    .pipe(babel({presets: ['@babel/preset-env']}))
    .pipe(uglify())
    .pipe(gulp.dest("./build/assets/js/"));
}

function build() {
  return gulp.src(["./src/**/*", "!./src/**/*.css", "!./src/**/*.js"])
    .pipe(gulp.dest("./build"));
}

function zip() {
  return gulp.src("./build/**/*")
    .pipe(gulp_zip("build.zip"))
    .pipe(gulp.dest("./build"));
}

function clean() {
  return del("./build");
}

function delete_excess() {
  return del(["./build/**/*", "!./build/*.zip"]);
}

exports.css = gulp.task(css);
exports.js = gulp.task(js);
exports.zip = gulp.task(zip);
exports.build = gulp.series(clean, build, css, js, zip, delete_excess);
exports.default = gulp.series(clean, build, css, js);
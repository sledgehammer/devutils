var gulp = require('gulp');
var noprotocol = require('gulp-noprotocol');
var livereload = require('gulp-livereload');

gulp.task('css', function() {
    return gulp.src([
        'node_modules/angular-material/angular-material.min.css',
        'sass/main.scss'
    ])
        .pipe(noprotocol.css())
        .on('error', noprotocol.notify)
        .pipe(noprotocol.bundle('bundle.css'))
        .pipe(gulp.dest('public/build'));
});

gulp.task('bundle-libs', function() {
    return gulp.src([
        'node_modules/jquery/dist/jquery.min.js',
        'node_modules/@reactivex/rxjs/dist/global/Rx.umd.min.js',
        'node_modules/angular/angular.js', // @todo Use the .min.js
        'node_modules/angular-animate/angular-animate.min.js',
        'node_modules/angular-aria/angular-aria.min.js',
        'node_modules/angular-messages/angular-messages.min.js',
        'node_modules/angular-material/angular-material.min.js',
        'node_modules/angular-ui-router/release/angular-ui-router.min.js',
    ])
    .pipe(noprotocol.bundle('libs.bundle.js'))
    .on('error', noprotocol.notify)
    .pipe(gulp.dest('public/build'));
});

gulp.task('bundle-app', function () {
    return gulp
        .src('js/**/*.{js,html}')
        .pipe(noprotocol.angular({
            deps: ['ngMaterial', 'ui.router'],
            templateCache: {
                strip: __dirname + '/js/'
            },
            minify: false 
        }))
        .on('error', noprotocol.notify)
        .pipe(gulp.dest('public/build'));
});

gulp.task('watch', ['css', 'bundle-app', 'bundle-libs'], function() {

    livereload.listen();
    gulp.watch(['sass/**/*.{scss,sass}', 'js/**/*.scss'], ['css']);
    gulp.watch('js/**/*.{js,html}', ['bundle-app']);
    gulp.watch([
        'public/build/*.{css,js}',
        'public/**/*.html'
    ]).on('change', livereload.changed);
    gulp.watch(['gulpfile.js'], function () {
        noprotocol.notify('Stopping `gulp watch`, gulpfile.js was changed');
        process.exit();
    });
});

gulp.task('deploy', ['css', 'bundle-libs', 'bundle-app']);

gulp.task('default', ['watch']);
let mix = require('laravel-mix');
mix.js('src/index.js', 'dist/js')
mix.sass('src/assets/style.scss','dist/css')
mix.disableSuccessNotifications();

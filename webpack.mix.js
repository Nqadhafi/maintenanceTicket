const mix = require('laravel-mix');

mix
  .postCss('resources/css/app.css', 'public/css', [
    require('postcss-import'),
    require('tailwindcss'),
    require('autoprefixer'),
  ])
  .options({ processCssUrls: false })
  .disableNotifications();

if (mix.inProduction()) {
  mix.version();
} else {
  mix.sourceMaps();
}

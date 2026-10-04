/* Shared, same-origin, single-thread media engine. No cross-origin isolation required. */
(function () {
  'use strict';
  var pending;
  function load() {
    if (pending) return pending;
    var base = window.UFX_MEDIA && window.UFX_MEDIA.base;
    if (!base) return Promise.reject(new Error('Media assets are missing. Clear the site cache and reload.'));
    base = new URL(base, document.baseURI).href;
    pending = new Promise(function (resolve, reject) {
      if (window.FFmpeg) return resolve();
      var script = document.createElement('script');
      script.src = base + 'ffmpeg.min.js';
      script.onload = resolve;
      script.onerror = function () { script.remove(); reject(new Error('Could not download the media engine. Reload and try again.')); };
      document.head.appendChild(script);
    }).then(function () {
      var engine = window.FFmpeg.createFFmpeg({log:false, mainName:'main', corePath:base + 'ffmpeg-core.js'});
      return engine.load().then(function () {
        // Always overwrite stale output left by an interrupted/failed operation.
        var run = engine.run;
        engine.run = function () {
          return run.apply(engine, ['-y'].concat(Array.prototype.slice.call(arguments)));
        };
        return engine;
      });
    }).catch(function (error) { pending = null; throw error; });
    return pending;
  }
  window.UFXMediaEngine = {load:load};
})();

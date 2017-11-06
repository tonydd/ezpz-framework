/**
 * Instanciate worker cache
 */
self.addEventListener('install', function(event) {
    console.log('INIT WORKER');

    event.waitUntil(
        fetch('?ctl=static&action=registerWorker')
        .then(
            function(response) {
                if (response.status !== 200) {
                    console.log('Looks like there was a problem. Status Code: ' +
                        response.status);
                    return;
                }

                // Examine the text in the response
                response.json().then(function(data) {
                    console.log(data);
                });
            }
        )
        .catch(function(err) {
            console.log('Fetch Error :-S', err);
        })
    );

    event.waitUntil(
        caches.open('v1').then(function(cache) {
            return cache.addAll([
                '/?ctl=static&action=css',
                '/?ctl=static&action=js'
            ]);
        })
    );
});

/**
 * Activate worker
 */
self.addEventListener('activate', function () {
    self.clients.claim()
});

/**
 * URL processing
 */
self.addEventListener('fetch', function(event) {
    console.debug(event.request.url);

    if (serviceHelper.stringContains(event.request.url, '/ez/')) {
        console.log('rewrite :scream:');
        var trueUrl = serviceHelper.buildRealurl(event.request.url);
        event.respondWith(fetch(trueUrl));
    }
    else {
        event.respondWith(
            fetch(event.request)
        );
    }
});

var serviceHelper = {
    server_RegisterWorker: function () {
        fetch('?ctl=static&action=registerWorker')
            .then(
                function(response) {
                    if (response.status !== 200) {
                        console.log('Looks like there was a problem. Status Code: ' +
                            response.status);
                        return;
                    }

                    // Examine the text in the response
                    response.json().then(function(data) {
                        console.log(data);
                    });
                }
            )
            .catch(function(err) {
                console.log('Fetch Error :-S', err);
            });

    },

    stringContains: function (haystack, needle) {
        return haystack.indexOf(needle) !== -1;
    },

    buildRealurl: function(targetUrl) {

        var urlOb = new URL(targetUrl);

        var components = urlOb.pathname.split('/');
        var ctl = components[2];
        var action = components[3];

        var output = urlOb.origin + '/?ctl=' + ctl + '&action=' + action + urlOb.search.replace('?', '&');
        console.log('rewriting to ' + output);
        return output;
    }
};
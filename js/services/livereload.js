/**
 * Connect to a livereload server.
 *
 * Usage: livereload(35729).subscribe(function (file) { ... }) 
 */
app.factory('livereload', function () {
    function livereload(port) {
        return Rx.Observable.create(function (observer) {
            var ws = new WebSocket("ws://localhost:1808/livereload");
            ws.onerror = function (e) {
                observer.error(new Error('Websocket failed'));
            }
            ws.onopen = function() {
                ws.send(JSON.stringify({
                    command: 'hello',
                    protocols: [
                        'http://livereload.com/protocols/official-7',
                    ]
                }));
            };
            ws.onclose = function() {
                observer.error(new Error('The livereload server stopped')); 
            };
            // message received
            ws.onmessage = function (e){
                try {
                    var message = JSON.parse(e.data);
                    if (message.command === 'reload') {
                        observer.next(message.path);
                    }
                } catch (e) {
                    observer.error(e);
                }
            };
    
        });
    };
    return livereload;
});
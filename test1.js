    var port = 8080,
        http = require('http'),
//        req = require('http').request(),
//        url = require('url');
//        get = require('get'),
        https = require('https'),
        fs = require('fs');

    var options = {
        key: fs.readFileSync('/etc/ssl/node-selfsigned.key'),
        cert: fs.readFileSync('/etc/ssl/node-selfsigned.crt')
    };

    var Pusher = require('pusher');

//    var query = require('https://ec2-18-223-235-58.us-east-2.compute.amazonaws.com:9000?type=1').parse(req.url,true).query;
    
    fs.readFile('index.html', function (err, html) {
        if (err) {
            console.log('Arijit');
            throw err;
        }

        https.createServer(options, function (req, res) {
//            var querystring = require('querystring');
//            var q = querystring.parse(req.url);
//            console.log(req.headers.referer);
//            console.log(req.body);
//            var pa=req.headers.referer;
//            console.log(pa.searchParams.get('type'));
//            console.log(req.get('host'));
//            console.log(document.location.href);
//            console.log(req.url.parse);
//            console.log(req.url);
//            var pusher = new Pusher({
//                appId: '656003',
//                key: '14f98a47ec413e002594',
//                secret: 'a92df5cee3d31aee71aa',
//                cluster: 'ap2',
//                encrypted: true
//            });
//            pusher.trigger('my-channel', 'my-event', {
////                "type": query.type
//            });


            res.writeHeader(200, {"Content-Type": "text/html"});
            res.write(html);
            res.end();
        }).listen(port);
        console.log('Listening on port', port);
    });






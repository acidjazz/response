
/**
 * local jade delivery via localhost web port 3000
 */

var jade = require('/usr/lib/node_modules/jade');
var http = require('http');
var path = require('path');
//var fs = require('fs');

http.createServer(function(req, res) {

  if (req.method != 'POST') {
    res.writeHead(500, {'Content-Type': 'text/json'});
    res.end(JSON.stringify({'error': 'post required'}));
    return true;
  }

  var body = '';

  req.on('data', function(data) {
    body += data;
  });

  req.on('end', function() {

    var post = JSON.parse(body);

    jade.renderFile(post.file, post.data, function(error, output) {

      if (error) {
        res.writeHead(500, {'Content-Type': 'text/html'});
        res.end(error.message); 
      } else {
        res.writeHead(200, {'Content-Type': 'text/html'});
        res.end(output);
      }
      
    });

  });

}).listen(3000);

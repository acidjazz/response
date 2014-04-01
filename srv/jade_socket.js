


var jade = require('/usr/lib/node_modules/jade');
var net = require('net');
var fs = require('fs');

fs.unlink('/tmp/jade.sock');

var server = net.createServer(function(socket) {

  socket.on('data', function(data) {

    var obj = {};

    try {
      var json = JSON.parse(data);
    } catch (error) {

      obj = {
          status: 'error',
          message: error.message
        };

      socket.write(obj.toString());
      socket.end();

    }

    jade.renderFile(json.file, json.data, function(error, output) {

      if (error) {
        obj = {
          status: 'error',
          message: error.message,
        };
      } else {
        obj = { status: 'success', html: output, };
      }

      socket.write(JSON.stringify(obj));
      socket.end();

    });

  });

});

server.listen('/tmp/jade.sock');

console.log('server is listening on jade.sock');

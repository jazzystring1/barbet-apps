var express = require('express');
var socket = require('socket.io');
var online_users = {};

var app = express();

var port_number = server.listen(process.env.PORT || 3000);


var server = app.listen(port_number, function () {
	console.log("Listening on port", port_number);
})


app.use(express.static('public'));

var io = socket(server);

//GetIndexKey

function val2key(val,array){
    for (var key in array) {
        this_val = array[key];
        if(this_val == val){
            return true;
            break;
        }
    }
}

io.on('connection', function (socket) {
	console.log("User Connected", socket.id);

	socket.on('whosonline', function (data) {
		 if(val2key(data, online_users) == true) {
			io.emit('whosonline', online_users);
		} else { 
			online_users[socket.id] = data;
			console.log(online_users);
			io.emit('whosonline', online_users);
		}
	});

	socket.on('join', function (data) {
		socket.join(data);
	});

	socket.on('chat', function (data) {
		io.emit('chat', data);
	});

	socket.on('typing', function (data) {
		socket.broadcast.emit('typing', data);
	});

	socket.on('non-typing', function (data) {
		io.emit('non-typing', data);
	});


	socket.on('disconnect', function () {
		console.log("User Disconnected :", socket.id);
		delete online_users[socket.id];
		console.log(online_users);
	})

	})

	
	


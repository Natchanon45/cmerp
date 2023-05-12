var express = require( 'express' );

var app = express();

var io = require( 'socket.io' );

var path = require( 'path' );

var server = io.listen( app.listen( 300 ) );

require('./io')( server );

app.get('/', function( req, res ) {
	res.sendFile( path.resolve( __dirname, 'public', 'index.html' ) );
});




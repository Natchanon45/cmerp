module.exports = function(io) {
	io.on( 'connect', function( client ) {

		client.on( 'work_on_io', function( data ) {
			io.emit( 'work_on_io', data );
		});
	});
}
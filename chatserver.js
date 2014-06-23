var rooms = [];
module.exports.getUserFeeds = function (chatpage, socket, io, pool,async)
{
	socket.on('senddata', function (data)
    {
		socket.user_id = data.user_id;
		socket.room_id=data.room_id;
        socket.room = 'room' + data.room_id;
		rooms['room' + data.room_id] = 'room' + data.room_id;
        socket.join('room' + data.room_id);
		pool.getConnection(function (err, connection)
		{
			 async.parallel([
				function(callback)
				{
					connection.query('SELECT user_id,username FROM users where user_id=' + data.user_id + '', function (error1, userdata)
					{
						 if (error1) return callback(error1);
						 callback(null, userdata);
					});
				},
				function (callback)
				{
					if(data.user_id)
					connection.query('SELECT user_id,username FROM users', function (error3, memdata)
					{
						if (error3) return callback(error3);
						callback(null, memdata);
					});
					else
						callback(null,null);
				},
				function(callback)
				{
					if(data.user_id)
					connection.query('SELECT comment_id,comment,users.user_id,username,comments.added FROM comments INNER JOIN users ON comments.user_id=users.user_id',function(error4,converdata){
						if (error4) return callback(error4);
						callback(null, converdata);
					});
					else
						callback(null,null);
				}
			 ], function (err, results)
				{
					if(err) throw err;
					socket.emit('chatdata',
					{
						memdata:results[1],
						converdata:results[2],
					});
					socket.broadcast.to('room'+ data.room_id +'').emit('newuser', {userdata:results[0]});
					connection.release();
				});
		});
	});
	
	socket.on('sendcomment', function (data)
	{
		pool.getConnection(function (err, connection)
        {
			connection.query('INSERT INTO comments (user_id,comment,added) VALUES (' + data.user_id + ',"' + data.msg + '","' + data.datetime + '")', function (err, result)
            {
				if (err) throw err;
				async.parallel([
					function (callback)
					{
						connection.query('SELECT comments.*,username,comments.added from comments JOIN users ON comments.user_id=users.user_id WHERE comments.comment_id=' + result.insertId + '', function (err2, comments)
						{
							if (err2) return callback(err2);
							callback(null, comments);
						});
					},
					function (callback)
                    {
                        connection.query('SELECT count(comment_id) as tot_comment  from comments', function (err3, comment_count)
                        {
                            if (err3) return callback(err3);
                            callback(null, comment_count);
                        });
                    },
					], function (err, results)
					{
						if (err) throw err;
						if (results[0])
						{
							chatpage. in('room'+ data.room_id +'').emit('showcomment',
                            {
								room_comment: results[0],
                                comment_count: results[1]
                            });
						}
						connection.release();
					});	
			});
		});
	});
	socket.on('disconnect', function ()
    {
        console.log("user disconnected");
		pool.getConnection(function (err, connection)
        {
			connection.query('DELETE from users where user_id='+socket.user_id+'', function (err, removeuser)
			{
				if (err) throw err;
			});
			connection.query('DELETE from comments where user_id='+socket.user_id+'', function (err, removecomments)
			{
				if (err) throw err;
			});
			connection.release();
		});
		socket.broadcast.to('room'+ socket.room_id +'').emit('removeuser', {user_id:socket.user_id});
        socket.leave(socket.room);
    });
	
};
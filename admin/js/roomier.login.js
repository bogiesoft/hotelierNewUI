$( document ).ready(function() {
	var serviceURL="http://booking.roomier.gr/";
	var hotelierURL = "http://booking.roomier.gr/hotelier/index.html";
	var roomierURL = "http://booking.roomier.gr/admin/index.html";
	
	$('#login-notify').hide();
	$('#resetPass-notify').hide();
	$('#resetPanel').hide();
	$('#registerPanel').hide();
	
	if(typeof(Storage) == "undefined") {
		// Sorry! No Web Storage support..
		$('#login-notify').removeAttr('class');
		$('#login-notify').addClass('alert-box error');
		$('#login-notify').html("Your browser does not support HTML5 localStorage. Try upgrading.");
		$('#login-notify').show();
		console.log("Sorry! No Web Storage support..");
	}
	
	$('#shresetPanel').on('click', function(){
		$('#loginPanel').hide();
		$('#resetPanel').show();
	});
	
	
	$('#backloginPanel').on('click', function(){
		$('#resetPanel').hide();
		$('#loginPanel').show();
	});
	
	$('#login-notify-close').on('click', function(){
		$('#login-notify').hide();
	});
	
	$('#resetPanel-notify-close').on('click', function(){
		$('#resetPanel-notify').hide();
	});
	
	$(document).keypress(function(e) {
		if(e.which == 13) {
			localStorage.clear();
			var data = {
					"action": "login",
					"email" : $("#login-form-username").val(),
					"password" : $("#login-form-password").val()
				};
		
			//console.log(JSON.stringify(data));
			login(data);
		}
	});

	$('#login-form-admin').on("click", function(){
		localStorage.clear();
		var data = {
				"action": "login",
				"email" : $("#login-form-username").val(),
				"password" : $("#login-form-password").val()
			};
	
		//console.log(JSON.stringify(data));
		login(data);
			
	});
	
	$('#resetPass-form-admin').on("click", function(){
		localStorage.clear();
		var data = {
				"action": "reset_password",
				"email" : $("#newpass-form-username").val()
			};
	
		console.log(JSON.stringify(data));
		
		$.ajax({
           url: serviceURL+"login.php",
           dataType: "json",
		   type: "post",
            data: JSON.stringify(data),
			success: function (resp) {
				
				$('#resetPass-notify').removeAttr('class');
				if(resp.resp == 'true') {
					$('#resetPass-notify').addClass('alert bg-danger');
					$('#resetPass-notify-txt').html(resp.responce);
					$('#resetPass-notify').show();
					console.log(JSON.stringify(resp));
				} else {
					$('#resetPass-notify').addClass('alert bg-danger');
					$('#resetPass-notify-txt').html(resp.responce);
					$('#resetPass-notify').show();
				}
               
            },
			error: function(err) {
				console.log("err " + JSON.stringify(err));
				$('#resetPass-notify').addClass('alert-box error');
				$('#resetPass-notify').html(resp.responce);
				$('#resetPass-notify').show();
			}
       });	
	});
	
	function login(data) {
		$.ajax({
           url: serviceURL+"login.php",
           dataType: "json",
		   type: "post",
            data: JSON.stringify(data),
			success: function (resp) {
				$('#login-notify').removeAttr('class');
				if(resp.resp == 'true') {
					$('#login-notify').addClass('alert bg-success');
					$('#login-notify-txt').html(resp.responce);
					if(resp.data.type.localeCompare("admin") == 0 )
						$('#login-notify-txt').append(" If you do not redirect automatically click <a href='"+roomierURL+"index.html'> here</a>");
					else 
						$('#login-notify-txt').append(" If you do not redirect automatically click <a href='"+hotelierURL+"index.html'> here</a>");
					
					$('#login-notify').show();
					localStorage.setItem("token", resp.data.token);
					localStorage.setItem("email", $("#login-form-username").val());
					localStorage.setItem("userID", resp.data.userID);
					localStorage.setItem("name", resp.data.name);
					localStorage.setItem("surname", resp.data.surname);
					localStorage.setItem("type", resp.data.type);
					console.log(JSON.stringify(resp));
					console.log("localstorage::token :" + localStorage.getItem("token"));
					console.log("pathname: " + window.location.host);
					// similar behavior as an HTTP redirect
					if(resp.data.type.localeCompare("admin") == 0 )
						window.location.replace(roomierURL);
					else 
						window.location.replace(hotelierURL); 
				} else {
					localStorage.clear();
					$('#login-notify').addClass('alert bg-danger');
					$('#login-notify-txt').html(resp.responce);
					$('#login-notify').show();
					console.log("localstorage::token :" + localStorage.getItem("token"));
				}
               
            },
			error: function(err) {
				localStorage.clear();
				console.log("err " + JSON.stringify(err));
				$('#login-notify').addClass('alert bg-danger');
				$('#login-notify-txt').html("Response error...please contact with administrator.");
				$('#login-notify').show();
			}
       });
	}
	
});
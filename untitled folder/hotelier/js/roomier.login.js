$( document ).ready(function() {

	hideAll();
	$('#loginPanel').show();
	var up2date = new Date();

	if(typeof(Storage) == "undefined") {
		// Sorry! No Web Storage support..
		$('#login-notify').removeAttr('class');
		$('#login-notify').addClass('alert-box error');
		$('#login-notify').html("Your browser does not support HTML5 localStorage. Try upgrading.");
		$('#login-notify').show();
		console.log("Sorry! No Web Storage support..");
	}

	$('#shresetPanel').on('click', function(){
		hideAll();
		$('#resetPanel').show();
	});

	$('#shregisterPanel').on('click', function(){
		hideAll();
		$('#registerPanel').show();
	});


	$('#backloginPanel').on('click', function(){
		hideAll();
		$('#loginPanel').show();
	});

	$('#backloginPanel_2').on('click', function(){
		hideAll();
		$('#loginPanel').show();
	});

	$('#login-notify-close').on('click', function(){
		$('#login-notify').hide();
	});

	$('#resetPanel-notify-close').on('click', function(){
		$('#resetPanel-notify').hide();
	});

	$('#register-notify-close').on('click', function(){
		$('#register-notify').hide();
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

	$('#register-form-submit').on("click",function(){
		var data = {
				"action": "signup",
				'name' : $('#register-form-name').val(),
				'surname': $('#register-form-surname').val(),
				'email' : $('#register-form-email').val(),
				'phone' : $('#register-form-phone').val(),
				'mobile' : $('#register-form-mobile').val(),
				'address' : $('#register-form-address').val(),
				'postcode' : $('#register-form-zip').val(),
				'town' : $('#register-form-town').val(),
				'country' : $('#register-form-country').val(),
				'afm' : $('#register-form-vat').val(),
				'doy' : $('#register-form-doy').val(),
				'password' : $('#register-form-password').val()
			};


		$.ajax({
           url: serviceURL+"login.php",
           dataType: "json",
		   type: "post",
            data: JSON.stringify(data),
			success: function (resp) {
				$('#register-notify').removeAttr('class');
				if(resp.resp == 'true') {
					hideAll();
					$('#login-notify').addClass('alert bg-success');
					$('#login-notify-txt').html(resp.responce);
					$('#login-notify').show();
					console.log(JSON.stringify(resp));
					clearRegister();
					$('#loginPanel').show();
				} else {
					$('#register-notify').addClass('alert bg-danger');
					$('#register-notify-txt').html(resp.responce);
					$('#register-notify').show();
				}
				//console.log("test");
                //console.log(JSON.stringify(resp));

            },
			error: function(err) {
				console.log("Error: " + JSON.stringify(err));
				$('#register-notify').html("Error: Registration failed, please contact with support..."  );
			}
       });
	});

	function hideAll() {
		$('#login-notify').hide();
		$('#resetPass-notify').hide();
		$('#register-notify').hide();
		$('#loginPanel').hide();
		$('#resetPanel').hide();
		$('#registerPanel').hide();
	}

	function clearRegister() {
        $('#register-form-name').val("");
		$('#register-form-surname').val("");
		$('#register-form-email').val("");
		$('#register-form-phone').val("");
		$('#register-form-mobile').val("");
		$('#register-form-address').val("");
		$('#register-form-zip').val("");
		$('#register-form-town').val("");
		$('#register-form-country').val("");
		$('#register-form-vat').val("");
		$('#register-form-doy').val("");
		$('#register-form-password').val("");
		$('#register-form-repassword').val("");
    }

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
						window.location.replace(roomierURL+"?"+up2date.getTime());
					else
						window.location.replace(hotelierURL+"?"+up2date.getTime());
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

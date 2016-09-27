$( document ).ready(function() {
			
	if(typeof(Storage) == "undefined") {
		// Sorry! No Web Storage support..
		console.log("Sorry! No Web Storage support..");
	}
	
			
	
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
	
	
	
	
	$( "#booking-form-from" ).datepicker({
		format: 'yyyy-mm-dd',
		startDate: '+1d',
		autoclose: true
		//numberOfMonths: 2
	}).on('change', function(){
		$( "#booking-form-to" ).datepicker({
			format: 'yyyy-mm-dd',
			startDate: $('#booking-form-from').val(),
			autoclose: true
			//numberOfMonths: 2
		});
	});


	$( "#booking-form-to" ).datepicker({
		format: 'yyyy-mm-dd',
		startDate: '+1d',
		autoclose: true
		//numberOfMonths: 2
	});

	$('#booking-form-check').on("click", function(){
		localStorage.clear();
		var data = {
				"action": "check_available",
				"arrival" : $("#booking-form-from").val(),
				"departure" : $("#booking-form-to").val(),
				"rooms" : $("#booking-form-rooms-num").val(),
				"adults" : $("#booking-form-adults").val(),
				"children" : $("#booking-form-children").val()
			};
	
		console.log(JSON.stringify(data));
		//ogin(data);
			
	});
		
});
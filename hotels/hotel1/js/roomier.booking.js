$( document ).ready(function() {

	var $body = $("body");

	hideAll();
	$('#shCheckAvailForm').show();

	if(typeof(Storage) == "undefined") {
		// Sorry! No Web Storage support..
		console.log("Sorry! No Web Storage support..");
	}

	$('.glyphicon-remove').click(function(){
		$('.alert').hide();
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
		//localStorage.clear();

		$body.addClass("loading");
		var lang = (typeof $.cookie('lang') === 'undefined') ? 'en' : $.cookie("lang");
		var data = {
				"action": "check_available",
				"propertyID" : propertyID,
				"arrival_date" : $("#booking-form-from").val(),
				"departure_date" : $("#booking-form-to").val(),
				"rooms" : $("#booking-form-rooms-num").val(),
				"adults" : $("#booking-form-adults").val(),
				"children" : $("#booking-form-children").val(),
				"lang": lang
			};

		$.ajax({
			url: serviceURL+"availability_check.php",
			dataType: "json",
			type: "post",
			data: JSON.stringify(data),
			success: function (resp) {
				if(resp.resp == 'true') {
					//console.log("resp::true:: " + JSON.stringify(resp));
					//$( "#successTxt" ).html(resp.responce);
					//$('.bg-success').show();
					$('#shCheckAvailForm').hide();
					$('#shAvailRooms').show();
					$.get("tpl/rooms.html", function(dataTpl){
						$.each(resp.data, function( key, value ) {
							$( "#availRooms" ).append(dataTpl);
							$('.roomImg').removeClass('roomImg').addClass('roomImg' + value.roomtypeID);
							$('.roomDesc').removeClass('roomDesc').addClass('roomDesc' + value.roomtypeID);
							$('.Row1').removeClass('Row1').addClass('Row1_' + value.roomtypeID);
							$('.Row2').removeClass('Row2').addClass('Row2_' + value.roomtypeID);
							$(".roomImg" + value.roomtypeID).html(serviceURL+'uploads/files/roomtypes/' + value.roomtypeID);
							$(".roomDesc" + value.roomtypeID).html("<h4>" + value.roomtype_name + "</h4><br/>" + value.roomtype_descr);
							$.each(value.prices, function( key1, value1 ) {
								var d = new Date(Object.getOwnPropertyNames(value1));
								$(".Row1_" + value.roomtypeID).append("<td class='tbDate'>"+d.toLocaleString(lang, {weekday: 'short'})+"<br/>"+d.getDate()+"/"+ (d.getMonth()+1)+"</td>");
	              $(".Row2_" + value.roomtypeID).append("<td class='tbPrice'>"+Object.values(value1)+value.currency+"</td>");
							});

							$(".Row1_" + value.roomtypeID).append("<td class='tbDate'>Total Cost<br/>"+value.total_cost+value.currency+"</td>");
							$(".Row2_" + value.roomtypeID).append("<td class='tbPrice'><button class='btn btn-primary'  class='bookRoom' name='booking-form-check'>Book</button></td>");
						});
					});

				} else {
					console.log("resp::false:: " + JSON.stringify(resp));
					$('#warningTxt').html(resp.responce);
					$('.bg-warning').show();
				}

				$body.removeClass("loading");
			},
			error: function(err) {
				console.log("err " + JSON.stringify(err));
				$('#dangerTxt').html(err);
				$('.bg-danger').show();
				$body.removeClass("loading");
			}
		});
		console.log(JSON.stringify(data));
		//ogin(data);

	});

});

/***
 * Functions
 **/



function hideAll() {
	$('.alert').hide();
	$('#shCheckAvailForm').hide();
	$('#shAvailRooms').hide();
	//$('#roomTypesTb').remove();
	//$('#goBackToProperty').hide();
	//$('#fileupload').hide();
}

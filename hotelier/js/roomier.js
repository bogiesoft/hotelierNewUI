$(document).ready(function() {


    hideAll();
		var $body = $("body");
    var dashDate = months[up2date.getMonth()] + " '" + up2date.getFullYear();
    var dashDateDay = weekDays[up2date.getDay()] + " " + up2date.getDate()  + " " + months[up2date.getMonth()] + " '" + up2date.getFullYear();
    $(".dashCheckoutDate").html(dashDateDay);
    $(".dashCheckinDate").html(dashDateDay);
    $(".dashMonthlyBookings").html(dashDate);
    $('#dashboard-content').show();


    $(".user-settings").on('click', function(){
      if($( ".sidebar-right" ).is( ":visible" ))
        $(".sidebar-right").hide();
      else
        $(".sidebar-right").show();
    });


    if (typeof(Storage) == "undefined") {
        // Sorry! No Web Storage support..
        window.location.replace("login.html");
    }

    if (localStorage.getItem("token") && localStorage.getItem("type").localeCompare("hotelier") == 0) {

        $('#sub-item-1').removeClass('');
        $('#sub-item-1').addClass('children collapse in');

        $('#sub-item-2').removeClass('');
        $('#sub-item-2').addClass('children collapse in');

        $('#sub-item-3').removeClass('');
        $('#sub-item-3').addClass('children collapse in');

        $('#sub-item-4').removeClass('');
        $('#sub-item-4').addClass('children collapse in');

        $('body#bload').removeAttr('id');
        $('#username').html(localStorage.getItem("name") + " " + localStorage.getItem("surname"));
        getUserData();

        loadDashboardData();

        $('[data-toggle="properties"]').attr('data-url', serviceURL + 'properties.php');
        //$('[data-toggle="properties"]').attr('data-dtype', 'hotelier');
        $('[data-toggle="properties"]').attr('data-action', 'show_properties');
        $('[data-toggle="properties"]').attr('data-add', 'addProperty');
        $('[data-toggle="properties"]').attr('data-refresh', 'refreshProperty');

    } else {
        localStorage.clear();
        window.location.replace("login.html");
    }

    $("#logout").click(function() {
        localStorage.clear();
        window.location.replace("login.html");
    });

    $("#shProperty").click(function() {
        hideAll();
        $('.page-header').html('Property');
        $('#propertiesTb').show();
    });

    $(document).on("click", '#goBackToProperty', function() {
        hideAll();
        $('.page-header').html('Property');
        $('#propertiesTb').show();
    });

    $("#shDashboard").click(function() {
        hideAll();
        $('.page-header').html('Dashboard');
        $('#dashboard-content').show();
    });

    $("#shRoomTypes").click(function() {
        hideAll();
        $('.page-header').html('Room Types');
        $.get("tpl/roomTypeAll.html?"+up2date.getTime(), function(data) {
            $("#mainContent").append(data);
            $('#goBackToProperty').hide();
            $('[data-toggle="roomTypes"]').attr('data-url', serviceURL + 'room_types.php');
            $('[data-toggle="roomTypes"]').attr('data-action', 'show_roomtypes_all');
            $('[data-toggle="roomTypes"]').attr('data-add', 'addRoomtype');
            $('[data-toggle="roomTypes"]').attr('data-refresh', 'refreshRoomtype');
            $('[data-toggle="roomTypes"]').bootstrapTable();
        });
    });

		$("#shRoomsAvailability").click(function() {
				$body.addClass("loading");
				var d = new Date();

        hideAll();
        $('.page-header').html('Rooms Availability');
				$('.rooms-availability-month option:eq('+d.getMonth()+')').prop('selected', true);

				$.get("tpl/roomsAvailability.html?"+up2date.getTime(), function(data) {


					var yearNow = d.getFullYear();
					var yearEnd = yearNow + 10;
					$('.rooms-availability-year').empty();
					while(yearNow <= yearEnd){
						$('.rooms-availability-year').append("<option value='"+yearNow+"'>"+yearNow+"</option>");
						yearNow++;
					}

					var data = {
	            "action": "show_properties",
	            'email': localStorage.getItem("email"),
	            'token': localStorage.getItem("token")
	        };


	        $.ajax({
	            url: serviceURL + "properties.php",
	            dataType: "json",
	            type: "post",
	            data: JSON.stringify(data),
	            success: function(resp) {
	                if (resp.resp == 'true') {
	                    //console.log("resp::true:: " + JSON.stringify(resp));
											$('#propertiesList').empty();
											$('#propertiesList').append("<option value='all'>All properties</option>");
											$.each(resp.data, function(key,value){
												$('#propertiesList').append("<option value='"+value.propertyID+"'>"+value.property_name+"</option>");
											});
                      getRoomsAvailability('all',$('.rooms-availability-month').val(),$('.rooms-availability-year').val());
	                } else {
	                    //console.log("resp::false:: " + JSON.stringify(resp));
	                    $("#warningTxt").html(resp.responce);
	                    $('.bg-warning').show();
	                }
	            },
	            error: function(err) {
	                //console.log("err " + JSON.stringify(err));
	                $("#dangerTxt").html(err);
	                $('.bg-danger').show();
	            }
	        });
					$('#roomsCalendar').html(data);
					$('#roomsAvailability').show();
					$body.removeClass("loading");
				});

    });

    $(document).on("click", ".modalClose", function(e) {
      $("#modal-booking").hide();
    });

    $("#shCheckinCheckout").click(function() {
      $body.addClass("loading");
      hideAll();
      $('.page-header').html('Checkin - Checkout');

      $("#rooms-checkinout-date").datepicker({
          format: 'yyyy-mm-dd',
          startDate: '0d',
          autoclose: true
      });

      var data = {
          "action": "show_properties",
          'email': localStorage.getItem("email"),
          'token': localStorage.getItem("token")
      };


      $.ajax({
          url: serviceURL + "properties.php",
          dataType: "json",
          type: "post",
          data: JSON.stringify(data),
          success: function(resp) {
              if (resp.resp == 'true') {
                  console.log("resp::true:: " + JSON.stringify(resp));
                  $('#propertiesListCh').empty();
                  $('#propertiesListCh').append("<option>Select property</option>");
                  $.each(resp.data, function(key,value){
                    $('#propertiesListCh').append("<option value='"+value.propertyID+"'>"+value.property_name+"</option>");
                  });
              } else {
                  console.log("resp::false:: " + JSON.stringify(resp));
                  $("#warningTxt").html(resp.responce);
                  $('.bg-warning').show();
              }
          },
          error: function(err) {
              console.log("err " + JSON.stringify(err));
              $("#dangerTxt").html(err);
              $('.bg-danger').show();
          }
      });

      $('#rooms-checkinout-date').val(up2date.getFullYear() + "-" + ((up2date.getMonth()+1)>9 ? (up2date.getMonth()+1) : ("0"+(up2date.getMonth()+1))) + "-" + up2date.getDate());
      $('#checkinCheckout').show();
      $body.removeClass("loading");
    });

    $('#propertiesListCh').on("change", function(){
				getCheckinOut($(this).val(),$('#rooms-checkinout-date').val());
		});

    $('#rooms-checkinout-date').on("change", function(){
				getCheckinOut($('#propertiesListCh').val(),$(this).val());
		});

    $("#shBookings").click(function(){
      hideAll();
      $('.page-header').html('Bookings');
      var data = {
          "action": "show_properties",
          'email': localStorage.getItem("email"),
          'token': localStorage.getItem("token")
      };


      $.ajax({
          url: serviceURL + "properties.php",
          dataType: "json",
          type: "post",
          data: JSON.stringify(data),
          success: function(resp) {
              if (resp.resp == 'true') {
                  //console.log("resp::true:: " + JSON.stringify(resp));
                  $('#propertiesListBook').empty();
                  $('#propertiesListBook').append("<option value='all'>All properties</option>");
                  $.each(resp.data, function(key,value){
                    $('#propertiesListBook').append("<option value='"+value.propertyID+"'>"+value.property_name+"</option>");
                  });
                  getBookings('all');
                  $("#bookings").show();
              } else {
                  //console.log("resp::false:: " + JSON.stringify(resp));
                  $("#warningTxt").html(resp.responce);
                  $('.bg-warning').show();
              }
          },
          error: function(err) {
              console.log("err " + JSON.stringify(err));
              $("#dangerTxt").html(err);
              $('.bg-danger').show();
          }
      });

    });

    $('#propertiesListBook').on("change", function(){
      getBookings($(this).val());
    });

    $(".save-profile").on("click",function(){
      if (ValidateForm('.profile-form') === 'true') {
          console.log("errors found!!");
      } else {
        var data={
          "action": "update_user",
          'email': localStorage.getItem("email"),
          'token': localStorage.getItem("token"),
          'password': 'x'
        };

        $('.profile-form').each(function(key, value){
          data[$(this).attr("name").replace("profile-","")] = $(this).val();
        });
        //console.log(JSON.stringify(data));
        updateUserData(data);

      }

    });


    $('.profile-change-pass').on("click", function(){
      if (ValidateForm('.chpass') === 'true') {
          console.log("errors found!!");
      } else {
        var data={
          "action": "change_password",
          'email': localStorage.getItem("email"),
          'token': localStorage.getItem("token"),
          'old_password': $('input[name="chpass-old-pass"]').val(),
          'new_password': $('input[name="chpass-new-pass"]').val()
        };

        //console.log(JSON.stringify(data));
        $.ajax({
            url: serviceURL + "login.php",
            dataType: "json",
            type: "post",
            data: JSON.stringify(data),
            success: function(resp) {
                if (resp.resp == 'true') {
                    //console.log("resp::true:: " + JSON.stringify(resp));
                    $("#successTxt").html(resp.responce);
                    $('.bg-success').show();
                    if(localStorage.getItem("type").localeCompare("admin") == 0 ) {
                      localStorage.clear();
          						window.location.replace(roomierURL+"?"+up2date.getTime());
          					} else {
                      localStorage.clear();
          						window.location.replace(hotelierURL+"?"+up2date.getTime());
                    }
                } else {
                    //console.log("resp::false:: " + JSON.stringify(resp));
                    $("#warningTxt").html(resp.responce);
                    $('.bg-warning').show();
                }
            },
            error: function(err) {
                console.log("err " + JSON.stringify(err));
                $("#dangerTxt").html(err);
                $('.bg-danger').show();
            }
        });

      }

    });

    $('#propertiesList').on("change", function(){
				getRoomsAvailability($(this).val(),$('.rooms-availability-month').val(),$('.rooms-availability-year').val());
		});

		$('.rooms-availability-month').on("change", function(){
				getRoomsAvailability($('#propertiesList').val(),$(this).val(),$('.rooms-availability-year').val());
		});

		$('.rooms-availability-year').on("change", function(){
				getRoomsAvailability($('#propertiesList').val(),$('.rooms-availability-month').val(),$(this).val());
		});

    $(document).on("click", ".getBookEvent", function(e) {
      getBookEvent($(this).data('date'),$(this).data('status'),$(this).data('bookingid'));
    });

    $(document).on("click", '.hotelServices input[name="hotelService"]', function(e) {
      var roomsSrvTotal = 0;
      if ($('.roomServices input:checked[name="roomServices"]').length !== 0) {
          $('.roomServices input:checked[name="roomServices"]').each(function() {
              roomsSrvTotal = roomsSrvTotal + parseInt($(this).val());
          });

      }

      var propertySrvTotal = 0;
      if ($('.hotelServices input:checked[name="hotelService"]').length !== 0) {
          $('.hotelServices input:checked[name="hotelService"]').each(function() {
              console.log("hotelService:: " + $(this).val());
              propertySrvTotal = propertySrvTotal + parseInt($(this).val());
          });

      }

      $('input[name="booking-form-hotelServicesCost"]').val(propertySrvTotal);
      $('input[name="booking-form-roomServicesCost"]').val(roomsSrvTotal);
      $('input[name="booking-form-totalCost"]').val(parseInt($('input[name="booking-form-roomsCost"]').val())+roomsSrvTotal+propertySrvTotal);
    });

    $(document).on("click", '.roomServices input[name="roomServices"]', function(e) {
        var roomsSrvTotal = 0;
        if ($('.roomServices input:checked[name="roomServices"]').length !== 0) {
            $('.roomServices input:checked[name="roomServices"]').each(function() {
                roomsSrvTotal = roomsSrvTotal + parseInt($(this).val());
            });

        }

        var propertySrvTotal = 0;
        if ($('.hotelServices input:checked[name="hotelService"]').length !== 0) {
            $('.hotelServices input:checked[name="hotelService"]').each(function() {
                console.log("hotelService:: " + $(this).val());
                propertySrvTotal = propertySrvTotal + parseInt($(this).val());
            });

        }

        $('input[name="booking-form-hotelServicesCost"]').val(propertySrvTotal);
        $('input[name="booking-form-roomServicesCost"]').val(roomsSrvTotal);
        $('input[name="booking-form-totalCost"]').val(parseInt($('input[name="booking-form-roomsCost"]').val())+roomsSrvTotal+propertySrvTotal);
    });

    $('.glyphicon-remove').click(function() {
        $('.alert').hide();
    });


    var sprintf = function(str) {
        var args = arguments,
            flag = true,
            i = 1;

        str = str.replace(/%s/g, function() {
            var arg = args[i++];

            if (typeof arg === 'undefined') {
                flag = false;
                return '';
            }
            return arg;
        });
        if (flag) {
            return str;
        }
        return '';
    };

    /***
     * Property Images upload
     **


    /***
     * Add/Edit Properties and Services (hotelier)
     **/

    // Edit Name
    var ch = false;
    var prevEle = '';
    $(document).on("click", "._property_name_class_editable", function(e) {

        var id = $(this).attr('id');
        e.stopPropagation(); //<-------stop the bubbling of the event here
        var value = $('#' + id).html();

        updateValName('#' + id, value);

    });


    //Edit Property status

    $(document).on("click", "._status_class_editable", function(e) {

        var id = $(this).attr('id');
        e.stopPropagation(); //<-------stop the bubbling of the event here
        var value = $('#' + id).html();

        updateValStatus('#' + id, value);

    });



    $(document).on("click", '#addProperty', function(e) {
        $.get("tpl/property.html?"+up2date.getTime(), function(data) {
            $("#addProp").html(data);
        });
    });

    $(document).on("click", '#roomTypesCancel', function(e) {
        $("#addProp").html('');
    });

    $(document).on("click", '#addRoomtype', function(e) {
        $.get("tpl/roomType.html?"+up2date.getTime(), function(data) {
            $("#addRoomType").html(data);
            $('[name="propertyID"]').val($('[data-toggle="roomTypes"]').data('property'));
        });
    });

    $(document).on("click", '#roomtypeCancel', function(e) {
        $("#addRoomType").html('');
    });

    $(document).on("click", '#roomAvailabilityCancel', function(e) {
        $("#addRoomType").html('');
    });

    $(document).on("click", '#editPropertyCancel', function(e) {
        //$('#property')[0].reset();
        $("#addProp").html('');
    });

    $(document).on("click", '#propertyCancel', function(e) {
        //$('#property')[0].reset();
        $("#addProp").html('');
    });



    $(document).on("submit", '#property', function(event) {
        event.preventDefault();
        var data = {
            "action": "add_property",
            'email': localStorage.getItem("email"),
            'token': localStorage.getItem("token"),
            'property_name': $('[name="property_name"]').val(),
            'eponymia': $('[name="eponymia"]').val(),
            'contact': $('[name="contact"]').val(),
            'phone': $('[name="phone"]').val(),
            'fax': $('[name="fax"]').val(),
            'emailprop': $('[name="emailprop"]').val(),
            'website': $('[name="website"]').val(),
            'address': $('[name="address"]').val(),
            'town': $('[name="town"]').val(),
            'postcode': $('[name="postcode"]').val(),
            'country': $('[name="country"]').val(),
            'geotag': $('[name="geotag"]').val(),
            'logo': 'notAvailable' //$('[name="logo"]').val()  --> TODO
        };


        $.ajax({
            url: serviceURL + "properties.php",
            dataType: "json",
            type: "post",
            data: JSON.stringify(data),
            success: function(resp) {
                if (resp.resp == 'true') {
                    //console.log("resp::true:: " + JSON.stringify(resp));
                    $('button[name="refresh"]').trigger("click");
                    $('#property')[0].reset();
                    $("#addProp").html('');
                    $("#successTxt").html(resp.responce);
                    $('.bg-success').show();
                } else {
                    //console.log("resp::false:: " + JSON.stringify(resp));
                    $("#warningTxt").html(resp.responce);
                    $('.bg-warning').show();
                }
            },
            error: function(err) {
                console.log("err " + JSON.stringify(err));
                $("#dangerTxt").html(err);
                $('.bg-danger').show();
            }
        });
        /* $.ajax({
        	url: serviceURL+"properties.php",
        	type: "POST",
        	data:  new FormData(this),
        	success: function(data) {
        		if(data=='invalid file') {
        			// invalid file format.
        			$("#err").html("Invalid File !").fadeIn();
        		} else {
        			// view uploaded file.
        			$("#preview").html(data).fadeIn();
        			$("#form")[0].reset();
        		}
        	  },
        	error: function(e) {
        	$("#err").html(e).fadeIn();
        	  }
        }); */
    });

    $(document).on("click", '#closeAvailability', function() {
        $("#addRoomType").html('');
    });


    $(document).on("submit", '#roomtype', function(event) {
        event.preventDefault();

        var data = {
            "action": "add_roomtype",
            'email': localStorage.getItem("email"),
            'token': localStorage.getItem("token"),
            'propertyID': $('[name="propertyID"]').val(),
            'roomtype_name': $('[name="roomtype_name"]').val(),
            'roomtype_descr': $('[name="roomtype_descr"]').val(),
            'quantity': $('[name="quantity"]').val(),
            'price': $('[name="price"]').val(),
            'currency': $('[name="currency"]').val(),
            'capacity_min': $('[name="capacity_min"]').val(),
            'capacity_max': $('[name="capacity_max"]').val(),
            'child_min': $('[name="child_min"]').val(),
            'child_max': $('[name="child_max"]').val(),
            'minimum_stay': $('[name="minimum_stay"]').val()
        };

        $.ajax({
            url: serviceURL + "room_types.php",
            dataType: "json",
            type: "post",
            data: JSON.stringify(data),
            success: function(resp) {
                if (resp.resp == 'true') {
                    //console.log("resp::true:: " + JSON.stringify(resp));
                    $('button[name="refresh"]').trigger("click");
                    $('#roomtype')[0].reset();
                    $("#addRoomType").html('');
                    $("#successTxt").html(resp.responce);
                    $('.bg-success').show();
                } else {
                    //console.log("resp::false:: " + JSON.stringify(resp));
                    $("#warningTxt").html(resp.responce);
                    $('.bg-warning').show();
                }
            },
            error: function(err) {
                console.log("err " + JSON.stringify(err));
                $("#dangerTxt").html(err);
                $('.bg-danger').show();
            }
        });
    });

    $(document).on("submit", '#editRoomtype', function(event) {
        event.preventDefault();
        var values1 = $("input[name='service_name[]']")
            .map(function() {
                return $(this).val();
            }).get();
        var values2 = $("input[name='service_descr[]']")
            .map(function() {
                return $(this).val();
            }).get();
        var values3 = $("input[name='service_price[]']")
            .map(function() {
                return $(this).val();
            }).get();
        var values4 = $("input[name='daily[]']").map(function() {
            if ($(this).is(':checked'))
                return $(this).val();
            return 0;
        }).get();

        var values5 = $("input[name='sp_price[]']")
            .map(function() {
                return $(this).val();
            }).get();
        var values6 = $("input[name='sp_startDate[]']")
            .map(function() {
                return $(this).val();
            }).get();
        var values7 = $("input[name='sp_endDate[]']")
            .map(function() {
                return $(this).val();
            }).get();


        var services = [];
        $.each(values1, function(key, value) {
            services.push({
                "service_name": value,
                "service_descr": values2[key],
                "price": values3[key],
                "currency": $('[name="currency"]').val(),
                "daily": values4[key],
                "type": "room",
                "propertyID": "",
                "roomtypeID": $('[name="roomtypeID"]').val()
            });
        });

        var sprice = [];
        $.each(values5, function(key, value) {
            sprice.push({
                "price": value,
                "startDate": values6[key],
                "endDate": values7[key],
                "propertyID": $('[name="propertyID"]').val(),
                "roomtypeID": $('[name="roomtypeID"]').val()
            });
        });

        var data = {
            "action": "update_roomtype",
            'email': localStorage.getItem("email"),
            'token': localStorage.getItem("token"),
            'roomtypeID': $('[name="roomtypeID"]').val(),
            "propertyID": $('[name="propertyID"]').val(),
            'roomtype_name': $('[name="roomtype_name"]').val(),
            'roomtype_descr': $('[name="roomtype_descr"]').val(),
            'quantity': $('[name="quantity"]').val(),
            'price': $('[name="price"]').val(),
            'currency': $('[name="currency"]').val(),
            'capacity_min': $('[name="capacity_min"]').val(),
            'capacity_max': $('[name="capacity_max"]').val(),
            'child_min': $('[name="child_min"]').val(),
            'child_max': $('[name="child_max"]').val(),
            'minimum_stay': $('[name="minimum_stay"]').val(),
            'services': services,
            'specialprices': sprice
        };

        $.ajax({
            url: serviceURL + "room_types.php",
            dataType: "json",
            type: "post",
            data: JSON.stringify(data),
            success: function(resp) {
                if (resp.resp == 'true') {
                    //console.log("resp::true:: " + JSON.stringify(resp));
                    $('button[name="refresh"]').trigger("click");
                    $('#editRoomtype')[0].reset();
                    $("#addRoomType").html('');
                    $("#successTxt").html(resp.responce);
                    $('.bg-success').show();
                } else {
                    //console.log("resp::false:: " + JSON.stringify(resp));
                    $("#warningTxt").html(resp.responce);
                    $('.bg-warning').show();
                }
            },
            error: function(err) {
                console.log("err " + JSON.stringify(err));
                $("#dangerTxt").html(err);
                $('.bg-danger').show();
            }
        });
    });

    $(document).on("submit", '#editProperty', function(event) {
        event.preventDefault();
        var values1 = $("input[name='service_name[]']")
            .map(function() {
                return $(this).val();
            }).get();
        var values2 = $("input[name='service_descr[]']")
            .map(function() {
                return $(this).val();
            }).get();
        var values3 = $("input[name='service_price[]']")
            .map(function() {
                return $(this).val();
            }).get();
        var values4 = $("input[name='daily[]']").map(function() {
            if ($(this).is(':checked'))
                return $(this).val();
            return 0;
        }).get();


        var services = [];
        $.each(values1, function(key, value) {
            services.push({
                "service_name": value,
                "service_descr": values2[key],
                "price": values3[key],
                "currency": $('[name="currency"]').val(),
                "daily": values4[key],
                "type": "property",
                "propertyID": $('[name="propertyID"]').val(),
                "roomtypeID": ""
            });
        });


        var data = {
            "action": "update_property",
            'email': localStorage.getItem("email"),
            'token': localStorage.getItem("token"),
            'propertyID': $('[name="propertyID"]').val(),
            'property_name': $('[name="property_name"]').val(),
            'eponymia': $('[name="eponymia"]').val(),
            'contact': $('[name="contact"]').val(),
            'phone': $('[name="phone"]').val(),
            'fax': $('[name="fax"]').val(),
            'emailprop': $('[name="emailprop"]').val(),
            'website': $('[name="website"]').val(),
            'address': $('[name="address"]').val(),
            'town': $('[name="town"]').val(),
            'postcode': $('[name="postcode"]').val(),
            'country': $('[name="country"]').val(),
            'geotag': $('[name="geotag"]').val(),
            'logo': 'notAvailable', //$('[name="logo"]').val()
            'services': services
        };

        $.ajax({
            url: serviceURL + "properties.php",
            dataType: "json",
            type: "post",
            data: JSON.stringify(data),
            success: function(resp) {
                if (resp.resp == 'true') {
                    //console.log("resp::true:: " + JSON.stringify(resp));



                    $('button[name="refresh"]').trigger("click");
                    $('#editProperty')[0].reset();
                    $("#addProp").html('');
                    $("#successTxt").html(resp.responce);
                    $('.bg-success').show();
                } else {
                    $("#warningTxt").html(resp.responce);
                    $('.bg-warning').show();
                    //console.log("resp::false:: " + JSON.stringify(resp));
                }
            },
            error: function(err) {
                console.log("err " + JSON.stringify(err));
                $("#dangerTxt").html(err);
                $('.bg-danger').show();
            }
        });
    });

    /***
     * Edit Rooms table
     **/

    // Edit Availability
    var ch = false;
    var prevEle = '';
    $(document).on("click", "._onDuty_class_editable", function(e) {

        var id = $(this).attr('id');
        e.stopPropagation(); //<-------stop the bubbling of the event here
        var value = $('#' + id).html();
        //console.log(" id:: " + id + ", value:: " + value);
        updateRoomStatus('#' + id, value);

    });

    $(document).on("click", "._room_name_class_editable", function(e) {

        var id = $(this).attr('id');
        e.stopPropagation(); //<-------stop the bubbling of the event here
        var value = $('#' + id).html();
        //console.log(" id:: " + id + ", value:: " + value);
        updateRoomName('#' + id, value);

    });

    $(document).on("click", "._startDate_class_editable", function(e) {

        var id = $(this).attr('id');
        e.stopPropagation(); //<-------stop the bubbling of the event here
        var value = $('#' + id).html();

        if ($('#' + id).parent().find('._onDuty_class_editable').text().localeCompare('Off') == 0)
            updateRoomStartDateAvailability('#' + id, value);
        else
            alert(sprintf('Room  "%s" must be Off', $('#' + id).parent().find('._room_identify_class_editable').text()));

    });

    $(document).on("click", "._endDate_class_editable", function(e) {

        var id = $(this).attr('id');
        e.stopPropagation(); //<-------stop the bubbling of the event here
        var value = $('#' + id).html();

        if ($('#' + id).parent().find('._onDuty_class_editable').text().localeCompare('Off') == 0) {
            //console.log("start::: " + $('#' + id).parent().find('._startDate_class_editable').text());

            if ($('#' + id).parent().find('._startDate_class_editable').text() != '')
                updateRoomEndDateAvailability('#' + id, value);
            else
                alert("startDate is not set!");
        } else
            alert(sprintf('Room  "%s" must be Off', $('#' + id).parent().find('._room_identify_class_editable').text()));

    });

    /***
     * Edit Room Services
     **/

    var max_fields = 10; //maximum input boxes allowed
    var wrapper = ".input_fields_wrap"; //Fields wrapper
    var add_button = $("#add_field_button"); //Add button ID
    var emptyService = '<input type="text" name="service_name[]">';
    var emptyDescr = '<input type="text" name="service_descr[]">';
    var emptyPrice = '<input type="text" name="service_price[]">';
    var emptyDaily = '<input type="checkbox" name="daily[]" value="1"/>';
    var valService = '';
    var valDescr = '';
    var valPrice = '';
    var valDaily = '';
    var x = 1; //initlal text box count
    $(document).on("click", ".add_field_button", function() {
        //console.log("max_fields:: " + x);
        if (x < max_fields) { //max input box allowed
            $('#roomTypesServicesTbl').find('tbody:last').append(sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href="#" class="remove_field">Remove</a></td></tr>',
                emptyService,
                emptyDescr,
                emptyPrice,
                emptyDaily
            ));
            x++; //text box increment
        } else
            alert("Maximum number of services is 10 per room type!");
    });


    $(document).on("click", ".addPropertyService", function() {
        //console.log("max_fields:: " + x);
        if (x < max_fields) { //max input box allowed
            $('#propertyServicesTbl').find('tbody:last').append(sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href="#" class="remove_field">Remove</a></td></tr>',
                emptyService,
                emptyDescr,
                emptyPrice,
                emptyDaily
            ));
            x++; //text box increment
        } else
            alert("Maximum number of services is 10 per Property!");
    });


    $(document).on("click", ".remove_field", function(e) { //user click on remove text
        e.preventDefault();
        $(this).closest('tr').remove();
        x--;
    });

    /***
     * Edit Room Special Prices
     **/

    var max_fields2 = 10; //maximum input boxes allowed
    var emptySPrice = '<input type="text" name="sp_price[]">';
    var emptystartDate = '<input type="text" name="sp_startDate[]" class="sp_startDate">';
    var emptyendDate = '<input type="text" name="sp_endDate[]" class="sp_endDate">';
    var x2 = 1; //initlal text box count
    $(document).on("click", ".add_field_price_button", function() {

        if (x2 < max_fields) { //max input box allowed
            $('#roomTypesSpecialPricesTbl').find('tbody:last').append(sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td><a href="#" class="remove_field_sprice">Remove</a></td></tr>',
                emptySPrice,
                emptystartDate,
                emptyendDate
            ));
            x2++; //text box increment



            /*
            $( ".sp_endDate" ).datepicker({
            	format: 'yyyy-mm-dd',
            	startDate: $('.sp_startDate').val(),
            	autoclose: true
            	//numberOfMonths: 2
            });*/

        } else
            alert("Maximum number of special prices is 10 per room type!");
    });

    $(document).on("focus", ".sp_startDate", function(e) {

        $(".sp_startDate").datepicker({
            format: 'yyyy-mm-dd',
            startDate: '+1d',
            autoclose: true
                //numberOfMonths: 2
        }).on('change', function() {
            $(".sp_endDate").datepicker({
                format: 'yyyy-mm-dd',
                startDate: $('.sp_startDate').val(),
                autoclose: true
                    //numberOfMonths: 2
            });
        });

    });

    $(document).on("focus", ".sp_endDate", function() {
        if ($(document).find('.sp_startDate').val().length !== 0) {
            $(".sp_endDate").datepicker({
                format: 'yyyy-mm-dd',
                startDate: $('.sp_startDate').val(),
                autoclose: true
                    //numberOfMonths: 2
            });
        } else
            alert("Special Price start date is not set!");
    });


    $(document).on("click", ".remove_field_sprice", function(e) { //user click on remove text
        e.preventDefault();
        $(this).closest('tr').remove();
        x--;
    });

    function loadDashboardData(){

      var data = {
          "action": "show_dashboard",
          'email': localStorage.getItem("email"),
          'token': localStorage.getItem("token"),
          'check_date': up2date.getFullYear() + "-" + (up2date.getMonth()+1) + "-" + up2date.getDate()
      };


      $.ajax({
          url: serviceURL + "dashboard.php",
          dataType: "json",
          type: "post",
          data: JSON.stringify(data),
          success: function(resp) {
              if (resp.resp == 'true') {
                  //console.log("resp::true:: " + JSON.stringify(resp));
                  // $('.dashMonthlyBookingsNum').val(resp.data.total_bookings);
                  // $('.dashPendingBookingsNum').val(resp.data.pending_bookings);
                  // $('.dashCheckinNum').val(resp.data.checkin);
                  // $('.dashCheckoutNUm').val(resp.data.checkout);

                  $(".dashMonthlyBookingsNum").html(resp.data.total_bookings);
                  $(".dashPendingBookingsNum").html(resp.data.pending_bookings);
                  $(".dashCheckinNum").html(resp.data.checkin);
                  $(".dashCheckinNum").html(resp.data.checkout);




              } else {
                  //console.log("resp::false:: " + JSON.stringify(resp));
                  $("#warningTxt").html(resp.responce);
                  $('.bg-warning').show();
              }
          },
          error: function(err) {
              console.log("err " + JSON.stringify(err));
              $("#dangerTxt").html(err);
              $('.bg-danger').show();
          }
      });
    }

    //Get user data
    function getUserData() {
      var data = {
          "action": "show_user",
          'email': localStorage.getItem("email"),
          'token': localStorage.getItem("token")
      };


      $.ajax({
          url: serviceURL + "users.php",
          dataType: "json",
          type: "post",
          data: JSON.stringify(data),
          success: function(resp) {
              if (resp.resp == 'true') {
                  //console.log("resp::true:: " + JSON.stringify(resp));
                  $('input[name="profile-name"]').val(localStorage.getItem("name"));
                  $('input[name="profile-surname"]').val(localStorage.getItem("surname"));
                  $('input[name="profile-email"]').val(localStorage.getItem("email"));
                  $('input[name="profile-phone"]').val(resp.data.phone);
                  $('input[name="profile-mobile"]').val(resp.data.mobile);
                  $('input[name="profile-address"]').val(resp.data.address);
                  $('input[name="profile-town"]').val(resp.data.town);
                  $('input[name="profile-postcode"]').val(resp.data.postcode);
                  $('select[name="profile-country"]').val(resp.data.country);
                  $('input[name="profile-afm"]').val(resp.data.afm);
                  $('input[name="profile-doy"]').val(resp.data.doy);
              } else {
                  //console.log("resp::false:: " + JSON.stringify(resp));
                  $("#warningTxt").html(resp.responce);
                  $('.bg-warning').show();
              }
          },
          error: function(err) {
              console.log("err " + JSON.stringify(err));
              $("#dangerTxt").html(err);
              $('.bg-danger').show();
          }
      });
    }

    function updateUserData(data) {

      $.ajax({
          url: serviceURL + "users.php",
          dataType: "json",
          type: "post",
          data: JSON.stringify(data),
          success: function(resp) {
              if (resp.resp == 'true') {
                  //console.log("resp::true:: " + JSON.stringify(resp));
                  $("#successTxt").html(resp.responce);
                  $('.bg-success').show();
                  localStorage.setItem("name", data.name);
        					localStorage.setItem("surname", data.surname);
                  $('#username').html(localStorage.getItem("name") + " " + localStorage.getItem("surname"));
              } else {
                  //console.log("resp::false:: " + JSON.stringify(resp));
                  $("#warningTxt").html(resp.responce);
                  $('.bg-warning').show();
              }
          },
          error: function(err) {
              console.log("err " + JSON.stringify(err));
              $("#dangerTxt").html(err);
              $('.bg-danger').show();
          }
      });
    }

		//Num of days in a month
		function daysInMonth(month,year) {
	    	return new Date(year, month, 0).getDate();
		}

    //get or create booking events
    function getBookEvent(bdate, status, bookingID) {
      var $body = $("body");
      $body.addClass('loading');

      if(status == 0){
        $(".modal-header-txt").html("Reservation Code/Pin: - / -");
        $(".complete-booking").each(function(){
          $(this).val('');
        });
        $("#modal-booking").show();
        $body.removeClass('loading');
        return false;
      } else if (status == 1) {
        changeBookData(bookingID,$body);
      } else if (status == 2) {
        changeBookData(bookingID,$body);
      } else {
        alert("the room is out of service at " + bdate);
      }
    }


    //Get rooms Availability
		function getRoomsAvailability(propertyID,month,year) {
					var $body = $("body");
					$body.addClass("loading");
          $("#roomsCalendar").html();
					var data = {
							"action": "show_rooms_availability",
							'email': localStorage.getItem("email"),
							'token': localStorage.getItem("token"),
							"propertyID": propertyID,
     					"month": month,
    					"year": year
					};


					$.ajax({
							url: serviceURL + "availability_show.php",
							dataType: "json",
							type: "post",
							data: JSON.stringify(data),
							success: function(resp) {
									if (resp.resp == 'true') {
											//console.log("resp::true:: " + JSON.stringify(resp));

											$.get("tpl/roomsAvailability.html?"+up2date.getTime(), function(data) {
							            $("#roomsCalendar").html(data);
							            var numOfmonthDays = daysInMonth(month,year);
                          var weekDays = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                          var cnt = numOfmonthDays;
													if(numOfmonthDays < 31){
														while(cnt <= 31){
															cnt++;
															$("#d"+cnt).remove();
															$(".room_d"+cnt).remove();
														}
													}

                          var ddate = new Date();
                          cnt = 1;
                          while(cnt <= numOfmonthDays){
                            ddate.setFullYear(parseInt(year),parseInt(month)-1, cnt);
                            $("#d"+cnt).html(weekDays[ddate.getDay()] + "<br/>" + ddate.getDate() + "/" + month);
                            cnt++;
                          }

													$.each(resp.RoomsData,function(key,value){
															$(".dummy-row").clone().appendTo("#roomsAvailabilityCalendar").removeClass('dummy-row').addClass('room-row_'+key);
															$('.room-row_'+key).find('.room_name').removeClass('room_name').addClass('room_name_'+key);
															$('.room_name_'+key).html(value.roomIdentify);
															var dn = 1;
															$.each(value.daydata, function(keydd,valuedd){
                                var bookingEvent = "<span class='getBookEvent' data-date='%s' data-status='%s' data-bookingid='%s'>%s</span>";
																$('.room-row_'+key).find('.room_d'+dn).removeClass('.room_d'+dn).addClass(value.roomIdentify + '_room_d'+dn);

                                if(valuedd.bookingID === "")
                                    valuedd.bookingID = 0;

																$('.' + value.roomIdentify + '_room_d'+dn).html(sprintf(bookingEvent,valuedd.date, valuedd.status, valuedd.bookingID, valuedd.price+valuedd.currency));

                                if(valuedd.status == 0){
                                  $('.' + value.roomIdentify + '_room_d'+dn).addClass('notBooked');
                                }

                                if(valuedd.status == 1){
                                  $('.' + value.roomIdentify + '_room_d'+dn).addClass('pendingBook');
                                }

                                if(valuedd.status == 2){
                                  $('.' + value.roomIdentify + '_room_d'+dn).addClass('booked');
                                }

                                if(valuedd.status == 3){
                                  $('.' + value.roomIdentify + '_room_d'+dn).addClass('outOfservice');
                                }
																dn++;
															});
													});
                          $(".dummy-row").remove();
							        });
									} else {
											//console.log("resp::false:: " + JSON.stringify(resp));
											$("#warningTxt").html(resp.responce);
											$('.bg-warning').show();
									}
							},
							error: function(err) {
									console.log("err " + JSON.stringify(err));
									$("#dangerTxt").html(err);
									$('.bg-danger').show();
							}
					});
					$('#roomsCalendar').html(data);
					$('#roomsAvailability').show();
					$body.removeClass("loading");

		}
    // Helper function that formats the file sizes
    function formatFileSize(bytes) {
        if (typeof bytes !== 'number') {
            return '';
        }

        if (bytes >= 1000000000) {
            return (bytes / 1000000000).toFixed(2) + ' GB';
        }

        if (bytes >= 1000000) {
            return (bytes / 1000000).toFixed(2) + ' MB';
        }

        return (bytes / 1000).toFixed(2) + ' KB';
    }

    function updateRoomStatus(currentEle, value) {
        var onDuty = '';
        var offDuty = '';
        //console.log("Current Element is"+currentEle);
        //console.log("checkbox:: "+$(currentEle).parent().find('input:checkbox').val());

        //Corrects corrupted flag after unexpected action
        if (ch && $(".thValSt").length == 0)
            ch = false;

        if (!ch) {
            //console.log("ch:: " + ch);
            $(currentEle).html('<select class="thValSt"></select>');
            if (value.localeCompare('onDuty') == 0)
                onDuty = 'selected';
            else
                offDuty = 'selected';

            $(".thValSt").append(sprintf('<option value="onDuty" %s>onDuty</option>', onDuty));
            $(".thValSt").append(sprintf('<option value="Off" %s>Off</option>', offDuty));
            ch = true;
        }
        $(".thValSt").focus();


        $(".thValSt").keyup(function(event) {
            if (event.keyCode == 13) {
                if (undefined != $(".thValSt").val()) {
                    var $body = $("body");
                    $body.addClass("loading");
                    var updata = {
                        "roomID": $(currentEle).parent().find('input:checkbox').val(),
                        "onDuty": $(".thValSt").val()
                    };
                    $(currentEle).html($(".thValSt").val());

                    ch = false;
                    //console.log(" 2...");
                    updateRoomStatusBackEnd(updata, $body);
                }
            }
        });

        $(".thValSt").focusout(function() { // you can use $('html')
            if (undefined != $(".thValSt").val()) {
                var $body = $("body");
                $body.addClass("loading");
                var updata = {
                    "roomID": $(currentEle).parent().find('input:checkbox').val(),
                    "onDuty": $(".thValSt").val()
                };
                $(currentEle).html($(".thValSt").val());
                ch = false;
                //console.log("1 ...");
                updateRoomStatusBackEnd(updata, $body);
            }
        });

    }


    function updateRoomName(currentEle, value) {
        var onDuty = '';

        //console.log("Current Element is" + currentEle);
        //console.log("checkbox:: " + $(currentEle).parent().find('input:checkbox').val());

        //Corrects corrupted flag after unexpected action
        if (ch && $(".thVal").length == 0)
            ch = false;

        if (!ch) {
            $(currentEle).html(sprintf('<input class="thVal" type="text" value="%s" />', value));
            ch = true;
        }
        $(".thVal").focus();


        $(".thVal").keyup(function(event) {
            if (event.keyCode == 13) {
                if (undefined != $(".thVal").val()) {
                    var $body = $("body");
                    $body.addClass("loading");
                    var updata = {
                        "roomID": $(currentEle).parent().find('input:checkbox').val(),
                        "room_name": $(".thVal").val()
                    };
                    $(currentEle).html($(".thVal").val());

                    ch = false;
                    //console.log(" 2...");
                    updateRoomNameBackEnd(updata, $body);
                }
            }
        });

        $(".thVal").focusout(function() { // you can use $('html')
            if (undefined != $(".thVal").val()) {
                var $body = $("body");
                $body.addClass("loading");
                var updata = {
                    "roomID": $(currentEle).parent().find('input:checkbox').val(),
                    "room_name": $(".thVal").val()
                };
                $(currentEle).html($(".thVal").val());
                ch = false;
                //console.log("1 ...");
                updateRoomNameBackEnd(updata, $body);
            }
        });

    }

    function updateRoomStartDateAvailability(currentEle, value) {

        //console.log("Current Element is" + currentEle);
        //console.log("checkbox:: " + $(currentEle).parent().find('input:checkbox').val());

        //Corrects corrupted flag after unexpected action
        if (ch && $(".thValDt").length == 0)
            ch = false;

        if (!ch) {
            $(currentEle).html(sprintf('<input class="thValDt" type="text" value="%s" id="ValDt"/>', value));
            $("#ValDt").datepicker({
                format: 'yyyy-mm-dd',
                startDate: '+1d',
                autoclose: true
                    //numberOfMonths: 2
            }).on('change', function() {
                if (undefined != $(".thValDt").val()) {
                    var $body = $("body");
                    $body.addClass("loading");
                    var updata = {
                        "roomID": $(currentEle).parent().find('input:checkbox').val(),
                        "startDate": $(".thValDt").val()
                    };
                    $(currentEle).html($(".thValDt").val());

                    ch = false;
                    //console.log(" 2...");
                    updateRoomStartDateAvailabilityBackEnd(updata, $body);
                }
            });
            ch = true;
        }
        $(".thValDt").focus();

    }

    function updateRoomEndDateAvailability(currentEle, value) {

        //console.log("Current Element is" + currentEle);
        //console.log("checkbox:: " + $(currentEle).parent().find('input:checkbox').val());

        //Corrects corrupted flag after unexpected action
        if (ch && $(".thValDt").length == 0)
            ch = false;

        if (!ch) {
            $(currentEle).html(sprintf('<input class="thValDt" type="text" value="%s" id="ValDt"/>', value));
            $("#ValDt").datepicker({
                format: 'yyyy-mm-dd',
                startDate: $(currentEle).parent().find('._startDate_class_editable').text(),
                autoclose: true
                    //numberOfMonths: 2
            }).on('change', function() {
                if (undefined != $(".thValDt").val()) {
                    var $body = $("body");
                    $body.addClass("loading");
                    var updata = {
                        "roomID": $(currentEle).parent().find('input:checkbox').val(),
                        "endDate": $(".thValDt").val()
                    };
                    $(currentEle).html($(".thValDt").val());

                    ch = false;
                    //console.log(" 2...");
                    updateRoomEndDateAvailabilityBackEnd(updata, $body);
                }
            });
            ch = true;
        }
        $(".thValDt").focus();

    }

    function updateRoomStatusBackEnd(updata, $body) {

        var data = {
            "action": "update_room_availability",
            'email': localStorage.getItem("email"),
            'token': localStorage.getItem("token"),
            "roomID": updata.roomID,
            "onDuty": updata.onDuty
        };
        //console.log("udata:: " + JSON.stringify(data));
        $.ajax({
            url: serviceURL + "rooms.php",
            dataType: "json",
            type: "post",
            data: JSON.stringify(data),
            success: function(resp) {
                //console.log(JSON.stringify(resp));
                $('#refreshRooms').trigger("click");
                $body.removeClass("loading");
                $("#successTxt").html(resp.responce);
                $('.bg-success').show();
            },
            error: function(err) {
                console.log("err " + JSON.stringify(err));
                $body.removeClass("loading");
                $('#dangerTxt').html(err);
                $('.bg-danger').show();
            }
        });

    }

    function updateRoomNameBackEnd(updata, $body) {

        var data = {
            "action": "update_room",
            'email': localStorage.getItem("email"),
            'token': localStorage.getItem("token"),
            "roomID": updata.roomID,
            "room_name": updata.room_name
        };
        //console.log("udata:: " + JSON.stringify(data));
        $.ajax({
            url: serviceURL + "rooms.php",
            dataType: "json",
            type: "post",
            data: JSON.stringify(data),
            success: function(resp) {
                //console.log(JSON.stringify(resp));
                $('#refreshRooms').trigger("click");
                $body.removeClass("loading");
                $("#successTxt").html(resp.responce);
                $('.bg-success').show();
            },
            error: function(err) {
                console.log("err " + JSON.stringify(err));
                $body.removeClass("loading");
                $('#dangerTxt').html(err);
                $('.bg-danger').show();
            }
        });

    }

    function updateRoomStartDateAvailabilityBackEnd(updata, $body) {

        var data = {
            "action": "update_room_availability_startDate",
            'email': localStorage.getItem("email"),
            'token': localStorage.getItem("token"),
            "roomID": updata.roomID,
            "startDate": updata.startDate
        };
        //console.log("udata:: " + JSON.stringify(data));
        $.ajax({
            url: serviceURL + "rooms.php",
            dataType: "json",
            type: "post",
            data: JSON.stringify(data),
            success: function(resp) {
                //console.log(JSON.stringify(resp));
                $('#refreshRooms').trigger("click");
                $body.removeClass("loading");
                $("#successTxt").html(resp.responce);
                $('.bg-success').show();
            },
            error: function(err) {
                console.log("err " + JSON.stringify(err));
                $body.removeClass("loading");
                $('#dangerTxt').html(err);
                $('.bg-danger').show();
            }
        });

    }

    function updateRoomEndDateAvailabilityBackEnd(updata, $body) {

        var data = {
            "action": "update_room_availability_endDate",
            'email': localStorage.getItem("email"),
            'token': localStorage.getItem("token"),
            "roomID": updata.roomID,
            "endDate": updata.endDate
        };
        //console.log("udata:: " + JSON.stringify(data));
        $.ajax({
            url: serviceURL + "rooms.php",
            dataType: "json",
            type: "post",
            data: JSON.stringify(data),
            success: function(resp) {
                //console.log(JSON.stringify(resp));
                $('#refreshRooms').trigger("click");
                $body.removeClass("loading");
                $("#successTxt").html(resp.responce);
                $('.bg-success').show();
            },
            error: function(err) {
                //console.log("err " + JSON.stringify(err));
                $body.removeClass("loading");
                $('#dangerTxt').html(err);
                $('.bg-danger').show();
            }
        });

    }

    function ValidateForm(classElement) {
        var has_error = 'false';
        $(classElement).each(function() {
            if ($(this).prop('required')) {
                //console.log("form-control(required)::" + $(this).attr("name") + ":: " + $(this).val());
                //if($(this).is( "select" ))
                //  //console.log("type select::"+$(this).attr("name") + " - " + $(this).val());
                if ($(this).val() === '') {
                    $(this).closest('.form-group').addClass('has-error');
                    has_error = 'true';
                } else {
                    if ($(this).attr("name").indexOf('email') != -1) {
                        //console.log($(this).attr("name") + " found");
                        if (validateEmail($(this).val())) {
                            $(this).closest('.form-group').removeClass('has-error');
                        } else {
                            has_error = 'true';
                            $(this).closest('.form-group').addClass('has-error');
                        }
                    } else if ($(this).attr("name").indexOf('phone') != -1 || $(this).attr("name").indexOf('mobile') != -1) {
                        //console.log($(this).attr("name"));
                        if (validateTelephoneNumber($(this).val())) {
                            $(this).closest('.form-group').removeClass('has-error');
                        } else {
                            has_error = 'true';
                            $(this).closest('.form-group').addClass('has-error');
                        }
                    } else {
                        if ($(this).val().length < 3) {
                            has_error = 'true';
                            $(this).closest('.form-group').addClass('has-error');
                        } else {
                            $(this).closest('.form-group').removeClass('has-error');
                        }

                    }
                }
            } else {
                //console.log("form-control(not req)::" + $(this).attr("name") + ":: " + $(this).val());
            }

        });

        return has_error;

    }
});

/***
 * Functions
 **/



function hideAll() {
    $('.alert').hide();
    $('#dashboard-content').hide();
    $('#propertiesTb').hide();
    $('#roomTypesTb').remove();
    $('#bookingsTb').remove();
    $('#goBackToProperty').hide();
    $('#roomsAvailability').hide();
    $("#bookings").hide();
    $("#checkinCheckout").hide();
    $('#fileupload').hide();
    $(".sidebar-right").hide();
    $('#modal-booking').hide();
}

function sprintf(str) {
    var args = arguments,
        flag = true,
        i = 1;

    str = str.replace(/%s/g, function() {
        var arg = args[i++];

        if (typeof arg === 'undefined') {
            flag = false;
            return '';
        }
        return arg;
    });
    if (flag) {
        return str;
    }
    return '';
};

// Function that validates email address through a regular expression.
function validateEmail(sEmail) {
    var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
    if (filter.test(sEmail)) {
        return true;
    } else {
        return false;
    }
}

function validatePass() {
    if($('.chpass-new-pass').val() !== $('.chpass-new-pass-confirm').val()){
      $('#confirmMessage').removeClass('has-success').addClass('has-error').html("Passwords Do Not Match!");
      $('.chpass-new-pass').removeClass('has-success').addClass('has-error');
      $('.chpass-new-pass-confirm').removeClass('has-success').addClass('has-error');
    } else {
      $('#confirmMessage').removeClass('has-error').addClass('has-success').html("Passwords Match!");
      $('.chpass-new-pass').removeClass('has-error').addClass('has-success');
      $('.chpass-new-pass-confirm').removeClass('has-error').addClass('has-success');
    }
}

function validateTelephoneNumber(num) {
    var filter = /^[0-9-+]+$/;
    if (filter.test(num) && num.length > 9) {
        return true;
    } else {
        return false;
    }
}

//Get property services (with room services)
function  getServices(propertyID, bookData, $body) {
  var lang = 'en';//(typeof $.cookie('lang') === 'undefined') ? 'en' : $.cookie("lang");
  var data = {
      "action": "show_services_property",
      "propertyID": propertyID,
      "lang": lang
  };

  $.ajax({
      url: serviceURL + "services_guests.php",
      dataType: "json",
      type: "post",
      data: JSON.stringify(data),
      success: function(resp) {
          if (resp.resp == 'true') {
              //console.log("resp.data::" + resp.data);
              if (resp.data.length !== 0) {
                  var check_in = new Date($('#booking-form-checkin').val());
                  var check_out = new Date($('#booking-form-checkout').val());
                  var timeDiff = Math.abs(check_out.getTime() - check_in.getTime());
                  var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));

                  //$("#check_in").html($('#booking-form-from').val());
                  //$("#check_out").html($('#booking-form-to').val());

                  //$("#numNights").html(diffDays);
                  var propertySrvTotal = 0;
                  $('#propertyServices').html('<ul>');
                  if (resp.data.propertyServices.length !== 0) {
                      $.each(resp.data.propertyServices, function(key, value) {
                          $('#propertyServices').append("<li class='hotelServices'><label>" + value.service_name + "</label><span>" + value.price + value.currency +
                              "&nbsp;<input name='hotelService' data-service='" + value.service_name + "' data-currency='" + value.currency
                              + "' data-roomtypeid='0' data-serviceid='" + value.serviceID + "' value='" + value.price + "' type='checkbox'></input></span></li>");
                      });
                      $.each(bookData.hotelServices,function(key,value){
                        $(".hotelServices input[name='hotelService']").each(function() {
                          if($(this).data('serviceid') == value.serviceID){
                            $(this).prop("checked", true);
                            propertySrvTotal = propertySrvTotal + parseInt(value.service_cost);
                          }
                        });
                      });
                      $('input[name="booking-form-hotelServicesCost"]').val(propertySrvTotal);
                  } else {
                      $('#propertyServices').append('<li id="emptyRow">No services available...</li>');
                  }
                  $('#propertyServices').append('</ul>');

                  $('#roomServices').html('<ul>');
                  var roomServiceFound = 'false';
                  //console.log("resp.data.roomsServices.length:: " + resp.data.roomsServices.length);
                  if (resp.data.roomsServices.length !== 0) {
                      $.each(bookData.rooms, function(key, val) {
                          //console.log("val.roomtypeID::"+val.roomtypeID);
                          $.each(resp.data.roomsServices, function(key, value) {
                              if (value.roomtypeID === val.roomtypeID) {
                                  roomServiceFound = 'true';
                                  if(value.daily === 1 ){
                                    $('#roomServices').append("<li class='roomServices'><label>" + val.roomIdentify + ":: " + value.service_name + "</label><span>(x" + diffDays + ")"
                                      + value.price + value.currency + "&nbsp;<input name='roomServices' data-service='" + val.roomType + ":: " + value.service_name
                                      + "' data-roomtypeid='" + value.roomtypeID + "' data-serviceID='" + value.serviceID + "' data-currency='" + value.currency + "' value='" + (value.price*diffDays)+ "' type='checkbox'></input></span></li>");
                                  } else {
                                    $('#roomServices').append("<li class='roomServices'><label>" + val.roomIdentify + ":: " + value.service_name + "</label><span>"
                                      + value.price + value.currency + "&nbsp;<input name='roomServices' data-service='" + val.roomType + ":: " + value.service_name
                                      + "' data-roomtypeid='" + value.roomtypeID + "' data-serviceID='" + value.serviceID + "' data-currency='" + value.currency + "' value='" + value.price + "' type='checkbox'></input></span></li>");
                                  }
                              }
                          });
                      });

                      var roomsSrvTotal = 0;
                      $.each(bookData.roomServices,function(key,value){
                        $(".roomServices input[name='roomServices']").each(function() {
                          if($(this).data('serviceid') == value.serviceID){
                            $(this).prop("checked", true);
                            roomsSrvTotal = roomsSrvTotal + parseInt(value.service_cost);
                          }
                        });
                      });
                      $('input[name="booking-form-roomServicesCost"]').val(roomsSrvTotal);
                  } else {
                      $('#roomServices').append('<li id="emptyRow">No services available...</li>');
                  }

                  if (roomServiceFound === 'false') {
                      $('#roomServices').append('<li id="emptyRow">No services available...</li>');
                  }

                  $('#roomServices').append('</ul>');
                  //console.log("roomServiceFound:: " + roomServiceFound);
              }
          } else {
              //console.log("resp::false:: " + JSON.stringify(resp));
              $('#warningTxt').html(resp.responce);
              $('.bg-warning').show();
          }

          $('input[name="booking-form-totalCost"]').val(parseInt($('input[name="booking-form-roomsCost"]').val())+roomsSrvTotal+propertySrvTotal);
          $("#modal-booking").show();
          $body.removeClass("loading");
      },
      error: function(err) {
          //console.log("err " + JSON.stringify(err));
          $('#dangerTxt').html("Error: unenable to find available rooms services...");
          $('.bg-danger').show();
          $body.removeClass("loading");
      }
  });

}

//get bookings
function getBookings(propertyID){
  var $body = $("body");
  $body.addClass('loading');
  $.get("tpl/bookings.html?"+up2date.getTime(), function(data) {
      $("#bookingsDt").html(data);
      $("#modal-booking").hide();
      $('[data-toggle="bookings"]').attr('data-url', serviceURL + 'bookings.php');
      $('[data-toggle="bookings"]').attr('data-action', 'show_property_bookings');
      $('[data-toggle="bookings"]').attr('data-propertyid', propertyID);
      $('[data-toggle="bookings"]').attr('data-status', '1');
      $('[data-toggle="bookings"]').attr('data-add', 'addBooking');
      $('[data-toggle="bookings"]').attr('data-refresh', 'refreshBookings');
      $('[data-toggle="bookings"]').bootstrapTable({
          onClickRow: function (row, $element) {
              $(".modal-header-txt").html("Reservation Code/Pin: " + row.reservationCode + "/" + row.pin);
              changeBookData(row.bookingID,$body);
          }
      });
  });
}

function changeBookData(bookingID,$body) {

  var data = {
      "action": "show_booking",
      'email': localStorage.getItem("email"),
      'token': localStorage.getItem("token"),
      'bookingID': bookingID
  };

  var propertyID = $("#propertiesListBook").val();

  $.ajax({
      url: serviceURL + "bookings.php",
      dataType: "json",
      type: "post",
      data: JSON.stringify(data),
      success: function(resp) {
          //console.log(JSON.stringify(resp));
          $.each(resp.data,function(key,value){
            //console.log(key);
            if(key.indexOf('notes') != -1 || key.indexOf('Notes') != -1){
              $("textarea[name='booking-form-" + key + "']").val(value);
            } else if(key.indexOf('country') != -1){
              $("select[name='booking-form-" + key + "']").val(value);
            }else {
              $("input[name='booking-form-" + key + "']").val(value);
            }
          });

          var cnt = 1;
          $('input[name="booking-form-roomsCost"]').val(resp.data.roomsTotalPrice);
          $('span.add-on').html(resp.data.bookingCurrency);
          $(".room-row").show();
          $('#bookingRoomsList').html($(".room-row").clone());

          $.each(resp.data.rooms, function(key,value){
            console.log(cnt);
            $(".room-row").clone().insertBefore(".room-row").removeClass('room-row').addClass('room-row_'+cnt);
            //$('.room-row_'+cnt).find('.'+classPrefix+'roomDesc').removeClass(classPrefix+'roomDesc').addClass(classPrefix+'roomDesc_'+cnt);
            $('.room-row_'+cnt).find('label').html(value.roomIdentify);
            cnt++;
          });
          $(".room-row").hide();
          //$("#successTxt").html(resp.responce);
          //$('.bg-success').show();
          getServices(propertyID, resp.data, $body);
          //$("#modal-booking").show();
          //$body.removeClass("loading");
      },
      error: function(err) {
          console.log("err " + JSON.stringify(err));
          $body.removeClass("loading");
          $('#dangerTxt').html(err);
          $('.bg-danger').show();
      }
  });
}

//Get Checnin/Out daily
function getCheckinOut(propertyID,ddate) {
  var $body = $("body");
  $body.addClass("loading");
  $("#roomsCheckinOut").html();

  $.get("tpl/checkin-checkout.html?"+up2date.getTime(), function(data) {
      $("#roomsCheckinOut").html(data);

      $('[data-toggle="checkins"]').attr('data-url', serviceURL + 'checkin.php');
      $('[data-toggle="checkins"]').attr('data-action', 'show_checkin');
      $('[data-toggle="checkins"]').attr('data-propertyid', propertyID);
      $('[data-toggle="checkins"]').attr('data-check_date', ddate);
      $('[data-toggle="checkins"]').attr('data-refresh', 'refreshCheckins');
      $('[data-toggle="checkins"]').bootstrapTable();

      $('[data-toggle="checkouts"]').attr('data-url', serviceURL + 'checkout.php');
      $('[data-toggle="checkouts"]').attr('data-action', 'show_checkout');
      $('[data-toggle="checkouts"]').attr('data-propertyid', propertyID);
      $('[data-toggle="checkouts"]').attr('data-check_date', ddate);
      $('[data-toggle="checkouts"]').attr('data-refresh', 'refreshCheckouts');
      $('[data-toggle="checkouts"]').bootstrapTable();

  });

  //$('#roomsCheckinOut').html(data);
  $body.removeClass("loading");

}

function roomTypesList(propertyID, property_name) {
    //hideAll();
    $('.page-header').html(property_name + ': Room Types');
    $.get("tpl/roomTypes.html?"+up2date.getTime(), function(data) {
        $("#mainContent").append(data);
        $('[data-toggle="roomTypes"]').attr('data-url', serviceURL + 'room_types.php');
        $('[data-toggle="roomTypes"]').attr('data-action', 'show_roomtypes');
        $('[data-toggle="roomTypes"]').attr('data-add', 'addRoomtype');
        $('[data-toggle="roomTypes"]').attr('data-refresh', 'refreshRoomtype');
        //console.log("propertyID:: " + propertyID);
        $('[data-toggle="roomTypes"]').attr('data-property', propertyID);
        $('[data-toggle="roomTypes"]').bootstrapTable({
            contextMenu: '#context-menu-roomtypes',
            contextMenuTrigger: 'both',
            onClickRow: function(row, $el) {
                $('#roomTypesTbl').find('.success').removeClass('success');
                $el.addClass('success');
            },
            onContextMenuItem: function(row, $el) {
                if ($el.data("item") == "edit") {
                    editRoomType(row.roomtypeID);
                } else if ($el.data("item") == "delete") {
                    deleteRoomType(row.roomtypeID);
                } else if ($el.data("item") == "roomServices") {
                    viewProperty(row.roomtypeID);
                } else if ($el.data("item") == "roomAvail") {
                    roomAvailability(row.roomtypeID, property_name);
                }
            }
        });
        $('#goBackToProperty').show();
    });
}

function deleteProperty(propertyID) {
    //console.log('propertyID:: ' + propertyID);
    if (confirm('Are you sure you want to this property?')) {
        var data = {
            "action": "delete_property",
            'email': localStorage.getItem("email"),
            'token': localStorage.getItem("token"),
            'propertyID': propertyID
        };

        $.ajax({
            url: serviceURL + "properties.php",
            dataType: "json",
            type: "post",
            data: JSON.stringify(data),
            success: function(resp) {
                if (resp.resp == 'true') {
                    //console.log("resp::true:: " + JSON.stringify(resp));
                    $('button[name="refresh"]').trigger("click");
                    $("#successTxt").html(resp.responce);
                    $('.bg-success').show();
                } else {
                    //console.log("resp::false:: " + JSON.stringify(resp));
                    $('#warningTxt').html(resp.responce);
                    $('.bg-warning').show();
                }
            },
            error: function(err) {
                console.log("err " + JSON.stringify(err));
                $('#dangerTxt').html(err);
                $('.bg-danger').show();
            }
        });
    }

}

function deleteRoomType(roomtypeID) {
    //console.log('roomtypeID:: ' + roomtypeID);
    if (confirm('Are you sure you want to this room type?')) {
        var data = {
            "action": "delete_roomtype",
            'email': localStorage.getItem("email"),
            'token': localStorage.getItem("token"),
            'roomtypeID': roomtypeID
        };

        $.ajax({
            url: serviceURL + "room_types.php",
            dataType: "json",
            type: "post",
            data: JSON.stringify(data),
            success: function(resp) {
                if (resp.resp == 'true') {
                    //console.log("resp::true:: " + JSON.stringify(resp));
                    $('button[name="refresh"]').trigger("click");
                    $("#successTxt").html(resp.responce);
                    $('.bg-success').show();
                } else {
                    //console.log("resp::false:: " + JSON.stringify(resp));
                    $('#warningTxt').html(resp.responce);
                    $('.bg-warning').show();
                }
            },
            error: function(err) {
                console.log("err " + JSON.stringify(err));
                $('#dangerTxt').html(err);
                $('.bg-danger').show();
            }
        });
    }

}

function editProperty(propertyID) {
    //console.log('propertyID:: ' + propertyID)
    var data = {
        "action": "show_property",
        'email': localStorage.getItem("email"),
        'token': localStorage.getItem("token"),
        'propertyID': propertyID
    };
    var image = new Image();
    d = new Date();
    //console.log("img:: " + serviceURL + "uploads/files/logos/logo_" + propertyID + ".png");
    image.src = serviceURL + "uploads/files/logos/logo_" + propertyID + ".png";
    var review = ('<img src="' + serviceURL + "uploads/files/logos/logo_" + propertyID + ".png?" + d.getTime() + '" style="width:120px;height:90px;"/>');
    $.ajax({
        url: serviceURL + "properties.php",
        dataType: "json",
        type: "post",
        data: JSON.stringify(data),
        success: function(resp) {
            if (resp.resp == 'true') {
                //console.log("resp::true:: " + JSON.stringify(resp));
                $.get("tpl/editProperty.html?"+up2date.getTime(), function(data) {
                    $("#addProp").html(data);

                    $(".singleupload").append(review);
                    if (image.width == 0) {
                        //var review = ('<img src="'+serviceURL+"uploads/files/logos/logo_"+propertyID+".png"+'" style="width:120px;height:90px;"/>');
                        //$(".singleupload").append(review);
                        //$(".singleupload").attr("src","hotelier/images/empty_bg?"+d.getTime());
                        //console.log("image is missing");
                        //review = ('<img src="images/empty_bg.png" style="width:120px;height:90px;"/>');
                        $(".singleupload").html('');
                        //$(".singleupload").attr("src","../uploads/files/logos/logo_"+propertyID+".png?"+d.getTime());
                        //$("#preview").attr("href", image.src+"?"+d.getTime());

                    }

                    //console.log(resp.data.property_name);
                    $("input[name='propertyID']").val(propertyID);
                    $("input[name='property_name']").val(resp.data.property_name);
                    $("input[name='eponymia']").val(resp.data.eponymia);
                    $("input[name='contact']").val(resp.data.contact);
                    $("input[name='phone']").val(resp.data.phone);
                    $("input[name='fax']").val(resp.data.fax);
                    $("input[name='emailprop']").val(resp.data.email);
                    $("input[name='website']").val(resp.data.website);
                    $("input[name='address']").val(resp.data.address);
                    $("input[name='town']").val(resp.data.town);
                    $("input[name='postcode']").val(resp.data.postcode);
                    $("input[name='country']").val(resp.data.country);
                    $("input[name='geotag']").val(resp.data.geotag);

                    $(document).find("#propertyServicesTbl tbody tr").remove();



                    $('#uploadbox').singleupload({
                        action: serviceURL + 'uploads/do_upload.php?property=' + propertyID + '&action=logo', //'do_upload.json', //action: 'do_upload.php'
                        inputId: 'singleupload_input',
                        previewClass: 'singleupload',
                        onError: function(code) {
                            console.debug('error code ' + res.code);
                        },
                        onSuccess: function(url, data) {
                                $('#return_url_text').val(serviceURL + url);
                            }
                            /*,onProgress: function(loaded, total) {} */
                    });



                    $.each(resp.data.services, function(key, value) {
                        //console.log("Property-value::" + value.service_name);
                        var valService = sprintf('<input type="text" name="service_name[]" value="%s">', value.service_name);
                        var valDescr = sprintf('<input type="text" name="service_descr[]" value="%s">', value.service_descr);
                        var valPrice = sprintf('<input type="text" name="service_price[]" value="%s">', value.price);

                        $("select[name='currency']").val(html_entity_decode(value.currency));
                        if (value.daily == '1')
                            var valDaily = '<input type="checkbox" name="daily[]" value="1" checked/>';
                        else
                            var valDaily = '<input type="checkbox" name="daily[]" value="1" />';

                        $('#propertyServicesTbl').find('tbody:last')
                            .append(sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href="#" class="remove_field">Remove</a></td></tr>',
                                valService,
                                valDescr,
                                valPrice,
                                valDaily
                            ));
                    });



                });



            } else {
                //console.log("resp::false:: " + JSON.stringify(resp));
                $('#warningTxt').html(resp.responce);
                $('.bg-warning').show();
            }
        },
        error: function(err) {
            console.log("err " + JSON.stringify(err));
            $('#dangerTxt').html(err);
            $('.bg-danger').show();
        }
    });
}

function html_entity_decode(str) {
  if(str) // check str is empty or not.
    return $("<div />").html(str).text();
  else
    return 'Argument mis-matched';
}

function editRoomType(roomtypeID) {
    //console.log('roomtypeID:: ' + roomtypeID);
    var data = {
        "action": "show_roomtype",
        'email': localStorage.getItem("email"),
        'token': localStorage.getItem("token"),
        'roomtypeID': roomtypeID
    };

    $.ajax({
        url: serviceURL + "room_types.php",
        dataType: "json",
        type: "post",
        data: JSON.stringify(data),
        success: function(resp) {
            if (resp.resp == 'true') {
                //console.log("resp::true:: " + JSON.stringify(resp));
                $.get("tpl/editRoomType.html?"+up2date.getTime(), function(data) {
                    $("#addRoomType").html(data);
                    //console.log(resp.data.roomtype_name);
                    $("input[name='roomtypeID']").val(roomtypeID);
                    $("input[name='propertyID']").val($('[data-toggle="roomTypes"]').data('property'));
                    $("input[name='roomtype_name']").val(resp.data.roomtype_name);
                    $("textarea[name='roomtype_descr']").val(resp.data.roomtype_descr);
                    $("input[name='quantity']").val(resp.data.quantity);
                    $("input[name='price']").val(resp.data.price);
                    $("[name='currency']").val(html_entity_decode(resp.data.currency));
                    $("input[name='capacity_min']").val(resp.data.capacity_min);
                    $("input[name='capacity_max']").val(resp.data.capacity_max);
                    $("[name='child_min']").val(resp.data.child_min);
                    $("[name='child_max']").val(resp.data.child_max);
                    $("input[name='minimum_stay']").val(resp.data.minimum_stay);
                    $("#fileupload").attr("action", serviceURL + "uploads");

                    $(document).find("#roomTypesServicesTbl tbody tr").remove();

                    $.each(resp.data.services, function(key, value) {
                        //console.log(value)
                        var valService = sprintf('<input type="text" name="service_name[]" value="%s">', value.service_name);
                        var valDescr = sprintf('<input type="text" name="service_descr[]" value="%s">', value.service_descr);
                        var valPrice = sprintf('<input type="text" name="service_price[]" value="%s">', value.price);

                        if (value.daily == '1')
                            var valDaily = '<input type="checkbox" name="daily[]" value="1" checked/>';
                        else
                            var valDaily = '<input type="checkbox" name="daily[]" value="1" />';

                        $('#roomTypesServicesTbl').find('tbody:last')
                            .append(sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href="#" class="remove_field">Remove</a></td></tr>',
                                valService,
                                valDescr,
                                valPrice,
                                valDaily
                            ));
                    });

                    $(document).find("#roomTypesSpecialPricesTbl tbody tr").remove();

                    $.each(resp.data.specialprices, function(key, value) {

                        var valSPrice = sprintf('<input type="text" name="sp_price[]"value="%s">', value.price);
                        var valstartDate = sprintf('<input type="text" name="sp_startDate[]"value="%s" class="sp_startDate">', value.startDate);
                        var valendDate = sprintf('<input type="text" name="sp_endDate[]"value="%s" class="sp_endDate">', value.endDate);

                        $('#roomTypesSpecialPricesTbl').find('tbody:last').append(sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td><a href="#" class="remove_field_sprice">Remove</a></td></tr>',
                            valSPrice,
                            valstartDate,
                            valendDate
                        ));
                    });

                    // Initialize the jQuery File Upload widget:
                    $('#fileupload').fileupload({
                        // Uncomment the following to send cross-domain cookies:
                        //xhrFields: {withCredentials: true},
                        url: serviceURL + 'uploads/?roomType=' + roomtypeID + "&type=roomtypes"
                    });


                    // Enable iframe cross-domain access via redirect option:
                    $('#fileupload').fileupload(
                        'option',
                        'redirect',
                        window.location.href.replace(
                            /\/[^\/]*$/,
                            '/cors/result.html?%s'
                        )
                    );


                    // Load existing files:
                    $('#fileupload').addClass('fileupload-processing');
                    $.ajax({
                        // Uncomment the following to send cross-domain cookies:
                        //xhrFields: {withCredentials: true},
                        url: $('#fileupload').fileupload('option', 'url'),
                        dataType: 'json',
                        context: $('#fileupload')[0]
                    }).always(function() {
                        $(this).removeClass('fileupload-processing');
                    }).done(function(result) {
                        $(this).fileupload('option', 'done')
                            .call(this, $.Event('done'), {
                                result: result
                            });

                    });


                });
            } else {
                //console.log("resp::false:: " + JSON.stringify(resp));
                $('#warningTxt').html(resp.responce);
                $('.bg-warning').show();
            }
        },
        error: function(err) {
            console.log("err " + JSON.stringify(err));
            $('#dangerTxt').html(err);
            $('.bg-danger').show();
        }
    });
}

function roomAvailability(roomtypeID, property_name) {
    $('.page-header').html(property_name + ': Rooms');
    $.get("tpl/roomAvailability.html?"+up2date.getTime(), function(data) {
        $("#addRoomType").html(data);
        $('[data-toggle="rooms"]').attr('data-url', serviceURL + 'rooms.php');
        $('[data-toggle="rooms"]').attr('data-action', 'show_roomtype_rooms');
        //$('[data-toggle="roomTypes"]').attr('data-add', 'addRoomtype');
        //console.log("roomtypeID:: " + roomtypeID);
        $('[data-toggle="rooms"]').attr('data-roomtype', roomtypeID);
        $('[data-toggle="rooms"]').attr('data-refresh', 'refreshRooms');
        $('[data-toggle="rooms"]').bootstrapTable();
        $('#goBackToProperty').show();
    });
}

function viewProperty(propertyID) {
    //console.log('propertyID:: ' + propertyID)
    var data = {
        "action": "show_property",
        'email': localStorage.getItem("email"),
        'token': localStorage.getItem("token"),
        'propertyID': propertyID
    };

    $.ajax({
        url: serviceURL + "properties.php",
        dataType: "json",
        type: "post",
        data: JSON.stringify(data),
        success: function(resp) {
            if (resp.resp == 'true') {
                //console.log("resp::true:: " + JSON.stringify(resp));
                $.get("tpl/viewProperty.html?"+up2date.getTime(), function(data) {
                    $("#addProp").html(data);
                    $('#property_name').html(resp.data.property_name);
                    $('#eponymia').html(resp.data.eponymia);
                    $('#contact').html(resp.data.contact);
                    $('#phone').html(resp.data.phone);
                    $('#fax').html(resp.data.fax);
                    $('#emailprop').html(resp.data.email);
                    $('#website').html(resp.data.website);
                    $('#address').html(resp.data.address);
                    $('#town').html(resp.data.town);
                    $('#postcode').html(resp.data.postcode);
                    $('#country').html(resp.data.country);
                    $('#geotag').html(resp.data.geotag);
                });

            } else {
                //console.log("resp::false:: " + JSON.stringify(resp));
                $('#warningTxt').html(resp.responce);
                $('.bg-warning').show();
            }
        },
        error: function(err) {
            console.log("err " + JSON.stringify(err));
            $('#dangerTxt').html(err);
        }
    });
}

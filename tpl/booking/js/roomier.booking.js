$(document).ready(function() {

    var $body = $("body");
    var trash = '<svg class="glyph stroked trash"><use xlink:href="#stroked-trash"/></svg>';
    var sessionIsValid = true;
    var reservation = {};
    reservation.rooms = [];

    if (typeof(Storage) == "undefined") {
        // Sorry! No Web Storage support..
        hideAll();
        alert("Sorry! No Web Storage support..");
        console.log("Sorry! No Web Storage support..");
    }

    /***
     * Check if sessionStorage is supported
     **/
    if (typeof(sessionStorage) == "undefined") {
        // Sorry! No Web Storage support..
        hideAll();
        alert("Sorry! No Web Storage support..");
        console.log("Sorry! No Web Storage support..");
        sessionIsValid = false;

    }

    /***
     * Shows Availability search form
     **/
    hideAll();
    $('.rm').hide();
    $('#shCheckAvailForm').show();

    //Hide notifications
    $('.glyphicon-remove').click(function() {
        $('.alert').hide();
    });

    /***
     * Check if bookcart had items-reservations before page refresh
     **/
    if (sessionStorage.reservation !== undefined) {
        //console.log("reservation:: "+ key + "-" + sessionStorage.reservation);
        var reserv = JSON.parse(sessionStorage.reservation);
        var totalp = 0;
        var currency = '&euro;';
        $(".booking-list").empty();
        $.each(reserv.rooms, function(key, value) {
            if (value !== undefined && value !== null) {
                //console.log("reservation:: " + key + "-" + value.roomType + " - " + value.totalPrice + ' - ' + value.currency);
                $(".booking-list").append("<li>" + value.roomType + ":: <br/>" + value.roomsNum + "x" + (value.totalPrice / value.roomsNum) + value.currency + " = " + value.totalPrice + value.currency + "<span class='trashBook' data-currency='" + value.currency + "' data-roomID='" + value.roomID + "'>" + trash + "</span></li>");
                totalp = totalp + value.totalPrice;
                currency = value.currency;
            }
        });
        if (totalp === 0) {
            $(".booking-list").append('<li id="emptyRow">No bookings...</li>');
        }
        $(".booking-list").append("<li id='book-total-cart'>Total: <span id='total-cart'>" + totalp + currency + "</span></li>");
    }

    /***
     * Datepicker handlers
     **/
    var arrivald = new Date();

    $("#booking-form-from").val(arrivald.getFullYear() + "-" + ((arrivald.getMonth()+1) < 10 ? "0" + (arrivald.getMonth()+1): (arrivald.getMonth()+1)) + "-" + arrivald.getDate());
    var departured = new Date($("#booking-form-from").val());
    departured.setDate(departured.getDate() + 1);
    $("#booking-form-to").val(departured.getFullYear() + "-" + ((departured.getMonth()+1) < 10 ? "0" + (departured.getMonth()+1): (departured.getMonth()+1)) + "-" + departured.getDate());
    $("#booking-form-from").datepicker({
        format: 'yyyy-mm-dd',
        startDate: '0d',
        autoclose: true
    }).on('change', function() {
        var d = new Date($('#booking-form-from').val());
        d.setDate(d.getDate() + 1);
        $("#booking-form-to").val(d.getFullYear() + "-" + ((d.getMonth()+1) < 10 ? "0" + (d.getMonth()+1): (d.getMonth()+1)) + "-" + d.getDate());
        $('#booking-form-to').datepicker('setStartDate',d);
        $('#booking-form-to').datepicker('update');
        //console.log($('#booking-form-from').val());
        // $("#booking-form-to").datepicker({
        //     format: 'yyyy-mm-dd',
        //     startDate: '+10d',//$('#booking-form-from').val(),
        //     autoclose: true
        //         //numberOfMonths: 2
        // });
    });

    $("#booking-form-to").datepicker({
        format: 'yyyy-mm-dd',
        startDate: '+1d',
        autoclose: true
            //numberOfMonths: 2
    });

    /***
     * Shows checkout form
     **/
    $('#checkout').on("click", function() {

        if($('.invoice-type').val() === 'receipt')
          $('#checkoutRooms-invoice').hide();
        else
          $('#checkoutRooms-invoice').show();

        if (sessionStorage.reservation !== undefined) {
            $body.addClass("loading");
            hideAll();
            $('#step-2').addClass("completed");
            $("#shCheckout").show();
            $('#checkoutForm').show();
            $("#checkoutPanel").html("");
            reservation = JSON.parse(sessionStorage.reservation);
            //console.log(reservation);

            $(".booking-list-checkout").html("");
            var totalp = 0;
            var currency = '&euro;';
            $.each(reservation.rooms, function(key, value) {
                //if(value !== undefined && value !== null) {
                //console.log("reservation:: " + key + "-" + value.roomType + " - " + value.totalPrice);
                $(".booking-list-checkout").append("<p>" + value.thump + "<span>" + value.roomType + "</span><br/>" + value.roomDesc +
                    "<br/><span class='chckCost'>Cost: " + value.totalPrice + value.currency + "</span></p>");
                totalp = totalp + value.totalPrice;
                currency = value.currency;
                //}
            });
            $(".booking-list-checkout").append("<p class='trc'><label>Room(s) cost:&nbsp;</label><span class='TotalRoomsCost'>" + totalp + "</span>" + currency + "</p>");
            // if (totalp === 0) {
            //     $(".booking-list-checkout").append('<li id="emptyRow">No bookings...</li>');
            // }
            $(".checkoutTotal").html(totalp + currency);
            //$("#checkoutPanel").append("<ul>");


            $('#checkoutRooms').show();

            var lang = (typeof $.cookie('lang') === 'undefined') ? 'en' : $.cookie("lang");
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
                            var check_in = new Date($('#booking-form-from').val());
                            var check_out = new Date($('#booking-form-to').val());
                            var timeDiff = Math.abs(check_out.getTime() - check_in.getTime());
                            var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));

                            $("#check_in").html($('#booking-form-from').val());
                            $("#check_out").html($('#booking-form-to').val());

                            $("#numNights").html(diffDays);
                            $('#propertyServices').html('<ul>');
                            if (resp.data.propertyServices.length !== 0) {
                                $.each(resp.data.propertyServices, function(key, value) {
                                    $('#propertyServices').append("<li class='hotelServices'><label>" + value.service_name + "</label><span>" + value.price + value.currency +
                                        "&nbsp;<input name='hotelService' data-service='" + value.service_name + "' data-currency='" + value.currency
                                        + "' data-roomtypeid='0' data-serviceid='" + value.serviceID + "' value='" + value.price + "' type='checkbox'></input></span></li>");
                                });
                            } else {
                                $('#propertyServices').append('<li id="emptyRow">No services available...</li>');
                            }
                            $('#propertyServices').append('</ul>');

                            $('#roomServices').html('<ul>');
                            var roomServiceFound = 'false';
                            //console.log("resp.data.roomsServices.length:: " + resp.data.roomsServices.length);
                            if (resp.data.roomsServices.length !== 0) {
                                $.each(reservation.rooms, function(key, val) {
                                    $.each(resp.data.roomsServices, function(key, value) {
                                        if (value.roomtypeID === val.roomID) {
                                            roomServiceFound = 'true';
                                            if(value.daily === 1 ){
                                              $('#roomServices').append("<li class='roomServices'><label>" + val.roomType + ":: " + value.service_name + "</label><span>(x" + diffDays + ")"
                                                + value.price + value.currency + "&nbsp;<input name='roomServices' data-service='" + val.roomType + ":: " + value.service_name
                                                + "' data-roomtypeid='" + value.roomtypeID + "' data-serviceID='" + value.serviceID + "' data-currency='" + value.currency + "' value='" + (value.price*diffDays)+ "' type='checkbox'></input></span></li>");
                                            } else {
                                              $('#roomServices').append("<li class='roomServices'><label>" + val.roomType + ":: " + value.service_name + "</label><span>"
                                                + value.price + value.currency + "&nbsp;<input name='roomServices' data-service='" + val.roomType + ":: " + value.service_name
                                                + "' data-roomtypeid='" + value.roomtypeID + "' data-serviceID='" + value.serviceID + "' data-currency='" + value.currency + "' value='" + value.price + "' type='checkbox'></input></span></li>");
                                            }
                                        }
                                    });
                                });
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

                    $body.removeClass("loading");
                },
                error: function(err) {
                    //console.log("err " + JSON.stringify(err));
                    $('#dangerTxt').html("Error: unenable to find available rooms services...");
                    $('.bg-danger').show();
                    $body.removeClass("loading");
                }
            });
        } else {
            alert("You must choose at least one room!");
        }
    });

    $('.invoice-type').on("change",function(){
      console.log("$(this).val(): " + $(this).val());
      if($(this).val() === 'receipt')
        $('#checkoutRooms-invoice').hide();
      else
        $('#checkoutRooms-invoice').show();
    });

    if($('.invoice-type').val() === 'receipt')
      $('#checkoutRooms-invoice').hide();

    $(document).on("click", '.hotelServices input[name="hotelService"]', function(e) {
        $(".hotel-list-services").empty();
        var serviceTotal = 0;
        if ($('.hotelServices input:checked[name="hotelService"]').length !== 0) {
            $('.hotelServices input:checked[name="hotelService"]').each(function() {
                //console.log("hotelService:: " + $(this).val());
                serviceTotal = serviceTotal + parseInt($(this).val());
                $(".hotel-list-services").append("<li><label>" + $(this).data("service") + "</label> " + $(this).val() + $(this).data("currency") + "</li>");
            });
        } else {
            $(".hotel-list-services").append("<li id='emptyRow'>None service is selected...</li>");
        }
        //$(".checkoutTotal").html(serviceTotal + parseInt($('.TotalRoomsCost').text()) + $(this).data("currency"));
        $(".rooms-list-services").empty();
        if ($('.roomServices input:checked[name="roomServices"]').length !== 0) {
            $('.roomServices input:checked[name="roomServices"]').each(function() {
                serviceTotal = serviceTotal + parseInt($(this).val());
                $(".rooms-list-services").append("<li><label>" + $(this).data("service") + "</label> " + $(this).val() + $(this).data("currency") + "</li>");
            });

        } else {
            $(".rooms-list-services").append("<li id='emptyRow'>None service is selected...</li>");
        }
        $(".checkoutTotal").html(serviceTotal + parseInt($('.TotalRoomsCost').text()) + $(this).data("currency"));
    });

    $(document).on("click", '.roomServices input[name="roomServices"]', function(e) {
        $(".rooms-list-services").empty();
        var serviceTotal = 0;
        if ($('.roomServices input:checked[name="roomServices"]').length !== 0) {
            $('.roomServices input:checked[name="roomServices"]').each(function() {
                serviceTotal = serviceTotal + parseInt($(this).val());
                $(".rooms-list-services").append("<li><label>" + $(this).data("service") + "</label> " + $(this).val() + $(this).data("currency") + "</li>");
            });

        } else {
            $(".rooms-list-services").append("<li id='emptyRow'>None service is selected...</li>");
        }

        $(".hotel-list-services").empty();
        if ($('.hotelServices input:checked[name="hotelService"]').length !== 0) {
            $('.hotelServices input:checked[name="hotelService"]').each(function() {
                console.log("hotelService:: " + $(this).val());
                serviceTotal = serviceTotal + parseInt($(this).val());
                $(".hotel-list-services").append("<li><label>" + $(this).data("service") + "</label> " + $(this).val() + $(this).data("currency") + "</li>");
            });
        } else {
            $(".hotel-list-services").append("<li id='emptyRow'>None service is selected...</li>");
        }
        $(".checkoutTotal").html(serviceTotal + parseInt($('.TotalRoomsCost').text()) + $(this).data("currency"));
    });

    $('#complete_book').on('click', function() {
        //$('input.complete-booking[name^="booking-form-"]').each(function() {
        //var has_error = ValidateForm('.complete-booking');

        if (ValidateForm('.complete-booking') === 'true') {
            console.log("errors found!!");
        } else {
            console.log("no errors found....");
            var serviceTotal = 0;
            var classPrefix = "confirmation-reservation-" ;
            var data = {
              "action": "add_booking",
              "propertyID": propertyID,
              "check-in" : $('#booking-form-from').val(),
              "check-out" : $('#booking-form-to').val(),
              "lang" : (typeof $.cookie('lang') === 'undefined') ? 'en' : $.cookie("lang"),
              "guests": [],
              "rooms" : []
            };

            $('.confirmation-reservation-checkin').html($('#booking-form-from').val());
            $('.confirmation-reservation-checkout').html($('#booking-form-to').val());

            if (sessionStorage.reservation !== undefined) {
                $body.addClass("loading");

                var reservation = JSON.parse(sessionStorage.reservation);
                //console.log(reservation);

                var totalp = 0;
                var currency = '&euro;';
                var cnt = 1;
                var rowClone = $(".room-row").clone();//$(".guests").clone().appendTo(".guests_div").removeClass('guests').addClass('guests_clone');
                $.each(reservation.rooms, function(key, value) {
                    totalp = totalp + value.totalPrice;
                    currency = value.currency;//.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
                                   //return '&#'+i.charCodeAt(0)+';';
                                //});

                    data.rooms.push({"roomID":value.roomID, "roomType":value.roomType, "totalPrice": value.totalPrice, "currency": currency});

                    //$(".room-row").clone().appendTo("#confirmation-reservation").removeClass('room-row').addClass('room-row_'+cnt);
                    $(".room-row").clone().insertBefore(".room-row-total").removeClass('room-row').addClass('room-row_'+cnt);
                    $('.room-row_'+cnt).find('.'+classPrefix+'roomDesc').removeClass(classPrefix+'roomDesc').addClass(classPrefix+'roomDesc_'+cnt);
                    $('.room-row_'+cnt).find('.'+classPrefix+'quests').removeClass(classPrefix+'quests').addClass(classPrefix+'quests_'+cnt);
                    $('.room-row_'+cnt).find('.'+classPrefix+'cost').removeClass(classPrefix+'cost').addClass(classPrefix+'cost_'+cnt);
                    $('.room-row_'+cnt).find('.room-row-num').removeClass('room-row-num').addClass('room-row-num_'+cnt);
                    $('.room-row-num_'+cnt).html('Room ' + cnt);
                    $('.'+classPrefix+'roomDesc_'+cnt).html(value.roomType);
                    $('.'+classPrefix+'quests_'+cnt).html('-');
                    $('.'+classPrefix+'cost_'+cnt).html(value.totalPrice + currency);

                    //Services
                    $('.room-row_'+cnt).find('.'+classPrefix+'rooms-services').removeClass(classPrefix+'rooms-services').addClass(classPrefix+'rooms-services_'+value.roomID);
                    if ($('.roomServices input:checked[name="roomServices"]').length !== 0)
                        $('.'+classPrefix+'rooms-services_'+value.roomID).empty();

                    cnt++;
                });
                data.roomsTotal = totalp;
                data.roomsCurrency = currency;
                $('.confirmation-reservation-grand-total').html(totalp + currency);
            }

            $('.row select[name="adults"]').each(function(key, value) {
                data.guests.push({
                    'adults': this.value,
                    'children': $('.row select[name="children"]')[key].value,
                    'infants': $('.row select[name="infants"]')[key].value
                });
            });

            $('.complete-booking').each(function() {
                data[$(this).attr("name").replace("booking-form-","")] = $(this).val();
                if($(this).attr("name").indexOf('firstname') != -1){
                  $('.confirmation-reservation-fullname').html($(this).val()+' ');
                }

                if($(this).attr("name").indexOf('lastname') != -1){
                  $('.confirmation-reservation-fullname').append($(this).val());
                }

                if($(this).attr("name").indexOf('email') != -1){
                  $('.confirmation-reservation-email').html($(this).val());
                }

                if($(this).attr("name").indexOf('telephone') != -1){
                  $('.confirmation-reservation-telephone').html($(this).val());
                }


            });

            if ($('.hotelServices input:checked[name="hotelService"]').length !== 0) {
                data.hotelServices = [];
                $('.hotelServices input:checked[name="hotelService"]').each(function() {
                    serviceTotal = serviceTotal + parseInt($(this).val());
                    data.hotelServices.push({"serviceID": $(this).data("serviceid"),
                                             "roomtypeID": $(this).data("roomtypeid"),
                                             "service-name": $(this).data("service"),
                                             "service-cost": $(this).val(),
                                             "service-currency": $(this).data("currency")
                                           });
                });
            } else {
              data.hotelServices = [];
            }

            if ($('.roomServices input:checked[name="roomServices"]').length !== 0) {
                data.roomServices = [];
                $('.roomServices input:checked[name="roomServices"]').each(function() {
                    serviceTotal = serviceTotal + parseInt($(this).val());
                    data.roomServices.push({"serviceID": $(this).data("serviceid"),
                                            "roomtypeID": $(this).data("roomtypeid"),
                                            "service-name": $(this).data("service"),
                                            "service-cost": $(this).val(),
                                            "service-currency": $(this).data("currency")
                                          });
                   $('.'+classPrefix+'rooms-services_'+$(this).data("roomtypeid")).append("<li><label>" + $(this).data("service") + "</label> " + $(this).val() + $(this).data("currency") + "</li>");
                });
                totalp = totalp + serviceTotal;
                console.log("totalp "+ totalp)
            } else {
              data.roomServices = [];
            }
            $('.confirmation-reservation-grand-total').html(totalp + currency);
            $.ajax({
                url: serviceURL + "bookings.php",
                dataType: "json",
                type: "post",
                data: JSON.stringify(data),
                success: function(resp) {

                  console.log("book:: " + JSON.stringify(resp));
                  $(".confirmation-reservation-code").html(resp.data.reservationCode);
                  $(".confirmation-reservation-pin").html(resp.data.pin);
                  $(".room-row").hide();
                  hideAll();
                  $('#step-3').addClass("completed");
                  $('#shBookingConfirmation').show();
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
    });

    /***
     * Add/Remove guests in checkout form
     **/
    //Add guests
    $('#addGuests').on('click', function() {

        if ($(".guests").length === 1) {
            $(".guests").clone().appendTo(".guests_div").removeClass('guests').addClass('guests_clone');
        }

        if ($(".guests").length === 0 && $(".guests_clone").length === 1) {
            $(".guests_clone").clone().appendTo(".guests_div").removeClass('guests_clone').addClass('guests');
        }

        if ($(".guests").length === 0 && $(".guests_clone").length > 1) {
            $(".guests_clone").each(function(key, value) {
                //console.log("guests_clone::" + key);
                $(this).removeClass('guests_clone').addClass('guests');
                $(this).clone().appendTo(".guests_div").removeClass('guests').addClass('guests_clone');
                // Will stop running after firs child
                return (key !== 0);
            });
        }

        $('.rm').show();
    });

    //  remove guests
    $(document).on("click", '.rmGuests', function() {
        //console.log("--rmGuests ln:: " + $('.rmGuests').length);
        if ($('.rmGuests').length > 1)
            this.closest(".row").remove();
        if ($('.rmGuests').length === 1)
            $('.rm').hide();

        //console.log("x-rmGuests ln:: " + $('.rmGuests').length);
    });
    /***
     * Back Availability search form
     **/
    $('#backCheckAvail').on("click", function() {
        hideAll();
        $('#step-1').removeClass("completed");
        $('#shCheckAvailForm').show();
    });

    $('#backCheckAvail-2').on("click", function() {
        hideAll();
        $('#step-1').removeClass("completed");
        $('#step-2').removeClass("completed");
        $('#shCheckAvailForm').show();
    });


    /***
     * Trashbook hanlder: Deletes a reservation from BookCart
     **/


    $(document).on("click", '.trashBook', function(e) {
        if (sessionStorage.reservation !== undefined) {
            reservation = JSON.parse(sessionStorage.reservation);
            //console.log("reservation::" + JSON.stringify(reservation));
            //console.log("roomID::" + $(this).attr('data-roomID')); //$('.trashBook').data("roomID"));
            //console.log("roomID::" + $(this).attr('data-roomID'));
            var index = reservation.rooms.map(function(d) {
                if (d['roomID'] !== null)
                    return d['roomID'];
            }).indexOf(parseInt($(this).attr('data-roomID')));

            delete reservation.rooms[index];
            //$(this).closest('li').remove();
            var totalp = 0;
            var currency = $(this).attr('data-currency');

            if (currency === undefined || currency === null)
                currency = '&euro;';
            var rsrv = {};
            rsrv.rooms = [];
            $(".booking-list").empty();
            $.each(reservation.rooms, function(key, value) {
                if (value !== undefined && value !== null) {
                    //console.log("reservation:: " + key + "-" + value.roomType + " - " + value.totalPrice);
                    $(".booking-list").append("<li>" + value.roomType + ":: <br/>" + value.roomsNum + "x" + (value.totalPrice / value.roomsNum) + value.currency + " = " + value.totalPrice + value.currency + "<span class='trashBook' data-currency='" + value.currency + "' data-roomID='" + value.roomID + "'>" + trash + "</span></li>");
                    totalp = totalp + value.totalPrice;
                    rsrv.rooms.push({
                        'roomID': value.roomID,
                        'roomType': value.roomType,
                        'roomDesc': value.roomDesc,
                        'thump': value.thump,
                        'roomsNum': value.roomsNum,
                        'totalPrice': value.totalPrice,
                        'currency': value.currency
                    });
                }
            });
            if (totalp === 0) {
                $(".booking-list").append('<li id="emptyRow">No bookings...</li>');
            }
            $(".booking-list").append("<li id='book-total-cart'>Total: <span id='total-cart'>" + totalp + currency + "</span></li>");
            sessionStorage.reservation = JSON.stringify(rsrv);
            //console.log("delreserv:::" + JSON.stringify(rsrv));
        }
    });

    /***
     * Bookroom handler: Adds a reservation to BookCart
     **/

    $(document).on("click", '.bookRoom', function(e) {
        //console.log("bookRoom data::" + $(this).data("roomid"));

        if (sessionStorage.reservation !== undefined) {

            //console.log("reservation book:: " + sessionStorage.reservation);

            reservation = JSON.parse(sessionStorage.reservation);
            //console.log("reservation .length:: " + reservation.rooms.length);
            if (reservation.rooms.length === $('.row select[name="adults"]').length)
                if (!confirm("You have reached the number of rooms you asked. Are you sure?"))
                    return;
                else
                    console.log("Choose " + ($('.row select[name="adults"]').length - reservation.rooms.length) + " more rooms");
                //$.each(reserv.rooms, function( key, value ) {
                //	console.log("reservation:: "+ key + "-" + value.roomType);
                //});
        }
        var totalp = parseInt($('#total-cart').text());
        $('#emptyRow').remove();
        $('#book-total-cart').remove();
        //alert("Room(s) added to cart");
        var roomsNum = 1; //$("#booking-form-rooms-num-"+$(this).data("roomid")).val();
        var total = $(this).data("total");
        var currency = $(this).data("currency");
        if (currency === undefined || currency === null)
            currency = '&euro;';
        var totalPrice = $(this).data("total") * roomsNum;
        var roomName = $(this).data("room-name");
        var thump = $(this).data("thump");
        var desc = $(this).data("desc");

        reservation.rooms.push({
            'roomID': $(this).data("roomid"),
            'roomType': roomName,
            'roomDesc': desc,
            'thump': thump,
            'roomsNum': roomsNum,
            'totalPrice': totalPrice,
            'currency': currency
        });
        sessionStorage.reservation = JSON.stringify(reservation);
        //console.log("totalp:: " + totalp);
        //console.log("totalPrice:: " + totalPrice);
        //console.log("res:: " + JSON.stringify(reservation));
        //$(".bookCartItems").html(":: "+ (roomsNum*totalPrice));
        if ((total + totalPrice) === 0) {
            $(".booking-list").append('<li id="emptyRow">No bookings...</li>');
        } else
            $(".booking-list").append("<li>" + roomName + ":: <br/>" + roomsNum + "x" + total + currency + " = " + totalPrice + currency + "<span class='trashBook' data-currency='" + currency + "' data-roomID='" + $(this).data("roomid") + "'>" + trash + "</span></li>");
        $(".booking-list").append("<li id='book-total-cart'>Total: <span id='total-cart'>" + (totalp + totalPrice) + currency + "</span></li>");


    });



    /***
     * Bookcheck: Checks if there are available rooms
     **/

    $('#booking-form-check').on("click", function(event) {
        //localStorage.clear();
        //alert(event.target.id);
        if (ValidateForm('.availability_form') === 'true') {
            console.log("errors found!!");
        } else {
            console.log("no errors found....");
            if (sessionStorage.reservation !== undefined) {
                //sessionStorage.reservation.removeItem('rooms');
                //sessionStorage.clear();
                reservation.rooms = [];
                sessionStorage.removeItem('reservation');
                var totalp = 0;
                var currency = '&euro;';
                $(".booking-list").empty();
                $(".booking-list").append('<li id="emptyRow">No bookings...</li>');
                $(".booking-list").append("<li id='book-total-cart'>Total: <span id='total-cart'>" + totalp + currency + "</span></li>");
            }

            $('#step-1').addClass("completed");
            $body.addClass("loading");
            var lang = (typeof $.cookie('lang') === 'undefined') ? 'en' : $.cookie("lang");
            // var data = {
            //     "action": "check_available",
            //     "propertyID": propertyID,
            //     "arrival_date": $("#booking-form-from").val(),
            //     "departure_date": $("#booking-form-to").val(),
            //     "rooms": 1, //$("#booking-form-rooms-num").val(),
            //     "adults": $("#booking-form-adults").val(),
            //     "children": $("#booking-form-children").val(),
            //     "infants": $("#booking-form-infants").val(),
            //     "lang": lang
            // };

            var data = {
                "action": "check_available",
                "propertyID": propertyID,
                "arrival_date": $("#booking-form-from").val(),
                "departure_date": $("#booking-form-to").val(),
                "rooms": [],
                "lang": lang
            };

            $('.row select[name="adults"]').each(function(key, value) {
                data.rooms.push({
                    'adults': this.value,
                    'children': $('.row select[name="children"]')[key].value,
                    'infants': $('.row select[name="infants"]')[key].value
                });
            });

            //console.log("newData:: " + JSON.stringify(data));
            //console.log("rooms length:: " + data.rooms.length);
            //console.log("adults length:: " + $('.row select[name="adults"]').length);

            $.ajax({
                url: serviceURL + "availability_check.php",
                dataType: "json",
                type: "post",
                data: JSON.stringify(data),
                success: function(resp) {
                    if (resp.resp == 'true') {
                        d = new Date();

                        //console.log("resp.data::" + resp.data);
                        if (resp.data.length !== 0) {
                            $("#availRooms").html("");
                            $('#shCheckAvailForm').hide();
                            $('#shAvailRooms').show();
                            $.get("tpl/rooms.php", function(dataTpl) {
                                $.each(resp.data, function(key, value) {
                                    var tmpTpl = dataTpl.replace('#pilltab1', '#pilltab1_' + value.roomtypeID);
                                    tmpTpl = tmpTpl.replace('#pilltab2', '#pilltab2_' + value.roomtypeID);
                                    tmpTpl = tmpTpl.replace('#pilltab3', '#pilltab3_' + value.roomtypeID);
                                    tmpTpl = tmpTpl.replace(/myBtn/g, 'myBtn_' + value.roomtypeID);
                                    tmpTpl = tmpTpl.replace(/modal-room/g, 'modal-room_' + value.roomtypeID);
                                    tmpTpl = tmpTpl.replace(/close/g, 'close_' + value.roomtypeID);
                                    tmpTpl = tmpTpl.replace(/slider/g, 'slider_' + value.roomtypeID);
                                    tmpTpl = tmpTpl.replace(/control_next/g, 'control_next_' + value.roomtypeID);
                                    tmpTpl = tmpTpl.replace(/control_prev/g, 'control_prev_' + value.roomtypeID);
                                    $("#availRooms").append(tmpTpl);
                                    //$('#slider').removeAttr("id").attr('id', 'slider_' + value.roomtypeID);
                                    $('#pilltab1').removeAttr("id").attr('id', 'pilltab1_' + value.roomtypeID);
                                    $('#pilltab2').removeAttr("id").attr('id', 'pilltab2_' + value.roomtypeID);
                                    $('#pilltab3').removeAttr("id").attr('id', 'pilltab3_' + value.roomtypeID);
                                    $('#roomImg').removeAttr('id').attr('id', 'roomImg_' + value.roomtypeID);
                                    $('#calendar').removeAttr('id').attr('id', 'calendar_' + value.roomtypeID);
                                    $('.room-dummy').removeClass('room-dummy').addClass('room room_' + value.roomtypeID);
                                    $('.room-dummy2').removeClass('room-dummy2').addClass('room room_' + value.roomtypeID);
                                    $('.roomImg').removeClass('roomImg').addClass('roomImg_' + value.roomtypeID);
                                    $('.roomDesc').removeClass('roomDesc').addClass('roomDesc_' + value.roomtypeID);
                                    $('.room-header').removeClass('room-header').addClass('room-header_' + value.roomtypeID);
                                    $('.room-header-2').removeClass('room-header-2').addClass('room-header-2_' + value.roomtypeID);
                                    $('.Row1').removeClass('Row1').addClass('Row1_' + value.roomtypeID);
                                    $('.Row2').removeClass('Row2').addClass('Row2_' + value.roomtypeID);
                                    $('.Row3').removeClass('Row3').addClass('Row3_' + value.roomtypeID);
                                    $('.Row4').removeClass('Row4').addClass('Row4_' + value.roomtypeID);
                                    //$(".roomImg" + value.roomtypeID).append('<img src="'+imgHotelsURL + value.roomtypeID+"thumbnail?"+d.getTime()+'" style="width:120px;height:90px;"/>');
                                    var firstImg = true;
                                    var thump = "";
                                    var thump_lg = "";

                                    var countImg = 0;
                                    $.each(value.images, function(keyImg, valueImg) {
                                        //console.log("keyImg:: " + keyImg.length); // style="width:120px;height:90px;"
                                        //$(".roomImg" + value.roomtypeID).append('<img src="'+imgHotelsURL+ value.roomtypeID+"/thumbnail/"+valueImg+"?"+d.getTime()+'"/>');
                                        if (firstImg) {
                                            thump = '<img src="' + imgHotelsURL + value.roomtypeID + "/" + valueImg + "?" + d.getTime() + '" height="80" width="100">';
                                            thump_lg = '<img src="' + imgHotelsURL + value.roomtypeID + "/" + valueImg + "?" + d.getTime() + '" height="160" width="200">';
                                        }

                                        $(".roomImg_" + value.roomtypeID).append('<li><img src="' + imgHotelsURL + value.roomtypeID + "/" + valueImg + "?" + d.getTime() + '" height="300" width="500"></li>');
                                        countImg++;
                                    });

                                    if(countImg <= 1){
                                      //console.log('slideCount:: ' + countImg);
                                      $('span.control_prev_' + value.roomtypeID).hide();
                                      $('span.control_next_' + value.roomtypeID).hide();
                                    }

                                    $(".room-header_" + value.roomtypeID).html("<h3>" + value.roomtype_name + "</h3><br/>");
                                    $(".room-header-2_" + value.roomtypeID).html("<h3>" + value.roomtype_name + "</h3>");
                                    $("#roomImg_" + value.roomtypeID).html(thump_lg);
                                    $(".roomDesc_" + value.roomtypeID).html("<p>" + value.roomtype_descr + "</p>");

                                    var ln = value.prices.length;
                                    var mn = Math.ceil(ln/7);
                                    var keys = Object.keys(value.prices)
                                    var firstDay = new Date(Object.keys(value.prices[keys[0]]));
                                    //if (firstDay.getDay() === 6)
                                    if ((firstDay.getDay()+ln) > 7)
                                        mn++;

                                    var str = "<table>";
                                    str += "<thead><td></td><td>Sun</td><td>Mon</td><td>Tue</td><td>Wed</td><td>Thu</td><td>Fri</td><td>Sat</td></thead>";
                                    for(i=1; i <= mn;i++){
                                       str += "<tr>";
                                       str += "<td class='week_num'>WEEK " + i + "</td>";
                                       for(y=0; y <= 6;y++){
                                         str += "<td class='week_norate week_"+i+"_"+y+"_"+value.roomtypeID+" '>-</td>";
                                       }
                                       str += "</tr>";
                                    }
                                    str += "</table>";
                                    $("#pilltab2_" + value.roomtypeID).append(str);
                                    var i=0;
                                    var week = 1;
                                    var weekNum = 0;
                                    $.each(value.prices, function(key1, value1) {

                                        var d = new Date(Object.getOwnPropertyNames(value1));

                                        var price = $.map(value1, function(val, key) {
                                            return val;
                                        });

                                        $(".week_"+week+"_"+d.getDay()+"_" + value.roomtypeID).html(d.getDate() + "/" + (d.getMonth() + 1) + "<br/>" + price + value.currency);
                                        $(".week_"+week+"_"+d.getDay()+"_" + value.roomtypeID).removeClass('week_norate').addClass('week_rate');
                                        if (d.getDay() === 6)
                                            week++;
                                    });

                                    // $.each(value.prices, function(key1, value1) {
                                    //     var d = new Date(Object.getOwnPropertyNames(value1));
                                    //     $(".Row1_" + value.roomtypeID).append("<td class='tbDate'>" + d.toLocaleString(lang, {
                                    //         weekday: 'short'
                                    //     }) + "<br/>" + d.getDate() + "/" + (d.getMonth() + 1) + "</td>");
                                    //     var price = $.map(value1, function(val, key) {
                                    //         return val;
                                    //     });
                                    //     //$(".Row2_" + value.roomtypeID).append("<td class='tbPrice'>"+Object.values(value1)+value.currency+"</td>");
                                    //     $(".Row2_" + value.roomtypeID).append("<td class='tbPrice'>" + price + value.currency + "</td>");
                                    // });
                                    // var rooms='<label>Rooms:</label><select id="booking-form-rooms-num-'+value.roomtypeID+'">'
                                    //           +'<option value="1">1</option>'
                                    // 					+'<option value="2">2</option>'
                                    // 					+'</select><br/>';
                                    $(".Row3_" + value.roomtypeID).append("Total Cost " + value.total_cost + value.currency + "<br/>");
                                    // $(".Row3_" + value.roomtypeID).append(rooms+"<button class='btn btn-primary bookRoom' name='booking-form-check' data-roomID='"+value.roomtypeID
                                    // 					 +"' data-currency='"+value.currency+"' data-total='"+ value.total_cost+"' data-room-name='"+ value.roomtype_name+"'>Reserve</button></span>");
                                    $(".Row3_" + value.roomtypeID).append("<br/><button class='btn btn-primary bookRoom' name='booking-form-check' data-roomID='" + value.roomtypeID +
                                        "' data-currency='" + value.currency + "' data-thump='" + thump + "' data-total='" + value.total_cost + "' data-room-name='" + value.roomtype_name +
                                        "' data-desc='" + value.roomtype_descr + "'>Reserve</button></span>");

                                    var avail = (getDates($('#booking-form-from').val(),$('#booking-form-to').val())).toString();
                                    //console.log('avail:: ' + avail);
                                    $('#calendar_' + value.roomtypeID).attr('data-date', avail.toString());
                                    $('#calendar_' + value.roomtypeID).datepicker({ format: 'yyyy-mm-dd', startDate: '+1d' });
                                    //$('#calendar_' + value.roomtypeID).datepicker('setEndDate', $('#booking-form-to').val());
                                    //$('#calendar_' + value.roomtypeID).datepicker('setStartDate',$('#booking-form-from').val());
                                  //  $('#calendar_' + value.roomtypeID).datepicker('update');
                                    // $('#calendar_' + value.roomtypeID).eCalendar({eventTitle: 'Availability',events: [
                                    //     {type:'booked', title: 'Evento de Abertura', description: 'Abertura das Olimpíadas Rio 2016', datetime: new Date(2016, new Date().getMonth(), 12, 17)},
                                    //     {type:'not_avail', title: 'Tênis de Mesa', description: 'BRA x ARG - Semifinal', datetime: new Date(2016, new Date().getMonth(), 23, 16)},
                                    //     {type:'pending', title: 'Ginástica Olímpica', description: 'Classificatórias de equipes', datetime: new Date(2016, new Date().getMonth(), 31, 16)}
                                    // ], bookings: [{startDate: $("#booking-form-from").val(), endDate: $("#booking-form-to").val()}]});
                                });

                                var check_in = new Date($('#booking-form-from').val());
                                var check_out = new Date($('#booking-form-to').val());
                                var timeDiff = Math.abs(check_out.getTime() - check_in.getTime());
                                var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));

                                $('.check-in-out').html( check_in.getDate() + "/" + (check_in.getMonth()+1) + "/" + check_in.getFullYear() + " - "
                                  + (check_out.getDate()+1) + "/" + check_out.getMonth() + "/" + check_out.getFullYear());

                            });

                            $('.c-event-grid').hide();
                        } else {
                            $('#warningTxt').html("No rooms available with your criterials");
                            $('.bg-warning').show();
                        }


                    } else {
                        //console.log("resp::false:: " + JSON.stringify(resp));
                        $('#warningTxt').html(resp.responce);
                        $('.bg-warning').show();
                    }

                    $body.removeClass("loading");
                },
                error: function(err) {
                    //console.log("err " + JSON.stringify(err));
                    $('#dangerTxt').html("Error: unenable to find available rooms...");
                    $('.bg-danger').show();
                    $body.removeClass("loading");
                }
            });
            //console.log(JSON.stringify(data));
            //ogin(data);
        }
    });


    function getDates(startDate, endDate, interval) {

     //addFn = addFn || Date.prototype.addDays;
     interval = interval || 1;

     var retVal = [];
     var current = new Date(startDate);
     var end = new Date(endDate);

     while (current <= end) {
      var xday = current.getFullYear() + "-" + (current.getMonth() + 1) + "-" + current.getDate();
      retVal.push(xday.toString());
      current.setDate(current.getDate() + 1);
     }

     return retVal;

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
                    } else if ($(this).attr("name").indexOf('telephone') != -1) {
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
    $('#shCheckAvailForm').hide();
    $('#shAvailRooms').hide();
    $('#shCheckout').hide();
    $('#checkoutRooms').hide();
    $('#checkoutForm').hide();
    $('#shBookingConfirmation').hide();
}
// Usage (reload a css)
// var restyled = 'css/custom.booking.css';
// freshStyle(restyled);
function freshStyle(stylesheet) {
    $('#mainStyle').attr('href', stylesheet);
}

// Function that validates email address through a regular expression.
function validateEmail(sEmail) {
    var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
    if (filter.test(sEmail)) {
        return true;
    } else {
        return false;
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

function numweek(d) {
    var day = d.getDay();
    if (day == 0)
        day = 7;
    d.setDate(d.getDate() + (4 - day));
    var year = d.getFullYear();
    var ZBDoCY = Math.floor((d.getTime() - new Date(year, 0, 1, -6)) / 86400000);
    return 1 + Math.floor(ZBDoCY / 7);
}

function getSunday(d) {
  d = new Date(d);
  var day = d.getDay(),
      diff = d.getDate() - day + (day == 0 ? -6:0); // adjust when day is sunday
  return new Date(d.setDate(diff));
}

function getSaturday(d) {
  d = new Date(d);
  var day = d.getDay(),
      diff = d.getDate() - day + (day == 0 ? -6:6); // adjust when day is Saturday
  return new Date(d.setDate(diff));
}

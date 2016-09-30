angular.module('app.bookingsCtrl', [])


.controller('bookingsCtrl', ['$scope', '$http', function($scope, $http) {
    $scope.authData = {};
    $scope.authData.email = window.localStorage.email;
    $scope.authData.token = window.localStorage.token;



    //-----FUNCTIONS------
    //function - getAllProperties
    $scope.getAllProperties = function() {
        $scope.authData.action = 'show_properties';
        //get all properties
        $http({
            method: "POST",
            url: serviceURL + "properties.php",
            data: $scope.authData
        })

        .then(function successPost(response) {
            if (response.data.resp == "true") {
                console.log(response);
                $scope.properties = response.data.data;
            } else {
                console.log('login failed');
            }
        })
    };

    //function - propertyChanged
    $scope.propertyChanged = function(selectedProperty) {
        //if is used so this function is not run when "select property is selected"
        if (selectedProperty.length) {
            //call function getPropertyBookings
            $scope.getPropertyBookings(selectedProperty);
        }
    };

    //function - getPropertyBookings
    $scope.getPropertyBookings = function(selectedProperty) {
        $scope.authData.action = 'show_property_bookings';
        $scope.authData.propertyID = selectedProperty;
        $scope.authData.status = "1";

        //get all bookings for selected Property
        $http({
            method: "POST",
            url: serviceURL + "bookings.php",
            data: $scope.authData
        })

        .then(function successPost(response) {
            if (response.data.resp == "true") {
                console.log(response);
                $scope.bookings = response.data.data

            } else {
                console.log('login failed');
            }
        })
    };

    //function - bookingDetails
    $scope.bookingDetails = function(bookingID) {
        $scope.booking = {};
        $scope.authData.action = 'show_booking';
        $scope.authData.bookingID = bookingID;

        $http({
            method: "POST",
            url: serviceURL + "bookings.php",
            data: $scope.authData
        })

        .then(function successPost(response) {
            if (response.data.resp == "true") {
                console.log("Booking details");
                console.log(response);
                $scope.booking.address = response.data.data.address;
                $scope.booking.currency = response.data.data.bookingCurrency;
                $scope.booking.status = response.data.data.bookingStatus;
                $scope.booking.totalPrice = response.data.data.bookingTotalPrice;
                $scope.booking.origin = response.data.data.booking_origin;
                $scope.booking.checkIn = response.data.data.checkin;
                $scope.booking.checkOut = response.data.data.checkout;
                $scope.booking.country = response.data.data.country;
                $scope.booking.email = response.data.data.email;
                $scope.hotelServices = response.data.data.hotelServices; //array
                $scope.booking.lastName = response.data.data.lastname;
                $scope.booking.name = response.data.data.name;
                $scope.booking.notes = response.data.data.notes;
                $scope.booking.orderDate = response.data.data.orderDate;
                $scope.booking.paymentMethod = response.data.data.paymentMethod;
                $scope.booking.paymentReceipt = response.data.data.paymentReceipt;
                $scope.booking.phone = response.data.data.phone;
                $scope.booking.pin = response.data.data.pin;
                $scope.booking.propertyID = response.data.data.propertyID;
                $scope.booking.reservationCode = response.data.data.reservationCode;
                $scope.roomServices = response.data.data.roomServices; //array
                $scope.rooms = response.data.data.rooms; //array
                $scope.booking.reservationCode = response.data.data.reservationCode;
                $scope.booking.reservationCode = response.data.data.reservationCode;
                $scope.booking.roomsTotalPrice = response.data.data.roomsTotalPrice;
                $scope.booking.city = response.data.data.city;


            } else {
                console.log('login failed');
            }
        })

    };


    //--------CALL FUNCTIONS HERE-------
    $scope.getAllProperties();



}])

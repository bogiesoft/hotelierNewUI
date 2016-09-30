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
    $scope.propertyChanged = function(selectedProperty){
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
    $scope.bookingDetails = function(bookingID){
      $scope.authData.action = 'show_booking';
      $scope.authData.bookingID = bookingID;

      $http({
        method: "POST",
        url: serviceURL + "bookings.php",
        data: $scope.authData
      })

      .then(function successPost(response) {
          if (response.data.resp == "true") {
              console.log(response);

          } else {
              console.log('login failed');
          }
      })

    };


//--------CALL FUNCTIONS HERE-------
$scope.getAllProperties();



}])

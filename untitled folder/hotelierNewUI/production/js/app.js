angular.module('app', [
    'app.login',
    'ngRoute',
    'app.roomAvailability',
    'app.bookingsCtrl'
])


//routing
.config(function($routeProvider) {
    $routeProvider
        .when("/", {
            templateUrl: templatesUrl + "main.html"
        })
        .when("/rooms-availability", {
            templateUrl: templatesUrl + "rooms-availability.html",
            controller: 'roomAvailabilityCtrl'
        })
        .when("/bookings", {
            templateUrl: templatesUrl + "bookings.html",
            controller : 'bookingsCtrl'
        })
        .when("/bookingDetails/:bookingID", {
            templateUrl: templatesUrl + "bookingDetails.html",
            controller: 'bookingDetailsCtrl'
        })
        .when("/check-in-out", {
            templateUrl: templatesUrl + "check-in-out.html"
        });
});

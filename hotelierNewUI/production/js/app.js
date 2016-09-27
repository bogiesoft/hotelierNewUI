angular.module('app', [
    'ngRoute',
    'app.login',
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
        .when("/check-in-out", {
            templateUrl: templatesUrl + "check-in-out.html"
        });
});

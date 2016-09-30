angular.module('app', [
    'ngRoute',
    'app.mainPageCtrl',
    'app.profileCtrl',
    'app.login',
    'app.bookingsCtrl'
])


//routing
.config(function($routeProvider) {
    $routeProvider
        .when("/", {
            templateUrl: templatesUrl + "main.html"
        })
        .when("/profile", {
            templateUrl: templatesUrl + "profile.html",
            controller: 'profileCtrl'
        })
        .when("/rooms-availability", {
            templateUrl: templatesUrl + "rooms-availability.html"
        })
        .when("/bookings", {
            templateUrl: templatesUrl + "bookings.html",
            controller: 'bookingsCtrl'
        })
        .when("/check-in-out", {
            templateUrl: templatesUrl + "check-in-out.html"
        });

});

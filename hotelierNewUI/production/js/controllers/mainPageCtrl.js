angular.module('app.mainPageCtrl', [])


.controller('mainPageCtrl', ['$scope', '$http', function($scope, $http) {
    $scope.user = {};

    $scope.user.name = window.localStorage.name;
    $scope.user.surname = window.localStorage.surname;
    $scope.user.email = window.localStorage.email;
    $scope.user.type = window.localStorage.type;


    $scope.logout = function(){
      //clear localStorage so user can not access data
      window.localStorage.clear();
      //redirect user to login section
      window.location = serviceURL + "/hotelier/login.html"
    }
}])

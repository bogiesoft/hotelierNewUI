angular.module('app.profileCtrl', [])


.controller('profileCtrl', ['$scope', '$http', function($scope, $http) {
  $scope.authData = {};
  $scope.authData.email = window.localStorage.email;
  $scope.authData.token = window.localStorage.token;

  $scope.showUserInfo = function(){
    $scope.authData.action = 'show_user';
    $http({
        method: 'POST',
        url: serviceURL + 'users.php',
        data: $scope.authData
    })
    .then(function(response){
      console.log(response);
      $scope.address = response.data.data.address;
      $scope.afm = response.data.data.afm;
      $scope.doy = response.data.data.doy;
      $scope.email = response.data.data.emain;
      $scope.last_login_date = response.data.data.last_login_date;
      $scope.mobile = response.data.data.mobile;
      $scope.name = response.data.data.name;
      $scope.phone = response.data.data.phone;
      $scope.postcode = response.data.data.postcode;
      $scope.surname = response.data.data.surname;
      $scope.town = response.data.data.town;
      $scope.userID = response.data.data.userID;
      $scope.registration_date = response.data.data.registration_date;
    })
  }

  //--------CALL FUNCTIONS HERE-------
  $scope.showUserInfo();


}])

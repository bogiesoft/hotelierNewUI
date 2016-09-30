angular.module('app.login', [])


.controller('loginCtrl', ['$scope', '$http', function($scope, $http) {
    if (window.localStorage.token) {
        window.location.href = 'http://localhost/www.roomier.gr/hotelierNewUI/production/index.html';
    }

    $scope.loginData = {};

    $scope.login = function() {
        $scope.loginData.action = 'login';
        console.log('Doing login', $scope.loginData);

        //sending data for login via
        $http({
            method: "POST",
            url: serviceURL + "login.php",
            data: $scope.loginData
        })

        .then(function successPost(response) {

                if (response.data.resp == 'true') {
                    console.log('login successfull');
                    window.localStorage.token = response.data.data.token;
                    window.localStorage.userID = response.data.data.userID;
                    window.localStorage.name = response.data.data.name;
                    window.localStorage.surname = response.data.data.surname;
                    window.localStorage.type = response.data.data.type;
                    window.localStorage.email = $scope.loginData.email;

                    console.log(window.localStorage);
                    window.location.href = 'http://localhost/www.roomier.gr/hotelierNewUI/production/index.html';

                } else {
                    console.log('login failed');
                }
            },

            //if POST request fails
            function failedPOST(response) {
                console.log('POST failed');
                console.log(response.data);
            });
    };

}])

angular.module('app.tableDirective', [])

.directive("tableDirective", [function() {
    return {
        restrict: "A",
        link: function(scope, elem, attrs) {
          $().DataTable();
        }
    }
}]);

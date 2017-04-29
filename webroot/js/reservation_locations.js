/**
 * @fileoverview BlockRolePermissions Javascript
 * @author nakajimashouhei@gmail.com (Shohei Nakajima)
 */


/**
 * LocationRolePermissions Javascript
 *
 * @param {string} Controller name
 * @param {function($scope)} Controller
 */
NetCommonsApp.controller('LocationRolePermissions', ['$scope', function($scope) {

  /**
   * initialize
   *
   * @return {void}
   */
  $scope.initializeRoles = function(data) {
    $scope.roles = data.roles;
  };

  /**
   * Click role
   *
   * @return {void}
   */
  $scope.clickRole = function($event, roleKey) {
    var baseRole = $scope.roles[roleKey];

    angular.forEach($scope.roles, function(role) {
      var element = $('input[type="checkbox"]' +
                      '[name="data[ReservationLocationReservable]' +
                      '[' + role['roleKey'] + ']' +
                      '[value]"]');

      if (! $event.currentTarget.checked) {
        if (baseRole['level'] > role['level']) {
          if (! angular.isUndefined(element[0]) && ! element[0].disabled) {
            element[0].checked = false;
          }
        }
      } else {
        if (baseRole['level'] < role['level']) {
          if (! angular.isUndefined(element[0]) && ! element[0].disabled) {
            element[0].checked = true;
          }
        }
      }
    });
  };

}]);

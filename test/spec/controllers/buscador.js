'use strict';

describe('Controller: BuscadorCtrl', function () {

  // load the controller's module
  beforeEach(module('rescateApp'));

  var BuscadorCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    BuscadorCtrl = $controller('BuscadorCtrl', {
      $scope: scope
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(scope.awesomeThings.length).toBe(3);
  });
});

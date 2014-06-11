'use strict';

describe('Controller: MiembrosIdCtrl', function () {

  // load the controller's module
  beforeEach(module('rescateApp'));

  var MiembrosIdCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    MiembrosIdCtrl = $controller('MiembrosIdCtrl', {
      $scope: scope
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(scope.awesomeThings.length).toBe(3);
  });
});

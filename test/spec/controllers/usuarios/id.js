'use strict';

describe('Controller: UsuariosIdCtrl', function () {

  // load the controller's module
  beforeEach(module('elRescateApp'));

  var UsuariosIdCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    UsuariosIdCtrl = $controller('UsuariosIdCtrl', {
      $scope: scope
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(scope.awesomeThings.length).toBe(3);
  });
});

'use strict';

describe('Controller: ReportesAvisitarCtrl', function () {

  // load the controller's module
  beforeEach(module('rescateApp'));

  var ReportesAvisitarCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    ReportesAvisitarCtrl = $controller('ReportesAvisitarCtrl', {
      $scope: scope
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(scope.awesomeThings.length).toBe(3);
  });
});

'use strict';

describe('Directive: goBack', function () {

  // load the directive's module
  beforeEach(module('rescateApp'));

  var element,
    scope;

  beforeEach(inject(function ($rootScope) {
    scope = $rootScope.$new();
  }));

  it('should make hidden element visible', inject(function ($compile) {
    element = angular.element('<go-back></go-back>');
    element = $compile(element)(scope);
    expect(element.text()).toBe('this is the goBack directive');
  }));
});

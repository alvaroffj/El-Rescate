'use strict';

describe('Directive: toggleMinNav', function () {

  // load the directive's module
  beforeEach(module('rescateApp'));

  var element,
    scope;

  beforeEach(inject(function ($rootScope) {
    scope = $rootScope.$new();
  }));

  it('should make hidden element visible', inject(function ($compile) {
    element = angular.element('<toggle-min-nav></toggle-min-nav>');
    element = $compile(element)(scope);
    expect(element.text()).toBe('this is the toggleMinNav directive');
  }));
});

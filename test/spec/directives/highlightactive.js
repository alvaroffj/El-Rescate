'use strict';

describe('Directive: highlightActive', function () {

  // load the directive's module
  beforeEach(module('rescateApp'));

  var element,
    scope;

  beforeEach(inject(function ($rootScope) {
    scope = $rootScope.$new();
  }));

  it('should make hidden element visible', inject(function ($compile) {
    element = angular.element('<highlight-active></highlight-active>');
    element = $compile(element)(scope);
    expect(element.text()).toBe('this is the highlightActive directive');
  }));
});

'use strict';

describe('Service: reportes', function () {

  // load the service's module
  beforeEach(module('rescateApp'));

  // instantiate service
  var reportes;
  beforeEach(inject(function (_reportes_) {
    reportes = _reportes_;
  }));

  it('should do something', function () {
    expect(!!reportes).toBe(true);
  });

});

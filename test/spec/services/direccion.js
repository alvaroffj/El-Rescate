'use strict';

describe('Service: direccion', function () {

  // load the service's module
  beforeEach(module('rescateApp'));

  // instantiate service
  var direccion;
  beforeEach(inject(function (_direccion_) {
    direccion = _direccion_;
  }));

  it('should do something', function () {
    expect(!!direccion).toBe(true);
  });

});

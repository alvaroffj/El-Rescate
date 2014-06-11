'use strict';

describe('Service: miembros', function () {

  // load the service's module
  beforeEach(module('rescateApp'));

  // instantiate service
  var miembros;
  beforeEach(inject(function (_miembros_) {
    miembros = _miembros_;
  }));

  it('should do something', function () {
    expect(!!miembros).toBe(true);
  });

});

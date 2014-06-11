'use strict';

describe('Service: buscador', function () {

  // load the service's module
  beforeEach(module('rescateApp'));

  // instantiate service
  var buscador;
  beforeEach(inject(function (_buscador_) {
    buscador = _buscador_;
  }));

  it('should do something', function () {
    expect(!!buscador).toBe(true);
  });

});

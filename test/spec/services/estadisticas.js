'use strict';

describe('Service: estadisticas', function () {

  // load the service's module
  beforeEach(module('rescateApp'));

  // instantiate service
  var estadisticas;
  beforeEach(inject(function (_estadisticas_) {
    estadisticas = _estadisticas_;
  }));

  it('should do something', function () {
    expect(!!estadisticas).toBe(true);
  });

});

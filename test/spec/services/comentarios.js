'use strict';

describe('Service: comentarios', function () {

  // load the service's module
  beforeEach(module('rescateApp'));

  // instantiate service
  var comentarios;
  beforeEach(inject(function (_comentarios_) {
    comentarios = _comentarios_;
  }));

  it('should do something', function () {
    expect(!!comentarios).toBe(true);
  });

});

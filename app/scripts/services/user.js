'use strict';

angular.module('rescateApp')
	.service('User', function User() {
		var usuario = {
			usuario_id: 0,
			usuario_nom: '',
			usuario_ape: ''
		};
		return usuario;
	});

'use strict';

angular.module('rescateApp')
  .controller('LoginCtrl', function ($scope, $location, $modal, User, login, ngProgressLite) {
    $scope.awesomeThings = [
      'HTML5 Boilerplate',
      'AngularJS',
      'Karma'
    ];

    $scope.cargando = true;
    $scope.log = {};
    $scope.isLogged = false;
	
	$scope.doLogin = function() {
		if($scope.loginForm.$valid) {
			$scope.cargando = true;
			$scope.isLogged = true;
			login.login($scope.log, function(d) {
				$scope.cargando = false;
				if(d.usuario_id && d.usuario_id>0) {
					User.usuario_id = d.usuario_id;
					location.reload();
				} else {
					showNotificacion("Usuario o contraseña incorrectos");
					$scope.isLogged = false;
				}
			});
		}
	}

	login.fetchMisiones(function(d) {
		$scope.misiones = d;
		$scope.cargando = false;
	});

	$scope.$watch('cargando', function(c) {
		if(c) ngProgressLite.start();
		else ngProgressLite.done();
	});

	$scope.registrar = function() {
        var modalInstance = $modal.open({
            templateUrl: 'registro.html',
            controller: registroCtrl,
            resolve: {
                misiones: function() {
                    return $scope.misiones;
                }
            },
            backdrop: 'static',
            windowClass: 'modal-sm'
        });

        modalInstance.result.then(function (res) {
        	$scope.log.user = res.usuario_user;
        }, function () {});
    }

    $scope.recuperar = function() {
        var modalInstance = $modal.open({
            templateUrl: 'recuperar.html',
            controller: recuperarCtrl,
            resolve: {},
            backdrop: 'static',
            windowClass: 'modal-sm'
        });

        modalInstance.result.then(function (res) {
        	$scope.log.user = res.usuario_user;
        }, function () {});
    }

});

var recuperarCtrl = ['$scope', '$modalInstance', 'login', function ($scope, $modalInstance, login) {
    $scope.cargando = false;
    $scope.reg = {};

    $scope.ok = function () {
        $scope.cargando = true;
    };

    $scope.cancel = function () {
    	if(!$scope.cargando) $modalInstance.dismiss('cancel');
    };

    $scope.doRecuperar = function(form) {
		if(form.$valid) {
			$scope.cargando = true;
			login.recuperar($scope.reg, function(d) {
				$scope.cargando = false;
				showNotificacion(d.MENSAJE);
				if(d.ERROR == 0) {
        			$modalInstance.close($scope.reg);
					$scope.reg = {};
				}
			});
		}
	}
}];

var registroCtrl = ['$scope', '$modalInstance', 'misiones', 'login', function ($scope, $modalInstance, misiones, login) {
    $scope.cargando = false;
    $scope.reg = {};
    $scope.misionero = false;
    $scope.misiones = misiones;

    $scope.ok = function () {
        $scope.cargando = true;
    };

    $scope.cancel = function () {
    	if(!$scope.cargando) $modalInstance.dismiss('cancel');
    };

    $scope.doRegistro = function(form) {
		if(form.$valid) {
			$scope.cargando = true;
			login.registro($scope.reg, function(d) {
				$scope.cargando = false;
				showNotificacion(d.MENSAJE);
				if(d.ERROR == 0) {
        			$modalInstance.close($scope.reg);
					$scope.login = true;
					$scope.reg = {};
				}
			});
		}	
	}

    $scope.$watch('reg.usuario_user', function(d) {
		if(d) {
			var aux = d.split('@');
			if(aux.length == 2 && aux[1] == 'myldsmail.net') {
				$scope.misionero = true;
			} else {
				$scope.misionero = false;
			}
		} else  {
			$scope.misionero = false;
		}
	});
}];

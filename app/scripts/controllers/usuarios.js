'use strict';

angular.module('rescateApp')
  .controller('UsuariosCtrl', function ($scope, $filter, $location, $stateParams, $modal, User, estadisticas, ngProgressLite) {
    $scope.acceso = User.acceso;
    $scope.User = User;
    $scope.ascDes = 1;
	$scope.orden = "";
	$scope.currOrden = "";

	$scope.setOrden = function(ord) {
		if(ord != $scope.currOrden) {
			$scope.orden = "-"+ord;
			$scope.ascDes = -1;
			$scope.currOrden = ord;
		} else {
			$scope.ascDes = $scope.ascDes*-1;
			$scope.orden = ($scope.ascDes>0)?ord:"-"+ord;
		}
	}

	$scope.flechaOrden = function() {
		var res = ($scope.ascDes<0)?"fa fa-angle-down":"fa fa-angle-up";
		return res;
	}

	$scope.isColActive = function(ord) {
		return ($scope.currOrden == ord);
	}

	$scope.cargar = function() {
		// console.log($scope.carga);
		if($scope.carga.area_id != '' || $scope.carga.mision_id != '' || $scope.carga.estaca_id != '' || $scope.carga.barrio_id != '') {
			$scope.cargando = true;
			var par = {
		        get:'usuarios', 
		        params: {
		        	area_id: $scope.carga.area_id,
		        	mision_id: $scope.carga.mision_id,
		        	estaca_id: $scope.carga.estaca_id,
		        	barrio_id: $scope.carga.barrio_id
		        }
		    }
		    var aux = angular.fromJson($scope.cargaAux);
	    	if($scope.carga.barrio_id != '') {
	    		$scope.nivel = "barrio";
				$scope.unidad = $scope.carga.barrio_id;
	    		$scope.titulo = " / Barrio "+aux.barrio_nom;
	    	} else if($scope.carga.estaca_id != '') {
	    		$scope.nivel = "estaca";
				$scope.unidad = $scope.carga.estaca_id;
	    		$scope.titulo = " / Estaca "+aux.estaca_nom;
	    	} else if($scope.carga.mision_id != '') {
	    		$scope.nivel = "mision";
				$scope.unidad = $scope.carga.mision_id;
	    		$scope.titulo = " / Misión "+aux.mision_nom;
	    	} else if($scope.carga.area_id) {
	    		$scope.nivel = "area";
				$scope.unidad = $scope.carga.area_id;
	    		$scope.titulo = " / Area "+aux.area_nom;
	    	}

	    	$location.path("/usuarios/"+$scope.nivel+"/"+$scope.unidad);

		    estadisticas.get(par, function(d) {
		    	$scope.cargando = false;
		    	if(!angular.isUndefined(d)) {
		    		$scope.usuarios = d.usuarios;
		    	}
		    });
		}
	}

	$scope.registrar = function() {
        var modalInstance = $modal.open({
            templateUrl: 'registro.html',
            controller: registroCtrl,
            resolve: {
                acceso: function() {
                    return $scope.acceso;
                }
            },
            backdrop: 'static',
            windowClass: 'modal-sm'
        });

        modalInstance.result.then(function (res) {
        	$scope.log.user = res.usuario_user;
        }, function () {});
    }

	if($stateParams.nivel && $stateParams.unidad) {
		$scope.nivel = $stateParams.nivel;
		$scope.unidad = $stateParams.unidad;
		if($stateParams.nivel == "barrio") {
			$scope.carga.barrio_id = $stateParams.unidad;
			var res = $filter("filter")($scope.acceso.barrio, {barrio_id: $stateParams.unidad});
		} else if($stateParams.nivel == "estaca") {
			$scope.carga.estaca_id = $stateParams.unidad;
			var res = $filter("filter")($scope.acceso.estaca, {estaca_id: $stateParams.unidad});
		} else if($stateParams.nivel == "mision") {
			$scope.carga.mision_id = $stateParams.unidad;
			var res = $filter("filter")($scope.acceso.mision, {mision_id: $stateParams.unidad});
		} else if($stateParams.nivel == "area") {
			$scope.carga.area_id = $stateParams.unidad;
			var res = $filter("filter")($scope.acceso.area, {area_id: $stateParams.unidad});
		}

		$scope.cargaAux = angular.toJson(res[0]);

		var par = {
	        get:'usuarios', 
	        params: {
	        	area_id: $scope.carga.area_id,
	        	mision_id: $scope.carga.mision_id,
	        	estaca_id: $scope.carga.estaca_id,
	        	barrio_id: $scope.carga.barrio_id
	        }
	    }

		$scope.cargando = true;
	    estadisticas.get(par, function(d) {
	    	$scope.cargando = false;
	    	if(!angular.isUndefined(d)) {
	    		$scope.usuarios = d.usuarios;
	    	}
	    });
	}

	$scope.$watch('cargaAux', function(d) {
		if(!angular.isUndefined(d) && d.length > 0) {
			// console.log(d);
			var aux = angular.fromJson(d);
			// console.log(aux);
			if(aux.barrio_id) {
				$scope.carga.barrio_id = aux.barrio_id;
				$scope.carga.estaca_id = aux.estaca_id;
				$scope.carga.mision_id = aux.mision_id;
				$scope.carga.area_id = "";
			} else if(aux.estaca_id) {
				$scope.carga.estaca_id = aux.estaca_id;
				$scope.carga.mision_id = aux.mision_id;
				$scope.carga.area_id = "";
				$scope.carga.barrio_id = "";
			} else if(aux.mision_id) {
				$scope.carga.mision_id = aux.mision_id;
				$scope.carga.area_id = "";
				$scope.carga.barrio_id = "";
				$scope.carga.estaca_id = "";
			} else if(aux.area_id) {
				$scope.carga.area_id = 1;
				$scope.carga.mision_id = "";
				$scope.carga.estaca_id = "";
				$scope.carga.barrio_id = "";
			}
			// console.log($scope.carga);
			if($scope.cargaInicial) {
				$scope.cargaInicial = false;
				$scope.cargarEstadisticas();
			}
		} else {
			$scope.carga = {};
		}
	});

	$scope.$watch('cargando', function(c) {
		if(c) ngProgressLite.start();
		else ngProgressLite.done();
	});
});

var registroCtrl = ['$scope', '$modalInstance', 'acceso', 'login', function ($scope, $modalInstance, acceso, login) {
    $scope.cargando = false;
    $scope.reg = {};
    $scope.acceso = acceso;

    $scope.cancel = function () {
    	if(!$scope.cargando) $modalInstance.dismiss('cancel');
    };

    $scope.doRegistro = function(form) {
		if(form.$valid) {
			$scope.cargando = true;
			var user = {
				'permisos_usuarios': $scope.reg.permisos_usuarios,
				'usuario_user': $scope.reg.usuario_user,
				'usuario_nom': $scope.reg.usuario_nom,
				'usuario_ape': $scope.reg.usuario_ape
			};
			var aux = angular.fromJson($scope.reg.unidad);
			if(aux.barrio_id) {
				user.unidad = aux.barrio_id;
				user.nivel = 1;
			} else if(aux.estaca_id) {
				user.unidad = aux.estaca_id;
				user.nivel = 2;
			} else if(aux.mision_id) {
				user.unidad = aux.mision_id;
				user.nivel = 3;
			} else if(aux.area_id) {
				user.nivel = 0;
			}
			login.registroFull(user, function(d) {
				$scope.cargando = false;
				showNotificacion(d.MENSAJE);
				if(d.ERROR == 0) {
					$scope.reg = {};
				}
			});
		}	
	}
}];

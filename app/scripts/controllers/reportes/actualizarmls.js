'use strict';

angular.module('rescateApp')
	.controller('ReportesActualizarmlsCtrl', function ($scope, $filter, User, reportes, miembros, ngProgressLite) {
		$scope.awesomeThings = [
			'HTML5 Boilerplate',
			'AngularJS',
			'Karma'
		];

		$scope.carga = {};
		$scope.acceso = User.acceso;

		$scope.cargarReporte = function() {
			if($scope.carga.barrio_id != '') {
				$scope.nivel = "barrio";
				$scope.unidad = $scope.carga.barrio_id;
				$scope.cargando = true;
				var data = {
					'reporte': 'actualizarmls',
					'barrio_id': $scope.carga.barrio_id
				};

			    reportes.fetchReporte(data, function(d) {
			    	$scope.cargando = false;
			    	$scope.miembros = d;
			    });
			}
		}

		$scope.actualizar = function(persona_id) {
			var data = {
                do:'mlsactualizar', 
                params: {
                    id: persona_id
                }
            }
            var res = $filter("filter")($scope.miembros, {persona_id: persona_id}, true);
    		res[0].actualizado = true;
            miembros.do(data, function(d) {
            	if(d.ERROR != 0) {
            		res[0].actualizado = false;
            	}
            });
		}

		$scope.$watch('cargaAux', function(d) {
			if(!angular.isUndefined(d) && d.length > 0) {
				var aux = angular.fromJson(d);
				if(aux.barrio_id) {
					$scope.carga.barrio_id = aux.barrio_id;
					$scope.carga.estaca_id = aux.estaca_id;
					$scope.carga.mision_id = aux.mision_id;
				} else if(aux.estaca_id) {
					$scope.carga.estaca_id = aux.estaca_id;
					$scope.carga.mision_id = aux.mision_id;
					$scope.carga.barrio_id = "";
				} else if(aux.mision_id) {
					$scope.carga.mision_id = aux.mision_id;
					$scope.carga.barrio_id = "";
					$scope.carga.estaca_id = "";
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

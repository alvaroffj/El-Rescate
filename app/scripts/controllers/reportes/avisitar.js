'use strict';

angular.module('rescateApp')
	.controller('ReportesAvisitarCtrl', function ($scope, User, reportes, ngProgressLite) {
		$scope.awesomeThings = [
			'HTML5 Boilerplate',
			'AngularJS',
			'Karma'
		];
		$scope.carga = {};
		$scope.acceso = User.acceso;
		$scope.ascDes = 1;
		$scope.orden = "";
		$scope.currOrden = "";
		$scope.classFuente = ['','label-primary', 'label-success', 'label-warning', 'label-info'];
		$scope.filtroPreAux = {
	    	'all': true
	    }

	    $scope.printDiv = function(divName) {
		  var printContents = document.getElementById(divName).innerHTML;
	     var originalContents = document.body.innerHTML;

	     document.body.innerHTML = printContents;

	     window.print();

	     document.body.innerHTML = originalContents;
		} 

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

		$scope.cargarReporte = function() {
			if($scope.carga.barrio_id != '') {
				$scope.cargando = true;
				var data = {
					'reporte': 'avisitar',
					'barrio_id': $scope.carga.barrio_id
				};

			    reportes.fetchReporte(data, function(d) {
			    	$scope.cargando = false;
			    	$scope.miembros = d;
			    });
			}
		}

		$scope.$watch('filtroPreAux', function(d) {
			$scope.filtroPre = {};
			// console.log(d);
			if(d.all) {
		 		$scope.filtroPre.all = true;
		 	}
		 	if(d.persona_cabezahogar) {
		 		$scope.filtroPre.persona_cabezahogar = true;
		 	}
		 	if(d.persona_investido) {
		 		$scope.filtroPre.persona_investido = true;
		 	}
		 	if(d.persona_sellado) {
		 		$scope.filtroPre.persona_sellado = true;
		 	}
		 	if(d.persona_exmisionero) {
		 		$scope.filtroPre.persona_exmisionero = true;
		 	}
		 	if(d.sin_rut) {
		 		$scope.filtroPre.filtro = {
		 			'persona_rut': null,
		 			'persona_no_rut': false,
		 			'persona_adulto': true
		 		}
		 	}
		 	if(d.sin_dir_mls) {
		 		$scope.filtroPre.filtro = {
		 			'direccion_mls': false,
		 			'persona_adulto': true
		 		}
		 	}
		 	$scope.filtroPre.persona_ofisac = [];
		 	if(d.oficio_n) $scope.filtroPre.persona_ofisac.push("N");
		 	if(d.oficio_d) $scope.filtroPre.persona_ofisac.push("D");
		 	if(d.oficio_m) $scope.filtroPre.persona_ofisac.push("M");
		 	if(d.oficio_p) $scope.filtroPre.persona_ofisac.push("P");
		 	if(d.oficio_e) $scope.filtroPre.persona_ofisac.push("E");
		 	if(d.oficio_s) $scope.filtroPre.persona_ofisac.push("S");
		 	if($scope.filtroPre.persona_ofisac.length == 0) delete $scope.filtroPre.persona_ofisac;
		}, true);

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

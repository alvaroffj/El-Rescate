'use strict';

angular.module('rescateApp')
  .controller('MiembrosCtrl', function ($rootScope, $scope, $location, $filter, $stateParams, miembros, User, ngProgressLite) {
    $scope.awesomeThings = [
      'HTML5 Boilerplate',
      'AngularJS',
      'Karma'
    ];

    $scope.acceso = User.acceso;
    $scope.ascDes = 1;
	$scope.orden = "";
	$scope.currOrden = "";
	$scope.bus = {};
    $scope.bus.m = 20;
    $scope.classFuente = ['','label-primary', 'label-success', 'label-warning', 'label-info'];
    // $scope.carga = User.carga;
    $scope.carga = {}
    $scope.filtroAut = null;
    $scope.filtros = ['Cabeza de hogar', 'Poseedores del Sacerdocio'];
    $scope.filtrosAttr = [
    	{
	    	'persona_cabezahogar': true
	    },
	    {
	    	'persona_ofisac': 'E'
	    }
    ];

    $scope.filtroPreAux = {
    	'all': true
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

	$scope.tooltipDirecciones = function(p) {
		var txt = "", aux;
		if(p.direccion.length > 0) {
			for(var i=0; i<p.direccion.length; i++) {
				aux = p.direccion[i];
				if(i) txt = txt + "<br />";
				txt = txt + "<b>" + aux.direccion_fuente + "</b>: " + aux.direccion + ", " + aux.comuna_nom + ", " + aux.region_nom;
				aux = null;
			}
			txt = "<p style='text-align:left;margin:0px;'>" + txt + "</p>";
		} else {
			txt = "No hay direcciones";
		}

		return txt;
	}

	$scope.cargarMiembros = function() {
		if($scope.carga.barrio_id) {
			$scope.nivel = "barrio";
			$scope.unidad = $scope.carga.barrio_id;
			$location.path("/miembros/barrio/"+$scope.carga.barrio_id);
		} else if($scope.carga.estaca_id) {
			$scope.nivel = "estaca";
			$scope.unidad = $scope.carga.estaca_id;
			$location.path("/miembros/estaca/"+$scope.carga.estaca_id);
		} else if($scope.carga.mision_id) {
			$scope.nivel = "mision";
			$scope.unidad = $scope.carga.mision_id;
			$location.path("/miembros/mision/"+$scope.carga.mision_id);
		}

		$scope.cargando = true;
		$scope.miembros = [];
	    miembros.fetchAll($scope.carga, function(d) {
	    	$scope.cargando = false;
	    	$scope.miembros = d;
	    });
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
		}
		$scope.cargaAux = angular.toJson(res[0]);
		$scope.cargando = true;
	    miembros.fetchAll($scope.carga, function(d) {
	    	$scope.cargando = false;
	    	$scope.miembros = d;
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
	 	// console.log($scope.filtroPre);
	}, true);

	$scope.$watch('cargando', function(c) {
		if(c) {
			ngProgressLite.start();
		} else {
			ngProgressLite.done();	
		} 
	});
});

angular.module('rescateApp')
.filter('filterMultiple',['$filter',function ($filter) {
	return function (items, keyObj) {
		if(angular.isDefined(items)) {
			var filterObj = {
				data:items,
				filteredData:[],
				stop: false,
				applyFilter : function(obj,key) {
					if(key=="all") {
						this.filteredData = this.data;
						this.stop = true;
					} else if(!this.stop){
						var fData = [];
						if(obj || obj == "null" || obj == false) {
							var fObj = {};
							if(angular.isArray(obj)) {
								if(obj.length > 0){	
									for(var i=0;i<obj.length;i++){
										if(angular.isString(obj[i])){
											fObj[key] = obj[i];
											fData = _.union(fData, $filter('filter')(this.data,fObj));	
										}
									}
								}
							} else if(angular.isObject(obj)){
								fData = _.union(fData, $filter('filter')(this.data,obj));
							} else {
								fObj[key] = obj;
								if(obj == "null") {
									fObj[key] = null;
								}
								fData = _.union(fData, $filter('filter')(this.data,fObj));
							}						
							if(fData.length > 0){
								this.filteredData = _.union(this.filteredData, fData);
							}

						}
					}
				}
			};

			if(keyObj){
				angular.forEach(keyObj,function(obj,key){
					filterObj.applyFilter(obj,key);
				});			
			}
			return filterObj.filteredData;
		} else return null;
		
	}
}]);
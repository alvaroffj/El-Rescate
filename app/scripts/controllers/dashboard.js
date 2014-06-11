'use strict';

angular.module('rescateApp')
  .controller('DashboardCtrl', function ($scope, $filter, estadisticas, User, ngProgressLite) {
    $scope.acceso = User.acceso;
    $scope.data;
    $scope.nivelNom = ["", "Barrio", "Estaca", "Mision"];
    $scope.nivelVal = ["", "barrio_nom", "estaca_nom", "mision_nom"];
    $scope.nivelKey = ["", "barrio_id", "estaca_id", "mision_id"];
    $scope.ramaFiltro = {};

    $scope.visitasData = [];
    $scope.visitasD3Data = {};
    $scope.visitasD3 = [];
    $scope.visitasD3Data.visited = [];
    $scope.visitasD3Data.verified = [];
    $scope.visitasD3Data.mlsupdated = [];
    $scope.visitasD3Data.tomlsupdate = [];
    $scope.visitasD3 = [
    	{
    		'key': 'Visitados',
    		'color': '#8170CA',
    		'values': $scope.visitasD3Data.visited
    	},
    	{
    		'key': 'Verificados',
    		'color': '#E87352',
    		'values': $scope.visitasD3Data.verified
    	},
    	{
    		'key': 'Actualizados MLS',
    		'color': '#60CD9B',
    		'values': $scope.visitasD3Data.mlsupdated
    	},
    	{
    		'key': 'Para Actualizar MLS',
    		'color': '#31C0BE',
    		'values': $scope.visitasD3Data.tomlsupdate
    	},
    ];

    $scope.xAxisTickFormatFunction = function() {
        return function(d) {
            return d3.time.format('%d/%m/%y')(moment.unix(d).toDate());
        }
    };

    $scope.yAxisTickFormatFunction = function() {
        return function(d) {
            return Math.round(d);
        }
    };

    $scope.xFunction = function(){
        return function(d) {
            return d.key;
        };
    }
    $scope.yFunction = function(){
        return function(d) {
            return d.v;
        };
    }

    var colorArray = ['#8170CA', '#E87352', '#60CD9B', '#31C0BE', '#66b5d7', '#eec95a'];
	$scope.colorFunction = function() {
		return function(d, i) {
	    	return colorArray[i];
	    };
	}

	var colorBiArray = ['#31C0BE', '#ededed'];
	$scope.colorBi = function() {
		return function(d, i) {
	    	return colorBiArray[i];
	    };
	}

	$scope.legendColorFunction = function(){
        return function(d){
            return '#E01B5D';
        }
    };

    $scope.toolTipContentFunction = function(){
		return function(key, x, y, e, graph) {
	    	return  '<p><strong>' + key + ': </strong>' +  (x.substring(0, x.length-3)) + '</p>';
		}
	}

	$scope.cargarEstadisticas = function() {
		// console.log($scope.carga);
		if($scope.carga.area_id != '' || $scope.carga.mision_id != '' || $scope.carga.estaca_id != '' || $scope.carga.barrio_id != '') {
			$scope.cargando = true;
			var par = {
		        get:'all', 
		        params: {
		        	fecha: $filter('date')(new Date, 'yyyy-MM-dd'),
		        	area_id: $scope.carga.area_id,
		        	mision_id: $scope.carga.mision_id,
		        	estaca_id: $scope.carga.estaca_id,
		        	barrio_id: $scope.carga.barrio_id
		        }
		    }
		    var aux = angular.fromJson($scope.cargaAux);
	    	if($scope.carga.barrio_id != '') {
	    		$scope.titulo = "Barrio "+aux.barrio_nom;
	    	} else if($scope.carga.estaca_id != '') {
	    		$scope.titulo = "Estaca "+aux.estaca_nom;
	    	} else if($scope.carga.mision_id != '') {
	    		$scope.titulo = "Misión "+aux.mision_nom;
	    	} else if($scope.carga.area_id) {
	    		$scope.titulo = "Area "+aux.area_nom;
	    	}

		    estadisticas.get(par, function(d) {
		    	$scope.cargando = false;
		    	if(!angular.isUndefined(d)) {
		    		$scope.data = d.unidades;
		    		$scope.lastMod = d.lastMod;
		    		$scope.lastCom = d.lastCom;
			    	var rows = [], adultos = 0, ruts = 0, total = 0;
			    	$scope.d3Total = [];
			    	for(var i=0; i<d.unidades.length; i++) {
				        $scope.d3Total.push({key:d.unidades[i].unidad_nom, v:d.unidades[i].total*1});
				        adultos += d.unidades[i].adult*1;
				        ruts += d.unidades[i].rut*1;
				        total += d.unidades[i].total*1;
			    	}
			        $scope.d3Rut = [{key: 'Ruts', v: ruts}, {key:'Sin Rut', v:(adultos - ruts)}];
			        $scope.d3Adulto = [{key:'Adultos', v:adultos}, {key:'No Adultos', v:(total-adultos)}];
			        
			        $scope.visitasData = d.visVer;
			        $scope.visitasD3Data.visited = [];
			        $scope.visitasD3Data.verified = [];
			        $scope.visitasD3Data.mlsupdated = [];
			        $scope.visitasD3Data.tomlsupdate = [];
			        for(var i=0; i<d.visVer.length; i++) {
			        	$scope.visitasD3Data.visited.push([moment(d.visVer[i].when).unix(), d.visVer[i].visited*1]);
			        	$scope.visitasD3Data.verified.push([moment(d.visVer[i].when).unix(), d.visVer[i].verified*1]);
			        	$scope.visitasD3Data.mlsupdated.push([moment(d.visVer[i].when).unix(), d.visVer[i].mlsupdated*1]);
			        	$scope.visitasD3Data.tomlsupdate.push([moment(d.visVer[i].when).unix(), d.visVer[i].tomlsupdate*1]);
			        }
			        $scope.visitasD3 = [
			        	{
				    		'key': 'Visitados',
				    		'color': '#8170CA',
				    		'values': $scope.visitasD3Data.visited
				    	},
				    	{
				    		'key': 'Verificados',
				    		'color': '#E87352',
				    		'values': $scope.visitasD3Data.verified
				    	},
				    	{
				    		'key': 'Actualizados MLS',
				    		'color': '#60CD9B',
				    		'values': $scope.visitasD3Data.mlsupdated
				    	},
				    	{
				    		'key': 'Para Actualizar MLS',
				    		'color': '#31C0BE',
				    		'values': $scope.visitasD3Data.tomlsupdate
				    	},
			        ];
		    	}
		    });	    
		}
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
    $scope.cargaInicial = true;
    if($scope.acceso.area && $scope.acceso.area.length == 1) {
    	$scope.cargaAux = angular.toJson($scope.acceso.area[0]);
    } else if($scope.acceso.mision && $scope.acceso.mision.length == 1) {
    	$scope.cargaAux = angular.toJson($scope.acceso.mision[0]);
    } else if($scope.acceso.estaca && $scope.acceso.estaca.length == 1) {
    	$scope.cargaAux = angular.toJson($scope.acceso.estaca[0]);
    } else if($scope.acceso.barrio && $scope.acceso.barrio.length == 1) {
    	$scope.cargaAux = angular.toJson($scope.acceso.barrio[0]);
    }
  });

'use strict';

angular.module('rescateApp')
  .controller('UsuariosIdCtrl', function ($scope, $stateParams, estadisticas) {
  	$scope.modcom_tiempoD3 = [];
  	if($stateParams.id) {
    	$scope.cargando = true;
    	var par = {
    		get:'usuario', 
	        params: {
	        	id: $stateParams.id
	        }
    	}
	    estadisticas.get(par, function(d) {
            if(d!="false") {
		    	$scope.perfil = d.perfil;
		    	$scope.mod_datos = d.mod_datos;
		    	$scope.com_datos = d.com_datos;
		    	$scope.mod_datos.tiempo = d.mod_datos.tiempo;
		    	$scope.mod_tiempoD3Data = [];
		    	$scope.com_tiempoD3Data = [];
		    	for(var i=0; i<d.mod_datos.tiempo.length; i++) {
		    		$scope.mod_tiempoD3Data.push([moment(d.mod_datos.tiempo[i].modificado).unix(), d.mod_datos.tiempo[i].num_mod]);
		    	}
		    	for(var i=0; i<d.com_datos.tiempo.length; i++) {
		    		$scope.com_tiempoD3Data.push([moment(d.com_datos.tiempo[i].dia).unix(), d.com_datos.tiempo[i].num_com]);
		    	}
		    	var mod = {
		    		'key': 'Modificados',
		    		'color': '#E87352',
		    		'values': $scope.mod_tiempoD3Data
		    	};
		    	var com = {
		    		'key': 'Comentarios',
		    		'color': '#8170CA',
		    		'values': $scope.com_tiempoD3Data
		    	};
		    	if(com.values.length > 0 && mod.values.length > 0) {
		    		$scope.modcom_tiempoD3 = [com, mod];
		    	} else if(com.values.length > 0) {
		    		$scope.modcom_tiempoD3 = [com];
		    	} else if(mod.values.length > 0) {
		    		$scope.modcom_tiempoD3 = [mod];
		    	}
    	    	$scope.cargando = false;
            } else $location.path("/usuarios");
	    });
    } 

    $scope.xFunction = function(){
        return function(d) {
            return d.unidad;
        };
    }
    $scope.yFunction = function(){
        return function(d) {
            return d.num;
        };
    }

    $scope.toolTipContentFunction = function(){
		return function(key, x, y, e, graph) {
			if(x) {
	    		return  '<p><strong>' + key + ': </strong>' +  (x.substring(0, x.length-3)) + '</p>';
			} else return '';
		}
	}

    $scope.xAxisTickFormatFunction = function() {
        return function(d) {
        	if(d) 
            	return d3.time.format('%d/%m/%y')(moment.unix(d).toDate());
        	else return '';
        }
    };

    var colorArray = ['#8170CA', '#E87352', '#60CD9B', '#31C0BE', '#66b5d7', '#eec95a'];
	$scope.colorFunction = function() {
		return function(d, i) {
	    	return colorArray[i];
	    };
	}
});
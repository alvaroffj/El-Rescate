'use strict';

angular.module('rescateApp')
  .controller('BuscadorCtrl', function ($scope, buscador, direccion, ngProgressLite) {
    $scope.awesomeThings = [
      'HTML5 Boilerplate',
      'AngularJS',
      'Karma'
    ];

    $scope.ascDes = 1;
	$scope.orden = "name";
	$scope.currOrden = "name";
	$scope.bus = {};
    $scope.bus.m = 20;

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

    $scope.cargando = false;
	$scope.buscando = [];
	$scope.feedback = "Complete los campos para buscar";
    $scope.personas = false;
    $scope.bienesRaices = false;

    direccion.getRegiones(function(d) {
    	$scope.regiones = d;
    });

    direccion.getComunas(function(d) {
    	$scope.comunas = d;
    });

    $scope.tooltipDirecciones = function(p) {
        var txt = "";

        txt = "<b>2008</b>: " + p.address2008 + ", " + p.comuna2008 + ", " + p.region2008;
        txt = txt + "<br /><b>2012</b>: " + p.address2012 + ", " + p.comuna2012 + ", " + p.region2012;
        txt = txt + "<br /><b>2013</b>: " + p.address2013 + ", " + p.comuna2013 + ", " + p.region2013;

        txt = "<p style='text-align:left;margin:0px;'>" + txt + "</p>";
        return txt;
    }

    $scope.search = function() {
    	$scope.buscando.push(0);
    	buscador.getPersonas($scope.bus, function(d) {
    		$scope.personas = d;
    		$scope.buscando.pop();
    	});
    	$scope.buscando.push(0);
    	buscador.getBienesRaices($scope.bus, function(d) {
    		$scope.bienesRaices = d;
    		$scope.buscando.pop();
    	})
    }

    $scope.$watch('bus.g', function() {
        $scope.bus.c = '';
    });

 //    $scope.$watch('buscando', function(c) {
	// 	if(c==1) ngProgressLite.start();
	// 	else if(c==0) ngProgressLite.done();
	// });
  });

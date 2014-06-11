'use strict';

angular.module('rescateApp')
	.factory('reportes', function ($http) {
		var baseURL = "index.php";
		
		return {
			fetchReporte: function(data, cb) {
				$http
                    .post(baseURL+"?sec=reportes&get=reporte", data)
                    .success(function(d, s) {
                        cb(d);
                    });
			}
		};
	});
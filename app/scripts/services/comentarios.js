'use strict';

angular.module('rescateApp')
	.factory('comentarios', function ($http) {
		var baseURL = "index.php";
		// var baseURL = "/index.php";

		return {
			get: function(data, cb) {
				$http
					.post(baseURL+"?sec=comentarios&get="+data.get, data.params)
					.success(function(d, s) {
						cb(d);
					});
			},
			do: function(data, cb) {
				$http
					.post(baseURL+"?sec=comentarios&do="+data.do, data.params)
					.success(function(d, s) {
						cb(d);
					});
			}
		};
	});

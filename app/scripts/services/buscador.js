'use strict';

angular.module('rescateApp')
    .factory('buscador', function ($http) {
        var personas = [];
        var bienesRaices = [];
        var baseURL = "index.php";

        return {
            getPersonas: function (data, cb) {
                $http
                    .post(baseURL+"?sec=buscador&do=searchPersonas", data)
                    .success(function(d, s) {
                            cb(d);
                    });
            },
            getBienesRaices: function (data, cb) {
                $http
                    .post(baseURL+"?sec=buscador&do=searchBR", data)
                    .success(function(d, s) {
                            cb(d);
                    });
            }
        };
    });
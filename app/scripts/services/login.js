'use strict';

angular.module('rescateApp')
    .factory('login', function ($http) {
        var baseURL = "index.php";


        return {
            login: function (data, cb) {
                $http
                    .post(baseURL+"?sec=log&do=in", data)
                    .success(function(d, s) {
                        cb(d);
                    });
            },
            fetchUser: function(cb) {
                $http
                    .post(baseURL+"?sec=log&get=user")
                    .success(function(d, s) {
                        cb(d);
                    });
            },
            fetchAcceso: function(cb) {
                $http
                    .post(baseURL+"?sec=log&get=acceso")
                    .success(function(d, s) {
                        cb(d);
                    });
            },
            fetchMisiones: function(cb) {
                $http
                    .post(baseURL+"?sec=log&get=misiones")
                    .success(function(d, s) {
                        cb(d);
                    });
            },
            registro: function(data, cb) {
                $http
                    .post(baseURL+"?sec=log&do=add", data)
                    .success(function(d, s) {
                        cb(d);
                    });
            },
            registroFull: function(data, cb) {
                $http
                    .post(baseURL+"?sec=log&do=addFull", data)
                    .success(function(d, s) {
                        cb(d);
                    });
            },
            recuperar: function(data, cb) {
                $http
                    .post(baseURL+"?sec=log&do=rec", data)
                    .success(function(d, s) {
                        cb(d);
                    });
            }

        };
    });

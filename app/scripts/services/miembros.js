'use strict';

angular.module('rescateApp')
    .factory('miembros', function ($http) {
        var miembros = false;
        var estados = [];
        var baseURL = "index.php";

        return {
            fetchAll: function(data, cb) {
                if(miembros["'"+data.barrio_id+"'"]) {
                    cb(miembros["'"+data.barrio_id+"'"]);
                } else {
                    $http
                        .post(baseURL+"?sec=miembros&get=all", data)
                        .success(function(d, s) {
                            if(!miembros) miembros = [];
                            miembros["'"+data.barrio_id+"'"] = d;
                            cb(d);
                        });
                }
            },
            find: function(id, cb) {
                $http
                    .post(baseURL+"?sec=miembros&get=det", {'id': id})
                    .success(function(d, s) {
                        cb(d);
                    });
            },
            save: function(data, cb) {
                $http
                    .post(baseURL+"?sec=miembros&do=save", data)
                    .success(function(d, s) {
                        cb(d);
                    });
            },
            verificar: function(data, cb) {
                $http
                    .post(baseURL+"?sec=miembros&do=verificar", data)
                    .success(function(d, s) {
                        cb(d);
                    });
            },
            undoVerificar: function(data, cb) {
                $http
                    .post(baseURL+"?sec=miembros&do=undoverificar", data)
                    .success(function(d, s) {
                        cb(d);
                    });
            },
            getEstados: function(cb) {
                if(estados.length>0) {
                    cb(estados);
                } else {
                    $http
                        .post(baseURL+"?sec=miembros&get=estados")
                        .success(function(d, s) {
                            estados = d;
                            cb(d);
                        });
                }
            },
            get: function(data, cb) {
                $http
                    .post(baseURL+"?sec=miembros&get="+data.get, data.params)
                    .success(function(d, s) {
                        cb(d);
                    });
            },
            do: function(data, cb) {
                $http
                    .post(baseURL+"?sec=miembros&do="+data.do, data.params)
                    .success(function(d, s) {
                        cb(d);
                    });
            },
            fetchLog: function(id, cb) {
                $http
                    .post(baseURL+"?sec=miembros&get=log", {'id': id})
                    .success(function(d, s) {
                        cb(d);
                    });
                // $http
                //     .post(baseURL+"historial.json")
                //     .success(function(d, s) {
                //         cb(d);
                //     });
            }
        };
    });

'use strict';

angular.module('rescateApp')
    .factory('direccion', function ($http) {
        var regiones = [];
        var comunas = [];
        var estados = [];
        var fuentes = [];
        var baseURL = "index.php";
        // var baseURL = "/index.php";

        return {
            insert: function(data, cb) {
                $http
                    .post(baseURL+"?sec=direccion&do=insert", data)
                    .success(function(d, s) {
                        cb(d);
                    });
            },
            update: function(data, cb) {
                $http
                    .post(baseURL+"?sec=direccion&do=update", data)
                    .success(function(d, s) {
                        cb(d);
                    });  
            },
            getEstructura: function(cb) {
                if(regiones.length>0 && comunas.length>0 && estados.length>0 && fuentes.length>0) {
                    cb(regiones, comunas, estados, fuentes);
                } else {
                    $http
                        .post(baseURL+"?sec=direccion&get=estructura")
                        .success(function(d, s) {
                            regiones = d.regiones;
                            comunas = d.comunas;
                            estados = d.estados;
                            fuentes = d.fuentes;
                            cb(regiones, comunas, estados, fuentes);
                        });
                }
            },
            getRegiones: function (cb) {
                if(regiones.length>0) {
                    cb(regiones);
                } else {
                    $http
                        .post(baseURL+"?sec=direccion&get=regiones")
                        .success(function(d, s) {
                            regiones = d;
                            cb(d);
                        });
                }
            },
            getComunas: function (cb) {
                if(comunas.length>0) {
                    cb(comunas);
                } else {
                    $http
                        .post(baseURL+"?sec=direccion&get=comunas")
                        .success(function(d, s) {
                            comunas = d;
                            cb(d);
                        });
                }
            },
            getEstados: function(cb) {
                if(estados.length>0) {
                    cb(estados);
                } else {
                    $http
                        .post(baseURL+"?sec=direccion&get=estados")
                        .success(function(d, s) {
                            estados = d;
                            cb(d);
                        });
                }
            },
            getFuentes: function(cb) {
                if(fuentes.length>0) {
                    cb(fuentes);
                } else {
                    $http
                        .post(baseURL+"?sec=direccion&get=fuentes")
                        .success(function(d, s) {
                            fuentes = d;
                            cb(d);
                        });
                }
            }
        };
    });

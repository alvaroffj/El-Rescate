'use strict';

angular.module('rescateApp')
    .factory('estadisticas', function ($http) {
        var baseURL = "index.php";
        var local = false;

        return {
            get: function(data, cb) {
                // if(local) {
                //     cb(local);
                // } else {
                    $http
                        .post(baseURL+"?sec=dashboard&get="+data.get, data.params)
                        .success(function(d, s) {
                            // local = d;
                            cb(d);
                        });
                // }
            },
        }
    });

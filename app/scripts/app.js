'use strict';
var $notificacion;
function showNotificacion(msg) {
    $notificacion.html("<p>"+msg+"</p>");
    $notificacion.fadeIn();
    setTimeout("$notificacion.fadeOut()", 4000);
};

$(document).ready(function() {
    $notificacion = $("#notificacion");
});

var app = angular.module('rescateApp', [
    'ngCookies',
    'ngResource',
    'ngSanitize',
    'ngRoute',
    'ui.select2',
    'ui.bootstrap',
    'angulartics', 
    'angulartics.google.analytics',
    'angularMoment',
    'ngProgressLite',
    'ui.router',
    'nvd3ChartDirectives',
    'ngAnimate'
]);
app.config(['$httpProvider', '$locationProvider', function($httpProvider, $locationProvider) {
        // Use x-www-form-urlencoded Content-Type
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

        // Override $http service's default transformRequest
        $httpProvider.defaults.transformRequest = [function(data) {
            return angular.isObject(data) && String(data) !== '[object File]' ? jQuery.param(data) : data;
        }];
    }])
    .config(['$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider) {
    
        $urlRouterProvider.otherwise('/escritorio');
        
        $stateProvider
            .state('miembros', {
                url: '/miembros',
                templateUrl: 'views/miembros.html',
                controller: 'MiembrosCtrl',
                access: {
                    isFree: false
                }
            })

            .state('miembros.list', {
                url: '/:nivel/:unidad',
                access: {
                    isFree: false
                }
            })
            
            .state('miembros.detalle', {
                url: '/:nivel/:unidad/detalle/:id',
                templateUrl: 'views/miembros/:id.html',
                controller: 'MiembrosIdCtrl',
                access: {
                    isFree: false
                }
            })

            .state('escritorio', {
                url: '/escritorio',
                templateUrl: 'views/main.html',
                controller: 'DashboardCtrl',
                access: {
                    isFree: false
                }
            })

            .state('usuarios', {
                url: '/usuarios',
                templateUrl: 'views/usuarios_sidebar.html',
                controller: 'UsuariosCtrl',
                access: {
                    isFree: false
                }
            })

            .state('usuarios.list', {
                url: '/:nivel/:unidad',
                access: {
                    isFree: false
                }
            })
            
            .state('usuarios.detalle', {
                url: '/:nivel/:unidad/detalle/:id',
                templateUrl: 'views/usuarios/:id.html',
                controller: 'UsuariosIdCtrl',
                access: {
                    isFree: false
                }
            })

            .state('buscador', {
                url: '/buscador',
                templateUrl: 'views/buscador.html',
                controller: 'BuscadorCtrl',
                access: {
                    isFree: false
                }
            })

            .state('reportes/avisitar', {
                url: '/reportes/avisitar',
                templateUrl: 'views/reportes/avisitar.html',
                controller: 'ReportesAvisitarCtrl',
                access: {
                    isFree: false
                }
            })

            .state('reportes/actualizarmls', {
                url: '/reportes/actualizarmls',
                templateUrl: 'views/reportes/actualizarmls.html',
                controller: 'ReportesActualizarmlsCtrl',
                access: {
                    isFree: false
                }
            })

            .state('ayuda', {
                url: '/ayuda',
                templateUrl: 'views/ayuda.html',
                controller: 'AyudaCtrl',
                access: {
                    isFree: false
                }
            })
    }])
    

    .run(['$window', function ($window) {
        $window.moment.lang('es');
    }])
    .config(['ngProgressLiteProvider', function (ngProgressLiteProvider) {
        ngProgressLiteProvider.settings.speed = 1000;
    }]);
'use strict';

angular.module('rescateApp')
  .directive('pickadate', function ($filter) {
    return {
        restrict: 'A',
        require : 'ngModel',
        link : function (scope, element, attrs, ngModelCtrl) {
            $(function(){
                // console.log(element);
                element.pickadate({
                    format: 'dd-mm-yyyy',
                    formatSubmit: 'yyyy-mm-dd',
                    hiddenSuffix: '',
                    clear: 'Limpiar',
                    today: '',
                    selectYears: 100,
                    selectMonths: true,
                    max: true,
                    onStart: function() {
                        var ts = new Date(attrs.fechats).getTime();
                        this.set('select', new Date(attrs.fecha).getTime());
                    },
                    onSet: function (date) {
                        ngModelCtrl.$setViewValue($filter('date')(date.select, 'yyyy-MM-dd'));
                        scope.$$phase || scope.$apply();
                        // console.log(attrs);
                        // console.log(attrs.fecha);
                        // var ts = new Date(attrs.fechats).getTime();
                        // console.log(ts);
                    }
                });
            });
        }
    }
  });

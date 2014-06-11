'use strict';

angular.module('rescateApp')
  .directive('morrisChart', function () {
    return {
	  restrict: 'A',
	  scope: {
	    data: '='
	  },
	  link: function(scope, ele, attrs) {
	    var colors, data, func, options;
	    scope.graph = false;
	    scope.$watch('data', function () {
		    if(!scope.graph) {
		    	scope.graph = draw();
		    } else {
		    	scope.graph.setData(scope.data);
		    }
        }, true);

	    function draw() {
	    	data = scope.data;
		    switch (attrs.type) {
		      case 'line':
		        if (attrs.lineColors === void 0 || attrs.lineColors === '') {
		          colors = null;
		        } else {
		          colors = JSON.parse(attrs.lineColors);
		        }
		        options = {
		          element: ele[0],
		          data: data,
		          xkey: attrs.xkey,
		          ykeys: JSON.parse(attrs.ykeys),
		          labels: JSON.parse(attrs.labels),
		          lineWidth: attrs.lineWidth || 2,
		          lineColors: colors || ['#0b62a4', '#7a92a3', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
		          resize: true
		        };
		        return new Morris.Line(options);
		      case 'area':
		        if (attrs.lineColors === void 0 || attrs.lineColors === '') {
		          colors = null;
		        } else {
		          colors = JSON.parse(attrs.lineColors);
		        }
		        options = {
		          element: ele[0],
		          data: data,
		          xkey: attrs.xkey,
		          ykeys: JSON.parse(attrs.ykeys),
		          labels: JSON.parse(attrs.labels),
		          lineWidth: attrs.lineWidth || 2,
		          lineColors: colors || ['#0b62a4', '#7a92a3', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
		          behaveLikeLine: attrs.behaveLikeLine || false,
		          fillOpacity: attrs.fillOpacity || 'auto',
		          pointSize: attrs.pointSize || 4,
		          resize: true
		        };
		        return new Morris.Area(options);
		      case 'bar':
		        if (attrs.barColors === void 0 || attrs.barColors === '') {
		          colors = null;
		        } else {
		          colors = JSON.parse(attrs.barColors);
		        }
		        options = {
		          element: ele[0],
		          data: data,
		          xkey: attrs.xkey,
		          ykeys: JSON.parse(attrs.ykeys),
		          labels: JSON.parse(attrs.labels),
		          barColors: colors || ['#0b62a4', '#7a92a3', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
		          stacked: attrs.stacked || null,
		          resize: true
		        };
		        return new Morris.Bar(options);
		      case 'donut':
		        if (attrs.colors === void 0 || attrs.colors === '') {
		          colors = null;
		        } else {
		          colors = JSON.parse(attrs.colors);
		        }
		        options = {
		          element: ele[0],
		          data: data,
		          colors: colors || ['#0B62A4', '#3980B5', '#679DC6', '#95BBD7', '#B0CCE1', '#095791', '#095085', '#083E67', '#052C48', '#042135'],
		          resize: true
		        };
		        if (attrs.formatter) {
		          func = new Function('y', 'data', attrs.formatter);
		          options.formatter = func;
		        }
		        return new Morris.Donut(options);
		    }
	    }
	  }
	};
  });

'use strict';

angular.module('rescateApp')
  .directive('goBack', function () {
    return {
	  restrict: "A",
	  controller: [
	    '$scope', '$element', '$window', function($scope, $element, $window) {
	      return $element.on('click', function() {
	        return $window.history.back();
	      });
	    }
	  ]
	};
  });

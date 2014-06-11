'use strict';

angular.module('rescateApp')
	.filter('multiple', function ($filter) {
		return function (input, query, campos) {
			// console.log(campos);
			if(!angular.isUndefined(input) && !angular.isUndefined(query) && query.$!='') {
				var res = [],
					q = query.$.split(","),
					campos = angular.isUndefined(campos) ? false : campos.split(",");
				angular.forEach(input, function(i) {
					var aux = null, aux2 = null;
					angular.forEach(q, function(s) {
						s = s.trim().toUpperCase();
						if(s!='') {
							if(campos) {
								angular.forEach(campos, function(c) {
									if(!angular.isUndefined((i[c])))
										if((i[c]).toUpperCase().indexOf(s) >= 0) {
											aux2 = i;
										}
								});
							} else {
								aux2 = $filter('filter')([i], {$:s})[0];
							}
							if(aux2) aux = aux2; 
						}
					});
					if(aux) res.push(aux);
				});
				return res;
			} else {
				return input;
			}
		};
	});

'use strict';

angular.module('rescateApp')
  .controller('MiembrosIdCtrl', function ($scope, $stateParams, $location, $modal, $filter, User, miembros, direccion, comentarios, ngProgressLite) {
    $scope.awesomeThings = [
      'HTML5 Boilerplate',
      'AngularJS',
      'Karma'
    ];

    $scope.comparaHist = [];
    $scope.comparaHistObj = [];
    $scope.com = {};
    $scope.familia = false;
    $scope.historial = false;
    $scope.comentarios = false;
    $scope.comentando = false;
    $scope.tabActive = $location.hash();
    $scope.savingMiembro = false;
    $scope.iconVerificado = 'fa-check';
    $scope.classVerificado = 'btn-success';
    $scope.cargandoMLS = false;
    // $scope.carga = User.carga;


    $scope.classFuente = ['','label-primary', 'label-success', 'label-warning', 'label-info'];

	$scope.sacerdocio = {
		'':'',
		'N': ['No Ordenado', 'info'],
		'D': ['Diacono', 'info'],
		'M': ['Maestro', 'info'],
		'P': ['Presbitero', 'info'],
		'E': ['Elder', 'info'],
		'S': ['Sumo Sacerdote', 'info'],
		'O': ['Obispo', 'info']
	};
    $scope.investido = {
    	'true': ['Investido', 'primary', 'I'],
    	'false': ['No Investido', 'primary', ''],
    	'': ''
    };
    $scope.sellado = {
    	'true': ['Sellado', 'warning', 'S'],
    	'false': ['No Sellado', 'warning', ''],
    	'': ''
    };

    $scope.misionero = {
        'true': ['Misionero Retornado', 'info', 'MR'],
        'false': ['Misionero Retornado', 'info', '']
    }

    $scope.dateOptions = {
        'starting-day': 1,
        'show-weeks': false,
        'show-button-bar': false
    };   

    $scope.ascDes = 1;
    $scope.orden = "name";
    $scope.currOrden = "name";

    $scope.setOrden = function(ord) {
        if(ord != $scope.currOrden) {
            $scope.orden = "-"+ord;
            $scope.ascDes = -1;
            $scope.currOrden = ord;
        } else {
            $scope.ascDes = $scope.ascDes*-1;
            $scope.orden = ($scope.ascDes>0)?ord:"-"+ord;
        }
    }

    $scope.flechaOrden = function() {
        var res = ($scope.ascDes<0)?"fa fa-angle-down":"fa fa-angle-up";
        return res;
    }

    $scope.isColActive = function(ord) {
        return ($scope.currOrden == ord);
    }
    if($stateParams.id) {
    	$scope.cargando = true;
	    miembros.find($stateParams.id, function(d) {
            if(d!="false") {
    	    	$scope.miembro = d;
                if($scope.miembro.persona_fecnac) {
                    var aux = $scope.miembro.persona_fecnac.split("-");
                    $scope.miembro.persona_fecnac = new Date(aux[0], aux[1]-1, aux[2]);
                }
                if($scope.miembro.persona_fecfall) {
                    var aux = $scope.miembro.persona_fecfall.split("-");
                    $scope.miembro.persona_fecfall = new Date(aux[0], aux[1]-1, aux[2]);
                }
                // User.carga.barrio_id_aux = d.barrio_id;
                // $scope.miembro.actualizado_mls = new Date(d.actualizado_mls);
                // $scope.miembro.modificado = new Date(d.modificado);
    	    	$scope.cargando = false;
            } else $location.path("/miembros");
	    });
    } else {
    	// $location.path("/miembros");
    }

    $scope.mlsActualizado = function() {
        if($scope.miembro) {
            // console.log($scope.miembro.actualizado_mls+" | "+$scope.miembro.modificado);
            if(($scope.miembro.modificado_por != 0 && ($scope.miembro.actualizado_mls==null || $scope.miembro.actualizado_mls < $scope.miembro.modificado))) {
                return true;
            } else return false;
        } else return false;
    }

    $scope.setEnter = function(btn) {
        switch(btn) {
            case 'verificado':
                $scope.iconVerificado = 'fa-times';
                $scope.classVerificado = 'btn-danger';
                break;
        }
    }

    $scope.setLeave = function(btn) {
        switch(btn) {
            case 'verificado':
                $scope.iconVerificado = 'fa-check';
                $scope.classVerificado = 'btn-success';
                break;
        }
    }

    $scope.undoVerificar = function() {
        var modalInstance = $modal.open({
            templateUrl: 'undoVerificarForm.html',
            controller: undoVerificarCtrl,
            resolve: {
                persona: function() {
                    return $scope.miembro;
                }
            }
        });

        modalInstance.result.then(function (res) {
            $scope.miembro = res.persona;
            showNotificacion(res.MENSAJE);
            if($scope.historial) {
                $scope.carga('historial');
            }
        }, function () {
            // console.log('Modal dismissed at: ' + new Date());
        });
    }

    $scope.carga = function(tab) {
        switch(tab) {
            case 'familia':
                if(!$scope.familia.length) {
                    var data = {
                        get:'familia', 
                        params: {
                            id: $scope.miembro.persona_cabeza_id
                        }
                    }
                    miembros.get(data, function(d) {
                        $scope.familia = d;
                    });
                }
                break;
            case 'historial':
                var data = {
                    get:'log', 
                    params: {
                        id: $stateParams.id
                    }
                }
                miembros.get(data, function(d) {
                    $scope.historial = d;
                });
                break;
            case 'comentarios':
                $scope.comentando = true;
                var data = {
                    get:'all', 
                    params: {
                        id: $stateParams.id
                    }
                }
                comentarios.get(data, function(d) {
                    $scope.comentando = false;
                    $scope.comentarios = d;
                });
                break;
        }
    }

    miembros.getEstados(function(d) {
        $scope.perEstados = d;
    });

    direccion.getEstructura(function(r, c, e, f) {
        $scope.regiones = r;
        $scope.comunas = c;
        $scope.dirEstados = e;
        $scope.dirFuentes = f;
    });

    $scope.do = function(sec) {
        switch(sec) {
            case 'mlsActualizar':
                var data = {
                    do:'mlsactualizar', 
                    params: {
                        id: $stateParams.id
                    }
                }
                if(!$scope.cargandoMLS) {
                    $scope.cargandoMLS = true;
                    miembros.do(data, function(d) {
                        $scope.cargandoMLS = false;
                        // $scope.miembro.actualizado_mls = new Date(d.res.persona.actualizado_mls);
                        // $scope.miembro.modificado = new Date(d.res.persona.modificado);
                        $scope.miembro.modificado = d.res.modificado;
                        $scope.miembro.actualizado_mls = d.res.actualizado_mls;
                        $scope.miembro.modificado_por = d.res.modificado_por;
                        $scope.miembro.usuario_nom = d.res.usuario_nom;
                        $scope.miembro.usuario_ape = d.res.usuario_ape;
                    });
                }
                break;
        }
    }

    $scope.guardar = function() {
        var lala = $filter('date')($scope.miembro.persona_fecnac, "yyyy-MM-dd");
    	// console.log(lala);
        $scope.savingMiembro = true;
        var miembroSave = {
            'persona_id': $scope.miembro.persona_id,
            'persona_nom': $scope.miembro.persona_nom,
            'persona_rut_h': $scope.miembro.persona_rut_h,
            'persona_telefono': $scope.miembro.persona_telefono,
            'persona_email': $scope.miembro.persona_email,
            'persona_fecnac': $filter('date')($scope.miembro.persona_fecnac, "yyyy-MM-dd"),
            'persona_fecfall': $filter('date')($scope.miembro.persona_fecfall, "yyyy-MM-dd"),
            'persona_estado_id': $scope.miembro.persona_estado_id,
            'persona_no_rut': $scope.miembro.persona_no_rut,
            'persona_visitado': $scope.miembro.persona_visitado
        }
        miembros.save(miembroSave, function(d) {
            showNotificacion(d.MENSAJE);
            $scope.savingMiembro = false;
            $scope.miembro.persona_nom = d.persona.persona_nom;
            $scope.miembro.persona_rut_h = d.persona.persona_rut_h;
            $scope.miembro.persona_telefono = d.persona.persona_telefono;
            $scope.miembro.persona_email = d.persona.persona_email;
            if(d.persona.persona_fecnac) {
                var auxNac = d.persona.persona_fecnac.split("-");
                $scope.miembro.persona_fecnac = new Date(auxNac[0], auxNac[1]-1, auxNac[2]);
            }
            if(d.persona.persona_fecfall) {
                var auxFall = d.persona.persona_fecfall.split("-");
                $scope.miembro.persona_fecfall = new Date(auxFall[0], auxFall[1]-1, auxFall[2]);
            }
            $scope.miembro.persona_estado_id = d.persona.persona_estado_id;
            $scope.miembro.persona_no_rut = d.persona.persona_no_rut;
            $scope.miembro.modificado = d.persona.modificado;
            $scope.miembro.actualizado_mls = d.persona.actualizado_mls;
            // $scope.miembro.actualizado_mls = new Date(d.persona.actualizado_mls);
            // $scope.miembro.modificado = new Date(d.persona.modificado);
            $scope.miembro.usuario_nom = d.persona.usuario_nom;
            $scope.miembro.usuario_ape = d.persona.usuario_ape;
            $scope.miembro.persona_edad = d.persona.persona_edad;
            if($scope.historial) {
                $scope.carga('historial');
            }
        });
    }

    $scope.changeDirEst = function(d) {
        // console.log(d);
        var aux = {'direccion_id':d.direccion_id,'direccion_estado_id':d.direccion_estado_id};
        direccion.update(aux, function(r) {
            // console.log(r);
        });
    }

    $scope.changeDirVis = function(d) {
        // console.log(d);
        var aux = {'direccion_id':d.direccion_id,'direccion_visitado':d.direccion_visitado};
        direccion.update(aux, function(r) {
            // console.log(r);
        });
    }

    $scope.comentar = function() {
        var data = {
            do:'insert', 
            params: {
                comentario: $scope.com.comentario,
                persona_id: $scope.miembro.persona_id,
                barrio_id: $scope.miembro.barrio_id,
                estaca_id: $scope.miembro.estaca_id,
                mision_id: $scope.miembro.mision_id
            }
        }
        $scope.comentando = true;
        comentarios.do(data, function(c) {
            $scope.comentando = false;
            $scope.comentarios.push(c);
            $scope.com = {};
        });
    }

    $scope.$watch('cargando', function(c) {
        if(c) ngProgressLite.start();
        else ngProgressLite.done();
    });

    $scope.openDirNew = function () {
        var modalInstance = $modal.open({
            templateUrl: 'dirNewForm.html',
            controller: dirNewCtrl,
            resolve: {
                comunas: function () {
                    return $scope.comunas;
                },
                regiones: function () {
                    return $scope.regiones;
                },
                estados: function () {
                    return $scope.dirEstados;
                },
                fuentes: function () {
                    return $scope.dirFuentes;
                },
                persona: function() {
                    return $scope.miembro;
                }
            }
        });

        modalInstance.result.then(function (dirNew) {
            $scope.miembro.direccion.push(dirNew);
        }, function () {});
    };

    $scope.openVerificar = function () {
        var modalInstance = $modal.open({
            templateUrl: 'verificarForm.html',
            controller: verificarCtrl,
            resolve: {
                comunas: function () {
                    return $scope.comunas;
                },
                regiones: function () {
                    return $scope.regiones;
                },
                persona: function() {
                    return $scope.miembro;
                }
            }
        });

        modalInstance.result.then(function (res) {
            $scope.miembro = res.persona;
            showNotificacion(res.MENSAJE);
            if($scope.historial) {
                $scope.carga('historial');
            }
        }, function () {});
    };
  });

var verificarCtrl = ['$scope', '$modalInstance', 'comunas', 'regiones', 'persona', 'miembros', function ($scope, $modalInstance, comunas, regiones, persona, miembros) {
    $scope.comunas = comunas;
    $scope.regiones = regiones;
    $scope.persona = persona;
    $scope.cargando = false;
    var dirSel = 0;
    for(var i=0; i<persona.direccion.length; i++) {
        if(persona.direccion[i].direccion_estado_id == 2) {
            dirSel = i;
            i = persona.direccion.length;
        }
    }
    
    $scope.ver = {
        'persona_id': persona.persona_id,
        'persona_visitado': persona.persona_visitado,
        'persona_fuera_unidad': persona.persona_fuera_unidad,
        'persona_verificado': true,
        'direccion_id': persona.direccion[dirSel].direccion_id
    }

    $scope.ok = function () {
        $scope.cargando = true;
        miembros.verificar($scope.ver, function(r) {
            $scope.cargando = false;
            $modalInstance.close(r);
        });
    };

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
}];

var undoVerificarCtrl = ['$scope', '$modalInstance', 'persona', 'miembros', function ($scope, $modalInstance, persona, miembros) {
    $scope.cargando = false;
    $scope.ver = {
        'persona_id': persona.persona_id,
        'persona_fuera_unidad': false,
        'persona_verificado': false
    }

    $scope.ok = function () {
        $scope.cargando = true;
        miembros.undoVerificar($scope.ver, function(r) {
            $scope.cargando = false;
            $modalInstance.close(r);
        });
    };

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
}];

var dirNewCtrl = ['$scope', '$modalInstance', 'comunas', 'regiones', 'estados', 'fuentes', 'persona', 'direccion', function ($scope, $modalInstance, comunas, regiones, estados, fuentes, persona, direccion) {
    $scope.comunas = comunas;
    $scope.regiones = regiones;
    $scope.estados = estados;
    $scope.fuentes = fuentes;

    $scope.dirNew = {
        'persona_id': persona.persona_id,
        'barrio_id': persona.barrio_id,
        'estaca_id': persona.estaca_id,
        'mision_id': persona.mision_id,
        'comuna_id': comunas[0].comuna_id,
        'region_id': regiones[0].region_id,
        'direccion_estado_id': 1,
        'direccion_fuente_id': 1,
        'direccion_visitado': false
    }

    $scope.ok = function () {
        // console.log("guardar");
        direccion.insert($scope.dirNew, function(r) {
            $modalInstance.close(r);
        });
    };

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
}];

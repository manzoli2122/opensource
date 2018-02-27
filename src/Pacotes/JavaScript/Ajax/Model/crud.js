window.excluirRecursoPeloId = function(id, texto, url, funcSucesso = function() {}) {
    alertConfimacao(texto, '',
        function() {
            alertProcessando();
            var token = document.head.querySelector('meta[name="csrf-token"]').content;

            $.ajax({
                url: url + "/" + id,
                type: 'post',
                data: { _method: 'delete', _token: token },
                success: function(retorno) {
                    alertProcessandoHide();
                    if (retorno.erro) {
                        toastErro(retorno.msg);
                    } else {
                        toastSucesso(retorno.msg);
                        funcSucesso();
                    }
                },
                error: function(erro) {
                    alertProcessandoHide();
                    toastErro("Ocorreu um erro");
                    console.log(erro);
                }
            });
        }
    );
}





window.modelShow = function( id , url , funcSucesso = function() {} ) {			
    alertProcessando();
    var token = document.head.querySelector('meta[name="csrf-token"]').content;
    $.ajax({
        url: url + "/" + id,
        type: 'get',
        data: { _token: token },
        success: function(retorno) {
            alertProcessandoHide();
            if (retorno.erro) {
                toastErro(retorno.msg);
            } else {
                funcSucesso(retorno.data);	
            }
        },
        error: function(erro) {
            alertProcessandoHide();
            toastErro("Ocorreu um erro na requisição ajax.");
            console.log(erro);
        }
    });				
}




		


window.modelEditar = function(id, url , funcSucesso = function() {} ) {			
    alertProcessando();
    var token = document.head.querySelector('meta[name="csrf-token"]').content;
    $.ajax({
        url: url + "/" + id + "/edit",
        type: 'get',
        data: { _token: token },
        success: function(retorno) {
            alertProcessandoHide();	
            if (retorno.erro) {
                toastErro(retorno.msg);
            } else {
                document.getElementById("div-pagina").innerHTML = retorno.data ;
                funcSucesso();
            }					
        },
        error: function(erro) {
            alertProcessandoHide();
            toastErro("Ocorreu um erro");
            console.log(erro);
        }
    });			
}




window.modelCreate = function( url  , funcSucesso = function() {} ) {			
    alertProcessando();
    var token = document.head.querySelector('meta[name="csrf-token"]').content;
    $.ajax({
        url: url ,
        type: 'get',
        data: { _token: token },
        success: function(retorno) {
            alertProcessandoHide();	
            if (retorno.erro) {
                toastErro(retorno.msg);
            } else {
                document.getElementById("div-pagina").innerHTML = retorno.data ;
                funcSucesso();
            }					
        },
        error: function(erro) {
            alertProcessandoHide();
            toastErro("Ocorreu um erro");
            console.log(erro);
        }
    });			
}


window.modelStore = function( url , funcSucesso = function() {} , funcError = function() {} ) {			
    alertProcessando();				
    var dados = $('#form-model').serialize() ;
    $.ajax({
        url: url ,
        type: 'post',
        data: dados ,
        success: function(retorno) {
            alertProcessandoHide();							
            if (retorno.erro) {	
                document.getElementById("div-pagina").innerHTML = retorno.data ;
                for (var i in retorno.msg) {
                    toastErro(retorno.msg[i]);
                }	  
                funcError();             
            } 
            else {
                toastSucesso(retorno.msg);
                modelVoltarIndex();
                funcSucesso();	
            }											
        },
        error: function(erro) {
            alertProcessandoHide();
            toastErro("Ocorreu um erro");
            console.log(erro);
        }
    });		
}





window.modelUpdateAjax = function(id, url , funcSucesso = function() {} , funcError = function() {}  ) {			
    alertProcessando();				
    var dados = $('#form-model').serialize() ;
    $.ajax({
        url: url + "/" + id ,
        type: 'post',
        data: dados ,
        success: function(retorno) {
            alertProcessandoHide();							
            if (retorno.erro) {							
                document.getElementById("div-pagina").innerHTML = retorno.data ;
                for (var i in retorno.msg) {
                    toastErro(retorno.msg[i]);
                }	
                funcError();
            } 
            else {
                toastSucesso(retorno.msg);
                modelVoltarIndex();	
            }											
        },
        error: function(erro) {
            alertProcessandoHide();
            toastErro("Ocorreu um erro");
            console.log(erro);
        }
    });		
}





window.modelDelete = function( id , route  ) {			 
    excluirRecursoPeloId( id , "Você Confirma a exclusão deste item?", route , 
        function(){
            $('[remover-apos-excluir]').remove();
            $('#divAlerta').slideDown();
        }
    );
}




window.modelVoltarIndex = function( ) {					
    document.getElementById("div-pagina").innerHTML = pagianIndex ;			
    modelIndexDataTableFunction();
}






window.modelRestaurar = function( id , url , funcSucesso = function() {} , funcError = function() {} ) {			
    alertProcessando();				
    var token = document.head.querySelector('meta[name="csrf-token"]').content;
    $.ajax({
        url: url + "/restore/" + id ,
        type: 'post',
        data: { _token: token },
        success: function(retorno) {
            alertProcessandoHide();							
            if (retorno.erro) {	                
               
                toastErro(retorno.msg);
                 
                funcError();             
            } 
            else {
                toastSucesso(retorno.msg);
                //modelVoltarIndex();
                funcSucesso();	
            }											
        },
        error: function(erro) {
            alertProcessandoHide();
            toastErro("Ocorreu um erro");
            console.log(erro);
        }
    });		
}


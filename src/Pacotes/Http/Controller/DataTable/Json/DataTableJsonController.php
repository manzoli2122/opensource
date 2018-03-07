<?php
namespace Manzoli2122\Pacotes\Http\Controller\DataTable\Json;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use DataTables;
use View;
use Manzoli2122\Pacotes\Constants\ErrosSQL;

class DataTableJsonController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $view;
    protected $route;
    protected $name ;
    protected $model;


    public function index(){
        return view("{$this->view}.index" );
    }



    /**
    * Processa a requisição AJAX do DataTable na página de listagem.
    * Mais informações em: http://datatables.yajrabox.com
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function getDatatable(){
        $models = $this->model->getDatatable();
        return Datatables::of($models)
            ->addColumn('action', function($linha) {
                return '<button data-id="'.$linha->id.'" type="button" class="btn btn-danger  btn-xs btn-datatable" btn-excluir title="Excluir">                               <i class="fa fa-times"> </i> </button> '
                     . '<button data-id="'.$linha->id.'" type="button" class="btn btn-success btn-xs btn-datatable" btn-editar  title="Editar"     style="margin-left: 10px;"> <i class="fa fa-pencil"></i> </button>'
                     . '<button data-id="'.$linha->id.'" type="button" class="btn btn-primary btn-xs btn-datatable" btn-show    title="Visualizar" style="margin-left: 10px;"> <i class="fa fa-search"></i> </button>' ;
            })->make(true);
    }








    public function show($id){    
        try {            
            if(!$model = $this->model->findModelJson($id) ){
                $msg = __('msg.erro_nao_encontrado', ['1' =>  $this->name ]);
                return response()->json(['erro' => true , 'msg' => $msg , 'data' => null ], 200);
            } 
            
            $html = (string) View::make("{$this->view}.show", compact("model"));            
            $html =  preg_replace( '/\r/' , '', $html)  ; 
            $html =  preg_replace( '/\n/' , '', $html)  ;
            $html =  preg_replace( '/\t/' , '', $html)  ;  
            $html =  preg_replace( '/(>)(\s+)(<)/' , '\1\3', $html)  ; 
            return response()->json(['erro' => false , 'msg' =>'Item encontrado com sucesso.' , 'data' => $html   ], 200);  
           
        } catch(\Illuminate\Database\QueryException $e) {
            $msg = $e->errorInfo[1] == ErrosSQL::DELETE_OR_UPDATE_A_PARENT_ROW ? 
                __('msg.erro_exclusao_fk', ['1' =>  $this->name  , '2' => 'Model']):
                __('msg.erro_bd');
            return response()->json(['erro' => true , 'msg' => $msg , 'data' => null ], 200);
        }
    }








    
    public function edit($id){        
        try {            
            if(!$model = $this->model->findModelJson($id) ){
                $msg = __('msg.erro_nao_encontrado', ['1' =>  $this->name ]);
                return response()->json(['erro' => true , 'msg' => $msg , 'data' => null ], 200);
            } 
            
            $html = (string) View::make("{$this->view}.edit", compact("model"));             
            $html =  preg_replace( '/\r/' , '', $html)  ; 
            $html =  preg_replace( '/\n/' , '', $html)  ;
            $html =  preg_replace( '/\t/' , '', $html)  ;  
            $html =  preg_replace( '/(>)(\s+)(<)/' , '\1\3', $html)  ; 
            return response()->json(['erro' => false , 'msg' =>'Item encontrado com sucesso.' , 'data' => $html   ], 200);  
            
        } catch(\Illuminate\Database\QueryException $e) {
            $msg = $e->errorInfo[1] == ErrosSQL::DELETE_OR_UPDATE_A_PARENT_ROW ? 
                __('msg.erro_exclusao_fk', ['1' =>  $this->name  , '2' => 'Model']):
                __('msg.erro_bd');
            return response()->json(['erro' => true , 'msg' => $msg , 'data' => null ], 200);
        }
    }









    public function update( Request $request ,  $id ){
        
        $dataForm = $request->all(); 
        $validate = validator( $dataForm , $this->model->rules($id) );
        
        if( !$model = $this->model->findModelJson($id) ){
            return response()->json(['erro' => true , 'msg' => $this->name . ' não encontrado no Sistema'  , 'data' => null ], 200);
        }

        if($validate->fails()){

            $errors = $validate->messages() ;
            $html = (string) View::make("{$this->view}.edit", compact("model", "errors" , "request")) ;
            $html =  preg_replace( '/\r/' , '', $html)  ; 
            $html =  preg_replace( '/\n/' , '', $html)  ;
            $html =  preg_replace( '/\t/' , '', $html)  ;  
            $html =  preg_replace( '/(>)(\s+)(<)/' , '\1\3', $html)  ; 
            $mensagens = $validate->messages();
            return response()->json(['erro' => true , 'msg' => $mensagens , 'data' => $html ], 200);
        }

        
        if( !$update = $model->update($dataForm) ){
            return response()->json(['erro' => true , 'msg' => $this->name . ' não alterado no sistema' , 'data' => null ], 200 );
        }
        
        return response()->json(['erro' => false , 'msg' => $this->name . ' alterado no sistema' , 'data' =>  $update ], 200 );

    }








    public function create()
    {
        $model = $this->model->replicate();
        $html = (string) View::make( "{$this->view}.create" , compact("model") ); 
        $html =  preg_replace( '/\r/' , '', $html)  ; 
        $html =  preg_replace( '/\n/' , '', $html)  ;
        $html =  preg_replace( '/\t/' , '', $html)  ;  
        $html =  preg_replace( '/(>)(\s+)(<)/' , '\1\3', $html)  ;            
        return response()->json(['erro' => false , 'msg' =>'' , 'data' => $html   ], 200);
    }



    

  
    public function store(Request $request)
    {
        $dataForm = $request->all();
        $validate = validator( $dataForm , $this->model->rules() );

        if($validate->fails()){
            $errors = $validate->messages() ;
            $model = $this->model->replicate();
            $html = (string) View::make("{$this->view}.create", compact( "model" , "errors" , "request")) ;
            $html =  preg_replace( '/\r/' , '', $html)  ; 
            $html =  preg_replace( '/\n/' , '', $html)  ;
            $html =  preg_replace( '/\t/' , '', $html)  ;  
            $html =  preg_replace( '/(>)(\s+)(<)/' , '\1\3', $html)  ; 
            $mensagens = $validate->messages();
            return response()->json(['erro' => true , 'msg' => $mensagens , 'data' => $html ], 200);
        }

        if( !$insert = $this->model->create($dataForm) ){
            return response()->json(['erro' => true , 'msg' => $this->name . ' não adicionado ao sistema' , 'data' => null ], 200 );
        }

        return response()->json(['erro' => false , 'msg' => $this->name . ' adicionado o sistema' , 'data' =>  $insert ], 200 );

    }








    public function destroy($id)
    {
        try {
            if(!$model = $this->model->findModelJson($id) ){
                $msg = __('msg.erro_nao_encontrado', ['1' =>  $this->name ]);
                return response()->json(['erro' => true , 'msg' => $msg , 'data' => null ], 200);
            }              
            $delete = $model->delete();        
            $msg = __('msg.sucesso_excluido', ['1' =>  $this->name ]);

        } catch(\Illuminate\Database\QueryException $e) {
            $erro = true;
            $msg = $e->errorInfo[1] == ErrosSQL::DELETE_OR_UPDATE_A_PARENT_ROW ? 
                __('msg.erro_exclusao_fk', ['1' =>  $this->name  , '2' => 'Model']):
                __('msg.erro_bd');
        }
        return response()->json(['erro' => isset($erro), 'msg' => $msg , 'data' => null  ], 200);
    }




}


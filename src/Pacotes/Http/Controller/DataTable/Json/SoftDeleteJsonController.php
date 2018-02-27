<?php
namespace Manzoli2122\Pacotes\Http\Controller\DataTable\Json;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use DataTables;
use View;
use Manzoli2122\Pacotes\Constants\ErrosSQL;

class SoftDeleteJsonController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $view;
    protected $route;
    protected $name ;
    protected $model;
    

    public function index(){        
        return view("{$this->view}.index"  );
    }




     /**
    * Processa a requisição AJAX do DataTable na página de listagem.
    * Mais informações em: http://datatables.yajrabox.com
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function getDatatable(){
        $models = $this->model->getDatatableApagados();
        return Datatables::of($models)
            ->addColumn('action', function($linha) {
                return '<button data-id="'.$linha->id.'" btn-excluir type="button" class="btn btn-danger btn-xs btn-datatable" title="Excluir" > <i class="fa fa-times"></i> </button> '
                    . '<button data-id="'.$linha->id.'" type="button" class="btn btn-success btn-xs btn-datatable" style="margin-left: 10px;" btn-restaurar title="Restaurar" ><i class="fa fa-arrow-circle-up"></i></button>'
                    . '<button data-id="'.$linha->id.'" type="button" class="btn btn-primary btn-xs btn-datatable" style="margin-left: 10px;" btn-show  title="Visualizar" ><i class="fa fa-search"></i></button>' ;
            })->make(true);
    }







    public function show($id)
    {    
        
        try {            
            if(!$model = $this->model->findModelSoftDeleteJson($id) ){
                
                $msg = __('msg.erro_nao_encontrado', ['1' =>  $this->name ]);
                return response()->json(['erro' => true , 'msg' => $msg , 'data' => null ], 200);
            } 
            else{
                
                $html = (string) View::make("{$this->view}.show", compact("model"));            
                return response()->json(['erro' => false , 'msg' =>'Item encontrado com sucesso.' , 'data' => $html   ], 200);  
            }
        } catch(\Illuminate\Database\QueryException $e) {
            $msg = $e->errorInfo[1] == ErrosSQL::DELETE_OR_UPDATE_A_PARENT_ROW ? 
                __('msg.erro_exclusao_fk', ['1' =>  $this->name  , '2' => 'Model']):
                __('msg.erro_bd');
            return response()->json(['erro' => true , 'msg' => $msg , 'data' => null ], 200);
        }
    }


    


    
    public function destroy($id){
        try {
            $model = $this->model->withTrashed()->find($id);
            $delete = $model->forceDelete();        
            $msg = __('msg.sucesso_excluido', ['1' =>  $this->name ]);

        } catch(\Illuminate\Database\QueryException $e) {
            $erro = true;
            $msg = $e->errorInfo[1] == ErrosSQL::DELETE_OR_UPDATE_A_PARENT_ROW ? 
                __('msg.erro_exclusao_fk', ['1' =>  $this->name  , '2' => 'Model']):
                __('msg.erro_bd');
        }
        return response()->json(['erro' => isset($erro), 'msg' => $msg], 200);
    }



    
   





    public function restore($id)
    {
        try {
            
            if( !$model = $this->model->findModelSoftDeleteJson($id) ){
                $msg = __('msg.erro_nao_encontrado', ['1' =>  $this->name ]);
                return response()->json(['erro' => true , 'msg' => $msg , 'data' => null ], 200);
            } 
            
            if( !$restore = $model->restore() ){
                return response()->json(['erro' => true , 'msg' => 'Não foi possível restaurar o item.'  , 'data' => null ], 200);
            }

            return response()->json(['erro' => false, 'msg' => 'Item restaurado com sucesso.' , 'data' => null  ], 200);

        } catch(\Illuminate\Database\QueryException $e) {
            $msg = $e->errorInfo[1] == ErrosSQL::DELETE_OR_UPDATE_A_PARENT_ROW ? 
                __('msg.erro_exclusao_fk', ['1' =>  $this->name  , '2' => 'Model']):
                __('msg.erro_bd');
            return response()->json(['erro' => true , 'msg' => $msg , 'data' => null ], 200);
        }
    
    }



}


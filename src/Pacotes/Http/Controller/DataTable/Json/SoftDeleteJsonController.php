<?php
namespace Manzoli2122\Pacotes\Http\Controller\DataTable\Json;


use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Http\Request;
use Manzoli2122\Pacotes\Constants\ErrosSQL;
use DataTables;

class SoftDeleteJsonController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $view_apagados;
    protected $view;
    protected $route;
    protected $name ;
    protected $model;
    

    public function show($id)
    {       
        $erro = false;   
        try {
            $msg = '';
            if(!$model = $this->model->find($id)){
                $model = null;
                $erro = true;
                $msg = __('msg.erro_nao_encontrado', ['1' =>  $this->name ]);
            } 

        } catch(\Illuminate\Database\QueryException $e) {
            $model = null;
            $erro = true;
            $msg = $e->errorInfo[1] == ErrosSQL::DELETE_OR_UPDATE_A_PARENT_ROW ? 
                __('msg.erro_exclusao_fk', ['1' =>  $this->name  , '2' => 'Model']):
                __('msg.erro_bd');
        }

        return response()->json(['erro' => $erro , 'msg' => $msg , 'data' => $model ], 200);
  
    }






    public function destroy($id)
    {
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







    public function index()
    {
        $models = $this->model->all();
        return response()->json(['data' => $models ]);


        return view("{$this->view}.index");
    }


    public function create()
    {
        return view("{$this->view}.create");
    }

  
    public function store(Request $request)
    {
        $this->validate($request , $this->model->rules());
        $dataForm = $request->all();              
        $insert = $this->model->create($dataForm);           
        if($insert){           
            return redirect()->route("{$this->route}.index")->with('success', __('msg.sucesso_adicionado', ['1' => $this->name ]));
        }
        else {
            return redirect()->route("{$this->route}.create")->withErrors(['message' => __('msg.erro_nao_store', ['1' => $this->name  ])]);
        }
    }


    


    public function edit($id)
    {
        $model = $this->model->find($id);
        if($model){
            return view("{$this->view}.edit", compact('model'));
        }
        return redirect()->route("{$this->route}.index")->withErrors(['message' => __('msg.erro_nao_encontrado', ['1' => $this->name ])]);
    }


    public function update( Request $request, $id)
    {
        $this->validate($request , $this->model->rules($id));        
        $dataForm = $request->all();                      
        $model = $this->model->find($id); 
        if(!$model){
            return redirect()->route("{$this->route}.index")->withErrors(['message' => __('msg.erro_nao_encontrado', ['1' => $this->name ])]);
        }       
        $update = $model->update($dataForm);     
        
        if($update){
            return redirect()->route("{$this->route}.index")->with('success', __('msg.sucesso_alterado', ['1' => $this->name ]));
        }        
        else {
            return redirect()->route("{$this->route}.edit" , ['id'=> $id])->withErrors(['errors' =>'Erro no Editar'])->withInput();
        }
    }


    public function destroySoft($id)
    {
        try {            
            $model = $this->model->find($id);
            $delete = $model->delete();                   
            $msg = __('msg.sucesso_excluido', ['1' => $this->name ]);
        } 
        catch(\Illuminate\Database\QueryException $e) 
        {
            $erro = true;
            $msg = $e->errorInfo[1] == ErrosSQL::DELETE_OR_UPDATE_A_PARENT_ROW ? 
                __('msg.erro_exclusao_fk', ['1' => $this->name , '2' => 'Model']):
                __('msg.erro_bd');
        }
        return response()->json(['erro' => isset($erro), 'msg' => $msg], 200);

    }




      /**
    * Processa a requisição AJAX do DataTable na página de listagem.
    * Mais informações em: http://datatables.yajrabox.com
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function getDatatable()
    {
        $models = $this->model->getDatatable();
        return Datatables::of($models)
            ->addColumn('action', function($linha) {
                return '<button data-id="'.$linha->id.'" btn-excluir type="button" class="btn btn-danger btn-xs" title="Excluir"> <i class="fa fa-times"></i> </button> '
                    . '<a href="'.route("{$this->route}.edit", $linha->id).'" class="btn btn-warning btn-xs" style="margin-left: 10px;" title="Editar"> <i class="fa fa-pencil"></i> </a> '
                    . '<a href="'.route("{$this->route}.show", $linha->id).'" class="btn btn-primary btn-xs" style="margin-left: 10px;" title="Visualizar"> <i class="fa fa-search"></i> </a>';
            })->make(true);
    }





//==================================================================================================================================
//==================================================================================================================================
//      FUNCOES PARA OS APAGADOS
//==================================================================================================================================
//==================================================================================================================================


    public function indexApagados()
    {
        return view("{$this->view_apagados}.index");
    }




    
    public function showApagado($id)
    {
        $model = $this->model->onlyTrashed()->find($id);
        if($model){
            return view("{$this->view_apagados}.show", compact('model'));
        }
        return redirect()->route("{$this->route}.apagados")->withErrors(['message' => __('msg.erro_nao_encontrado', ['1' => $this->name ])]);
    }





    public function restore($id)
    {
        $model = $this->model->withTrashed()->find($id);
        if(!$model){
            return redirect()->route("{$this->route}.apagados")->withErrors(['message' => __('msg.erro_nao_encontrado', ['1' => $this->name ])]);
        }   
        $restore = $model->restore();
        if($restore){
            return redirect()->route("{$this->route}.index")->with(['success' => 'Item restaurado com sucesso']);
        }
        else{
            return  redirect()->route("{$this->route}.showapagado",['id' => $id])->withErrors(['errors' => 'Falha ao Deletar']);
        }
    }





  



    
    /**
    * Processa a requisição AJAX do DataTable na página de listagem.
    * Mais informações em: http://datatables.yajrabox.com
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function getDatatableApagados()
    {
        $models = $this->model->getDatatableApagados();
        return Datatables::of($models)
            ->addColumn('action', function($linha) {
                return '<button data-id="'.$linha->id.'" btn-excluir type="button" class="btn btn-danger btn-xs" title="Excluir"> <i class="fa fa-times"></i> </button> '
                        . '<a href="'.route("{$this->route}.restore", $linha->id).'" class="btn btn-success btn-xs" title="Restaurar"> <i class="fa fa-arrow-circle-up"></i> </a>'
                        . '<a href="'.route("{$this->route}.showapagado", $linha->id).'" class="btn btn-primary btn-xs" title="Visualizar" style="margin-left: 3px;"> <i class="fa fa-search"></i> </a>';
            })->make(true);
    }

   

    



}


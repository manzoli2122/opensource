<?php
namespace Manzoli2122\Pacotes\Http\Controller\DataTable\Json;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Http\Request;
use Manzoli2122\Pacotes\Constants\ErrosSQL;
use DataTables;
use View;

class DataTableJsonController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $view;
    protected $route;
    protected $name ;
    protected $model;


    public function index(){
        $dataTable = (string) View::make("{$this->view}.dataTable");
        return view("{$this->view}.index" , compact('dataTable') );
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
                return '<button data-id="'.$linha->id.'" btn-excluir type="button" class="btn btn-danger btn-xs btn-datatable" title="Excluir" > <i class="fa fa-times"></i> </button> '
                    . '<button data-id="'.$linha->id.'" type="button" class="btn btn-success btn-xs btn-datatable" style="margin-left: 10px;" btn-editar ><i class="fa fa-pencil"></i></button>'
                    . '<button data-id="'.$linha->id.'" type="button" class="btn btn-primary btn-xs btn-datatable" style="margin-left: 10px;" btn-show ><i class="fa fa-search"></i></button>' ;
            })->make(true);
    }








    public function show($id)
    {    
        try {            
            if(!$model = $this->model->findModelJson($id) ){
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









    
    public function edit($id){        
        try {            
            if(!$model = $this->model->findModelJson($id) ){
                $msg = __('msg.erro_nao_encontrado', ['1' =>  $this->name ]);
                return response()->json(['erro' => true , 'msg' => $msg , 'data' => null ], 200);
            } 
            else{
                $html = (string) View::make("{$this->view}.edit", compact("model"));            
                return response()->json(['erro' => false , 'msg' =>'oi' , 'data' => $html   ], 200);  
            }
        } catch(\Illuminate\Database\QueryException $e) {
            $msg = $e->errorInfo[1] == ErrosSQL::DELETE_OR_UPDATE_A_PARENT_ROW ? 
                __('msg.erro_exclusao_fk', ['1' =>  $this->name  , '2' => 'Model']):
                __('msg.erro_bd');
            return response()->json(['erro' => true , 'msg' => $msg , 'data' => null ], 200);
        }
    }









    public function destroy($id)
    {
        try {
            $model = $this->model->find($id);  
            $delete = $model->delete();        
            $msg = __('msg.sucesso_excluido', ['1' =>  $this->name ]);

        } catch(\Illuminate\Database\QueryException $e) {
            $erro = true;
            $msg = $e->errorInfo[1] == ErrosSQL::DELETE_OR_UPDATE_A_PARENT_ROW ? 
                __('msg.erro_exclusao_fk', ['1' =>  $this->name  , '2' => 'Model']):
                __('msg.erro_bd');
        }
        return response()->json(['erro' => isset($erro), 'msg' => $msg], 200);
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


    










    public function update( Request $request ,  $id )
    {

        //$this->validate($request , $this->model->rules());


        $dataForm = $request->all(); 

        $validate = validator( $dataForm , $this->model->rules($id) );
        
        if( !$model = $this->model->find($id) ){
            return response()->json(['erro' => true , 'msg' => $id  , 'data' => null ], 200);
        }

        if($validate->fails()){

            $errors = $validate->messages() ;
            $html = (string) View::make("{$this->view}.edit", compact("model", "errors" , "request")) ;


            $mensagens = $validate->messages();
            return response()->json(['erro' => true , 'msg' => $mensagens , 'data' => $html ], 200);
        }

            
        if( !$model = $this->model->find($id) ){
            return response()->json(['erro' => true , 'msg' => $this->name . ' não encontrado no Sistema' , 'data' => null ], 200);
        }
        if( !$update = $model->update($dataForm) ){
            return response()->json(['erro' => true , 'msg' => $this->name . ' não alterado no sistema' , 'data' => null ], 200 );
        }
        
        return response()->json(['erro' => false , 'msg' => $this->name . ' alterado no sistema' , 'data' =>  $update ], 200 );

        /*
                             
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
        */


    }


    




      



}


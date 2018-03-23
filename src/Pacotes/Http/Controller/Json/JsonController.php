<?php
namespace Manzoli2122\Pacotes\Http\Controller\Json;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use DataTables;
use Exception ;

class JsonController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    
    protected $route;
    protected $name ;
    protected $model;


    
    public function index(){  
        try {
            if(!$models = $this->model->findJson() ){
               return response()->json( false , 500); 
            }
            return response()->json( $models , 200);
        }
        catch (Exception $ex) {
            return response()->json( false , 500);
        }        
    }



    public function show($id)
    {
        try {
            if( !$model = $this->model->find($id) ){
            return response()->json('', 404);
            }
            return response()->json($model);
        } catch(Exception $e) {            
            return response()->json( $e->getMessage() , 500);
        }
    }



    public function store(Request $request)
    {
        $data = $request->all();        
        $validate = validator($data, $this->model->rules());
        if( $validate->fails() ) {
            $messages = $validate->messages();
            return response()->json( $messages , 422);
        }
        try
        {
            if( !$insert = $this->model->create($data) ){
                return response()->json(['error' => 'error_insert'], 500);
            }
        }
        catch(Exception $e)
        {
            return response()->json( $e->getMessage() , 500);
        }
        
        return response()->json( $insert , 201);
    }

    

    

    public function update(Request $request, $id)
    {
        $data = $request->all();        
        $validate = validator($data, $this->model->rules($id));
        if( !$validate->fails() ) {
            $messages = $validate->messages();
            return response()->json( $messages , 422);
        }        
        if( !$model = $this->model->find($id) ){
           return response()->json( '' , 404);
        }      
        try
        {
            if( !$update = $model->update($data) ){
                return response()->json(['error' => 'not_update'], 500);
            }
        }
        catch(Exception $e)
        {
            return response()->json( $e->getMessage() , 500);
        }      
        return response()->json( $update , 200 );
    }





    public function destroy($id)
    {
        if( !$model = $this->model->find($id) ){
           return response()->json( '' , 404);
        }
        if( !$delete = $model->delete() ){
            return response()->json(['error' => 'not_delete'], 500);
        }
        return response()->json( $delete , 200 );
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



}


<?php
namespace Manzoli2122\Pacotes\Contracts\Models;

interface DataTableJson
{
    /**
     * @param int
     *
     * @return string
     */
    public function findModelJson($id);

    

    public function getDatatable( );


    public function rules( $id = '' );


    public function findModelSoftDeleteJson($id);

}

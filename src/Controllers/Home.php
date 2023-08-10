<?php

namespace AvegaCms\Controllers;

use AvegaCms\Models\Admin\UserModel;
use Config\Services;

class Home extends BaseController
{
    public function index()
    {
        $UM = new UserModel();

        d(Services::request()->getGet());

        /*$testFilters = [
            ['id'=>[1,2,3],'usePagination'=>false],
            ['q'=>'che','usePagination'=>false],
            ['q'=>'@','limit'=>10,'page'=>3],
            ['q'=>'@','limit'=>'x','usePagination'=>false]
        ];

        foreach ($testFilters as $key => $filter) {
            d($UM->filter($filter)->asArray()->find());
        }*/

        d($UM->asArray()->filter(['!id'=>[1,7,8],'s'=>'+login,-phone']));
        //d($UM->filter(['!id'=>[1,2,3],'q'=>'hotmail','limit'=>5,'page'=>3,'usePagination'=>true]));

        // $UM->builder()->getCompiledSelect()
        //return view('welcome_message');
    }
}

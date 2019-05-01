<?php
namespace App\HttpController\Api;

use EasySwoole\Component\Di;
use EasySwoole\Http\AbstractInterface\Controller;
use App\Libs\Conf;

class Base extends Controller
{

    public function mysql()
    {
        $db = Di::getInstance()->get('mysql');
        $res = $db->get('tb_game', 10);
        return $this->writeJson(200,$res);
    }

    public function index()
    {
        var_dump(Conf::get('easyswoole.mysql'));

        // TODO: Implement index() method.
        $this->response()->write('hello world,shima');
    }

    private function note(){

    }
}
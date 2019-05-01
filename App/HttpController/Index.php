<?php
namespace App\HttpController;

use App\Model\Game;
use EasySwoole\Component\Di;
use EasySwoole\Http\AbstractInterface\Controller;
use App\Libs\Conf;

class Index extends Controller
{

    public function mysql()
    {
        $db = Di::getInstance()->get('mysql');
        return $this->writeJson(200, 3333);
    }

    public function tporm()
    {
        $res = Game::where(['game_id'=>['>',1]])->find();
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
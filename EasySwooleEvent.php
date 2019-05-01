<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:33
 */

namespace EasySwoole\EasySwoole;

use App\Libs\Conf;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use App\Utility\Pool\MysqlPool;
use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Component\Di;
use App\Process\HotReload;
use EasySwoole\Mysqli\Mysqli;
use App\Console\TestConsole;
use EasySwoole\EasySwoole\Console\ModuleContainer;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // 在该事件中，可以进行一些系统常量的更改和全局配置
        date_default_timezone_set('Asia/Shanghai');

        ModuleContainer::getInstance()->set(new TestConsole());

        // 可配置多数据库
        $maxNum = Conf::get('easyswoole.mysql.POOL_MAX_NUM');
        $mysqlConf = PoolManager::getInstance()->register(MysqlPool::class , $maxNum);
        if ($mysqlConf === null) {
            // 当返回null时,代表注册失败,无法进行再次的配置修改
            // 注册失败不一定要抛出异常,因为内部实现了自动注册,不需要注册也能使用
            throw new \Exception('注册失败!');
        }

        //设置其他参数
        $mysqlConf->setMaxObjectNum(20)->setMinObjectNum(5);

        ################### 注册匿名连接池   #######################
        // 无需新建类 实现接口,直接实现连接池
        PoolManager::getInstance()->registerAnonymous('mysql', function () {
            $conf = Conf::get('easyswoole.mysql');;
            $dbConf = new \EasySwoole\Mysqli\Config($conf);
            return new Mysqli($dbConf);
        });

    }

    public static function mainServerCreate(EventRegister $register)
    {
        // 注册主服务回调事件
        // mysql热启动
        $register->add($register::onWorkerStart, function (\swoole_server $server, int $workerId) {
            if ($server->taskworker == false) {
                // 每个worker进程都预创建连接
                // 最小创建数量
                PoolManager::getInstance()->getPool(MysqlPool::class)->preLoad(5);
            }
        });

        // 添加一个自定义进程用于监听文件变化
        $swooleServer = ServerManager::getInstance()->getSwooleServer();
        $process = (new HotReload('HotReload', ['disableInotify' => false]))->getProcess();
        $swooleServer->addProcess($process);

        // 常用组件依赖注入到DI容器里面
        $mysqlConf = new \EasySwoole\Mysqli\Config(Conf::get('easyswoole.mysql'));
        Di::getInstance()->set('mysql', Mysqli::class, $mysqlConf);

        // 添加一个子服务监听，9503访问的可以走此逻辑
        $subPort = ServerManager::getInstance()->getSwooleServer()->addListener('0.0.0.0',9503,SWOOLE_TCP);
        $subPort->on('receive',function (\swoole_server $server, int $fd, int $reactor_id, string $data){
            // Logger::getInstance()->console("{$reactor_id} receive {$fd}: ". $data);
        });
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // 类似TP中的全局中间件，可以写在基类里面易于扩展。
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        //\App\Utility\TrackerManager::getInstance()->getTracker()->endPoint('request');

        $responseMsg = $response->getBody()->__toString();
        Logger::getInstance()->console("响应内容:".$responseMsg);
        Logger::getInstance()->console("响应状态码:".$response->getStatusCode());

        //tracker结束,结束之后,能看到中途设置的参数,调用栈的运行情况
        //TrackerManager::getInstance()->closeTracker();
    }
}
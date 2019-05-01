<?php
// 1. \EasySwoole\EasySwoole\Command\CommandRunner::run
function run(array $args):?string
{
    // ... code ...
    // 加载各项基础command类进容器,也就是php easyswoole出现的
    // 可以在easyswoole文件里面通过以下方式注册自己的命令
    CommandContainer::getInstance()->set(new Help());
    // ... code ...

    // 初始化核心类
    Core::getInstance()->initialize();
    // ... code ...
    // 常量赋值 \EasySwoole\EasySwoole\Core::__construct
    CommandContainer::getInstance()->hook($command,$args);
    // php easyswoole start
    $Start->exec($args);
}

// 2. \EasySwoole\EasySwoole\Core::initialize
function initialize()
{
    //检查全局文件是否存在.
    require_once EASYSWOOLE_ROOT . '/EasySwooleEvent.php';
    // ... code ...
    // ... code ...

    //先加载配置文件
    $this->loadEnv();

    // vendor/easyswoole/easyswoole/src/Command/CommandRunner.php:41 预先加载配置
    if(in_array('produce',$args)){
        Core::getInstance()->setIsDev(false);
    }
    Core::getInstance()->initialize();

    //执行框架初始化事件
    EasySwooleEvent::initialize();

    //临时文件和Log目录初始化
    $this->sysDirectoryInit();

    //注册错误回调
    $this->registerErrorHandler();
    return $this;
}

// 3. \EasySwoole\EasySwoole\Command\DefaultCommand\Start::exec
// 由 CommandRunner::run 调用
public function exec(array $args): ?string
{
    // ... code ...
    // 是否常驻内存？
    // ... code ...

    // 开启swoole服务
    Core::getInstance()->createServer();

    // \EasySwoole\EasySwoole\Core::createServer
    // $this->swooleServer = new \swoole_server($address,$port,...$args);
    // 注册默认事件回调
    $this->registerDefaultCallBack(ServerManager::getInstance()->getSwooleServer(),$conf['SERVER_TYPE']);
    // ... code ...

    // 注册默认事件回调： \EasySwoole\EasySwoole\Core::registerDefaultCallBack 【start】
    // 初始化HTTP相关参数，可以在EasySwooleEvent::initialize()通过注入到DI更改
    $namespace = Di::getInstance()->get(SysConst::HTTP_CONTROLLER_NAMESPACE)
    // ... code ...
    $dispatcher = new Dispatcher($namespace,$depth,$max);
    $dispatcher->setControllerPoolWaitTime($waitTime);
    // ... code ...
    // 注册onRequest
    // /vendor/easyswoole/easyswoole/src/Core.php:262
    EventHelper::on($server,EventRegister::onRequest,function (\swoole_http_request $request,\swoole_http_response $response)use($dispatcher){
        // /vendor/easyswoole/easyswoole/src/Swoole/EventHelper.php:24
        $server->on($event,$callback);

        $request_psr = new Request($request);
        $response_psr = new Response($response);
        try{
            // 全局事件Request拦截与预处理
            if(EasySwooleEvent::onRequest($request_psr,$response_psr)){
                $dispatcher->dispatch($request_psr,$response_psr);
            }
        }catch (\Throwable $throwable){
            call_user_func(Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER),$throwable,$request_psr,$response_psr);
        }finally{
            try{
                // 全局事件afterRequest,
                EasySwooleEvent::afterRequest($request_psr,$response_psr);
            }catch (\Throwable $throwable){
                call_user_func(Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER),$throwable,$request_psr,$response_psr);
            }
        }
        // 注册默认事件回调： \EasySwoole\EasySwoole\Core::registerDefaultCallBack 【end】


    });

    // ... code ...
    // 注册ontesk
    // ... code ...
    // 注册onfinish 空？
    // ... code ...
    // 注册onPipeMessage
    // ... code ...
    // 注册onWorkerStart
    // ... code ...

    // 全局事件mainServerCreate在创建
    // 此处可以覆盖默认事件回调，或者新增默认事件回调
    EasySwooleEvent::mainServerCreate(ServerManager::getInstance()->getMainEventRegister());

    //注册ConsoleService
    ConsoleService::getInstance()->__registerTcpServer();

    // ... code ...
    // 输出当前实例信息
    // echo $response;
    // ... code ...

    // 启动服务
    Core::getInstance()->start();
    return null;
}

// 4. 启动服务 \EasySwoole\EasySwoole\Core::start
function start()
{
    // ... code ...
    // 给主进程也命名
    // 注册crontab进程
    // 执行Actor注册进程
    // ... code ...
    ServerManager::getInstance()->start();
}

// 5. 启动服务 \EasySwoole\EasySwoole\ServerManager::start
//  $this->mainServerEventRegister = new EventRegister();
function start()
{
    // $this->mainServerEventRegister = new EventRegister();
    $events = $this->getMainEventRegister()->all();
    // 该对象可以在 EasySwooleEvent::mainServerCreate 中被覆盖
    foreach ($events as $event => $callback){
        // 如果有的话EventRegister里面有事件的话再注册一遍
    }

    // 子服务专家监听端口 ？？
    $this->attachListener();

    // ... code ...
    // 正式启动
}
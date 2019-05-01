<?php
require '../vendor/autoload.php';

\EasySwoole\EasySwoole\Core::getInstance()->initialize();

function generatePhoneList()
{
    $list = [];
    for ($i=0;$i <= 10000; $i++){
        array_push($list,'155'.\EasySwoole\Utility\Random::number(8));
    }
    return $list;
}

function generateTimeList(int $startTime,$max = 30000)
{
    $list = [];
    for ($i=0;$i<=$max;$i++){
        //模拟从早上7点到凌晨
        $t = mt_rand(
            25200,86400
        );
        array_push($list,$startTime+$t);
    }
    sort($list);
    return $list;
}

$config = \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL');
$db = new \App\Utility\Pool\MysqlPool($config);
$phoneList = generatePhoneList();
//模拟一个月的时间数据
$start = strtotime('20180101');


for ($i = 0; $i<=30; $i++){
    $timeList = generateTimeList($start);
    foreach ($timeList as $time){
        $phone = $phoneList[mt_rand(0,10000)];
        $target = $phoneList[mt_rand(0,10000)];
        $db->insert('user_phone_record',[
            'phone'=>$phone,
            'targetPhone'=>$target,
            'callTime'=>$time
        ]);
    }
    $start += 86400;
}

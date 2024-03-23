<?php

namespace App\HttpController;

use App\Queue\FooQueue;
use EasySwoole\Queue\Job;
use EasySwooleLib\Controller\BaseController;

class Queue extends BaseController
{
    // eg: curl "http://localhost:9501/Queue/produceOrdinaryJob"
    public function produceOrdinaryJob()
    {
        $jobData = new Job();
        $jobData->setJobData("this is my job data, time: " . date('Ymd h:i:s'));
        $produceResult = FooQueue::getInstance()->produceOrdinaryJob($jobData);
        return json(['produceResult' => $produceResult]);
    }
}

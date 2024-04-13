<?php

namespace App\Queue;

use EasySwoole\Component\Singleton;
use EasySwoole\Queue\Job;
use EasySwoole\Queue\Queue;

class FooQueue extends Queue
{
    use Singleton;

    public function produceOrdinaryJob($jobData)
    {
        $job = new Job();
        $job->setJobData($jobData);
        return $this->producer('foo')->push($job);
    }

    public function consumeOrdinaryJob()
    {
        $this->consumer('foo')->listen(function (Job $job) {
            var_dump($job->getJobData());
        });
    }
}

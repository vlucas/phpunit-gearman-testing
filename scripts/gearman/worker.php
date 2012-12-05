#!/usr/bin/env php
<?php
// Set time limit for run length
$timeStarted = time();
$timeRunLimit = 1200;

// Setup Gearman
$worker = new GearmanWorker();
$worker->addServer('127.0.0.1');

$worker->addFunction("reverse", "reverse_fn");

while (1) {
    print "Waiting for job...\n";
    $ret = $worker->work();
    if ($worker->returnCode() != GEARMAN_SUCCESS) break;

    // Enforce run time limit
    if ((time() - $timeStarted) > $timeRunLimit) {
      exit;
    }
}

// Functions registered
function reverse_fn($job) {
    $workload = $job->workload();
    echo "Received job: " . $job->handle() . "\n";
    echo "Workload: $workload\n";
    $result = strrev($workload);

    for($i=1; $i<=10;  $i++) {
        $job->sendStatus($i,10);
        sleep(1);
    }

    echo "Result: $result\n";
    return $result;
}


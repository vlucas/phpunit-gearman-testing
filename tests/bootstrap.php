<?php
// Backgrounding stuff...
// @link http://nsaunders.wordpress.com/2007/01/12/running-a-background-process-in-php/
// @link http://stackoverflow.com/questions/45953/php-execute-a-background-process

// See if a process is still running
function processIsRunning($pid)
{
    exec("ps $pid", $processState);
    return(count($processState) >= 2);
}

// Run a process in the background
function processBackground($cmd, $outputfile = null)
{
    return shell_exec("nohup $cmd > /dev/null 2> /dev/null & echo $!");
}

// Really cheap and probably aweful way to do this...
echo "\nRunning Gearman Worker Queue...\n------------------------\n";
$file = realpath(__DIR__ . '/../scripts/gearman/worker.php');
echo ">> File: " . $file;
$gearman_worker_pid = processBackground($file);
echo "\n>> PID: " . $gearman_worker_pid;
echo "------------------------\n";
echo "THIS WILL TAKE SEVERAL SECONDS TO RUN GEARMAN TASKS. PLEASE BE PATIENT.\n\n";


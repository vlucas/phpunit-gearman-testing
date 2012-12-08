<?php
namespace Test;

class GearmanTest extends \PHPUnit_Framework_TestCase
{
    public static $client;
    public static $runCount = 0;
    public static $taskCallbacks = array();

    public static function setupBeforeClass()
    {
        // Set up connection to gearmand
        self::$client = new \GearmanClient();
        self::$client->addServer('127.0.0.1');
        self::$client->setCompleteCallback(array(__CLASS__, 'gearman_task_complete'));
    }

    public function tearDown()
    {
        global $gearman_worker_pid;
        self::$runCount++;

        // Count all the test* methods to see how many tasks we have to run first
        $testMethods = array();
        foreach(get_class_methods($this) as $method) {
            if(strpos($method, 'test') === 0) {
                $testMethods[] = $method;
            }
        }

        // Run all tasks when all tests are done
        if(self::$runCount === count($testMethods)) {
            self::$client->runTasks();

            // Cleanup
            exec('kill ' . $gearman_worker_pid);
        }
    }

    public function testReverseTask()
    {
        $phpunit = $this;
        $taskId = $this->runTask("reverse", "ABC123", function($result) use($phpunit) {
            $phpunit->assertEquals("321CBA", $result);
        });
    }

    public function testReverseTask2()
    {
        $phpunit = $this;
        $taskId = $this->runTask("reverse", "DEF456", function($result) use($phpunit) {
            $phpunit->assertEquals("654FED", $result);
        });
    }

    /**
     * Add task to Gearman queue to run
     */
    protected function runTask($name, $params, $callback)
    {
        // Assign task id, store callback with task id, addTask to queue
        $taskId = uniqid(php_uname('n'), true);
        self::$taskCallbacks[$taskId] = $callback;
        $task = self::$client->addTask($name, $params, null, $taskId);

        // Return Task ID
        return $taskId;
    }

    /**
     * Gearman complete callback
     */
    public static function gearman_task_complete($task)
    {
        call_user_func(self::$taskCallbacks[$task->unique()], $task->data());
        echo "\nCOMPLETE: " . $task->unique() . " = " . $task->data();
    }
}


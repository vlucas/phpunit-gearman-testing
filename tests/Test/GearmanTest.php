<?php
namespace Test;

class GearmanTest extends \PHPUnit_Framework_TestCase
{
    public static $client;
    public static $pid;

    public static function setupBeforeClass()
    {
        // Set up connection to gearmand
        self::$client = new \GearmanClient();
        self::$client->addServer('127.0.0.1');

        // Really cheap and probably aweful way to do this...
        echo "\nRunning Gearman Worker Queue...\n------------------------\n";
        $file = realpath(__DIR__ . '/../scripts/gearman/worker.php');
        echo ">> File: " . $file;
        self::$pid = processBackground($file);
        echo "\n>> PID: " . self::$pid;
        echo "------------------------\n\n";
    }

    public static function teardownAfterClass()
    {
        exec('kill ' . self::$pid);
    }

    public function testReverseTask()
    {
        $task = self::$client->addTask("reverse", "ABC123");
        $result = self::$client->runTasks();
        $this->assertEquals("321CBA", $result);
    }
}

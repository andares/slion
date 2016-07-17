<?php
namespace Slion\Console\Job;

/**
 * Description of Job
 *
 * @author andares
 */
abstract class Job {
    abstract public function send2Storage($id, $time, $job);
    abstract public function regress($id, $time, $job);
    abstract public function done($id);
    abstract public function restoreQueue();
    abstract public function cleanQueue();
}

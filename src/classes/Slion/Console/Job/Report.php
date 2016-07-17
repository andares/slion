<?php
namespace Slion\Console\Job;

/**
 * Description of Report
 *
 * @author andares
 *
 * @property type $job_done_total
 * @property type $job_done_by_queue
 * @property type $job_remain
 * @property type $pop_delay_avg
 * @property type $pop_delay_danger
 * @property type $job_cost_avg
 * @property type $job_cost_danger
 * @property type $memory_usage
 * @property type $memory_peak_usage
 */
class Report extends Slion\Meta {
    /**
     * 定义任务从投递到取出的危险阀值
     * @var int
     */
    private static $danger_pop_delay   = 200;

    /**
     * 任务处理消耗时间的危险阀值
     * @var int
     */
    private static $danger_job_cost    = 5;

    protected static $_schema = [
        'logged_at'             => 0,
        'job_done_total'        => 0,
        'job_done_by_queue'     => [],
        'job_remain'            => [],
        'pop_delay_avg'         => 0,
        'pop_delay_danger'      => 0,
        'job_cost_avg'          => 0,
        'job_cost_danger'       => 0,
        'memory_usage'          => 0,
        'memory_peak_usage'     => 0,
    ];

    public $pop_delay_total    = 0;
    public $job_cost_total     = 0;

    private $start_time;

    public function logStart() {
        $this->start_time = time();
    }

    public function logDone($queue_key, $pop_delay) {
        $this->job_done_total += 1;
        !isset($this->_data['job_done_by_queue'][$queue_key]) &&
            $this->_data['job_done_by_queue'][$queue_key] = 0;
        $this->_data['job_done_by_queue'][$queue_key]++;

        $job_cost = time() - $this->start_time;
        $job_cost > static::$danger_job_cost && $this->job_cost_danger += 1;
        $this->job_cost_total += $job_cost;

        $pop_delay > static::$danger_pop_delay && $this->pop_delay_danger += 1;
        $this->pop_delay_total += $pop_delay;
    }

    public function setRemain($queue_key, $value) {
        $this->_data['job_remain'][$queue_key] = $value;
    }

    public function getQueueKeys() {
        return array_keys($this->job_done_by_queue);
    }

    public function formate() {
        // 计算平时耗时和等待时间
        $this->job_cost_total   &&
            $this->job_cost_avg  = round($this->job_cost_total    / $this->job_done_total, 4);
        $this->pop_delay_total  &&
            $this->pop_delay_avg = round($this->pop_delay_total   / $this->job_done_total, 4);

        $arr = $this->toArray();

        $arr['logged_at']           = date("Y-m-d H:i:s", $arr['logged_at']);
        $arr['job_done_by_queue']   = json_encode($arr['job_done_by_queue']);
        $arr['job_remain']          = json_encode($arr['job_remain']);
        $arr['memory_usage']        = $this->convertSize($arr['memory_usage']);
        $arr['memory_peak_usage']   = $this->convertSize($arr['memory_peak_usage']);

        return $arr;
    }

    protected function _confirm_logged_at($val) {
        return time();
    }

    protected function _confirm_memory_usage($val) {
        return \memory_get_usage(true);
    }

    protected function _confirm_memory_peak_usage($val) {
        return \memory_get_peak_usage(true);
    }

    protected function convertSize($size)
    {
       $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }
}

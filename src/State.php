<?php

namespace mglaman\Tito;


use mglaman\Tito\Task\TaskInterface;
use mglaman\Tito\Utility\Timer;

class State {
  protected $maxProcesses = 5;
  protected $totalProcesses = 75;
  protected $jobsStarted = 0;
  /** @var \mglaman\Tito\Fork[] */
  protected $currentJobs = [];
  protected $seenPids = [];

  protected $results = [];

  public $jobQueue = [];

  public function __construct($values = []) {
    foreach ($values as $key => $value) {
      $this->{$key} = $value;
    }
  }

  public function isValid() {
    return ($this->totalProcesses === -1) || ($this->jobsStarted < $this->totalProcesses);
  }

  public function pushJob(Fork $fork) {
    $this->currentJobs[$fork->getPid()] = $fork;
    $this->jobQueue[get_class($fork->getTask())][$fork->getPid()] = $fork;
    $this->seenPids[] = $fork->getPid();
    Timer::start($fork->getPid());
    $this->jobsStarted++;
  }

  public function popJob($pid) {
    Timer::stop($pid);
    $this->pushResult($this->currentJobs[$pid]->getTask(), Timer::read($pid));
    $fork = $this->currentJobs[$pid];
    unset($this->jobQueue[get_class($fork->getTask())][$fork->getPid()]);
    unset($this->currentJobs[$pid]);
    unset($fork);
  }

  /**
   * @return int
   */
  public function getMaxProcesses(): int {
    return $this->maxProcesses;
  }

  /**
   * @param int $maxProcesses
   *
   * @return State
   */
  public function setMaxProcesses(int $maxProcesses): State {
    $this->maxProcesses = $maxProcesses;
    return $this;
  }

  /**
   * @return int
   */
  public function getTotalProcesses(): int {
    return $this->totalProcesses;
  }

  /**
   * @param int $totalProcesses
   *
   * @return State
   */
  public function setTotalProcesses(int $totalProcesses): State {
    $this->totalProcesses = $totalProcesses;
    return $this;
  }

  /**
   * @return \mglaman\Tito\Fork[]
   */
  public function getCurrentJobs(): array {
    return $this->currentJobs;
  }

  /**
   * @return array
   */
  public function getSeenPids(): array {
    return $this->seenPids;
  }

  public function pushResult(TaskInterface $task, $time) {
    $this->results[get_class($task)][] = $time;
  }

  public function getResults() {
    return $this->results;
  }


}

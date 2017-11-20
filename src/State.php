<?php

namespace mglaman\Tito;


class State {
  protected $maxProcesses = 5;
  protected $totalProcesses = 20;
  protected $jobsStarted = 0;
  /** @var \mglaman\Tito\Fork[] */
  protected $currentJobs = [];
  protected $seenPids = [];

  public function __construct($values = []) {
    foreach ($values as $key => $value) {
      $this->{$key} = $value;
    }
  }

  public function isValid() {
    return ($this->totalProcesses === -1) || ($this->jobsStarted <= $this->totalProcesses);
  }

  public function pushJob(Fork $fork) {
    $this->currentJobs[$fork->getPid()] = $fork;
    $this->seenPids[] = $fork->getPid();
    $this->jobsStarted++;
  }

  public function popJob($pid) {
    unset($this->currentJobs[$pid]);
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



}

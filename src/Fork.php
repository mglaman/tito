<?php

namespace mglaman\Tito;

use mglaman\Tito\Task\Task;

class Fork {
  private $pid = 0;
  private $oid = 0;

  private $task;

  public function __construct(Task $task) {
    $this->task = $task;
  }

  public static function spawn(Task $task): Fork {
    $fork = new static($task);
    $fork->start();
    return $fork;
  }

  public function start() {
    $pid = pcntl_fork();

    switch ($pid) {
      case -1:
        print 'Could not launch new job, exiting' . PHP_EOL;
        exit(1);

      case 0:
        //Forked child, do your deeds....
        $this->task->run();
        exit(0);

      default:
        $this->pid = $pid;
        $this->oid = posix_getpid();
    }
  }

  public function __destruct() {
    if (0 !== $this->pid && posix_getpid() === $this->oid) {
      $this->kill();
    }
  }

  public function isRunning(): bool {
    return 0 !== $this->pid && FALSE !== posix_getpgid($this->pid);
  }

  public function getPid(): int {
    return $this->pid;
  }

  public function kill() {
    if ($this->isRunning()) {
      $this->signal(SIGKILL);
    }
    $this->pid = 0;
  }

  public function signal(int $signo) {
    if ($this->pid !== 0) {
      posix_kill($this->pid, $signo);
    }
  }

}

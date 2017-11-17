<?php

namespace mglaman\Tito;


class State {
  static $max_processes = 5;
  static $total_processes = 10;
  static $jobs_started = 0;
  /** @var \mglaman\Tito\Fork[] */
  static $current_jobs = [];
  static $parent_id = NULL;
  static function isValid() {
    return self::$jobs_started <= self::$total_processes;
  }
}

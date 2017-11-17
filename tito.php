<?php

require 'vendor/autoload.php';

use Facebook\WebDriver\Chrome\ChromeDriverService;
use mglaman\Tito\State;
use mglaman\Tito\Task\CommerceGuysTask;
use mglaman\Tito\Task\DrupalCommerceOrgTask;
use mglaman\Tito\Task\DrupalOrgTask;
use React\EventLoop\Timer\TimerInterface;

// Required if attempting ChromeDriver. Currently not provided in sample.
putenv(ChromeDriverService::CHROME_DRIVER_EXE_PROPERTY . '=' . __DIR__ . '/bin/chromedriver');

declare(ticks = 1);

const DEBUG = true;
function logger(String $string) {
  if (DEBUG === TRUE) print $string;
}

$loop = React\EventLoop\Factory::create();
$loop->addPeriodicTimer(1, function () {
  if (State::isValid()) {
    if (count(State::$current_jobs) < State::$max_processes) {
      logger("Added a job\n");
      $possible_jobs = [
        // Mink/Goutte
        new DrupalOrgTask(), new DrupalCommerceOrgTask(),
        // PhantomJS.
        new CommerceGuysTask(),
      ];
      $fork = \mglaman\Tito\Fork::spawn($possible_jobs[array_rand($possible_jobs)]);
      $fork->start();
      State::$current_jobs[$fork->getPid()] = $fork;
      State::$jobs_started++;
    }
  }
});
$loop->addPeriodicTimer(0.5, function() {
  // logger("Current pids: " . implode(', ', array_keys(\State::$current_jobs)) . "\n");
});
$loop->addPeriodicTimer(0.5, function() {
  foreach (State::$current_jobs as $pid => $current_job) {
    if ($current_job->isRunning()) {
      logger("$pid is currently running\n");
    } else {
      unset(State::$current_jobs[$pid]);
      logger("$pid is removed\n");
    }
  }

});
$loop->addPeriodicTimer(1, function(TimerInterface $timer) {
  if (empty(State::$current_jobs) && !State::isValid()) {
    $timer->getLoop()->stop();
    logger("All jobs done, killed the loop\n");
  }
});
$loop->run();

<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 11/19/17
 * Time: 5:54 PM
 */

namespace mglaman\Tito\Command;

use Facebook\WebDriver\Chrome\ChromeDriverService;
use mglaman\Tito\Fork;
use mglaman\Tito\State;
use mglaman\Tito\TaskFileParser;
use React\EventLoop\Factory;
use React\EventLoop\Timer\TimerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command {

  /**
   * @var \Symfony\Component\Console\Input\InputInterface
   */
  protected $input;

  /**
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * @var \mglaman\Tito\State
   */
  protected $state;

  protected $taskClasses = [];

  /**
   * @var \React\EventLoop\LoopInterface
   */
  protected $loop;

  protected function configure() {
    $this
      ->setName('test')
      ->addArgument('titofile', InputArgument::REQUIRED, 'Titofile')
      ->addOption('max', null, InputOption::VALUE_OPTIONAL, 'Max running tests', 5)
      ->addOption('total', null, InputOption::VALUE_OPTIONAL, 'Total tests to run', 100)
      ->setDescription('Runs the load test');
  }


  protected function initialize(InputInterface $input, OutputInterface $output) {
    parent::initialize($input, $output);
    // Required if attempting ChromeDriver. Currently not provided in sample.
    putenv(ChromeDriverService::CHROME_DRIVER_EXE_PROPERTY . '=' . __DIR__ . '/bin/chromedriver');
    $this->input = $input;
    $this->output= $output;

    $this->state = new State();
    $this->state->setMaxProcesses($input->getOption('max'));
    $this->state->setTotalProcesses($input->getOption('total'));
    $this->logger(sprintf('Maximum concurrent requests: %s', $this->state->getMaxProcesses()));
    $this->logger(sprintf('Total requests: %s', $this->state->getTotalProcesses()));

    $this->loop = Factory::create();
    pcntl_signal(SIGTERM, [$this, 'terminate']);
    pcntl_signal(SIGINT, [$this, 'terminate']);
  }

  public function terminate() {
    $this->loop->stop();
    foreach ($this->state->getCurrentJobs() as $pid => $current_job) {
      $current_job->kill();
      $this->state->popJob($pid);
    }
    $this->logger("All jobs done, killed the loop");
    $this->logger(sprintf('There were %s jobs', count($this->state->getSeenPids())));
    $this->results();
  }


  protected function execute(InputInterface $input, OutputInterface $output) {
    $titofile = $this->input->getArgument('titofile');
    require $titofile;
    $task_file_parser = new TaskFileParser($titofile);

    $this->taskClasses = $task_file_parser->getClasses();

    $this->loop->addPeriodicTimer(0.1, function () {
      pcntl_signal_dispatch();
    });

    // Create a timer for each task.
    foreach ($this->taskClasses as $class) {
      $this->loop->addPeriodicTimer(1, function () use ($class) {
        if (!isset($this->state->jobQueue[$class])) {
          $this->state->jobQueue[$class] = [];
        }
        if ($this->state->isValid()) {
          if (count($this->state->jobQueue[$class]) < $class::getNumberOfUsers()) {
            $this->logger("Added a $class job");
            $fork = Fork::spawn(new $class());
            $fork->start();
            $this->state->pushJob($fork);
          }
        }
      });
    }

    $this->loop->addPeriodicTimer(0.5, function() {
      foreach ($this->state->getCurrentJobs() as $pid => $current_job) {
        if ($current_job->isRunning()) {
          // $this->logger("$pid is currently running");
        } else {
          $this->state->popJob($pid);
          $this->logger("$pid is removed");
        }
      }

    });
    $this->loop->addPeriodicTimer(1, function(TimerInterface $timer) {
      if (empty($this->state->getCurrentJobs()) && !$this->state->isValid()) {
        $timer->getLoop()->stop();
        $this->logger("All jobs done, killed the loop");
        $this->logger(sprintf('There were %s jobs', count($this->state->getSeenPids())));
        $this->results();
      }
    });
    $this->loop->run();
  }

  protected function logger(String $string) {
    if ($this->output->isDebug()) {
      $this->output->writeln(sprintf('<comment>%s</comment>', $string));
    }
  }

  protected function results() {
    $results = $this->state->getResults();

    $processed = [];
    foreach ($results as $task => $times) {
      $processed[$task] = [
        'task' => $task,
        'executions' => count($times),
        'minimum' => round(min($times)),
        'average'=> round(array_sum($times) / count($times)),
        'maximum' => round(max($times)),
      ];
    }

    $table = new Table($this->output);
    $table
      ->setHeaders(['Task', 'Executions', 'Minimum', 'Average', 'Maximum'])
      ->setRows($processed);
    $table->render();
  }
}

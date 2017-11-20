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
use mglaman\Tito\Task\CommerceGuysTask;
use mglaman\Tito\Task\DrupalCommerceOrgTask;
use mglaman\Tito\Task\DrupalOrgTask;
use React\EventLoop\Factory;
use React\EventLoop\Timer\TimerInterface;
use Symfony\Component\Console\Command\Command;
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

  protected function configure() {
    $this
      ->setName('test')
      ->addOption('max', null, InputOption::VALUE_OPTIONAL, 'Max running tests', 5)
      ->addOption('total', null, InputOption::VALUE_OPTIONAL, 'Total tests to run', 20)
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
  }


  protected function execute(InputInterface $input, OutputInterface $output) {
    $loop = Factory::create();
    $loop->addPeriodicTimer(1, function () {
      if ($this->state->isValid()) {
        if (count($this->state->getCurrentJobs()) < $this->state->getMaxProcesses()) {
          $this->logger("Added a job");
          $possible_jobs = [
            // Mink/Goutte
            new DrupalOrgTask(), new DrupalCommerceOrgTask(),
            // PhantomJS.
            new CommerceGuysTask(),
          ];
          $fork = Fork::spawn($possible_jobs[array_rand($possible_jobs)]);
          $fork->start();

          $this->state->pushJob($fork);
        }
      }
    });
    $loop->addPeriodicTimer(0.5, function() {
      foreach ($this->state->getCurrentJobs() as $pid => $current_job) {
        if ($current_job->isRunning()) {
          $this->logger("$pid is currently running");
        } else {
          $this->state->popJob($pid);
          $this->logger("$pid is removed");
        }
      }

    });
    $loop->addPeriodicTimer(1, function(TimerInterface $timer) {
      if (empty($this->state->getCurrentJobs()) && !$this->state->isValid()) {
        $timer->getLoop()->stop();
        $this->logger("All jobs done, killed the loop");
        $this->logger(sprintf('There were %s jobs', count($this->state->getSeenPids())));
      }
    });
    $loop->run();
  }

  protected function logger(String $string) {
    if ($this->output->isDebug()) {
      $this->output->writeln(sprintf('<comment>%s</comment>', $string));
    }
  }
}

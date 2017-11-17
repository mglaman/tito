<?php

namespace mglaman\Tito\Task;

use Behat\Mink\{Mink,Session};
use Behat\Mink\Driver\{GoutteDriver, Goutte\Client as GoutteClient};

class DrupalOrgTask extends Task {
  function doTask() {
    $mink = new Mink(array(
      'goutte' => new Session(new GoutteDriver(new GoutteClient())),
    ));
    $session = $mink->getSession('goutte');
    $session->visit("https://www.drupal.org/");
    $session->getPage()->clickLink('Download & Extend');
    sleep(1);
    $session->getPage()->clickLink('Try a hosted Drupal demo');
    sleep(0.5);
    $session->getPage()->clickLink('Find out more about the Drupal Association Hosting Supporter Program');
    echo $session->getCurrentUrl() . PHP_EOL;
  }
}

<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 11/16/17
 * Time: 10:49 PM
 */

namespace mglaman\Tito\Task;

use Behat\Mink\{Mink,Session};
use Behat\Mink\Driver\{GoutteDriver, Goutte\Client as GoutteClient};

class DrupalCommerceOrgTask extends Task {
  function doTask() {
    $mink = new Mink(array(
      'goutte' => new Session(new GoutteDriver(new GoutteClient())),
    ));
    $session = $mink->getSession('goutte');
    $session->visit("https://drupalcommerce.org/");
    usleep(100);
    $session->getPage()->clickLink('Documentation');
    usleep(300);
    $session->getPage()->clickLink('Installing Drupal Commerce');
    echo $session->getCurrentUrl() . PHP_EOL;
  }
}

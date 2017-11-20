<?php

/**
 * Contains the test cases as per-class.
 */

use Behat\Mink\{Mink,Session};
use Behat\Mink\Driver\{GoutteDriver, Goutte\Client as GoutteClient};
use mglaman\Tito\Task\Task;
use mglaman\Tito\Task\TaskInterface;

class CommerceGuysTask implements TaskInterface {
  function run() {
    /** @var \Facebook\WebDriver\Remote\RemoteWebDriver $driver */
    $driver = \mglaman\WebDriver\DriverFactory::phantomjs();
    try {
      $driver->get('https://commerceguys.com/celebrate-drupal-commerce-2');
      $driver->findElement(\Facebook\WebDriver\WebDriverBy::linkText('subscribe to our newsletter'))->click();
      sleep(1);
      $driver->findElement(\Facebook\WebDriver\WebDriverBy::linkText('PARTNERS'))->click();
      sleep(0.5);
      $driver->findElement(\Facebook\WebDriver\WebDriverBy::linkText('Become a Commerce Guys Delivery Partner'))->click();
      sleep(0.25);
    } catch (\Exception $e) {
      $driver->takeScreenshot('failure-' . getmypid() . '.png');
    }
    echo $driver->getCurrentUrl() . PHP_EOL;
  }
}

class DrupalCommerceOrgTask implements TaskInterface {
  function run() {
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

class DrupalOrgTask implements TaskInterface {
  function run() {
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

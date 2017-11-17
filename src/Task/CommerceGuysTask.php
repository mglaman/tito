<?php

namespace mglaman\Tito\Task;


use Facebook\WebDriver\WebDriverBy;
use mglaman\WebDriver\DriverFactory;

class CommerceGuysTask extends Task {
  function doTask() {
    /** @var \Facebook\WebDriver\Remote\RemoteWebDriver $driver */
    $driver = DriverFactory::phantomjs();
    try {
      $driver->get('https://commerceguys.com/celebrate-drupal-commerce-2');
      $driver->findElement(WebDriverBy::linkText('subscribe to our newsletter'))->click();
      sleep(1);
      $driver->findElement(WebDriverBy::linkText('PARTNERS'))->click();
      sleep(0.5);
      $driver->findElement(WebDriverBy::linkText('Become a Commerce Guys Delivery Partner'))->click();
      sleep(0.25);
    } catch (\Exception $e) {
      $driver->takeScreenshot('failure-' . getmypid() . '.png');
    }
    echo $driver->getCurrentUrl() . PHP_EOL;
  }
}

<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 11/16/17
 * Time: 10:48 PM
 */

namespace mglaman\Tito\Task;

abstract class Task implements TaskInterface {
  public function run() {
    $this->doTask();
  }

  abstract function doTask();
}

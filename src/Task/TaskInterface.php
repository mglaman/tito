<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 11/17/17
 * Time: 7:40 AM
 */

namespace mglaman\Tito\Task;


interface TaskInterface {

  /**
   * The number of active users that can run at a given time.
   *
   * @return int
   */
  public static function getNumberOfUsers(): int;

  /**
   * Executes the task.
   */
  public function run(): void;
}

<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 11/20/17
 * Time: 6:17 AM
 */

namespace mglaman\Tito;


class TaskFileParser {

  protected $taskClasses = [];

  public function __construct($filename) {
    $titofile = file_get_contents($filename);
    $classes = [];
    $tokens = token_get_all($titofile);
    foreach ($tokens as $key => $token) {
      if (is_array($token)) {
        if ($token[0] === T_CLASS) {
          $class_token = $tokens[$key + 2];
          if ($class_token[0] === T_STRING) {
            $classes[] = $class_token[1];
          }
        }
      }
    }
    $this->taskClasses = $classes;
  }

  public function getClasses() {
    return $this->taskClasses;
  }

}

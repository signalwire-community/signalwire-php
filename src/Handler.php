<?php
namespace SignalWire;

class Handler {
  const GLOBAL = 'GLOBAL';
  static protected $queue = array();

  static public function view(){
    print_r(array_keys(self::$queue));
  }

  static public function register(String $evt, Callable $callable, String $uniqueId = self::GLOBAL){
    $event = self::_cleanEventName($evt, $uniqueId);
    if (!isset(self::$queue[$event])) {
      self::$queue[$event] = array();
    }
    self::$queue[$event][] = $callable;
  }

  static public function registerOnce(String $evt, Callable $callable, String $uniqueId = self::GLOBAL){
    $wrapper = function($params) use ($evt, $callable, $uniqueId, &$wrapper) {
      self::deRegister($evt, $wrapper, $uniqueId);
      call_user_func_array($callable, func_get_args());
    };
    self::register($evt, $wrapper, $uniqueId);
  }

  static public function deRegister(String $evt, Callable $callable = null, String $uniqueId = self::GLOBAL){
    if (!self::isQueued($evt, $uniqueId)) {
      return false;
    }
    $event = self::_cleanEventName($evt, $uniqueId);
    if (is_callable($callable) && isset(self::$queue[$event])) {
      foreach (self::$queue[$event] as $index => $handler){
        if ($handler === $callable) {
          unset(self::$queue[$event][$index]);
        }
      }
    } elseif (isset(self::$queue[$event])) {
      self::$queue[$event] = array();
    }
    if (!count(self::$queue[$event])) {
      unset(self::$queue[$event]);
    }
    return true;
  }

  static public function deRegisterAll(String $evt){
    $find = self::_cleanEventName($evt, "");
    foreach (self::$queue as $event => $callbacks){
      if (strpos($event, $find) === 0) {
        unset(self::$queue[$event]);
      }
    }
  }

  static public function trigger(String $evt, $params, String $uniqueId = self::GLOBAL){
    if (!self::isQueued($evt, $uniqueId)) {
      return false;
    }
    $event = self::_cleanEventName($evt, $uniqueId);
    if (isset(self::$queue[$event])) {
      foreach (self::$queue[$event] as $callable){
        $callable($params);
      }
    }
    return true;
  }

  static public function isQueued(String $evt, String $uniqueId = self::GLOBAL){
    $event = self::_cleanEventName($evt, $uniqueId);
    return array_key_exists($event, self::$queue) && count(self::$queue[$event]) > 0;
  }

  static public function clear(){
    self::$queue = array();
  }

  static public function queueCount(String $evt, String $uniqueId = self::GLOBAL){
    if (!self::isQueued($evt, $uniqueId)) {
      return 0;
    }
    $event = self::_cleanEventName($evt, $uniqueId);
    return count(self::$queue[$event]);
  }

  static private function _cleanEventName($event, $uniqueId) {
    return trim($event) . "|" . trim($uniqueId);
  }
}

<?php
namespace SignalWire\Messages;

class Connect extends BaseMessage {
  const VERSION_MAJOR = 2;
  const VERSION_MINOR = 1;
  const VERSION_REVISION = 0;

  protected $method = 'blade.connect';

  public function __construct(String $project, String $token){
    $params = array(
      'version' => array(
        'major' => self::VERSION_MAJOR,
        'minor' => self::VERSION_MINOR,
        'revision' => self::VERSION_REVISION
      ),
      'authentication' => array(
        'project' => $project,
        'token' => $token
      )
    );

    $this->buildRequest(array(
      'method' => $this->method,
      'params' => $params
    ));
  }
}

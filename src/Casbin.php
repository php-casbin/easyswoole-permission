<?php

declare(strict_types=1);

namespace EasySwoole\Permission;

use Casbin\Enforcer;
use Casbin\Model\Model;
use EasySwoole\Permission\Adapters\DatabaseAdapter;
use EasySwoole\Permission\Model\RulesModel;

class Casbin
{
  public $enforcer;

  /**
   * @var DatabaseAdapter
   */
  public $adapter;

  /**
   * @var Model
   */
  public $model;

  /**
   * @var bool
   */
  public $log;

  /**
   * @var array
   */
  public $config = [];

  public function __construct(Config $config)
  {
    if ($config->getAdapter() == DatabaseAdapter::class) {
      $this->adapter = new DatabaseAdapter(new RulesModel());
    }

    $this->model = new Model();
    if ('file' === $config->getModelConfigType()) {
      $this->model->loadModel($config->getModelConfigFilePath());
      $this->model->loadModel($config->getModelConfigFilePath());
    } elseif ('text' === $config->getModelConfigType()) {
      $this->model->loadModel($config->getModelConfigText());
    }

    $this->log = $config->isLogEnable() ?: false;
  }

  public function enforcer($newInstance = false)
  {
    if ($newInstance || is_null($this->enforcer)) {
      $this->enforcer = new Enforcer($this->model, $this->adapter, $this->log);
    }

    return $this->enforcer;
  }

  private function mergeConfig(array $a, array $b)
  {
    foreach ($a as $key => $val) {
      if (isset($b[$key])) {
        if (gettype($a[$key]) != gettype($b[$key])) {
          continue;
        }
        if (is_array($a[$key])) {
          $a[$key] = $this->mergeConfig($a[$key], $b[$key]);
        } else {
          $a[$key] = $b[$key];
        }
      }
    }

    return $a;
  }

  public function __call($name, $params)
  {
    return call_user_func_array([$this->enforcer(), $name], $params);
  }
}

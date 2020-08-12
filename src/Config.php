<?php

declare(strict_types=1);

namespace EasySwoole\Permission;

use EasySwoole\Permission\Adapters\DatabaseAdapter;

class Config
{
  /**
   * @var string
   */
  protected $model_config_type = 'file';

  /**
   * @var string
   */
  protected $model_config_file_path = __DIR__ . '/casbin-rbac-model.conf';

  /**
   * @var string
   */
  protected $model_config_text = '';

  /**
   * @var string
   */
  protected $adapter = DatabaseAdapter::class;

  /**
   * @var bool
   */
  protected $log_enable = false;

  /**
   * @return string
   */
  public function getModelConfigType(): string
  {
    return $this->model_config_type;
  }

  /**
   * @param string $model_config_type
   */
  public function setModelConfigType(string $model_config_type): void
  {
    $this->model_config_type = $model_config_type;
  }

  /**
   * @return string
   */
  public function getModelConfigFilePath(): string
  {
    return $this->model_config_file_path;
  }

  /**
   * @param string $model_config_file_path
   */
  public function setModelConfigFilePath(string $model_config_file_path): void
  {
    $this->model_config_file_path = $model_config_file_path;
  }

  /**
   * @return string
   */
  public function getModelConfigText(): string
  {
    return $this->model_config_text;
  }

  /**
   * @param string $model_config_text
   */
  public function setModelConfigText(string $model_config_text): void
  {
    $this->model_config_text = $model_config_text;
  }

  /**
   * @return string
   */
  public function getAdapter(): string
  {
    return $this->adapter;
  }

  /**
   * @param string $adapter
   */
  public function setAdapter(string $adapter): void
  {
    $this->adapter = $adapter;
  }

  /**
   * @return bool
   */
  public function isLogEnable(): bool
  {
    return $this->log_enable;
  }

  /**
   * @param bool $log_enable
   */
  public function setLogEnable(bool $log_enable): void
  {
    $this->log_enable = $log_enable;
  }
}

<?php

declare(strict_types=1);

namespace EasySwoole\Permission;

use Casbin\Persist\Adapter;

class Config
{
    const CONFIG_TYPE_FILE = 'file';

    const CONFIG_TYPE_TEXT = 'text';

    /**
     * @var string
     */
    protected $model_config_type = self::CONFIG_TYPE_FILE;

    /**
     * @var string
     */
    protected $model_config_file_path = __DIR__ . '/casbin-rbac-model.conf';

    /**
     * @var string
     */
    protected $model_config_text = '';

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var bool
     */
    protected $log_enable = false;

    /**
     * @var string
     */
    protected $logger_class = 'EasySwoole\Permission\Logger';

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
     * @return Adapter|null
     */
    public function getAdapter(): ?Adapter
    {
        return $this->adapter;
    }

    /**
     * @param Adapter $adapter
     */
    public function setAdapter(Adapter $adapter): void
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

    /**
     * Get the logger class
     *
     * @return string
     */
    public function getLoggerClass()
    {
        return $this->logger_class;
    }
}

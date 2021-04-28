<?php


namespace EasySwoole\Permission;

use Casbin\Exceptions\InvalidFilePathException;
use Casbin\Log\Log;
use Casbin\Model\Model;
use EasySwoole\Component\Context\ContextManager;
use EasySwoole\Component\Context\Exception\ModifyError;
use InvalidArgumentException;
use Casbin\Exceptions\CasbinException;
use Casbin\Enforcer as BaseEnforcer;

/**
 * Class Enforcer
 * @package EasySwoole\Permission
 * @mixin EnforcerIDE
 */
class Enforcer
{
    /**
     * @return BaseEnforcer
     * @throws CasbinException
     */
    protected static function init()
    {
        $config = \EasySwoole\EasySwoole\Config::getInstance()->getConf('Enforcer');

        $config = new Config($config);

        if ($config->isLogEnable()) {
            $loggerClass = $config->getLoggerClass();
            $logger = new $loggerClass();
            if (!$logger instanceof Log) {
                throw new InvalidArgumentException("Enforcer config is invalid.");
            }
            Log::setLogger($logger);
        }

        $model = new Model();
        if (Config::CONFIG_TYPE_FILE === $config->getModelConfigType()) {
            $model->loadModel($config->getModelConfigFilePath());
        } elseif (Config::CONFIG_TYPE_TEXT === $config->getModelConfigType()) {
            $model->loadModelFromText($config->getModelConfigText());
        }

        $adapter = $config->getAdapter();

        return new BaseEnforcer($model, $adapter, $config->isLogEnable());
    }

    /**
     * @return BaseEnforcer|mixed
     * @throws CasbinException
     * @throws ModifyError
     */
    protected static function getInstance()
    {
        $enforcer = ContextManager::getInstance()->get(\Casbin\Enforcer::class);
        if (is_null($enforcer)) {
            $enforcer = self::init();
            ContextManager::getInstance()->set(\Casbin\Enforcer::class, $enforcer);
        }

        return $enforcer;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws CasbinException
     * @throws ModifyError
     */
    public static function __callStatic($name, $arguments)
    {
        return self::getInstance()->{$name}(...$arguments);
    }
}
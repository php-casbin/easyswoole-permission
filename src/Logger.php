<?php

namespace EasySwoole\Permission;

use Casbin\Log\Logger as CasbinLogger;
use EasySwoole\Log\Logger as ESLogger;
use EasySwoole\Component\Singleton;

class Logger extends ESLogger implements CasbinLogger
{
    use Singleton;

    /**
     * DefaultLogger is the implementation for a Logger using golang log.
     *
     * @var bool
     */
    public $enable = false;

    /**
     * controls whether print the message.
     *
     * @param bool $enable
     */
    public function enableLog(bool $enable): void
    {
        $this->enable = $enable;
    }

    /**
     * returns if logger is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enable;
    }

    /**
     * formats using the default formats for its operands and logs the message.
     *
     * @param mixed ...$v
     */
    public function write(...$v): void
    {
        if ($this->enable) {
            $content = '';
            foreach ($v as $value) {
                if (\is_array($value)) {
                    $value = json_encode($value);
                } elseif (\is_object($value)) {
                    $value = json_encode($value);
                }
                $content .= $value;
            }
            $content .= PHP_EOL;
            $this->log($content);
        }
    }

    /**
     * formats according to a format specifier and logs the message.
     *
     * @param string $format
     * @param mixed  ...$v
     */
    public function writef(string $format, ...$v): void
    {
        $content = '';
        $content .= sprintf($format, ...$v);
        $content .= PHP_EOL;
        $this->log($content);
    }
}
<?php

namespace PetrKnap\Php\ServiceManager;

use InvalidArgumentException;

/**
 * Config builder
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2016-03-05
 * @category Patterns
 * @package  PetrKnap\Php\ServiceManager
 * @version  0.1
 * @license  https://github.com/petrknap/php-servicemanager/blob/master/LICENSE MIT
 */
class ConfigBuilder
{
    const
        SERVICES = "services",
        INVOKABLES = "invokables",
        FACTORIES = "factories",
        SHARED = "shared",
        SHARED_BY_DEFAULT = "shared_by_default";

    private $config = [
        self::SERVICES => [/* service name => service instance pairs */],
        self::INVOKABLES => [/* service name => class name pairs for classes that do not have required constructor arguments */],
        self::FACTORIES => [/* service name => factory pairs; factories may be any callable, string name resolving to an invokable class, or string name resolving to a FactoryInterface instance */],
        self::SHARED => [/* service name => flag pairs; the flag is a boolean indicating */],
        self::SHARED_BY_DEFAULT => false // boolean, indicating if services in this instance should be shared by default
    ];

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $serviceName
     * @param mixed $serviceInstance
     * @return $this
     */
    public function addService($serviceName, $serviceInstance)
    {
        $this->config[self::SERVICES][$serviceName] = $serviceInstance;

        return $this;
    }

    /**
     * @param string $serviceName
     * @param string $className
     * @return $this
     */
    public function addInvokable($serviceName, $className)
    {
        if (!is_string($className)) {
            $this->throwInvalidArgumentException("Second", "string", $className);
        }

        $this->config[self::INVOKABLES][$serviceName] = $className;

        return $this;
    }

    /**
     * @param string $serviceName
     * @param string|callable $factory
     * @return $this
     */
    public function addFactory($serviceName, $factory)
    {
        if (!is_string($factory) && !is_callable($factory)) {
            $this->throwInvalidArgumentException("Second", "string or callable", $factory);
        }

        $this->config[self::FACTORIES][$serviceName] = $factory;

        return $this;
    }

    /**
     * @param string $serviceName
     * @param bool $isShared
     * @return $this
     */
    public function addShared($serviceName, $isShared)
    {
        if (!is_bool($isShared)) {
            $this->throwInvalidArgumentException("Second", "boolean", $isShared);
        }

        $this->config[self::SHARED][$serviceName] = $isShared;

        return $this;
    }

    /**
     * @param bool $isShared
     * @return $this
     */
    public function setSharedByDefault($isShared)
    {
        if (!is_bool($isShared)) {
            $this->throwInvalidArgumentException("First", "boolean", $isShared);
        }

        $this->config[self::SHARED_BY_DEFAULT] = $isShared;

        return $this;
    }

    private function throwInvalidArgumentException($argumentPosition, $expected, $argument)
    {
        throw new InvalidArgumentException(
            sprintf(
                "%s argument must be %s, %s given.",
                $argumentPosition,
                $expected,
                gettype($argument)
            )
        );
    }
}

<?php

namespace PetrKnap\Nette\Bootstrap\PhpUnit;

use Nette\DI\Container;
use PetrKnap\Nette\Bootstrap\Bootstrap;

abstract class NetteTestCase extends \PHPUnit_Framework_TestCase
{
    const NETTE_BOOTSTRAP_CLASS = null; // string

    /**
     * @var Container
     */
    private static $container;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        if (static::NETTE_BOOTSTRAP_CLASS !== null) {
            $bootstrapClass = static::NETTE_BOOTSTRAP_CLASS;
        } elseif (defined("NETTE_BOOTSTRAP_CLASS")) {
            $bootstrapClass = NETTE_BOOTSTRAP_CLASS;
        } else {
            throw new \RuntimeException("Unknown Nette Bootstrap class");
        }

        self::$container = call_user_func("{$bootstrapClass}::getContainer", array(
            Bootstrap::OPTION_IS_TEST_RUN => true
        ));
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return self::$container;
    }
}

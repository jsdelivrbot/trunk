<?php

namespace PetrKnap\Php\Enum;

use PetrKnap\Php\Enum\Exception\EnumNotFoundException;

/**
 * Abstract enum object
 *
 * @author  Petr Knap <dev@petrknap.cz>
 * @since   2016-01-23
 * @package PetrKnap\Php\Enum
 * @license https://github.com/petrknap/php-enum/blob/master/LICENSE MIT
 */
abstract class AbstractEnum
{
    /**
     * @var self[][]
     */
    private static $instances;

    /**
     * @var mixed[][]
     */
    private static $members = array();

    /**
     * @var string
     */
    private $memberName;

    /**
     * @var mixed
     */
    private $memberValue;

    /**
     * @param string $memberName
     */
    protected function __construct($memberName)
    {
        $members = &self::$members[get_called_class()];

        if (!$members) {
            $members = $this->members();
        }

        if (!($memberName === null && !$this->exists(null))) {
            $this->memberName = $memberName;
            $this->memberValue = $this->get($memberName);
        }
    }

    /**
     * Creates magical factories for easier access to enum
     *
     * @param string $memberName enum key
     * @param array $args ignored
     * @return self
     */
    public static function __callStatic($memberName, array $args)
    {
        $className = get_called_class();

        $instances = &self::$instances[$className];

        if (!is_array($instances)) {
            $instances = array();
        }

        $instance = &$instances[$memberName];

        if (!($instance instanceof $className)) {
            $instance = new $className($memberName);
        }

        return $instance;
    }

    /**
     * Returns member name
     *
     * @return string
     */
    public function getName()
    {
        return $this->memberName;
    }

    /**
     * Returns member value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->memberValue;
    }

    /**
     * Generates and returns members of enum as associative array (keys are names and values are values)
     *
     * NOTE: Can not be merged with static {@link getMembers()} due to its abstraction.
     *
     * @return mixed[] [first_name => first_value, second_name => second_value,...]
     */
    abstract protected function members();

    /**
     * Returns members of enum
     *
     * NOTE: Can not be merged with non-static {@link members()} due to its inner logic
     *
     * @return mixed[] [first_name => first_value, second_name => second_value,...]
     */
    public static function getMembers()
    {
        $className = get_called_class();

        $members = &self::$members[$className];

        if (empty($members)) {
            new $className(null);
        }

        return $members;
    }

    /**
     * @param string $memberName
     * @return bool
     */
    private function exists($memberName)
    {
        return array_key_exists($memberName, self::$members[get_called_class()]);
    }

    /**
     * @param string $memberName
     * @return mixed
     * @throws EnumNotFoundException
     */
    private function get($memberName)
    {
        if (!$this->exists($memberName)) {
            throw new EnumNotFoundException(
                sprintf(
                    "%s does not exist in %s",
                    $memberName,
                    get_called_class()
                )
            );
        }

        return self::$members[get_called_class()][$memberName];
    }

    /**
     * @param mixed $value
     * @return self
     * @throws EnumNotFoundException
     */
    public static function findByValue($value)
    {
        foreach (self::getMembers() as $n => $v) {
            if ($value === $v) {
                return self::__callStatic($n, []);
            }
        }
        throw new EnumNotFoundException(
            sprintf(
                "Value not found in %s",
                get_called_class()
            )
        );
    }
}

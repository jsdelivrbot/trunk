<?php

namespace PetrKnap\Php\FileStorage\Plugin;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;

abstract class AbstractIndexPlugin implements PluginInterface
{
    /**
     * @var mixed
     */
    protected $secondArgument;

    /**
     * @var FilesystemInterface
     */
    protected $fileSystem;

    /**
     * @var string
     */
    private $method;

    /**
     * @param string $method
     * @param mixed $secondArgument
     */
    public function __construct($method, $secondArgument)
    {
        $this->method = $method;
        $this->secondArgument = $secondArgument;
    }

    public static function register(FilesystemInterface $outerFileSystem, $secondArgument)
    {
        foreach (["addPathToIndex", "removePathFromIndex", "getPathsFromIndex"] as $method) {
            $outerFileSystem->addPlugin(new static($method, $secondArgument));
        }
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return $this->method;
    }

    public function handle()
    {
        return call_user_func_array([$this, $this->method], func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function setFilesystem(FilesystemInterface $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * Adds path to indexes
     *
     * @param string $path
     * @param string $innerPath
     */
    abstract public function addPathToIndex($path, $innerPath);

    /**
     * Removes path from indexes
     *
     * @param string $path
     * @param string $innerPath
     */
    abstract public function removePathFromIndex($path, $innerPath);

    /**
     * @see FilesystemInterface::listContents
     * @param string $directory
     * @param bool $recursive
     * @return array
     */
    abstract public function getPathsFromIndex($directory, $recursive);
}

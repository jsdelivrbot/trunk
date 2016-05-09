<?php

namespace PetrKnap\Php\FileStorage\File;

use PetrKnap\Php\FileStorage\FileInterface;
use PetrKnap\Php\FileStorage\StorageManagerInterface;

/**
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2015-05-08
 * @category FileStorage
 * @package  PetrKnap\Php\FileStorage\File
 * @license  https://github.com/petrknap/php-filestorage/blob/master/LICENSE MIT
 */
class File implements FileInterface
{
    /**
     * @var string
     */
    private $pathToFile;

    /**
     * @var string
     */
    private $realPathToFile;


    /**
     * @var StorageManagerInterface
     */
    private $storageManager;

    /**
     * @param StorageManagerInterface $storageManager
     * @param string $pathToFile user-friendly (readable) path to file
     */
    public function __construct(StorageManagerInterface $storageManager, $pathToFile)
    {
        $this->pathToFile = $pathToFile;
        $this->storageManager = $storageManager;
        $this->realPathToFile = $this->storageManager->generateRealPath($pathToFile);
    }

    public function getPathToFile()
    {
        return $this->pathToFile;
    }

    public function exists()
    {
        return file_exists($this->realPathToFile);
    }

    /**
     * @throws FileNotFoundException if file does not exist
     */
    private function checkIfFileExists()
    {
        if (!$this->exists()) {
            throw new FileNotFoundException("File {$this} not found.");
        }
    }

    public function create()
    {
        if ($this->exists()) {
            throw new FileExistsException("File {$this} exists.");
        }

        $path = dirname($this->realPathToFile);
        if (!file_exists($path)) {
            @mkdir($path, 0744, true);
        }

        $return = touch($this->realPathToFile);

        if ($return === false) {
            throw new FileAccessException("Couldn't create file {$this}.");
        }

        $this->storageManager->assignFile($this);

        return $this;
    }

    public function read()
    {
        $this->checkIfFileExists();

        $file = fopen($this->realPathToFile, "rb");

        if ($file === false) {
            throw new FileAccessException("Couldn't open file {$this} for read.");
        }

        $return = stream_get_contents($file);

        if (fclose($file) === false) {
            throw new FileAccessException("Couldn't close file {$this}.");
        }

        if ($return === false) {
            throw new FileAccessException("Couldn't read from file {$this}.");
        }

        return $return;
    }

    public function write($data, $append = false)
    {
        $this->checkIfFileExists();

        $append = ($append === true || $append === FILE_APPEND);

        $file = fopen($this->realPathToFile, $append ? "ab" : "wb");

        if ($file === false) {
            throw new FileAccessException("Couldn't open file {$this} for write.");
        }

        $return = fwrite($file, $data);

        if (fclose($file) === false) {
            throw new FileAccessException("Couldn't close file {$this}.");
        }

        if ($return === false) {
            throw new FileAccessException("Couldn't write to {$this}.");
        }

        return $this;
    }

    public function delete()
    {
        $this->checkIfFileExists();

        if (!@unlink($this->realPathToFile)) {
            throw new FileAccessException("Couldn't delete file {$this}.");
        }

        $this->storageManager->unassignFile($this);

        return $this;
    }

    public function __toString()
    {
        return "'{$this->pathToFile}' stored as '{$this->realPathToFile}'";
    }
}

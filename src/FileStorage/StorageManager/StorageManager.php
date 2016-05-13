<?php

namespace PetrKnap\Php\FileStorage\StorageManager;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Nunzion\Expect;
use PetrKnap\Php\FileStorage\File\File;
use PetrKnap\Php\FileStorage\FileInterface;
use PetrKnap\Php\FileStorage\StorageManager\Exception\AssignException;
use PetrKnap\Php\FileStorage\StorageManager\Exception\IndexDecodeException;
use PetrKnap\Php\FileStorage\StorageManager\Exception\IndexReadException;
use PetrKnap\Php\FileStorage\StorageManager\Exception\IndexWriteException;
use PetrKnap\Php\FileStorage\StorageManagerInterface;

/**
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2016-05-09
 * @category FileStorage
 * @package  PetrKnap\Php\FileStorage\StorageManager
 * @license  https://github.com/petrknap/php-filestorage/blob/master/LICENSE MIT
 */
class StorageManager implements StorageManagerInterface
{
    const INDEX_FILE = "index.json";
    const INDEX_FILE__DATA = "files";
    const INDEX_FILE__DATA__PATH_TO_FILE = "ptf";

    /**
     * @var FileSystemInterface
     */
    private $filesystem;

    /**
     * @param string|FilesystemInterface $storagePathOrFilesystem
     */
    public function __construct($storagePathOrFilesystem)
    {
        if (is_string($storagePathOrFilesystem)) {
            $storagePathOrFilesystem = new Filesystem(new Local($storagePathOrFilesystem));
        }
        Expect::that($storagePathOrFilesystem)->isInstanceOf(FilesystemInterface::class);

        $this->filesystem = $storagePathOrFilesystem;
    }

    /**
     * @inheritdoc
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @inheritdoc
     */
    public function getPathToFile(FileInterface $file)
    {
        $pathToFile = $file->getPath();

        $sha1 = sha1($pathToFile);
        $md5 = md5($pathToFile);
        $lastDotPosition = strrpos($pathToFile, ".");
        $ext = $lastDotPosition === false ? "" : substr($pathToFile, $lastDotPosition);

        $dirs = str_split($sha1, 2);

        $sha1Prefix = array_pop($dirs);

        $fileName = "{$sha1Prefix}-{$md5}{$ext}";

        $realPath = "";
        foreach ($dirs as $dir) {
            $realPath = "{$realPath}/{$dir}";
        }
        $realPath = "{$realPath}/{$fileName}";

        return $realPath;
    }

    /**
     * Returns path to index file
     *
     * @param FileInterface $file
     * @return string
     */
    private function getPathToIndex(FileInterface $file)
    {
        return dirname($this->getPathToFile($file)) . "/" . self::INDEX_FILE;
    }

    /**
     * Loads index file
     *
     * @param string $pathToIndex
     * @return array
     * @throws IndexDecodeException
     * @throws IndexReadException
     */
    private function readIndex($pathToIndex)
    {
        if ($this->filesystem->has($pathToIndex)) {
            $contentOfIndex = @$this->filesystem->read($pathToIndex);

            if ($contentOfIndex === false) {
                throw new IndexReadException("Could not read index file '{$pathToIndex}'");
            }

            $decodedIndex = json_decode($contentOfIndex, true);

            if ($decodedIndex === null) {
                throw new IndexDecodeException("Could not decode index file '{$pathToIndex}'");
            }

            return $decodedIndex;
        } else {
            return [];
        }
    }

    /**
     * Saves index file
     *
     * @param string $pathToIndex
     * @param array $data
     * @throws IndexWriteException
     */
    private function writeIndex($pathToIndex, array $data)
    {
        if (!$this->filesystem->has($pathToIndex)) {
            $return = @$this->filesystem->write($pathToIndex, json_encode($data));
        } else {
            $return = @$this->filesystem->update($pathToIndex, json_encode($data));
        }

        if ($return === false) {
            throw new IndexWriteException("Could not read index file '{$pathToIndex}'");
        }
    }

    /**
     * @inheritdoc
     */
    public function assignFile(FileInterface $file)
    {
        if (!$file->exists()) {
            throw new AssignException("Could not assign nonexistent file");
        }

        $pathToIndex = $this->getPathToIndex($file);
        $data = $this->readIndex($pathToIndex);
        $files = &$data[self::INDEX_FILE__DATA][basename($this->getPathToFile($file))];
        $files = [
            self::INDEX_FILE__DATA__PATH_TO_FILE => $file->getPath()
        ];
        $this->writeIndex($pathToIndex, $data);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function unassignFile(FileInterface $file)
    {
        $pathToIndex = $this->getPathToIndex($file);
        $data = $this->readIndex($pathToIndex);
        unset($data[self::INDEX_FILE__DATA][basename($this->getPathToFile($file))]);
        $this->writeIndex($pathToIndex, $data);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFiles()
    {
        foreach ($this->filesystem->listContents("/", true) as $item) {
            $pathToItem = "/{$item["path"]}";
            if (preg_match('|' . self::INDEX_FILE . '$|', $pathToItem)) {
                $index = $this->readIndex($pathToItem);
                foreach ($index[self::INDEX_FILE__DATA] as $file => $metaData) {
                    yield new File($this, $metaData[self::INDEX_FILE__DATA__PATH_TO_FILE]);
                }
            }
        }
    }
}

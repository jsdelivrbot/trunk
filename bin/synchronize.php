#!/usr/bin/env php
<?php

$synchronize = new Synchronize();

foreach(scandir(__DIR__ . "/../src/") as $package) {
    if (in_array($package, array(".", ".."))) {
        continue;
    }
    printf("Processing %s:\n", $package);

    print("\t* Register package");
    $synchronize->registerPackage($package);
    print(" [done]\n");

    print("\t* Update git");
    $synchronize->git($package);
    print(" [done]\n");

    print("\t* Update LICENSE");
    $synchronize->license($package);
    print(" [done]\n");

    print("\t* Update composer.json");
    $synchronize->composer($package);
    print(" [done]\n");

    print("\t* Update phpunit.xml");
    $synchronize->phpunit($package);
    print(" [done]\n");
}

class Synchronize
{
    private $composerFile;
    private $composer;
    private $composerRequireDev;

    public function __construct()
    {
        $this->composerFile = __DIR__ . "/../composer.json";
        $this->composer = json_decode($this->read($this->composerFile), true);
        $this->composerRequireDev = [
            "phpunit/phpunit" => $this->composer["require-dev"]["phpunit/phpunit"]
        ];
        $this->composer["require-dev"] = $this->composerRequireDev;
    }

    public function git($package)
    {
        $this->write(
            __DIR__ . "/../src/" . $package . "/.gitignore",
            $this->read(__DIR__ . "/../.gitignore")
        );

        $this->write(
            __DIR__ . "/../src/" . $package . "/.gitattributes",
            $this->read(__DIR__ . "/../.gitattributes")
        );
    }

    public function license($package)
    {
        $this->write(
            __DIR__ . "/../src/" . $package . "/LICENSE",
            $this->read(__DIR__ . "/../LICENSE")
        );
    }

    public function composer($package)
    {
        $composerFile = __DIR__ . "/../src/" . $package . "/composer.json";
        $composer = json_decode($this->read($composerFile), true);

        $composer["WARNING"] = "This file is updated automatically. All keys will be overwritten, except of 'description' and 'require'.";
        $composer["name"] = $this->composer["name"] . "-" . strtolower($package);
        $composer["homepage"] = $this->composer["homepage"] . "-" . strtolower($package);
        $composer["license"] = $this->composer["license"];
        $composer["authors"] = $this->composer["authors"];
        $composer["require"] = array_merge($composer["require"], $this->composer["require"]);
        $composer["require-dev"] = $this->composerRequireDev;
        $composer["autoload"] = array("psr-4" => array("PetrKnap\\Php\\" . $package ."\\" => "."));

        $this->write($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }

    public function phpunit($package)
    {
        $this->write(
            __DIR__ . "/../src/" . $package . "/phpunit.xml",
            $this->read(__DIR__ . "/../phpunit.xml")
        );
    }

    public function registerPackage($package)
    {
        $this->composer["require-dev"]["petrknap/php-" . strtolower($package)] = "dev-master";
        $this->write($this->composerFile, json_encode($this->composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }

    private function read($file)
    {
        $content = file_get_contents($file);
        if (false === $content) {
            throw new Exception("Could not read from '{$file}'");
        }

        return $content;
    }

    private function write($file, $content)
    {
        if (false === file_put_contents($file, $content)) {
            throw new Exception("Could not write to '{$file}'");
        }
    }
}

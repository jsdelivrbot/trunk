# php-profiler

PHP profiler by [Petr Knap].

* [What is profiling?](#what-is-profiling)
* [Components](#components)
    * [Profile](#profile)
    * [SimpleProfiler](#simpleprofiler)
        * [Usage](#usage)
    * [AdvancedProfiler](#advancedprofiler)
* [How to install](#how-to-install)


## What is profiling?

> In software engineering, **profiling** (*"program profiling"*, *"software profiling"*) is a form of dynamic program analysis that measures, for example, the space (memory) or time complexity of a program, the usage of particular instructions, or the frequency and duration of function calls. Most commonly, profiling information serves **to aid program optimization**.
-- [Profiling (computer programming) - Wikipedia, The Free Encyclopedia]


## Components

### Profile

[`Profile`] is base data structure returned by profilers.


### SimpleProfiler

[`SimpleProfiler`] is easy-to-use and quick static class for PHP code profiling. You can extend it and make your own specific profiler just for your use-case.

#### Usage

```php
SimpleProfiler::enable();            // Enable profiler
$img = Image::fromFile("./img.png"); // Do what you need to do before you start profiling
SimpleProfiler::start();             // Start profiling where you wish to start profiling
$img->rotate(180);                   // Do what you need to profile here
$profile = SimpleProfiler::finish(); // Finish profiling where you wish to finish profiling
unset($img);                         // Do what you need to do after you finish profiling
var_dump($profile);                  // Process your profile here
```


### AdvancedProfiler

[`AdvancedProfiler`] is advanced version of [`SimpleProfiler`] and is developed dynamically. If you want to see an example of usage, then visit [`AdvancedProfilerTest`].


## How to install

Run `composer require petrknap/php-profiler` or merge this JSON code with your project `composer.json` file manually and run `composer install`. Instead of `dev-master` you can use [one of released versions].

```json
{
    "require": {
        "petrknap/php-profiler": "dev-master"
    }
}
```

Or manually clone this repository via `git clone https://github.com/petrknap/php-profiler.git` or download [this repository as ZIP] and extract files into your project.



[Petr Knap]:http://petrknap.cz/
[Profiling (computer programming) - Wikipedia, The Free Encyclopedia]:https://en.wikipedia.org/w/index.php?title=Profiling_(computer_programming)&oldid=697419059
[`Profile`]:https://github.com/petrknap/php-profiler/blob/master/src/Profiler/Profile.php
[`SimpleProfiler`]:https://github.com/petrknap/php-profiler/blob/master/src/Profiler/SimpleProfiler.php
[`AdvancedProfiler`]:https://github.com/petrknap/php-profiler/blob/master/src/Profiler/AdvancedProfiler.php
[`AdvancedProfilerTest`]:https://github.com/petrknap/php-profiler/blob/master/tests/Profiler/AdvancedProfilerTest.php
[one of released versions]:https://github.com/petrknap/php-profiler/releases
[this repository as ZIP]:https://github.com/petrknap/php-profiler/archive/master.zip

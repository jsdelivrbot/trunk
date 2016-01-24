# php-enum

Enumerated type for PHP by [Petr Knap].


## What is enum?

> In computer programming, an **enumerated type** (also called **enumeration** or **enum**, or **factor** in the R programming language, and a categorical variable in statistics) is a data type consisting of a set of named values called **elements**, **members**, **enumeral**, or **enumerators** of the type. The enumerator names are usually identifiers that behave as constants in the language. A variable that has been declared as having an enumerated type can be assigned any of the enumerators as a value. In other words, an *enumerated type has values that are different from each other*, and that can be compared and assigned, but which are not specified by the programmer as having any particular concrete representation in the computer's memory; compilers and interpreters can represent them arbitrarily.
-- [Enumerated type - Wikipedia, The Free Encyclopedia]

### Usage of php-enum

#### Enum declaration
```php
/**
 * @method static DayOfWeekEnum SUNDAY()
 * @method static DayOfWeekEnum MONDAY()
 * @method static DayOfWeekEnum TUESDAY()
 * @method static DayOfWeekEnum WEDNESDAY()
 * @method static DayOfWeekEnum THURSDAY()
 * @method static DayOfWeekEnum FRIDAY()
 * @method static DayOfWeekEnum SATURDAY()
 */
class DayOfWeekEnum extends \PetrKnap\Php\Enum\AbstractEnum
{
    protected function __construct($constantName)
    {
        self::setConstants([
            "SUNDAY" => 0,
            "MONDAY" => 1,
            "TUESDAY" => 2,
            "WEDNESDAY" => 3,
            "THURSDAY" => 4,
            "FRIDAY" => 5,
            "SATURDAY" => 6
        ]);
        parent::__construct($constantName);
    }
}
```

#### Enum usage
```php
if (DayOfWeekEnum::FRIDAY() == DayOfWeekEnum::FRIDAY()) {
    echo "This is OK.";
}
```

```php
if (DayOfWeekEnum::FRIDAY() == DayOfWeekEnum::MONDAY()) {
    echo "We are going to Hell!";
}
```

```php
switch($dayOfWeek)
{
    case DayOfWeekEnum::FRIDAY():
        echo "Finally it is Friday!";
        break;
    case DayOfWeekEnum::SATURDAY():
    case DayOfWeekEnum::SUNDAY():
        echo "It is leasure time!";
        break;
    default:
        echo "Just another working day...";
}
```

```php
if (date('w') == DayOfWeekEnum::FRIDAY()->getValue()) {
    echo "Finally it is Friday!";
}
```


## How to install

Run `composer require petrknap/php-enum` or merge this JSON code with your project `composer.json` file manually and run `composer install`. Instead of `dev-master` you can use [one of released versions].

```json
{
    "require": {
        "petrknap/php-enum": "dev-master"
    }
}
```

Or manually clone this repository via `git clone https://github.com/petrknap/php-enum.git` or download [this repository as ZIP] and extract files into your project.



[Petr Knap]:http://petrknap.cz/
[Enumerated type - Wikipedia, The Free Encyclopedia]:https://en.wikipedia.org/w/index.php?title=Enumerated_type&oldid=701057934
[one of released versions]:https://github.com/petrknap/php-enum/releases
[this repository as ZIP]:https://github.com/petrknap/php-enum/archive/master.zip

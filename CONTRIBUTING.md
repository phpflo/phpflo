# Contributing

Contributions are **welcome** and will be fully **credited**.

We accept contributions via Pull Requests on [Github](https://github.com/phpflo/phpflo).


## Pull Requests

- **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - Check your code against the PSR-2 coding standard

- **Add tests!** - Your patch won't be accepted if it doesn't have tests and don't follow our unit tests coding standards.

- **Document any change in behaviour** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [SemVer v2.0.0](http://semver.org/). Randomly breaking public APIs is not an option.

- **Create feature branches** - Don't ask us to pull from your master branch.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](http://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting.


## Tests

### Coding standards
- **Test function name format**

Use camelCase e.g. `testMethodName`

- **FQCN in tests**

Use the `::class` call to when using a fully qualified class namespace

- **Expected Exception**

Use method call `$this->expectException...` on the first lines of your test methods instead of annotation.

- **Class Dependencies bootstraping**

When bootstrapping dependencies of your tested class, mock them when they're interface. Don't use a concrete implementation of your interface.

Also put your base bootstrap logic in the `setUp` function or in `@dataProvider` of your tested class. Giving you a single point of modification if dependency signature changes.

- **`expects` method**

Use the `expects` method to ensure not only what your tested class methods return but also their expected behavior.

```php
<?php

class CalculatorLooper {
    
    /** @var CalculatorInterface $calculator */
    private $calculator;
    
    public function __construct(CalculatorInterface $calculator){
        $this->calculator = $calculator;
    }
    
    public function add(int $loopMax) {
        $i = 1;
        while($i <= $loopMax) {
            $this->calculator->add();   
            $i++;
        }
        
        return 'Addition loop is done!';
    }   
}


class CalculatorLooperTest extends \PHPUnit\Framework\TestCase {
    
    // BAD !
    public function testLoopAdd() {
        $calculator = $this->createMock(CalculatorInterface::class);
        $calculatorLooper = new CalculatorLooper($calculator);
        
        $this->assetSame('Addition loop is done', $calculatorLooper->add(3));
    }
    
    // GOOD !
    public function testLoopAdd() {
        $calculator = $this->createMock(CalculatorInterface::class);
        $calculatorLooper = new CalculatorLooper($calculator);
        $calculator
            ->expects($this->at(3))
            ->method('add');
        
        $this->assertSame('Addition loop is done', $calculatorLooper->add(3));
    }   
}
```

- **Tested class method's return check**

Use `assertSame` over `assertEquals` when possible to ensure strict type checking.

### Running
``` bash
$ bin/phpunit
```


**Happy coding**!
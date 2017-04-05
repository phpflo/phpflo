# Contributing

Contributions are **welcome** and will be fully **credited**.

We accept contributions via Pull Requests on [Github](https://github.com/phpflo/phpflo).

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

Also put your basic bootstrap logic in the `setUp` function or in `@dataProvider` of your tested class. This gives a single point of modification if dependency signature changes.

- **`expects` method**

Use the `expects` method to  not only ensure what your tested class methods return but also their expected behavior.

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
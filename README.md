## Installing heptacom/dependency-resolver

The recommended way to install heptacom/dependency-resolver is through
[Composer](http://getcomposer.org).

Next, run the Composer command to install the latest stable version of dependency-resolver:

```bash
composer require heptacom/dependency-resolver
```

You can then later update dependency-resolver using composer:

 ```bash
composer update heptacom/dependency-resolver
 ```

### Usage
```php
$tree  = [
    'A' => [],
    'B' => ['A'],
    'C' => ['B'],
    'D' => ['C', 'A'],
    'E' => ['C', 'B'],
];
$resolution = \Algorithm\DependencyResolver::resolve($tree);
print($resolution);
// ['A','B','C','D','E']
```
OR 
```php
$tree  = [
    'A' => ['B'],
    'B' => ['C'],
    'C' => ['A'],
];
$resolution = \Algorithm\DependencyResolver::resolve($tree);
// RuntimeException : Circular dependency: C -> A
```
**Documentation**
- <https://www.electricmonk.nl/log/2008/08/07/dependency-resolving-algorithm/>
- <http://mamchenkov.net/wordpress/2016/11/22/dependency-resolution-with-graphs-in-php/>

## Contributors
**Joshua Behrens**
- <https://github.com/JoshuaBehrens>

**Anthony K GROSS** (Original author)
- <http://anthonykgross.fr>
- <https://twitter.com/anthonykgross>
- <https://github.com/anthonykgross>

## Copyright and license
Code and documentation copyright 2020. Code released under [the MIT license](https://github.com/anthonykgross/dependency-resolver/blob/master/LICENSE).


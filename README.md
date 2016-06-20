# jnjxp.routeless
A failed routing responder for [Aura\Router]

[![Latest version][ico-version]][link-packagist]
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]

## Installation
```
composer require jnjxp/routeless
```

## Usage
See [Aura\Router] documentation.

### Instantiation
```php
use Jnjxp\Routeless\RoutingiFailedResponder;

$factories = [
    MyCustomRule::class => function () {
        return new My\Custom\Rule\Responder();
    }
];

$failResponder = new RoutingFailedResponder($factories);

```

### Responder Signature
```php
function __invoke(Request $request, Response $response, Route $route);
```

### [Aura\Router]
See [Handling Failure To Match][Failure]
```php
$route = $matcher->match($request);
if (! $route) {
    $failedRoute = $matcher->getFailedRoute();
    $response = $failedResponder($request, $response, $failedRoute):
}
```

### [Radar\Adr]
See: [ Radar.Project > Container Configuration > *Configuration (aka "Providers")* ][Radar.Project docs].
```php
use Radar\Adr\Boot;
use Jnjxp\Routeless\Config as RoutlessConfig;

$boot = new Boot();
$adr = $boot->adr([RoutelessConfig::class]);

// or
$routelessConfig = new RoutelessConfig(
    [
        MyCustomRule::class => MyCustomResponse::class,
        MyOtherRule::class => MyOtherResponse::class,
    ]
);

$adr = $boot->adr([$routlessConfig]);

```


[Aura\Router]: https://github.com/auraphp/Aura.Router
[Failure]: https://github.com/auraphp/Aura.Router/blob/3.x/docs/getting-started.md#handling-failure-to-match
[Radar\Adr]: https://github.com/radarphp/Radar.Project
[Radar.Project docs]: https://github.com/radarphp/Radar.Project/blob/1.x/docs/container.md#configuration-aka-providers

[ico-version]: https://img.shields.io/packagist/v/jnjxp/routeless.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/jnjxp/jnjxp.routeless/develop.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/jnjxp/jnjxp.routeless.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/jnjxp/jnjxp.routeless.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/jnjxp/routeless
[link-travis]: https://travis-ci.org/jnjxp/jnjxp.routeless
[link-scrutinizer]: https://scrutinizer-ci.com/g/jnjxp/jnjxp.routeless
[link-code-quality]: https://scrutinizer-ci.com/g/jnjxp/jnjxp.routeless

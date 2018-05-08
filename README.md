# zend-stratigility-note

zend-stratigility学习笔记

笔记为3.0版本基础下,要求php版本为 php7.1以上

> 安装 composer require zendframework/zend-diactoros zendframework/zend-stratigility


### 中间件基本使用

1. 创建一个中间件或者中间件管道

2. 使用中间件创建一个复苏

3. 引导服务区监听request

```php
  use Zend\Stratigility\MiddlewarePipe;
  use Zend\Diactoros\Server;

  require __DIR__ . '/../vendor/autoload.php';

  $app    = new MiddlewarePipe();
  $server = Server::createServer(
    [$app, 'handle'],
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
  );

  $server->listen(function ($req, $res) {
    return $res;
  });

```


### 中间件结合到路由使用

中间件代码是在request 与 response之间处理,接受请求,用户进行自定义处理,然后返回相应的输出或者传递到下一个中间件

(基本使用)[https://github.com/ericivan/zend-stratigility-note/blob/master/baseMiddleware/index.php]

```php 

require './vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Stratigility\Middleware;
use function Zend\Stratigility\Middleware;
use function Zend\Stratigility\path;

$app = new \Zend\Stratigility\MiddlewarePipe();

$sever = \Zend\Diactoros\Server::createServer([$app, 'handle'], $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

$app->pipe(middleware(function (ServerRequestInterface $req, RequestHandlerInterface $handle) {
    if (!in_array($req->getUri()->getPath(), ['/', ''], true)) {
        return $handle->handle($req);
    }

    $response = new Response();

    $response->getBody()->write('Hello World');

    return $response;
}));

$app->pipe(path('/foo',middleware(function ($req, $handle) {
    $response = new Response();
    $response->getBody()->write('FOO!');

    return $response;
})));



//Not Found
$app->pipe(new NotFoundHandler(function () {
    return new Response();
}));


$sever->listen(function ($req, $res) {
    return $res;
});
```

上面代码有两个中间件,第一个是登录页,监听根路由,如果路由不是 / 或者空开始,就传递到 handle 处理,反之直接返回 Hello World

第二个路由匹配 /foo,匹配的是 /foo ,/foo/, /foo/anything 的路由地址

最后一个是没有任何路由匹配的处理,会返回一个404的状态


# zend-stratigility-note

zend-stratigility学习笔记

笔记为3.0版本基础下,要求php版本为 php7.1以上,遵循psr-15中间件开发标准

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

[基本使用代码](https://github.com/ericivan/zend-stratigility-note/blob/master/baseMiddleware/index.php)

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

### 中间件可以单独写成一个class,使用 PathMiddlewareDecorator ( 利用辅助方法 path()) 来关联 中间件和路由

> TalkMiddleware 是自定义编写的[中间件](https://github.com/ericivan/zend-stratigility-note/blob/master/src/TalkMiddleware.php)

```php

    $app->pipe(path('/custom', new \Shirly\TalkMiddleware(new Response())));
    
```


###  错误处理

应用中以下几种情况可能需要进行错误处理 

- php自身报错
- 异常抛出
- 中间件无法请求处理

基础代码里面最后的中间件 NotFoundHandler() 是内置的 404 处理中间件,这个类宝行了一个能够提供404状态还有请求方法以及路由
url消息的响应原形实体

需要注意的是该中间件是应用程序最后一个中间件，一旦被响应了，就不会调用更深层嵌套的其他处理

当然，你也可以自己定义，这是[示例](https://github.com/ericivan/zend-stratigility-note/blob/master/src/NotFoundMiddleware.php)

#### php 错误以及异常 处理

Zend\Stratigility\Middleware\ErrorHandler 是一个注册在顶层应用的中间件，作用如下

- 创建一个捕捉 error_handing() 的处理器，使他抛出错误异常实体

- 在中间件委托调用的时候调用

- 如果没有异常捕捉到，结果返回一个response

- 如果没有发现异常，就会引起一个能被捕捉到的异常

- 任何被捕捉到的异常都会被转换成一个错误响应


为了生成错误响应，我们提供在实例化过程中向ErrorHandler注入具有以下签名的可调用对象的功能
```php
$app->pipe(new Middleware\ErrorHandler(
    function (Throwable $e,ServerRequestInterface $req,ResponseInterface $response):ResponseInterface {
        $response->getBody()->write($e->getMessage());

        return $response;
    }
));
```

上面的方法看起来比较麻烦，框架提供了一个默认的实现方法
```php
    // setup error handling
    $app->pipe(new ErrorHandler(new Response(), new ErrorResponseGenerator($isDevelopmentMode));
    
    // setup layers
    $app->pipe(/* ... */);
    $app->pipe(/* ... */);
```

ErrorResponseGenerator 提供了一个5XX系列状态错误的错误信息,并且接受一个是否是开发模式的参数，开发模式会在响应
中返回一些栈调用的信息

当然，ErrorResponseGenerator这东西也是可以自定义的

### 错误监听 ErrorHandler Listeners

ErrorHandler 还提供了绑定监听的功能，当异常被触发或者被捕捉到的时候，提供抛出异常，原始请求，最后的响应，
但是注意的是，这些实体都是不可改变的，所以监听功能一般用于监测或者记录日志

监听回调必须遵循以下格式

```php
    Psr\Http\Message\ResponseInterface;
    Psr\Http\Message\ServerRequestInterface;
    
    function (
        Throwable|Exception $e,
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : void
```

关联监使用 ErrorHandler::attachListener():

```php

$errorHandler->attachListener(function ($throwable, $request, $response) use ($logger) {
    $message = sprintf(
        '[%s] %s %s: %s',
        date('Y-m-d H:i:s'),
        $request->getMethod(),
        (string) $request->getUri(),
        $throwable->getMessage()
    );
    $logger->error($message);
});

```







<?php

require '../vendor/autoload.php';


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Stratigility\Middleware;
use function Zend\Stratigility\Middleware;
use function Zend\Stratigility\path;

$app = new \Zend\Stratigility\MiddlewarePipe();

$sever = \Zend\Diactoros\Server::createServer([$app, 'handle'], $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

//错误处理
$app->pipe(new Middleware\ErrorHandler(
    function (Throwable $e,ServerRequestInterface $req,ResponseInterface $response):ResponseInterface {
        $response->getBody()->write($e->getMessage());

        return $response;
    }
));

$app->pipe(middleware(function (ServerRequestInterface $req, RequestHandlerInterface $handle) {
    if (!in_array($req->getUri()->getPath(), ['/', ''], true)) {
        return $handle->handle($req);
    }
    $response = new Response();

    $response->getBody()->write('Hello World');

    return $response;
}));

$app->pipe(path('/custom', new \Shirly\TalkMiddleware(new Response())));

$app->pipe(path('/foo',middleware(function ($req, $handle) {
    $response = new Response();
    $response->getBody()->write('FOO!');

    return $response;
})));


//Not Found
$app->pipe(new \Shirly\NotFoundMiddleware(new Response()));

$sever->listen(function ($req, $res) {
    return $res;
});
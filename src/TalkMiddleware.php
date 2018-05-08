<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/8
 * Time: 15:37
 */

namespace Shirly;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TalkMiddleware implements MiddlewareInterface
{

    /**
     * @var ResponseInterface 
     */
    private $response;

    public function __construct(
        ResponseInterface $response
    )
    {
        $this->response = $response;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $this->response;

        $response->getBody()->write('Love Shirly');

        return $response;
    }

}
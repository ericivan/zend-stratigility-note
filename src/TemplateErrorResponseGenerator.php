<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/8
 * Time: 11:50
 */

namespace Shirly;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Stratigility\Middleware\ErrorResponseGenerator;

class TemplateErrorResponseGenerator
{
    private $isDevelopmentMode;


    public function __construct(
        $isDevelopmentMode=false
    )
    {
        $this->isDevelopmentMode = $isDevelopmentMode;

//        $this->renderer = $renderer;
    }

    public function __invoke(\Throwable $e, ServerRequestInterface $request, ResponseInterface $response)
    {
        $response = $response->withStatus(404);

    }
}
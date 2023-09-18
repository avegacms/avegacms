<?php

namespace AvegaCms\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use AvegaCms\Utils\Cms;
use ReflectionException;

class ThrottlerCorsFilter implements FilterInterface
{
    /**
     * @param  RequestInterface  $request
     * @param $arguments
     * @return RequestInterface|ResponseInterface|string|void
     * @throws ReflectionException
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (Cms::settings('core.auth.useCors')) {
            if (strtoupper($request->getMethod()) === 'OPTIONS') {
                return Services::response()
                    ->setHeader('Access-Control-Allow-Origin', '*')
                    ->setHeader('Access-Control-Allow-Credentials', 'true')
                    ->setHeader(
                        'Access-Control-Allow-Headers',
                        [
                            'X-API-KEY',
                            'Origin',
                            'DNT',
                            'X-Auth-Token',
                            'X-Requested-With',
                            'X-CustomHeader',
                            'Content-Type',
                            'Content-Length',
                            'Accept',
                            'Access-Control-Request-Method',
                            'Authorization',
                            'Keep-Alive',
                            'User-Agent',
                            'If-Modified-Since',
                            'Cache-Control',
                            'Content-Range',
                            'Range'
                        ]
                    )
                    ->setHeader('Access-Control-Allow-Methods', ['GET', 'PATCH', 'POST', 'PUT', 'OPTIONS', 'DELETE']);
            }

            Services::response()->setHeader('Access-Control-Allow-Origin', '*');
        }

        $throttler = Services::throttler();

        // Restrict an IP address to no more than 1 request per second across the entire site.
        if ($throttler->check(md5($request->getIPAddress()), 60, MINUTE) === false) {
            return Services::response()->setStatusCode(429);
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param  RequestInterface  $request
     * @param  ResponseInterface  $response
     * @param  array|null  $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}

<?php

namespace AvegaCms\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ThrottlerCorsFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, scriptзрз
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (service('settings')->get('core.auth.useCors')) {
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
                    ->setHeader('Access-Control-Allow-Methods', ['GET','PATCH','POST','PUT','OPTIONS','DELETE']);
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
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}

<?php

declare(strict_types=1);

namespace AvegaCms\Filters;

use AvegaCms\Config\Services;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ThrottlerFilter implements FilterInterface
{
    /**
     * @param mixed|null $arguments
     *
     * @return ResponseInterface|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Restrict an IP address to no more than 1 request per second across the entire site.
        if (Services::throttler()->check(md5($request->getIPAddress()), 60, MINUTE) === false) {
            return Services::response()->setStatusCode(429);
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param array|null $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

<?php

declare(strict_types = 1);

namespace AvegaCms\Filters;

use AvegaCms\Exceptions\AvegaCmsApiException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Config\Services;

class ThrottlerFilter implements FilterInterface
{
    /**
     * @param  RequestInterface  $request
     * @param $arguments
     * @return ResponseInterface|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Restrict an IP address to no more than 1 request per second across the entire site.
        if (Services::throttler()->check(md5($request->getIPAddress()), 60, MINUTE) === false) {
            return Services::response()->setStatusCode(429);
        }

        try {
            if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
                if ($request->getBody() === null) {
                    throw AvegaCmsApiException::forNoData();
                }

                json_decode($request->getBody(), false);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw AvegaCmsApiException::forInvalidJSON(json_last_error_msg());
                }

                if ($request->getBody() !== 'php://input' && empty(request()->getJSON(true))) {
                    throw AvegaCmsApiException::forUndefinedData();
                }
            }
        } catch (AvegaCmsApiException $e) {
            Services::response()->setStatusCode(400, $e->getMessage())->send();
            exit();
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

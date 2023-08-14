<?php

namespace AvegaCms\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Exception;
use AvegaCms\Libraries\Authentication\Authentication;
use AvegaCms\Libraries\Authentication\Exceptions\AuthenticationException;

class AuthenticationFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param  RequestInterface  $request
     * @param  array|null  $arguments
     *
     * @return ResponseInterface
     */
    public function before(RequestInterface $request, $arguments = null): ResponseInterface
    {
        $response = Services::response();

        try {
            (new Authentication(service('settings')->get('core.auth')))->checkUserAccess();
        } catch (AuthenticationException|Exception $e) {
            return Services::response()->setStatusCode($e->getCode(), $e->getMessage());
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

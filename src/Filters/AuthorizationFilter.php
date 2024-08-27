<?php

declare(strict_types=1);

namespace AvegaCms\Filters;

use AvegaCms\Config\Services;
use AvegaCms\Libraries\Authorization\Authorization;
use AvegaCms\Libraries\Authorization\Exceptions\AuthenticationException;
use AvegaCms\Utilities\Cms;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class AuthorizationFilter implements FilterInterface
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
     * @param array|null $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            if (($settings = Cms::settings('core')) === null) {
                throw new Exception('Auth settings not found');
            }
            (new Authorization($settings))->checkUserAccess();
            unset($settings);
        } catch (AuthenticationException|Exception $e) {
            return Services::response()->setStatusCode(401, $e->getMessage());
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

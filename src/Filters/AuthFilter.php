<?php

namespace AvegaCms\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class AuthFilter implements FilterInterface
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
     * @param RequestInterface $request
     * @param array|null $arguments
     *
     * @return ResponseInterface
     */
    public function before(RequestInterface $request, $arguments = null): ResponseInterface
    {
        $request = Services::request();
        $response = Services::response();
        $auth = service('settings')->get('core.auth');

        if (empty($authHeader = $request->getServer('HTTP_AUTHORIZATION'))) {
            return $response->setStatusCode(401, 'Access denied');
        }

        if ($auth['useWhiteIpList'] && !empty($auth['whiteIpList']) && in_array(
                $request->getIPAddress(),
                $auth['whiteIpList']
            )) {
            return $response->setStatusCode(401, 'Access denied');
        }

        $authHeader = explode(' ', $authHeader);
        $cont = count($authHeader);

        $authType = match ($authHeader[0]) {
            'Session' => ($cont === 1 && $auth['useSession']) ? ['type' => 'session'] : false,
            'Token'   => ($cont === 2 && $auth['useToken']) ? ['type' => 'token', 'token' => $authHeader[1]] : false,
            'Bearer'  => ($cont === 2 && $auth['useJwt']) ? ['type' => 'jwt', 'token' => $authHeader[1]] : false,
            default   => false
        };

        if ($authType === false) {
            return $response->setStatusCode(401, 'Access denied');
        }

        unset($authHeader, $cont);

        // Проверка прав доступа
        print_r($authType);
        exit();
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}

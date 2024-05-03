<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers;

use AvegaCms\Utilities\Cms;
use ReflectionException;

class Cors extends BaseController
{

    /**
     * @throws ReflectionException
     */
    public function PreFlight()
    {
        if (Cms::settings('core.auth.useCors')) {
            /*
            // Проверяем заголовок Origin для разрешенного источника
            $allowedOrigins = ['http://example.com', 'https://example.com'];
            $origin = $request->getHeaderLine('Origin');

            // Если Origin не в списке разрешенных, то отвечаем ошибкой или другим кодом
            if (!in_array($origin, $allowedOrigins)) {
                return service('response')->setStatusCode(403); // Отправляем код 403 (Forbidden)
            }
            */

            return $this->response->setHeader('Access-Control-Allow-Origin', '*')
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
                ->setHeader('Access-Control-Allow-Methods', ['GET', 'PATCH', 'POST', 'PUT', 'OPTIONS', 'DELETE'])
                ->setStatusCode(200);
        }

        return $this->response->setStatusCode(400);
    }
}

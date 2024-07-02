<?php

declare(strict_types = 1);

namespace AvegaCms\Traits;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

trait AvegaCmsApiResponseTrait
{
    use ResponseTrait;

    /**
     * @param  array|string  $messages
     * @param  int  $status  HTTP status code
     * @param  string|null  $code  Custom, API-specific, error code
     * @param  string  $customMessage
     * @return ResponseInterface
     */
    protected function fail(
        array|string $messages,
        int $status = 400,
        ?string $code = null,
        string $customMessage = ''
    ): ResponseInterface {
        $response = [
            'error' => [
                'code'    => $code ?? $status,
                'message' => ( ! is_array($messages)) ? [$messages] : $messages
            ]
        ];

        return $this->respond($response, $status, $customMessage);
    }
    
    /**
     * @param  array|string|null  $payload
     * @param  array|string|null  $meta
     * @param  string  $message
     * @return ResponseInterface
     */
    protected function cmsRespond(
        array|string|null $payload = null,
        array|string|null $meta = null,
        string $message = ''
    ): ResponseInterface {
        $data = null;
        if ( ! is_null($payload)) {
            $data['data'] = is_array($payload) ? $payload : [$payload];
            if (in_array('pagination', array_keys($data['data']))) {
                $meta['pagination'] = $data['data']['pagination'];
                unset($data['data']['pagination']);
            }

            if (in_array('list', array_keys($data['data']))) {
                $data['data'] = $data['data']['list'];
            }
        }

        if ( ! is_null($meta)) {
            $data['meta'] = $meta;
        }
        unset($payload, $meta);
        return $this->respond($data, 200, $message);
    }

    /**
     * @param  int|string|null  $data
     * @param  string  $message
     * @return ResponseInterface
     */
    protected function cmsRespondCreated(int|string|null $data = null, string $message = ''): ResponseInterface
    {
        return $this->respond(['data' => ['id' => $data]], $this->codes['created'], $message);
    }

}

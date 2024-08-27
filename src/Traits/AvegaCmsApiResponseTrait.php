<?php

declare(strict_types=1);

namespace AvegaCms\Traits;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

trait AvegaCmsApiResponseTrait
{
    use ResponseTrait;

    protected function cmsRespond(
        array|string|null $payload = null,
        array|string|null $meta = null,
        string $message = ''
    ): ResponseInterface {
        $data = null;
        if (null !== $payload) {
            $data['data'] = is_array($payload) ? $payload : [$payload];
            if (in_array('pagination', array_keys($data['data']), true)) {
                $meta['pagination'] = $data['data']['pagination'];
                unset($data['data']['pagination']);
            }

            if (in_array('list', array_keys($data['data']), true)) {
                $data['data'] = $data['data']['list'];
            }
        }

        if (null !== $meta) {
            $data['meta'] = $meta;
        }
        unset($payload, $meta);

        return $this->respond($data, 200, $message);
    }

    protected function cmsRespondCreated(int|string|null $data = null, string $message = ''): ResponseInterface
    {
        return $this->respond(['data' => ['id' => $data]], $this->codes['created'], $message);
    }

    protected function cmsRespondFail(
        array|string $messages,
        int $status = 400,
        int|string|null $code = null,
        string $customMessage = ''
    ): ResponseInterface {
        $response = [
            'error' => [
                'code'    => $code ?? $status,
                'message' => (! is_array($messages)) ? [$messages] : $messages,
            ],
        ];

        return $this->respond($response, $status, $customMessage);
    }
}

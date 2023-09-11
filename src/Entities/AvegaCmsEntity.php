<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;
use Exception;


class AvegaCmsEntity extends Entity
{
    protected array|null $rawData;
    protected            $datamap = [];
    protected            $dates   = ['created_at', 'updated_at', 'publish_at'];
    protected            $casts   = [];

    public function __construct(?array $data = null)
    {
        parent::__construct($data);
    }

    /**
     * @param  array|null  $data
     * @return $this|AvegaCmsEntity
     * @throws Exception
     */
    public function fill(?array $data = null): AvegaCmsEntity|static
    {
        $this->rawData = $data;

        if ( ! is_array($data)) {
            return $this;
        }

        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }

        return $this;
    }
}
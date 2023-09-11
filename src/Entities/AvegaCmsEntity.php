<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class AvegaCmsEntity extends Entity
{
    protected array|null $rawData;
    protected            $datamap = [];
    protected            $dates   = ['created_at', 'updated_at', 'publish_at'];
    protected            $casts   = [];

    public function __construct(?array $data = null)
    {
        $this->rawData = $data;
        parent::__construct($data);
    }
}
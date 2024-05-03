<?php

declare(strict_types=1);

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

    /**
     * Общий метод, который вернет все общедоступные и защищенные значения этого объекта в виде массива.
     * Доступ ко всем значениям осуществляется через магический метод __get(), поэтому к ним будут применены любые
     * приведения и т.д.
     *
     * @param  bool  $onlyChanged
     * @param  bool  $cast
     * @param  bool  $recursive
     * @return array
     * @throws Exception
     */
    public function toArray(bool $onlyChanged = false, bool $cast = true, bool $recursive = false): array
    {
        $this->_cast = $cast;

        $keys = array_filter(array_keys($this->attributes), static fn($key) => ! str_starts_with($key, '_'));

        if (is_array($this->datamap)) {
            $keys = array_unique(
                [
                    ...array_diff($keys, $this->datamap),
                    ...array_values(array_intersect_key(array_flip($this->datamap), array_flip($keys)))
                ]
            );
        }

        $return = [];

        foreach ($keys as $key) {
            if (($onlyChanged && ! $this->hasChanged($key))) {
                continue;
            }

            $return[$key] = $this->__get($key);

            if ($recursive) {
                if ($return[$key] instanceof self) {
                    $return[$key] = $return[$key]->toArray($onlyChanged, $cast, $recursive);
                } elseif (is_callable([$return[$key], 'toArray'])) {
                    $return[$key] = $return[$key]->toArray();
                }
            }
        }

        $this->_cast = true;

        return $return;
    }
}
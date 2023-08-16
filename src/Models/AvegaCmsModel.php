<?php

namespace AvegaCms\Models;

use CodeIgniter\Model;

class AvegaCmsModel extends Model
{
    //AvegaCms model settings
    protected array  $filterFields      = [];
    protected array  $searchFields      = [];
    protected array  $sortableFields    = [];
    protected array  $filterCastsFields = [];
    protected string $searchFieldAlias  = 'q';
    protected string $sortFieldAlias    = 's';
    protected array  $filterEnumValues  = [];
    protected int    $limit             = 20;
    protected int    $maxLimit          = 100;
    protected int    $page              = 1;
    private array    $filterFieldsMap   = [];
    private string   $fieldMapFlag      = '';
    private array    $filterWhereSings  = ['!', '>=', '<=', '>', '<'];
    private array    $filterSortSings   = ['+' => 'ASC', '-' => 'DESC', '~' => 'RANDOM'];

    /**
     * @param  array|null  $fields
     * @return AvegaCmsModel|array|static
     */
    public function filter(?array $fields = []): AvegaCmsModel|array|static
    {
        if ( ! empty($fields = array_filter($fields, fn($value) => $value !== '' && $value !== null))) {
            $this->filterCastsFields[$this->searchFieldAlias] = 'string';
            $this->filterCastsFields[$this->sortFieldAlias] = 'string';

            if ( ! empty($this->searchFields)) {
                $this->_preparingSetsFields('search', $fields);
            }

            if ( ! empty($this->filterFields)) {
                $this->_preparingSetsFields('where', $fields);
                $this->_preparingSetsFields('sort', $fields);
            }

            if ( ! empty($this->filterFieldsMap)) {
                foreach (['search', 'sort', 'where'] as $type) {
                    if ($this->filterFieldsMap[$type] ?? false) {
                        switch ($type) {
                            case 'search':
                                if ( ! empty($search = trim($fields[$this->searchFieldAlias] ?? ''))) {
                                    $search = explode(' ', $search);
                                    $this->builder()->groupStart();
                                    foreach ($this->searchFields as $key => $field) {
                                        $this->builder()->orGroupStart();
                                        foreach ($search as $word) {
                                            $this->builder()->orLike($field, trim($word));
                                        }
                                        $this->builder()->groupEnd();
                                        unset($fields[$key]);
                                    }
                                    $this->builder()->groupEnd();
                                    unset($fields[$this->searchFieldAlias]);
                                }
                                break;
                            case 'sort':

                                foreach ($this->filterFieldsMap[$type] as $item) {
                                    $this->builder()->orderBy($item['field'], $item['value']);
                                }
                                break;
                            case 'where':
                                $this->builder()->groupStart();
                                foreach ($this->filterFieldsMap[$type] as $item) {
                                    match ($item['flag']) {
                                        '>=', '<=', '>', '<' => ! is_array($item['value']) ?
                                            $this->builder()->where(
                                                [$item['field'] . ' ' . $item['flag'] => $item['value']]
                                            ) : '',

                                        '!'                  => is_array($item['value']) ?
                                            $this->builder()->whereNotIn($item['field'], $item['value']) :
                                            $this->builder()->where([$item['field'] . ' !=' => $item['value']]),

                                        default              => is_array($item['value']) ?
                                            $this->builder()->whereIn($item['field'], $item['value']) :
                                            $this->builder()->where([$item['field'] => $item['value']])
                                    };
                                }
                                $this->builder()->groupEnd();
                                break;
                        }
                    }
                }
            }

            $this->limit = (int) (is_int($fields['limit'] ?? '') && $fields['limit'] > 0 ? $fields['limit'] : $this->limit);
            $this->page = (int) (is_int($fields['page'] ?? '') && $fields['page'] > 0 ? $fields['page'] : $this->page);

            if ($this->limit > $this->maxLimit) {
                $this->limit = $this->maxLimit;
            }

            if ($this->page == 1) {
                $this->builder()->limit($this->limit);
            }

            unset($fields['limit'], $fields['page']);
        }

        return $this;
    }

    /**
     * @param  int|null  $limit
     * @param  int|null  $offset
     * @return array
     */
    public function pagination(int|null $limit = null, int|null $offset = null): array
    {
        if (is_null($limit)) {
            $limit = $this->limit;
        }

        if (is_null($offset)) {
            $offset = $this->page;
        }

        return [
            'list'       => $this->paginate($limit),
            'pagination' => [
                'page'  => $offset,
                'limit' => $limit,
                'total' => $this->pager->getTotal()
            ]
        ];
    }
    
    /**
     * @param  string  $type
     * @param  array  $fields
     * @return void
     */
    private function _preparingSetsFields(string $type, array $fields): void
    {
        $data = match ($type) {
            'search' => [$this->searchFieldAlias => $this->searchFieldAlias],
            'sort'   => [$this->sortFieldAlias => $this->sortFieldAlias],
            'where'  => $this->filterFields,
            default  => []
        };

        if (empty($data)) {
            return;
        }

        $excludeFieldsWhere = [
            ...array_keys($this->searchFields),
            ...[$this->searchFieldAlias, $this->sortFieldAlias, 'usePagination', 'limit', 'page']
        ];

        foreach ($data as $key => $field) {
            foreach ($fields as $k => $value) {
                switch ($type) {
                    case 'sort':
                        if ($this->sortFieldAlias === $k) {
                            foreach (explode(',', $value) as $sortField) {
                                $sortFlag = '';
                                foreach (array_keys($this->filterSortSings) as $sign) {
                                    if (str_starts_with($sortField, $sign)) {
                                        $sortFlag = $this->filterSortSings[$sign];
                                        $sortField = str_ireplace($sign, '', $sortField);
                                        break;
                                    }
                                }

                                if ( ! empty($this->sortableFields)) {
                                    if (in_array($sortField, $this->sortableFields)) {
                                        $this->filterFieldsMap[$type][$sortField] = [
                                            'field' => $this->filterFields[$sortField],
                                            'value' => $sortFlag
                                        ];
                                    }
                                } else {
                                    $this->filterFieldsMap[$type][$sortField] = [
                                        'field' => $this->filterFields[$sortField],
                                        'value' => $sortFlag
                                    ];
                                }
                            }
                            break;
                        }
                        break;

                    case 'search':
                    case 'where':

                        if ($type === 'where') {
                            foreach ($this->filterWhereSings as $sign) {
                                if (str_starts_with($k, $sign)) {
                                    $this->fieldMapFlag = $sign;
                                    $k = str_ireplace($sign, '', $k);
                                    break;
                                }
                            }
                            $k = ! in_array($k, $excludeFieldsWhere) ? $k : '';
                        } else {
                            $k = ($this->searchFieldAlias === $k) ? $k : '';
                        }

                        if ($k == $key && ! is_null(
                                $value = $this->castAs($value, $this->filterCastsFields[$key] ?? '')
                            )) {
                            $this->filterFieldsMap[$type][$key] = [
                                'field' => $field,
                                'value' => $value,
                                'flag'  => $this->fieldMapFlag
                            ];
                            break;
                        }

                        break;
                }
            }
        }
    }

    /**
     * @param $value
     * @param  string  $attribute
     * @param  string  $fieldName
     * @return mixed
     */
    protected function castAs($value, string $attribute, string $fieldName = ''): mixed
    {
        return match ($attribute) {
            'int',
            'integer'       => (int) $value,
            'double',
            'float'         => (float) $value,
            'string'        => (string) $value,
            'strtotime'     => strtotime($value),
            'bool',
            'boolean'       => (bool) $value,
            'enum'          => in_array(
                $value = strtolower($value),
                $this->filterEnumValues[$fieldName] ?? []
            ) ? $value : null,
            'array'         => (array) (
            (
            (is_string($value) && (str_starts_with($value, 'a:') || str_starts_with($value, 's:'))) ?
                unserialize($value) :
                $value
            )
            ),
            'int|array',
            'integer|array' => is_int($value) ? $this->castAs($value, 'int') : $this->castAs($value, 'array'),
            'double|array',
            'float|array'   => is_float($value) ? $this->castAs($value, 'float') : $this->castAs($value, 'array'),
            default         => null
        };
    }
}

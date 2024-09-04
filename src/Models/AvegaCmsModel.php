<?php

declare(strict_types=1);

namespace AvegaCms\Models;

use AvegaCms\Config\Services;
use AvegaCms\Models\Cast\CmsDatetimeCast;
use AvegaCms\Models\Cast\CmsFileCast;
use AvegaCms\Utilities\CmsFileManager;
use CodeIgniter\Model;

class AvegaCmsModel extends Model
{
    // Callbacks
    protected $allowCallbacks = true;
    protected $afterFind      = [];

    // AvegaCms model settings
    /**
     * @var array
     *
     * Указывается массив полей, которые будут участвовать в работе фильтра.
     * Массив формируется следующим образом, где массива ключ - это значение переменной в поисковой строке,
     * а значение это имя поля в таблице (если используется JOIN, то имя можно указать через точку users.email)
     *
     * Пример:
     * 'email' => 'users.email'
     */
    protected array $filterFields = [];

    protected array $searchFields       = [];
    protected array $sortableFields     = [];
    protected array $filterCastsFields  = [];
    protected string $searchFieldAlias  = 'q';
    protected string $sortFieldAlias    = 's';
    protected string $sortDefaultFields = '';
    protected array $filterEnumValues   = [];
    protected int $limit                = 20;
    protected int $maxLimit             = 100;
    protected int $page                 = 1;
    private array $filterFieldsMap      = [];
    private array $filterWhereSings     = ['!', '>=', '<=', '>', '<'];
    private array $filterSortSings      = ['+' => 'ASC', '-' => 'DESC', '~' => 'RANDOM'];
    protected array $castHandlers       = [
        'cmsdatetime' => CmsDatetimeCast::class,
        'cmsfile'     => CmsFileCast::class,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->afterFind = [
            ...['getCmsFilesAfterFind'],
            ...$this->afterFind,
        ];
    }

    public function filter(?array $fields = []): AvegaCmsModel
    {
        if (! empty($fields = array_filter($fields, static fn ($value) => $value !== '' && $value !== null))
            || ! empty($this->sortDefaultFields)
        ) {
            $this->filterCastsFields[$this->searchFieldAlias] = 'string';
            $this->filterCastsFields[$this->sortFieldAlias]   = 'string';

            if (! empty($this->searchFields)) {
                $this->_preparingSetsFields('search', $fields);
            }

            if (! empty($this->filterFields) || ! empty($this->sortDefaultFields)) {
                $this->_preparingSetsFields('where', $fields);
                $this->_preparingSetsFields('sort', $fields);
            }

            if (! empty($this->filterFieldsMap)) {
                foreach (['search', 'sort', 'where'] as $type) {
                    if ($this->filterFieldsMap[$type] ?? false) {
                        switch ($type) {
                            case 'search':
                                if (! empty($search = trim($fields[$this->searchFieldAlias] ?? ''))) {
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

                                        '!' => is_array($item['value']) ?
                                            $this->builder()->whereNotIn($item['field'], $item['value']) :
                                            $this->builder()->where([$item['field'] . ' !=' => $item['value']]),

                                        default => is_array($item['value']) ?
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

            if (filter_var($fields['limit'] ?? false, FILTER_VALIDATE_INT)) {
                $this->limit = $fields['limit'] > 0 ? (int) $fields['limit'] : $this->limit;
                if ($this->limit > $this->maxLimit) {
                    $this->limit = $this->maxLimit;
                }
            }

            if (! isset($fields['page'])) {
                $fields['page'] = Services::request()->getGet('page');
            }

            if (filter_var($fields['page'] ?? false, FILTER_VALIDATE_INT)) {
                $this->page = $fields['page'] > 0 ? (int) $fields['page'] : $this->page;
                if ($this->page === 1) {
                    $this->builder()->limit($this->limit);
                }
            }

            unset($fields['limit'], $fields['page']);
        }

        return $this;
    }

    public function apiPagination(?int $limit = null, ?int $offset = null): array
    {
        if (null === $limit) {
            $limit = $this->limit;
        }

        if (null === $offset) {
            $offset = $this->page;
        }

        return [
            'list'       => $this->paginate($limit),
            'pagination' => [
                'page'  => $offset,
                'limit' => $limit,
                'total' => $this->pager->getTotal(),
            ],
        ];
    }

    protected function castAs($value, string $attribute, string $fieldName): mixed
    {
        return match (strtolower($attribute)) {
            'int',
            'integer' => (int) $value,
            'double',
            'float'     => (float) $value,
            'string'    => (string) $value,
            'strtotime' => strtotime($value),
            'bool',
            'boolean' => is_string($value) ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : (bool) $value,
            'enum'    => in_array($value, $this->filterEnumValues[$fieldName] ?? [], true) ? $value : null,
            'array'   => (array) (
                (
                    (is_string($value) && (str_starts_with($value, 'a:') || str_starts_with($value, 's:'))) ?
                    unserialize($value) :
                    $value
                )
            ),
            'int|array',
            'integer|array' => is_int($value) ?
                $this->castAs($value, 'int', $fieldName) :
                (is_array($value) && ! empty($value) ? $this->castAs($value, 'array', $fieldName) : null),
            'double|array',
            'float|array' => is_float($value) ?
                $this->castAs($value, 'float', $fieldName) :
                (is_array($value) && ! empty($value) ? $this->castAs($value, 'array', $fieldName) : null),
            default => null
        };
    }

    protected function getCmsFilesAfterFind(array $data): array
    {
        $fileCastMap = [];

        foreach ($this->casts as $field => $cast) {
            if (in_array($cast, ['cmsfile','?cmsfile'], true)) {
                $fileCastMap[] = $field;
            }
        }

        if (! empty($data['data']) && ! empty($fileCastMap)) {
            $filesId = [];

            $getFile = static function (array|int|null $field) {
                if ($field === null) {
                    return null;
                }

                if (is_int($field)) {
                    return $field;
                }

                foreach ($field as &$file) {
                    $file = $files[$file] ?? null;
                }

                return array_filter($field, static fn ($el) => $el !== null);
            };

            if ($data['singleton'] === true) {
                foreach ($fileCastMap as $field) {
                    if (is_object($data['data'])) {
                        if (property_exists($data['data'], $field)) {
                            $filesId[] = $getFile($data['data']->{$field});
                        }
                    } else {
                        if (array_key_exists($field, $data['data'])) {
                            $filesId[] = $getFile($data['data'][$field]);
                        }
                    }
                }
            } else {
                foreach ($data['data'] as $item) {
                    foreach ($fileCastMap as $field) {
                        if (is_object($item) || is_string($item)) {
                            if (property_exists($item, $field)) {
                                $filesId[] = $getFile($item->{$field});
                            }
                        } else {
                            if (is_array($item) && array_key_exists($field, $item)) {
                                $filesId[] = $getFile($item[$field]);
                            }
                        }
                    }
                }
            }

            if (empty($filesId)) {
                return $data;
            }

            $files = array_column(CmsFileManager::getFiles(['id' => array_unique($filesId)], true), null, 'id');

            $setFile = static function (array|int|null $field) use ($files) {
                if ($field === null) {
                    return null;
                }

                if (is_int($field)) {
                    return $files[$field] ?? null;
                }

                foreach ($field as &$file) {
                    $file = $files[$file] ?? null;
                }

                return array_filter($field, static fn ($el) => $el !== null);
            };

            if ($data['singleton'] === true) {
                foreach ($fileCastMap as $field) {
                    if (property_exists($data['data'], $field)) {
                        $data['data']->{$field} = $setFile($data['data']->{$field});
                    }
                }
            } else {
                foreach ($data['data'] as &$item) {
                    foreach ($fileCastMap as $field) {
                        if (property_exists($item, $field)) {
                            $item->{$field} = $setFile($item->{$field});
                        }
                    }
                }
            }

            unset($files, $filesId);
        }

        return $data;
    }

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

        // Убираем все поля, которые не будут использоваться фильтре
        $excludeFieldsWhere = [
            ...array_keys($this->searchFields),
            ...[$this->searchFieldAlias, $this->sortFieldAlias, 'usePagination', 'limit', 'page'],
        ];

        // Если есть сортировка по умолчанию, то применяем её, если не была передана другая
        if ($type === 'sort' && ! empty($this->sortDefaultFields) && empty($fields[$this->sortFieldAlias] ?? '')) {
            $fields[$this->sortFieldAlias] = $this->sortDefaultFields;
        }

        foreach ($data as $key => $field) {
            foreach ($fields as $k => $value) {
                switch ($type) {
                    case 'sort':
                        if ($this->sortFieldAlias === $k) {
                            foreach (explode(',', $value) as $sortField) {
                                $sortFlag = '';

                                foreach (array_keys($this->filterSortSings) as $sign) {
                                    if (str_starts_with($sortField, $sign)) {
                                        $sortFlag  = $this->filterSortSings[$sign];
                                        $sortField = str_ireplace($sign, '', $sortField);
                                        break;
                                    }
                                }

                                if (! empty($this->sortableFields)) {
                                    if (in_array($sortField, array_keys($this->filterFields), true)) {
                                        $this->filterFieldsMap[$type][$sortField] = [
                                            'field' => $this->filterFields[$sortField],
                                            'value' => $sortFlag,
                                        ];
                                    }
                                } else {
                                    $this->filterFieldsMap[$type][$sortField] = [
                                        'field' => $this->filterFields[$sortField],
                                        'value' => $sortFlag,
                                    ];
                                }
                            }
                            break;
                        }
                        break;

                    case 'search':
                    case 'where':
                        $fieldMapFlag = '';

                        if ($type === 'where') {
                            foreach ($this->filterWhereSings as $sign) {
                                if (str_starts_with($k, $sign)) {
                                    $fieldMapFlag = $sign;
                                    $k            = str_ireplace($sign, '', $k);
                                    break;
                                }
                            }
                            $k = ! in_array($k, $excludeFieldsWhere, true) ? $k : '';
                        } else {
                            $k = ($this->searchFieldAlias === $k) ? $k : '';
                        }

                        if ($k === $key && null !== (
                            $value = $this->castAs($value, $this->filterCastsFields[$key] ?? '', $key)
                        )) {
                            $this->filterFieldsMap[$type][$key] = [
                                'field' => $field,
                                'value' => $value,
                                'flag'  => $fieldMapFlag,
                            ];
                            break;
                        }

                        break;
                }
            }
        }
    }
}

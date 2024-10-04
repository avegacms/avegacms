<?php

declare(strict_types=1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Utilities\Cms;
use Exception;
use ReflectionException;

class AttemptsEntranceModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'attempts_entrance';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'login',
        'code',
        'attempts',
        'delay',
        'expires',
        'user_ip',
        'user_agent',
        'created_at',
        'updated_at',
    ];
    protected array $casts = [
        'code'       => 'int',
        'attempts'   => 'int',
        'delay'      => 'int',
        'expires'    => 'int',
        'created_at' => 'cmsdatetime',
        'updated_at' => 'cmsdatetime',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'id'       => ['rules' => 'required|max_length[128]|alpha_dash'],
        'code'     => ['rules' => 'required|is_natural_no_zero|min_length[4]|max_length[6]'],
        'attempts' => ['rules' => 'required|is_natural_no_zero'],
        'delay'    => ['rules' => 'required|is_natural_no_zero'],
        'expires'  => ['rules' => 'required|is_natural_no_zero'],
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = ['setUserEntranceCode'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = ['setUserEntranceCode'];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // AvegaCms filter settings
    protected array $filterFields       = [];
    protected array $searchFields       = [];
    protected array $sortableFields     = [];
    protected array $filterCastsFields  = [];
    protected string $searchFieldAlias  = 'q';
    protected string $sortFieldAlias    = 's';
    protected string $sortDefaultFields = '';
    protected array $filterEnumValues   = [];
    protected int $limit                = 20;
    protected int $maxLimit             = 100;

    public function __construct()
    {
        parent::__construct();
    }

    public function getCode(string $login, int $delay = 0): ?object
    {
        $id = md5($login);

        $data = cache()->remember('AttemptsEntrance_' . $id, $delay, function () use ($id) {
            $this->builder()->select(['id', 'code', 'attempts', 'delay', 'expires'])->where(['id' => $id]);

            return $this->first();
        });

        return $data ?? null;
    }

    public function clear(string $login): bool
    {
        return $this->builder()->delete(['id' => md5($login)]);
    }

    /**
     * @throws Exception|ReflectionException
     */
    protected function setUserEntranceCode(array $data): array
    {
        if ($data['result'] === true) {
            $this->getCode($data['data']['login'], $data['data']['delay']);
            // Очищаем прошлые попытки
            $oldDate = now(Cms::settings('core.env.timezone')) - (Cms::settings('core.auth.attemptsClearTime') * MINUTE);
            $this->builder()->where(['updated_at <' => $oldDate])->delete();
        }

        return $data;
    }
}

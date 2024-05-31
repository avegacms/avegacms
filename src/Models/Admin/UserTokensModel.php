<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;

class UserTokensModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'user_tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'user_id',
        'access_token',
        'refresh_token',
        'expires',
        'user_ip',
        'user_agent',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'id'            => ['rules' => 'if_exist|required|alpha_dash|max_length[128]'],
        'user_id'       => ['rules' => 'if_exist|is_natural_no_zero'],
        'access_token'  => ['rules' => 'if_exist|required|alpha_numeric_punct|max_length[2048]'],
        'refresh_token' => ['rules' => 'if_exist|required|alpha_numeric_punct|max_length[64]'],
        'expires'       => ['rules' => 'if_exist|is_natural'],
        'user_ip'       => ['rules' => 'if_exist|max_length[255]'],
        'user_agent'    => ['rules' => 'if_exist|max_length[512]']
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected array $casts = [
        'user_id'    => 'int',
        'expires'    => 'int',
        'created_at' => 'cmsdatetime',
        'updated_at' => 'cmsdatetime'
    ];

    /**
     * @param  int  $userId
     * @return UserTokensModel
     */
    public function getUserTokens(int $userId): UserTokensModel
    {
        $this->builder()->where(['user_id' => $userId])
            ->orderBy('created_at', 'ASC');

        return $this;
    }
}

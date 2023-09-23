<?php

namespace AvegaCms\Validation;

use Config\Database;

class AvegaCmsRules
{
    /**
     * Example:
     *    unique_db_key[table.field_1+field_2....+field_n,where_field,where_value]
     *    unique_db_key[users.parent+is_admin+locale_id+nav_type+slug,id,{id}]
     *
     * @param $str
     * @param  string  $field
     * @param  array  $data
     * @param  string|null  $error
     * @return bool
     */
    public function unique_db_key($str, string $field, array $data, ?string &$error = null): bool
    {
        if (is_object($str) || is_array($str)) {
            return false;
        }

        // Grab any data for exclusion of a single row.
        [$field, $ignoreField, $ignoreValue] = array_pad(
            explode(',', $field),
            3,
            null
        );

        // Break the table and field apart
        sscanf($field, '%[^.].%[^.]', $table, $field);

        if (empty($table)) {
            $error = lang('Validation.uniqueDbKey.emptyTable');
            return false;
        }

        if (empty($fields = explode('+', $field))) {
            $error = lang('Validation.uniqueDbKey.emptyFields');
            return false;
        }

        $where = [];
        foreach ($fields as $field) {
            if ( ! isset($data[$field])) {
                $error = lang('Validation.uniqueDbKey.notField', [$field]);
                return false;
            }
            $where[$field] = $data[$field];
        }

        $row = Database::connect($data['DBGroup'] ?? null)
            ->table($table)
            ->select('1')
            ->where($where)
            ->limit(1);

        if (
            ! empty($ignoreField) && ! empty($ignoreValue)
            && ! preg_match('/^\{(\w+)\}$/', $ignoreValue)
        ) {
            $row = $row->where("{$ignoreField} !=", $ignoreValue);
        }

        if ($row->get()->getRow() !== null) {
            $error = lang('Validation.uniqueDbKey.notUnique');
            return false;
        }

        return true;
    }

    public function verify_password($value, ?string &$error = null): bool
    {
        if ( ! preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{6,32}$/', $value)) {
            $error = lang('Validation.verifyPassword');
            return false;
        }
        return true;
    }
}
<?php

namespace AvegaCms\Enums;

enum FileTargets: string
{
    case Preview    = 'PREVIEW';
    case OpenGraph  = 'OPENGRAPH';
    case Attributes = 'ATTRIBUTES';
    case Other      = 'OTHER';

    /**
     * @param  string|null  $key
     * @return array
     */
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true]) ?
            array_column(FileTargets::cases(), $key) : FileTargets::cases();
    }

    /**
     * @param  string  $name
     * @return string
     */
    public static function fromName(string $name): string
    {
        foreach (FileTargets::cases() as $enum) {
            if ($enum->name === $name) {
                return $enum->value;
            }
        }
        return '';
    }

    /**
     * @param  string  $value
     * @return string|null
     */
    public static function fromValue(string $value): string|null
    {
        foreach (FileTargets::cases() as $enum) {
            if ($enum->value === $value) {
                return $enum->name;
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public static function list(): array
    {
        $list = [];
        foreach (FileTargets::cases() as $enum) {
            $list[] = ['label' => lang('Enums.FileTargets.' . $enum->name), 'value' => $enum->value];
        }
        return $list;
    }
}

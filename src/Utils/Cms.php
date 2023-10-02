<?php

namespace AvegaCms\Utils;

use AvegaCms\Enums\SettingsReturnTypes;
use AvegaCms\Models\Admin\SettingsModel;
use AvegaCms\Entities\SettingsEntity;
use Config\Services;
use RuntimeException;
use ReflectionException;

class Cms
{
    private static object|null $access   = null;
    private static object|null $userData = null;

    /**
     * @return object|null
     */
    public static function userData(): object|null
    {
        return self::$userData;
    }

    /**
     * @return object|null
     */
    public static function userPermission(): object|null
    {
        return self::$access;
    }

    /**
     * @param  string  $key
     * @param  object|null  $value
     * @return void
     */
    public static function setUser(string $key, ?object $value = null): void
    {
        match ($key) {
            'user'       => self::$userData = $value ?? null,
            'permission' => self::$access = $value ?? null
        };
    }

    /**
     * @param  array  $data
     * @return void
     */
    public static function initClientSession(array $data = []): void
    {
        if ( ! Services::session()->has('avegacms')) {
            Services::session()->set('avegacms',
                [
                    'client'  => [

                        'locale'  => $data['client']['locale'] ?? [],
                        'user'    => $data['client']['user'] ?? [],
                        'confirm' => [

                            'use_cookie' => false,
                            'gdpr'       => false
                        ]
                    ],
                    'modules' => [],
                    'admin'   => []
                ]
            );
        }
    }

    /**
     * @param  string  $key
     * @param  string|null  $value
     * @param  array|null  $config
     * @return mixed
     * @throws RuntimeException|ReflectionException
     */
    public static function settings(string $key, ?string $value = null, ?array $config = []): mixed
    {
        [$entity, $slug, $property] = self::parseKey($key);

        $prefix = 'settings_';

        $SM = model(SettingsModel::class);

        if (empty($value)) {
            $settings = cache()->remember($prefix . $entity, DAY * 30, function () use ($entity, $SM) {
                if (empty($settings = $SM->getSettings($entity))) {
                    throw new RuntimeException('Unable to find a Settings array in DB.');
                }

                $processArray = function (&$settings) use (&$processArray) {
                    foreach ($settings as $key => &$item) {
                        if (is_array($item)) {
                            if (isset($item['return_type'])) {
                                $rt = $item['return_type'];
                                unset($item['return_type']);
                                $settings[$key] = self::castAs($item['value'], $rt);
                            } else {
                                $processArray($item);
                            }
                        }
                    }
                };

                $processArray($settings);

                return $settings;
            });

            if ( ! is_null($slug) && ! is_null($property)) {
                if ( ! isset($settings[$slug][$property])) {
                    throw new RuntimeException('Unable to find in Settings array slug/key.');
                }
                $settings = $settings[$slug][$property];
            } elseif ( ! is_null($slug)) {
                if ( ! isset($settings[$slug])) {
                    throw new RuntimeException('Unable to find in Settings array slug/key');
                }
                $settings = $settings[$slug];
            }

            return $settings;
        } else {
            if (($id = $SM->getId($entity, $slug, $property)) > 0) {
                return $SM->update($id, (new SettingsEntity(['value' => $value])));
            } else {
                return $SM->insert(
                    (new SettingsEntity(
                        [
                            'module_id'     => $config['module_id'] ?? 0,
                            'is_core'       => $config['is_core'] ?? 0,
                            'entity'        => $entity,
                            'slug'          => $slug ?? '',
                            'key'           => $property ?? '',
                            'value'         => $value,
                            'default_value' => $config['default_value'] ?? '',
                            'return_type'   => $config['return_type'] ?? SettingsReturnTypes::String->value,
                            'label'         => $config['label'] ?? '',
                            'context'       => $config['context'] ?? '',
                            'sort'          => $config['sort'] ?? 100
                        ]
                    ))
                );
            }
        }
    }

    /**
     * @param  string  $url
     * @param  int  $usePattern
     * @param  int  $id
     * @param  string  $slug
     * @param  int  $locale_id
     * @param  int  $parent
     * @return string
     */
    public function urlPattern(
        string $url,
        int $usePattern,
        int $id,
        string $slug,
        int $locale_id,
        int $parent
    ): string {
        return base_url(
            strtolower(
                $usePattern ?
                    str_ireplace(
                        ['{id}', '{slug}', '{locale_id}', '{parent}'],
                        [$id, $slug, $locale_id, $parent],
                        $url
                    ) :
                    $url
            )
        );
    }

    /**
     * @param  array  $array
     * @return object
     */
    public static function arrayToObject(array $array): object
    {
        return (object) array_map(function ($item) {
            if (is_array($item)) {
                return self::arrayToObject($item);
            }
            return $item;
        }, $array);
    }

    /**
     * @param  array  $data
     * @return array
     */
    public static function getTree(array $data): array
    {
        $tree = [];

        foreach ($data as $key => &$item) {
            if (isset($item['parent'])) {
                if ( ! $item['parent']) {
                    $tree[$key] = &$item;
                } else {
                    $data[$item['parent']]['list'][$key] = &$item;
                }
            }
        }

        return $tree;
    }

    /**
     * @param  string  $key
     * @return array
     * @throws RuntimeException
     */
    public static function parseKey(string $key): array
    {
        if (count($parts = explode('.', $key)) === 0) {
            throw new RuntimeException('$key cannot be empty');
        }

        $parts[1] = $parts[1] ?? null;
        $parts[2] = $parts[2] ?? null;

        return $parts;
    }

    /**
     * @param $value
     * @param  string  $type
     * @return integer|float|string|boolean|array|null
     */
    public static function castAs($value, string $type): mixed
    {
        return match ($type) {
            SettingsReturnTypes::Integer->value => (int) $value,
            SettingsReturnTypes::Double->value  => (double) $value,
            SettingsReturnTypes::Float->value   => (float) $value,
            SettingsReturnTypes::String->value  => (string) $value,
            SettingsReturnTypes::Boolean->value => (bool) $value,
            SettingsReturnTypes::Json->value    => $value,
            SettingsReturnTypes::Array->value   => (array) (
            (
            (is_string($value) && (str_starts_with($value, 'a:') || str_starts_with($value, 's:'))) ?
                unserialize($value) :
                $value
            )
            ),
            default                             => null
        };
    }
}
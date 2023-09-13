<?php

namespace AvegaCms\Libraries;

use AvegaCms\Models\Admin\SettingsModel;
use AvegaCms\Entities\SettingsEntity;
use Exception;
use ReflectionException;
use AvegaCms\Enums\SettingsReturnTypes;

class AvegaCmsSettings
{
    public SettingsModel $SM;
    public string        $prefix = 'settings_';

    public function __construct()
    {
        $this->SM = model(SettingsModel::class);
    }

    /**
     * @param  string  $key
     * @return mixed
     * @throws Exception
     */
    public function get(string $key): mixed
    {
        [$entity, $slug, $property] = $this->_parseKey($key);

        $settings = cache()->remember($this->prefix . $entity, DAY * 30, function () use ($entity) {
            if (empty($settings = $this->SM->getSettings($entity))) {
                throw new Exception('Unable to find a Settings array in DB.');
            }

            $processArray = function (&$settings) use (&$processArray) {
                foreach ($settings as $key => &$item) {
                    if (is_array($item)) {
                        if (isset($item['return_type'])) {
                            $rt = $item['return_type'];
                            unset($item['return_type']);
                            $settings[$key] = $this->_castAs($item['value'], $rt);
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
                throw new Exception('Unable to find in Settings array slug/key.');
            }
            $settings = $settings[$slug][$property];
        } elseif ( ! is_null($slug)) {
            if ( ! isset($settings[$slug])) {
                throw new Exception('Unable to find in Settings array slug/key');
            }
            $settings = $settings[$slug];
        }

        return $settings;
    }

    /**
     * @param  string  $key
     * @param  string|null  $value
     * @param  array|null  $config
     * @return bool
     * @throws ReflectionException|Exception
     */
    public function set(string $key, ?string $value = null, ?array $config = []): bool
    {
        [$entity, $slug, $property] = $this->_parseKey($key);

        return $this->SM->save(
            (new SettingsEntity(
                [
                    'id'            => $this->SM->getId($entity, $slug, $property),
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

    /**
     * @param  string|null  $entity
     * @return void
     */
    public function clear(?string $entity = null): void
    {
        if ($entity) {
            cache()->delete($this->prefix . $entity);
        } else {
            cache()->deleteMatching($this->prefix . '*');
        }
    }

    /**
     * @param $value
     * @param  string  $type
     * @return integer|float|string|boolean|array|null
     */
    private function _castAs($value, string $type): mixed
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

    /**
     * @param  string  $key
     * @return array
     * @throws Exception
     */
    private function _parseKey(string $key): array
    {
        if (count($parts = explode('.', $key)) === 0) {
            throw new Exception('$key cannot be empty');
        }

        $parts[1] = $parts[1] ?? null;
        $parts[2] = $parts[2] ?? null;

        return $parts;
    }
}

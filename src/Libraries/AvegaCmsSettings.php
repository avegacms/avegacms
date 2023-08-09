<?php

namespace AvegaCms\Libraries;

use AvegaCms\Models\Admin\SettingsModel;
use AvegaCms\Entities\SettingsEntity;
use InvalidArgumentException;
use RuntimeException;

class AvegaCmsSettings
{
    public object $settings;
    public string $prefix = 'settings_';

    public function __construct()
    {
        $this->settings = model(SettingsModel::class);
    }

    /**
     * @param string $key
     * @return integer|float|string|boolean|array|null|\CodeIgniter\Cache\CacheInterface|mixed
     */
    public function get(string $key)
    {
        [$entity, $slug, $property] = $this->_parseKey($key);

        if (is_null($settings = cache($fileCacheName = $this->prefix . $entity))) {
            if (empty($settings = $this->settings->getSettings($entity))) {
                throw new RuntimeException('Unable to find a Settings array in DB.');
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

            cache()->save($fileCacheName, $settings, DAY * 30);
        }

        if (!is_null($slug) && !is_null($property)) {
            if (!isset($settings[$slug][$property])) {
                throw new RuntimeException('Unable to find in Settings array slug/key.');
            }
            $settings = $settings[$slug][$property];
        } elseif (!is_null($slug)) {
            if (!isset($settings[$slug])) {
                throw new RuntimeException('Unable to find in Settings array slug/key');
            }
            $settings = $settings[$slug];
        }

        return $settings;
    }

    /**
     * @param string $key
     * @param string|null $value
     * @param array $config
     * @return void
     */
    public function set(string $key, ?string $value = null, ?array $config = [])
    {
        [$entity, $slug, $property] = $this->_parseKey($key);

        $this->settings->save(
            (new SettingsEntity(
                [
                    'id'            => $this->settings->getId($entity, $slug, $property),
                    'entity'        => $entity,
                    'slug'          => $slug ?? '',
                    'key'           => $property ?? '',
                    'value'         => $value,
                    'default_value' => $config['default_value'] ?? '',
                    'return_type'   => $config['return_type'] ?? 'string',
                    'label'         => $config['label'] ?? '',
                    'context'       => $config['context'] ?? '',
                    'sort'          => $config['sort'] ?? 100
                ]
            ))
        );
        /*
         * 1. Проверить существование модуля $module
         * 2.
         * */
    }

    /**
     * @param string|null $entity
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
     * @param string $type
     * @return integer|float|string|boolean|array|null
     */
    private function _castAs($value, string $type): mixed
    {
        return match ($type) {
            'int',
            'integer' => (int)$value,
            'double',
            'float'   => (float)$value,
            'string'  => (string)$value,
            'bool',
            'boolean' => (bool)$value,
            'array'   => (array)(
            (
            (is_string($value) && (strpos($value, 'a:') === 0 || strpos($value, 's:') === 0)) ?
                unserialize($value) :
                $value
            )
            ),
            default   => null
        };
    }

    /**
     * @param string $key
     * @return array
     *
     * @throws InvalidArgumentException
     */
    private function _parseKey(string $key): array
    {
        if (count($parts = explode('.', $key)) === 0) {
            throw new InvalidArgumentException('$key cannot be empty');
        }

        $parts[1] = $parts[1] ?? null;
        $parts[2] = $parts[2] ?? null;

        return $parts;
    }
}

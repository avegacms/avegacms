<?php

declare(strict_types = 1);

namespace AvegaCms\Utilities;

use AvegaCms\Enums\FileTypes;
use AvegaCms\Utilities\Exceptions\UploaderException;
use AvegaCms\Models\Admin\{FilesModel, FilesLinksModel};
use ReflectionException;

class CmsFileManager
{
    public static function getFiles(
        array $filter = [],
        bool $all = false
    ): array {
        $FLM = model(FilesLinksModel::class);
        $FLM->getFiles($filter);

        if ($all) {
            $result = $FLM->findAll();
        } else {
            $result = $FLM->apiPagination();
        }

        return $result;
    }

    /**
     * @param  int|null  $id
     * @param  int|null  $parent
     * @param  int|null  $moduleId
     * @param  int|null  $entityId
     * @param  int|null  $itemId
     * @return array
     */
    public static function getDirectoryData(?int $id, ?int $parent, ?int $moduleId, ?int $entityId, ?int $itemId): array
    {
        $result = [];

        if (is_null($id)) {
            $id = model(FilesLinksModel::class)->getDirectoryData($parent, $moduleId, $entityId, $itemId);
        }

        if ($id > 0) {
            $result = model(FilesModel::class)->getDirectories($id);
        }

        return $result;
    }

    /**
     * Регистрирует в файловом менеджере директорию
     * и создаёт её на сервере
     *
     * @param  string  $path
     * @param  array  $config
     * @return void
     * @throws UploaderException|ReflectionException
     */
    public static function createDirectory(string $path, array $config): void
    {
        Uploader::checkFilePath($path);

        $id = model(FilesModel::class)->insert(
            [
                'data'          => json_encode(['url' => $path]),
                'provider'      => $config['provider'] ?? 0,
                'type'          => FileTypes::Directory->value,
                'created_by_id' => $config['user_id'] ?? 0
            ]
        );

        if ($id) {
            model(FilesLinksModel::class)->insert(
                [
                    'id'            => $id,
                    'user_id'       => $config['user_id'] ?? 0,
                    'parent'        => $config['parent'] ?? 0,
                    'module_id'     => $config['module_id'] ?? 0,
                    'entity_id'     => $config['entity_id'] ?? 0,
                    'item_id'       => $config['item_id'] ?? 0,
                    'uid'           => $config['uid'] ?? '',
                    'type'          => FileTypes::Directory->value,
                    'created_by_id' => $config['user_id'] ?? 0
                ]
            );
        }
    }
}
<?php

namespace AvegaCms\Utilities;

use AvegaCms\Models\Admin\{FilesModel, FilesLinksModel};
use AvegaCms\Enums\FileTypes;
use ReflectionException;

class TestFilesGenerator
{

    /**
     * @param  string  $path
     * @param  string  $type
     * @param  int  $num
     * @param  array  $custom
     * @return bool
     * @throws ReflectionException
     */
    public static function run(string $path, string $type = 'mixed', int $num = 1, array $custom = []): bool
    {
        if (empty($directory = model(FilesLinksModel::class)->getDirectories($path))) {
            return false;
        }

        $fileTypes = [
            'images' => ['jpg', 'jpeg', 'png', 'gif'],
            'files'  => ['pdf', 'docx', 'xls', 'zip']
        ];

        helper(['file', 'text']);

        $FM  = model(FilesModel::class);
        $FLM = model(FilesLinksModel::class);

        for ($i = 0; $i < $num; $i++) {
            $ft       = $type === 'mixed' ? array_rand($fileTypes) : $type;
            $size     = rand(1, 10000);
            $name     = random_string('alnum', rand(32, 64));
            $ext      = $fileTypes[$ft][array_rand($fileTypes[$ft])];
            $title    = random_string('alnum', 64);
            $fileType = FileTypes::File->value;

            $original = $name . '.' . $ext;
            $urlPath  = 'uploads/' . $directory['url'];

            $file = [
                'provider' => $directory['provider'] ?? 0,
                'type'     => $fileType,
                'ext'      => $ext,
                'file'     => $original,
                'size'     => $size,
                'path'     => $urlPath . '/' . $original
            ];

            switch ($ft) {
                case 'images':

                    $file['path']  = ['original' => $urlPath . '/' . $original];
                    $file['thumb'] = ['original' => $urlPath . '/thumb_' . $original];
                    $file['alt']   = $title;
                    $fileType      = $file['type'] = FileTypes::Image->value;
                    break;
                case 'files':
                    $file['title'] = $file['alt'] = $title;
                    break;
            }

            $id = $FM->insert(
                [
                    'data'          => json_encode($file),
                    'provider'      => $directory['provider'] ?? 0,
                    'type'          => $fileType,
                    'created_by_id' => $custom['user_id'] ?? ($directory['user_id'] ?? 0)
                ]
            );

            if ($id) {
                $FLM->insert(
                    [
                        'id'            => $id,
                        'user_id'       => $directory['user_id'] ?? 0,
                        'parent'        => $directory['id'],
                        'module_id'     => $custom['module_id'] ?? ($directory['module_id'] ?? 0),
                        'entity_id'     => $custom['entity_id'] ?? ($directory['entity_id'] ?? 0),
                        'item_id'       => $custom['item_id'] ?? ($directory['item_id'] ?? 0),
                        'type'          => $fileType,
                        'created_by_id' => $custom['user_id'] ?? ($directory['user_id'] ?? 0)
                    ]
                );
            }
        }

        return true;
    }
}
<?php

declare(strict_types = 1);

namespace AvegaCms\Utilities;

use AvegaCms\Entities\FilesLinksEntity;
use AvegaCms\Enums\FileTypes;
use AvegaCms\Utilities\Exceptions\UploaderException;
use CodeIgniter\Files\File;
use Config\Mimes;
use Config\Services;
use AvegaCms\Models\Admin\{FilesModel, FilesLinksModel};
use ReflectionException;

class CmsFileManager
{
    /**
     * @param  array  $settings
     * @param  int  $userId
     * @return array|FilesLinksEntity|null
     * @throws UploaderException
     */
    public static function upload(array $settings, int $userId = 0): array|FilesLinksEntity|null
    {
        $request    = Services::request();
        $validator  = Services::validation();
        $directory  = [];
        $entityId   = $settings['entity_id'] ?? 0;
        $itemId     = $settings['item_id'] ?? 0;
        $uploadPath = FCPATH . 'uploads/';
        $FM         = model(FilesModel::class);
        $FLM        = model(FilesLinksModel::class);

        unset($settings['entity_id'], $settings['item_id']);

        if ( ! is_numeric($settings['directory_id'] ?? false) && empty($directory = $FLM->getDirectories($settings['directory_id']))) {
            throw UploaderException::forDirectoryNotFound();
        }

        $uploadPath .= $directory['data']['url'];

        $settings['field'] = $settings['field'] ?? 'file';

        if ($validator->setRules(self::uploadSettings($settings))->withRequest($request)->run() === false) {
            throw new UploaderException($validator->getErrors());
        }

        $uploadedFile = $request->getFile($settings['field']);

        if ( ! $uploadedFile->isValid()) {
            throw new UploaderException($uploadedFile->getErrorString() . '(' . $uploadedFile->getError() . ')');
        }

        if ($uploadedFile->hasMoved()) {
            throw UploaderException::forHasMoved($uploadedFile->getName());
        }

        $uploadedFile->move($uploadPath, $uploadedFile->getName());

        $file    = new File($uploadPath . '/' . $uploadedFile->getName());
        $isImage = mb_strpos(Mimes::guessTypeFromExtension($extension = $file->getExtension()) ?? '', 'image') === 0;

        $fileData = [
            'provider' => 0,
            'type'     => $isImage ? FileTypes::Image->value : FileTypes::File->value,
            'ext'      => $extension,
            'size'     => 0,
            'file'     => $uploadedFile->getName(),
            'path'     => $directory['data']['url'],
            'title'    => ''
        ];
    }

    /**
     * @param  array  $filter
     * @param  bool  $all
     * @return array|FilesLinksEntity|null
     */
    public static function getFiles(
        array $filter = [],
        bool $all = false
    ): array|FilesLinksEntity|null {
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
     * Возвращает информацию по директории
     *
     * @param  int|null  $id
     * @param  int|null  $parent
     * @param  int|null  $moduleId
     * @param  int|null  $entityId
     * @param  int|null  $itemId
     * @return FilesLinksEntity|null
     */
    public static function getDirectoryData(
        ?int $id,
        ?int $parent,
        ?int $moduleId,
        ?int $entityId,
        ?int $itemId
    ): FilesLinksEntity|null {
        return model(FilesLinksModel::class)->getDirectoryData($id, $parent, $moduleId, $entityId, $itemId);
    }

    /**
     * Регистрирует в файловом менеджере директорию
     * и создаёт её на сервере
     *
     * @param  string  $path
     * @param  array  $config
     * @return int
     * @throws UploaderException|ReflectionException
     */
    public static function createDirectory(string $path, array $config): int
    {
        $FM  = model(FilesModel::class);
        $FLM = model(FilesLinksModel::class);

        Uploader::checkFilePath($path);

        $directoryId = $FM->insert(
            [
                'data'          => json_encode(['url' => $path]),
                'provider'      => $config['provider'] ?? 0,
                'type'          => FileTypes::Directory->value,
                'created_by_id' => $config['user_id'] ?? 0
            ]
        );

        if ($directoryId) {
            $FLM->insert(
                [
                    'id'            => $directoryId,
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

        return $directoryId;
    }

    /**
     * @param  array  $settings
     * @return array
     */
    private static function uploadSettings(array $settings): array
    {
        $field        = $settings['field'];
        $max_upload   = (int) (ini_get('upload_max_filesize'));
        $max_post     = (int) (ini_get('post_max_size'));
        $memory_limit = (int) (ini_get('memory_limit'));

        $maxSize = ($memory_limit > 0 ?
                min($max_upload, $max_post, $memory_limit) :
                min($max_upload, $max_post)) * 1024;

        $uploadRule = 'uploaded[' . $field . ']|';

        $uploadRule .= 'max_size[' . $field . ',' . (($settings['max_size'] ?? ($maxSize + 1) || $settings['max_size'] > $maxSize) ? $maxSize : $settings['max_size']) . ']';

        if (isset($settings['max_dims'])) {
            $uploadRule .= '|max_dims[' . $field . ',' . $settings['max_dims'] . ']';
        }

        if (isset($settings['mime_in'])) {
            $uploadRule .= '|mime_in[' . $field . ',' . $settings['mime_in'] . ']';
        }

        if (isset($settings['ext_in'])) {
            $uploadRule .= '|ext_in[' . $field . ',' . $settings['ext_in'] . ']';
        }

        if (isset($settings['is_image'])) {
            $uploadRule .= '|is_image[' . $field . ']';
        }

        unset($settings);

        return [
            $field => [
                'rules' => $uploadRule
            ]
        ];
    }
}
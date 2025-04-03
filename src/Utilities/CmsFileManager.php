<?php

declare(strict_types=1);

namespace AvegaCms\Utilities;

use AvegaCms\Enums\FileTypes;
use AvegaCms\Models\Admin\FilesLinksModel;
use AvegaCms\Models\Admin\FilesModel;
use AvegaCms\Utilities\Exceptions\UploaderException;
use CodeIgniter\Files\File;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Images\Exceptions\ImageException;
use Config\Mimes;
use Config\Services;
use ReflectionException;

class CmsFileManager
{
    public const excludedDirs = [
        FCPATH . 'uploads',
        'uploads',
    ];

    /**
     * @return object|null
     *
     * @throws ReflectionException|UploaderException
     */
    public static function upload(
        array $entity,
        array|string|null $uploadConfig = null,
        array $fileConfig = []
    ): ?array {
        $request   = Services::request();
        $validator = Services::validation();
        $directory = is_array($uploadConfig) ? ($uploadConfig['directory'] ?? 'content') : (null === $uploadConfig ? '' : $uploadConfig);

        if ($validator->setRules(self::_entityRules())->run($entity) === false) {
            throw new UploaderException($validator->getErrors());
        }

        foreach (self::excludedDirs as $dir) {
            if (str_starts_with($directory, $dir)) {
                $directory = trim(substr($directory, strlen($dir)), '/');
                break;
            }
        }

        if (empty($directory)) {
            throw UploaderException::forEmptyPath();
        }

        if (empty($dirData = (new FilesLinksModel())->getDirectories($directory))) {
            throw UploaderException::forDirectoryNotFound($directory);
        }

        $field = $uploadConfig['field'] ?? 'file';

        if (! is_array($uploadConfig)) {
            $uploadConfig = ['directory' => $directory];
        }

        if (! $validator->setRules(self::uploadSettings($uploadConfig))->withRequest($request)->run()) {
            throw new UploaderException($validator->getErrors());
        }

        $uploadedFile = $request->getFile($field);

        if (! $uploadedFile->isValid()) {
            throw new UploaderException($uploadedFile->getErrorString() . '(' . $uploadedFile->getError() . ')');
        }

        return self::_setFile($uploadedFile->getPathname(), $dirData, $entity, $fileConfig, false, $uploadedFile->getClientName());
    }

    /**
     * @throws ReflectionException|UploaderException
     */
    public static function setFile(
        string $filePath,
        array $entity,
        array|string|null $uploadConfig = null,
        array $fileConfig = [],
        bool $saveOriginal = false
    ): ?array {
        $FLM       = (new FilesLinksModel());
        $validator = Services::validation();
        $directory = is_array($uploadConfig) ? ($uploadConfig['directory'] ?? 'content') : (null === $uploadConfig ? '' : $uploadConfig);

        if (file_exists($filePath) === false) {
            throw UploaderException::forFileNotFound($filePath);
        }

        if ($validator->setRules(self::_entityRules())->run($entity) === false) {
            throw new UploaderException($validator->getErrors());
        }

        foreach (self::excludedDirs as $dir) {
            if (str_starts_with($directory, $dir)) {
                $directory = trim(substr($directory, strlen($dir)), '/');
                break;
            }
        }

        if (empty($directory)) {
            throw UploaderException::forEmptyPath();
        }

        if (empty($dirData = $FLM->getDirectories($directory))) {
            throw UploaderException::forDirectoryNotFound($directory);
        }

        return self::_setFile($filePath, $dirData, $entity, $fileConfig, $saveOriginal);
    }

    public static function getFiles(
        array $filter = [],
        bool $all = false
    ): array {
        $FLM = (new FilesLinksModel());
        $FLM->getFiles($filter);

        if ($all) {
            $result = $FLM->findAll();
        } else {
            $result = $FLM->apiPagination();
        }

        return $result;
    }

    /**
     * Регистрирует в файловом менеджере директорию
     * и создаёт её на сервере
     *
     * @throws ReflectionException|UploaderException
     */
    public static function createDirectory(string $path, array $config): int
    {
        $FM  = (new FilesModel());
        $FLM = (new FilesLinksModel());

        self::checkFilePath($path);

        $directoryId = $FM->insert(
            [
                'data'          => ['url' => $path],
                'provider'      => $config['provider'] ?? 0,
                'type'          => FileTypes::Directory->value,
                'created_by_id' => $config['user_id'] ?? 0,
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
                    'created_by_id' => $config['user_id'] ?? 0,
                ]
            );
        } else {
            d($FM->errors());
        }

        return $directoryId;
    }

    public static function delete(array|int $filesId): bool
    {
        if (empty(($filesId = ! is_array($filesId) ? [$filesId] : $filesId))) {
            return false;
        }

        $FM = new FilesModel();

        if (empty($files = $FM->getFilesForDelete($filesId))) {
            return true;
        }

        $filesId = [];

        foreach ($files as $file) {
            $filesId[] = $file->id;
            if ($file->type === FileTypes::Image->value) {
                self::deleteFile($file->data['path']['original']);
                if (! empty($file->data['path']['webp'] ?? '')) {
                    self::deleteFile($file->data['path']['webp']);
                }
                if (! empty($file->data['thumb'] ?? '')) {
                    foreach ($file->data['thumb'] as $thumb) {
                        self::deleteFile($thumb);
                    }
                }
                if (! empty($file->data['variants'] ?? '')) {
                    foreach ($file->data['variants'] as $variant) {
                        foreach ($variant as $item) {
                            self::deleteFile($item);
                        }
                    }
                }
            } else {
                self::deleteFile($file->data['path']);
            }
        }

        return $FM->delete($filesId);
    }

    /**
     * @throws ReflectionException|UploaderException
     */
    public static function createThumb(string $filePath, array $config = []): array
    {
        $original = FCPATH . trim($filePath, '/');

        if (! file_exists($original)) {
            throw UploaderException::forFileNotFound($filePath);
        }

        $defConfig = Cms::settings('filemanager.uploadConfig');
        $fileName  = basename($original);
        $fileUrl   = pathinfo($filePath, PATHINFO_DIRNAME);
        $settings  = [
            'thumbPrefix'        => $config['thumbPrefix'] ?? $defConfig['thumbPrefix'],
            'thumbQuality'       => $config['thumbQuality'] ?? $defConfig['thumbQuality'],
            'thumbMaintainRatio' => $config['thumbMaintainRatio'] ?? $defConfig['thumbMaintainRatio'],
            'thumbMasterDim'     => $config['thumbMasterDim'] ?? $defConfig['thumbMasterDim'],
            'thumbWidth'         => $config['thumbWidth'] ?? $defConfig['thumbWidth'],
            'thumbHeight'        => $config['thumbHeight'] ?? $defConfig['thumbHeight'],
        ];

        try {
            $url = $fileUrl . '/' . $settings['thumbPrefix'] . $fileName;

            Services::image()
                ->withFile($original)
                ->resize(
                    $settings['thumbWidth'],
                    $settings['thumbHeight'],
                    $settings['thumbMaintainRatio'],
                    $settings['thumbMasterDim']
                )->save(FCPATH . $url, $settings['thumbQuality']);

            $result = [
                'original' => $url,
            ];

            if ($defConfig['createWebp']) {
                $result['webp'] = self::convertToWebp($url, webpQuality: $defConfig['webpQuality']);
            }

            return $result;
        } catch (ImageException $e) {
            throw UploaderException::forFailThumbCreated($e->getMessage());
        }
    }

    /**
     * Метод конвертации изображения в WebP формат
     *
     * @throws UploaderException
     */
    public static function convertToWebp(string $filePath, string $newPath = '', int $webpQuality = 80): string
    {
        $original = FCPATH . trim($filePath, '/');

        if (! empty($newPath)) {
            $newPath = trim($newPath, '/');
        }

        if (! file_exists($original)) {
            throw UploaderException::forFileNotFound($filePath);
        }

        if (! extension_loaded('gd') || ! function_exists('gd_info')) {
            throw UploaderException::forGDLibNotSupported();
        }

        $fileName = basename($original);

        // Если пытаемся преобразовать изображение в webp-формате
        if (getimagesize($original)['mime'] === 'image/webp') {
            $url = $filePath;
            if (! empty($newPath)) {
                if (! is_dir(FCPATH . $newPath)) {
                    throw UploaderException::forDirectoryNotFound($newPath);
                }
                if (! copy($original, FCPATH . ($url = $newPath . $fileName))) {
                    throw UploaderException::forNotMovedFile($url);
                }
            }

            return $url;
        }

        $fileName = pathinfo($original, PATHINFO_FILENAME) . '.webp';
        $fileUrl  = pathinfo($filePath, PATHINFO_DIRNAME);
        $url      = $fileUrl . '/' . $fileName;

        if (! empty($newPath)) {
            if (! is_dir(FCPATH . $newPath)) {
                throw UploaderException::forDirectoryNotFound($newPath);
            }
            $url = $newPath . '/' . $fileName;
        }

        try {
            Services::image()
                ->withFile($original)
                ->convert(IMAGETYPE_WEBP)
                ->save(FCPATH . $url, $webpQuality);

            return $url;
        } catch (ImageException $e) {
            throw UploaderException::forFiledToConvertImageToWebP($e->getMessage());
        }
    }

    /**
     * @throws ReflectionException|UploaderException
     */
    public static function resizeImage(string $filePath, array $settings): array
    {
        $original   = FCPATH . trim($filePath, '/');
        $fileName   = pathinfo($original, PATHINFO_BASENAME);
        $fileUrl    = pathinfo($filePath, PATHINFO_DIRNAME);
        $createWebp = Cms::settings('filemanager.uploadConfig')['createWebp'];
        $variants   = [];

        foreach ($settings as $prefix => $setting) {
            $url = $fileUrl . '/' . $prefix . '_' . $fileName;

            $result = Services::image()
                ->withFile($original)
                ->resize(
                    $setting['width'],
                    $setting['height'],
                    $setting['maintainRatio'] ?? true,
                    $setting['masterDim'] ?? 'height'
                )->save(FCPATH . $url, $setting['quality'] ?? 90);

            if ($result) {
                $variant['original'] = $url;
                if ($createWebp) {
                    $variant['webp'] = self::convertToWebp($url, webpQuality: $setting['quality']);
                }
                $variants[$prefix] = $variant;
            }
        }

        return $variants;
    }

    /**
     * @throws ReflectionException|UploaderException
     */
    public static function fitImage(string $filePath, array $settings): array
    {
        $original   = FCPATH . trim($filePath, '/');
        $fileName   = pathinfo($original, PATHINFO_BASENAME);
        $fileUrl    = pathinfo($filePath, PATHINFO_DIRNAME);
        $createWebp = Cms::settings('filemanager.uploadConfig')['createWebp'];
        $variants   = [];

        foreach ($settings as $prefix => $setting) {
            $url = $fileUrl . '/' . $prefix . '_' . $fileName;

            $fit = Services::image()
                ->withFile($original)
                ->fit($setting['width'], $setting['height'], $setting['position'] ?? 'center')
                ->save(FCPATH . $url, $setting['quality'] ?? 90);

            if ($fit) {
                $variant['original'] = $url;
                if ($createWebp) {
                    $variant['webp'] = self::convertToWebp($url, webpQuality: $setting['quality'] ?? 90);
                }
                $variants[$prefix] = $variant;
            }
        }

        return $variants;
    }

    /**
     * @throws UploaderException
     */
    public static function checkFilePath(string $path): void
    {
        $uploadPath = FCPATH . 'uploads';
        $path       = trim($path, '/');

        if (is_dir($uploadPath . '/' . $path)) {
            return;
        }

        if (empty($path = explode('/', $path))) {
            throw UploaderException::forEmptyPath();
        }

        $directoryPath = $uploadPath;

        foreach ($path as $directory) {
            if (! is_dir($directoryPath .= '/' . $directory)) {
                if (! mkdir($directoryPath, 0777, true)) {
                    throw UploaderException::forCreateDirectory($directoryPath);
                }
                if (! is_file($directoryPath . '/index.html')) {
                    $file = fopen($directoryPath . '/index.html', 'x+b');
                    fclose($file);
                }
            }
        }
    }

    private static function _entityRules(): array
    {
        return [
            'module_id' => ['rules' => 'if_exist|is_natural'],
            'entity_id' => ['rules' => 'if_exist|is_natural'],
            'item_id'   => ['rules' => 'if_exist|is_natural'],
            'user_id'   => ['rules' => 'if_exist|is_natural'],
        ];
    }

    /**
     * @throws ReflectionException|UploaderException
     */
    private static function _setFile(string $filePath, object $dirData, array $entity, array $fileConfig, bool $saveOriginal = false, ?string $clientFileName = null): ?array
    {
        $FM        = (new FilesModel());
        $FLM       = (new FilesLinksModel());
        $userId    = ($entity['user_id'] ?? 0);
        $defConfig = Cms::settings('filemanager.uploadConfig');

        $uploadedFile = new UploadedFile($filePath, basename($filePath), error: 0);

        if ($uploadedFile->hasMoved()) {
            throw UploaderException::forHasMoved($uploadedFile->getName());
        }

        $uploadPath = FCPATH . ($directory = ('uploads/' . $dirData->url)) . '/';
        $fileName   = $uploadedFile->getRandomName();
        $size       = $uploadedFile->getSize();
        $title      = $clientFileName ?? pathinfo($uploadedFile->getName(), PATHINFO_FILENAME);

        if ($saveOriginal === true) {
            // Делаем копию с оригинала
            if (! copy($filePath, $uploadPath . $fileName)) {
                throw UploaderException::forNotMovedFile($filePath);
            }
        } else {
            // Переносим файл в нужную директорию
            if (! rename($filePath, $uploadPath . $fileName)) {
                throw UploaderException::forNotMovedFile($filePath);
            }
        }

        // Получаем информацию по файлу
        $file     = new File($uploadPath . $fileName);
        $isImage  = mb_strpos(Mimes::guessTypeFromExtension($extension = $file->getExtension()) ?? '', 'image') === 0;
        $type     = ($isImage) ? FileTypes::Image->value : FileTypes::File->value;
        $dirFile  = $directory . '/' . $fileName;
        $fileData = [
            'provider' => 0,
            'type'     => $type,
            'data'     => [
                'title' => $title,
                'ext'   => $extension,
                'size'  => $size,
                'file'  => $fileName,
                'path'  => $dirFile,
            ],
            'created_by_id' => $userId,
        ];

        if ($type === FileTypes::Image->value) {
            $fileData['data']['thumb'] = self::createThumb($dirFile);
            $fileData['data']['path']  = ['original' => $dirFile];

            if ($defConfig['createWebp']) {
                $fileData['data']['path']['webp'] = self::convertToWebp(
                    $dirFile,
                    webpQuality: $defConfig['webpQuality']
                );
            }

            if (! empty($fileConfig)) {
                $fileData['data']['variants'] = match (($action = array_key_first($fileConfig))) {
                    'resize' => self::resizeImage($dirFile, $fileConfig[$action]),
                    'fit'    => self::fitImage($dirFile, $fileConfig[$action]),
                    default  => ''
                };
            }
        }

        if (($id = $FM->insert($fileData)) === false) {
            throw new UploaderException($FM->errors());
        }

        $fileLinks = [
            'id'            => $id,
            'user_id'       => $userId,
            'parent'        => $dirData->id,
            'module_id'     => $entity['module_id'] ?? $dirData->module_id,
            'entity_id'     => $entity['entity_id'] ?? 0,
            'item_id'       => $entity['item_id'] ?? 0,
            'uid'           => '',
            'type'          => $type,
            'created_by_id' => $userId,
        ];

        if (! $FLM->insert($fileLinks)) {
            throw new UploaderException($FLM->errors());
        }

        return self::getFiles(['id' => $id], true);
    }

    /**
     * @return list<array>
     *
     * @throws ReflectionException
     */
    private static function uploadSettings(array $settings): array
    {
        $defConfig   = Cms::settings('filemanager.uploadConfig');
        $field       = $settings['field'] ?? $defConfig['field'];
        $maxUpload   = (int) (ini_get('upload_max_filesize'));
        $maxPost     = (int) (ini_get('post_max_size'));
        $memoryLimit = (int) (ini_get('memory_limit'));

        $settings['maxSize'] = ($settings['maxSize'] ?? $defConfig['maxSize']) * 1024;

        $maxSize = ($memoryLimit > 0 ?
                min($maxUpload, $maxPost, $memoryLimit) :
                min($maxUpload, $maxPost)) * 1024;

        $maxSize    = ($settings['maxSize'] > $maxSize) ? $maxSize : $settings['maxSize'];
        $uploadRule = 'uploaded[' . $field . ']|max_size[' . $field . ',' . $maxSize . ']';

        if (isset($settings['maxDims'])) {
            $uploadRule .= '|max_dims[' . $field . ',' . $settings['maxDims'] . ']';
        }

        if (isset($settings['mimeIn'])) {
            $uploadRule .= '|mime_in[' . $field . ',' . $settings['mimeIn'] . ']';
        }

        $settings['extInImages'] ??= $defConfig['extInImages'];
        $settings['extInFiles'] ??= $defConfig['extInFiles'];

        $ext = match ($settings['extType'] ?? 'all') {
            'images' => implode(',', $settings['extInImages']),
            'files'  => implode(',', $settings['extInFiles']),
            default  => implode(',', $settings['extInFiles']) . ',' . implode(',', $settings['extInImages'])
        };

        $uploadRule .= '|ext_in[' . $field . ',' . trim($ext, ',') . ']';

        if (isset($settings['isImage'])) {
            $uploadRule .= '|ext_in[' . $field . ',' . trim(implode('|', $settings['extInImages']), '|') . ']';
            $uploadRule .= '|is_image[' . $field . ']';
        }

        unset($settings);

        return [
            $field => [
                'rules' => $uploadRule,
            ],
        ];
    }

    private static function deleteFile($file): void
    {
        if ((@unlink(FCPATH . $file)) === false) {
            log_message(
                'warning',
                'AvegaCms[CmsFileManager] ::  File ' . $file . ' could not be deleted'
            );
        }
    }
}

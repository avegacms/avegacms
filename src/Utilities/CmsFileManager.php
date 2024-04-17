<?php

declare(strict_types = 1);

namespace AvegaCms\Utilities;

use Config\Mimes;
use Config\Services;
use CodeIgniter\Files\File;
use CodeIgniter\Images\Exceptions\ImageException;
use AvegaCms\Enums\FileTypes;
use AvegaCms\Utilities\Exceptions\UploaderException;
use AvegaCms\Models\Admin\{FilesModel, FilesLinksModel};
use ReflectionException;

class CmsFileManager
{
    const entityRules = [
        'entity_id' => ['rules' => 'if_exist|is_natural'],
        'item_id'   => ['rules' => 'if_exist|is_natural'],
        'user_id'   => ['rules' => 'if_exist|is_natural']
    ];

    const excludedDirs = [
        FCPATH . 'uploads',
        'uploads'
    ];

    /**
     * @param  array  $entity
     * @param  array|string|null  $uploadConfig
     * @param  array  $fileConfig
     * @param  bool  $onlyUpload
     * @return object|null
     * @throws ReflectionException|UploaderException
     */
    public static function upload(
        array $entity,
        array|string $uploadConfig = null,
        array $fileConfig = [],
        bool $onlyUpload = false
    ): object|null {
        $request   = Services::request();
        $validator = Services::validation();
        $directory = is_array($uploadConfig) ? ($uploadConfig['directory'] ?? 'content') : (is_null($uploadConfig) ? '' : $uploadConfig);
        $userId    = $entity['user_id'] = ($entity['user_id'] ?? 0);
        $defConfig = Cms::settings('filemanager.uploadConfig');

        if ($validator->setRules(self::entityRules)->run($entity) === false) {
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

        if ($onlyUpload === false) {
            $FM  = (new FilesModel());
            $FLM = (new FilesLinksModel());
            if (($dirData = $FLM->getDirectories($directory)) === null) {
                throw UploaderException::forDirectoryNotFound($directory);
            }
        }

        $field = $uploadConfig['field'] ?? 'file';

        if ( ! is_array($uploadConfig)) {
            $uploadConfig = ['directory' => $directory];
        }

        if ( ! $validator->setRules(self::uploadSettings($uploadConfig))->withRequest($request)->run()) {
            throw new UploaderException($validator->getErrors());
        }

        $uploadedFile = $request->getFile($field);

        if ( ! $uploadedFile->isValid()) {
            throw new UploaderException($uploadedFile->getErrorString() . '(' . $uploadedFile->getError() . ')');
        }

        // Получаем текущие название файла без расширения
        $title = pathinfo($uploadedFile->getName(), PATHINFO_FILENAME);

        if ($uploadedFile->hasMoved()) {
            throw UploaderException::forHasMoved($uploadedFile->getName());
        }

        $uploadPath = FCPATH . ($directory = ('uploads/' . $directory)) . '/';

        // Переносим файл в нужную директорию
        $uploadedFile->move($uploadPath, ($fileName = $uploadedFile->getRandomName()));
        // Получаем информацию по файлу
        $file     = new File($uploadPath . $fileName);
        $isImage  = mb_strpos(Mimes::guessTypeFromExtension($extension = $file->getExtension()) ?? '', 'image') === 0;
        $type     = ($isImage) ? FileTypes::Image->value : FileTypes::File->value;
        $dirFile  = $directory . '/' . $fileName;
        $fileData = [
            'provider'      => 0,
            'type'          => $type,
            'data'          => [
                'title' => $title,
                'ext'   => $extension,
                'size'  => $uploadedFile->getSize(),
                'file'  => $fileName,
                'path'  => $dirFile
            ],
            'created_by_id' => $userId
        ];

        if ($type === FileTypes::Image->value) {
            if ($onlyUpload === false || ($onlyUpload && empty($fileConfig))) {
                $fileData['data']['thumb'] = self::createThumb($dirFile);
                $fileData['data']['path']  = ['original' => $dirFile];

                if ($defConfig['createWebp']) {
                    $fileData['data']['path']['webp'] = self::convertToWebp($dirFile,
                        webpQuality: $defConfig['webpQuality']);
                }
            }

            if ( ! empty($fileConfig)) {
                $fileData['data']['variants'] = match (($action = array_key_first($fileConfig))) {
                    'resize' => self::resizeImage($dirFile, $fileConfig[$action]),
                    'fit'    => self::fitImage($dirFile, $fileConfig[$action]),
                    default  => ''
                };
            }
        }

        if ($onlyUpload) {
            return (object) $fileData;
        }

        if (($id = $FM->insert($fileData)) === false) {
            throw new UploaderException($FM->errors());
        }

        $fileLinks = [
            'id'            => $id,
            'user_id'       => $userId,
            'parent'        => $dirData->id,
            'module_id'     => $dirData->module_id,
            'entity_id'     => $entity['entity_id'] ?? 0,
            'item_id'       => $entity['item_id'] ?? 0,
            'uid'           => '',
            'type'          => $type,
            'created_by_id' => $userId
        ];

        if ( ! $FLM->insert($fileLinks)) {
            throw new UploaderException($FLM->errors());
        }

        return self::getFiles(['id' => $id], true);
    }

    /**
     * @param  array  $filter
     * @param  bool  $all
     * @return object|null
     */
    public static function getFiles(
        array $filter = [],
        bool $all = false
    ): object|null {
        $FLM = (new FilesLinksModel());
        $FLM->getFiles($filter);

        if ($all) {
            $result = $FLM->findAll();
        } else {
            $result = $FLM->apiPagination();
        }

        return (object) $result;
    }

    /**
     * Возвращает информацию по директории
     *
     * @param  int|null  $id
     * @param  int|null  $parent
     * @param  int|null  $moduleId
     * @param  int|null  $entityId
     * @param  int|null  $itemId
     * @return object|null
     */
    public static function getDirectoryData(
        ?int $id,
        ?int $parent,
        ?int $moduleId,
        ?int $entityId,
        ?int $itemId
    ): object|null {
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

        self::checkFilePath($path);

        $directoryId = $FM->insert(
            [
                'data'          => ['url' => $path],
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
     * @param  string  $filePath
     * @param  array  $config
     * @return array
     * @throws ReflectionException|UploaderException
     */
    public static function createThumb(string $filePath, array $config = []): array
    {
        $original = FCPATH . trim($filePath, '/');

        if ( ! file_exists($original)) {
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
                'original' => $url
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
     * @param  string  $filePath
     * @param  string  $newPath
     * @param  int  $webpQuality
     * @return string
     * @throws UploaderException
     */
    public static function convertToWebp(string $filePath, string $newPath = '', int $webpQuality = 80): string
    {
        $original = FCPATH . trim($filePath, '/');

        if ( ! empty($newPath)) {
            $newPath = trim($newPath, '/');
        }

        if ( ! file_exists($original)) {
            throw UploaderException::forFileNotFound($filePath);
        }

        if ( ! extension_loaded('gd') || ! function_exists('gd_info')) {
            throw UploaderException::forGDLibNotSupported();
        }

        $fileName = basename($original);

        // Если пытаемся преобразовать изображение в webp-формате
        if (getimagesize($original)['mime'] === 'image/webp') {
            $url = $filePath;
            if ( ! empty($newPath)) {
                if ( ! is_dir(FCPATH . $newPath)) {
                    throw UploaderException::forDirectoryNotFound($newPath);
                }
                if ( ! copy($original, FCPATH . ($url = $newPath . $fileName))) {
                    throw UploaderException::forNotMovedFile($url);
                }
            }
            return $url;
        }

        $fileName = pathinfo($original, PATHINFO_FILENAME) . '.webp';
        $fileUrl  = pathinfo($filePath, PATHINFO_DIRNAME);
        $url      = $fileUrl . '/' . $fileName;

        if ( ! empty($newPath)) {
            if ( ! is_dir(FCPATH . $newPath)) {
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
     * @param  string  $filePath
     * @param  array  $settings
     * @return array
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
                ->resize($setting['width'], $setting['height'], $setting['maintainRatio'], $setting['masterDim'])
                ->save(FCPATH . $url, $setting['quality'] ?? 90);

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
     * @param  string  $filePath
     * @param  array  $settings
     * @return array
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
     * @param  string  $path
     * @return void
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
            if ( ! is_dir($directoryPath .= '/' . $directory)) {
                if ( ! mkdir($directoryPath, 0777, true)) {
                    throw UploaderException::forCreateDirectory($directoryPath);
                }
                if ( ! is_file($directoryPath . '/index.html')) {
                    $file = fopen($directoryPath . '/index.html', 'x+b');
                    fclose($file);
                }
            }
        }
    }

    /**
     * @param  array  $settings
     * @return array[]
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

        $settings['extInImages'] = $settings['extInImages'] ?? $defConfig['extInImages'];
        $settings['extInFiles']  = $settings['extInFiles'] ?? $defConfig['extInFiles'];

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
                'rules' => $uploadRule
            ]
        ];
    }
}
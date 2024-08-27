<?php

declare(strict_types=1);

namespace AvegaCms\Utilities;

use AvegaCms\Config\Mimes;
use AvegaCms\Config\Services;
use AvegaCms\Utilities\Exceptions\UploaderException;
use CodeIgniter\Files\File;

class Uploader
{
    protected static string $uploadPath = FCPATH . 'uploads';

    /**
     * @throws UploaderException
     */
    public static function file(string $field, string $filePath, array $settings): array
    {
        $request   = Services::request();
        $validator = Services::validation();

        self::checkFilePath($filePath);
        $validationRule = self::checkSettings($field, $settings);

        if ($validator->setRules($validationRule)->withRequest($request)->run() === false) {
            throw new UploaderException($validator->getErrors());
        }

        $file = $request->getFile($field);

        if (! $file->isValid()) {
            throw new UploaderException($file->getErrorString() . '(' . $file->getError() . ')');
        }

        if ($file->hasMoved()) {
            throw UploaderException::forHasMoved($file->getName());
        }

        $filepath = self::$uploadPath . '/' . $filePath;

        $file->move($filepath, $file->getName());

        $file     = new File($filepath . '/' . $file->getName());
        $isImage  = mb_strpos(Mimes::guessTypeFromExtension($extension = $file->getExtension()) ?? '', 'image') === 0;
        $fileName = $file->getFilename();

        return [
            'fileName'  => $fileName,
            'fileUrl'   => 'uploads/' . $filePath . '/' . $fileName,
            'pathName'  => $file->getPathname(),
            'size'      => (float) $file->getSizeByUnit('kb'),
            'hash'      => hash_file('md5', FCPATH . 'uploads/' . $filePath . '/' . $fileName),
            'isImage'   => $isImage,
            'fileType'  => $isImage ? 'IMAGE' : 'FILE',
            'extension' => $extension,
            'mimeType'  => $file->getMimeType(),
        ];
    }

    /**
     * @throws UploaderException
     */
    public static function checkFilePath(string $path): void
    {
        if (is_dir(self::$uploadPath . $path)) {
            return;
        }

        if (empty($path = explode('/', $path))) {
            throw UploaderException::forEmptyPath();
        }

        $directoryPath = self::$uploadPath;

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

    private static function checkSettings(string $field, array $settings): array
    {
        $maxSize = self::getMaxFileSize();

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
                'rules' => $uploadRule,
            ],
        ];
    }

    private static function getMaxFileSize(): int
    {
        $max_upload   = (int) (ini_get('upload_max_filesize'));
        $max_post     = (int) (ini_get('post_max_size'));
        $memory_limit = (int) (ini_get('memory_limit'));

        return ($memory_limit > 0 ? min($max_upload, $max_post, $memory_limit) : min($max_upload, $max_post)) * 1024;
    }
}

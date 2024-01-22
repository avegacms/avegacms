<?php

namespace AvegaCms\Utilities;

use AvegaCms\Utilities\Exceptions\EmailSenderException;
use AvegaCms\Models\Admin\EmailTemplateModel;
use ReflectionException;

class EmailSender
{
    /**
     * @param  string  $template
     * @param  string|array  $recipient
     * @param  array  $data
     * @param  string|null  $locale
     * @param  array|null  $attached
     * @param  array|null  $myConfig
     * @return void
     * @throws EmailSenderException
     * @throws ReflectionException
     */
    public static function run(
        string $template,
        string|array $recipient,
        array $data = [],
        ?string $locale = null,
        ?array $attached = null,
        ?array $myConfig = null
    ): void {
        //TODO Проверяем параметр получателя. Если это простой массив (или переданной строкой),
        // то будут отправлены как 'to', если в массиве присутствуют ключи 'cc'
        // или 'bcc' то по соответствующему сценарию
        if (empty($recipient = self::toArray($recipient))) {
            throw EmailSenderException::forNoRecipient();
        }

        if (is_null($config = $myConfig)) {
            $config = Cms::settings('core.email');
        }

        if (is_null($locale)) {
            $locale = Cms::settings('core.env.defLocale');
        }

        if ( ! file_exists(APPPATH . 'Views/template/email/foundation.php')) {
            throw EmailSenderException::forNoEmailFolder();
        }

        if (empty($eTemplate = model(EmailTemplateModel::class)->getEmailTemplate($template))) {
            throw EmailSenderException::forTemplateNotFound();
        }

        if ( ! empty($eTemplate['view'])) {
            if ( ! file_exists(APPPATH . 'Views/' . ($view = 'template/email/blocks/' . $eTemplate['view']) . '.php')) {
                throw EmailSenderException::forNoViewTemplate($view);
            }
            $emailData['content'] = view($view, [$data, ...['locale' => $locale]], ['debug' => false]);
        } else {
            $emailData['content'] = strtr($eTemplate['content'][$locale], self::prepData($data));
        }

        $html = view('template/email/foundation', $emailData, ['debug' => false]);
    }

    /**
     * @param  string|array  $email
     * @return array
     */
    public static function toArray(string|array $email): array
    {
        return ! is_array($email) ? ((str_contains($email, ',')) ?
            preg_split('/[\s,]/', $email, -1, PREG_SPLIT_NO_EMPTY) :
            (array) trim($email)) : $email;
    }

    /**
     * @param  array  $data
     * @return array
     */
    public static function prepData(array $data): array
    {
        $prepData = [];
        if ( ! empty($data)) {
            foreach ($data as $key => $value) {
                $prepData['{{' . strtoupper($key) . '}}'] = $value;
            }
        }

        return $prepData;
    }
}
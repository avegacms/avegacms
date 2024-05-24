<?php

declare(strict_types = 1);

namespace AvegaCms\Utilities;

use AvegaCms\Utilities\Exceptions\MailException;
use AvegaCms\Models\Admin\EmailTemplateModel;
use Config\Services;
use ReflectionException;

class Mail
{
    /**
     * @param  string  $template
     * @param  string|array  $recipient
     * @param  array  $data
     * @param  string|null  $locale
     * @param  array|null  $attached
     * @param  array|null  $myConfig
     * @return void
     * @throws MailException
     * @throws ReflectionException
     */
    public static function send(
        string $template,
        string|array $recipient,
        array $data = [],
        ?string $locale = null,
        ?array $attached = null,
        ?array $myConfig = null
    ): void {
        if (empty($recipient = self::toArray($recipient))) {
            throw MailException::forNoRecipient();
        }

        if ( ! isset($recipient['to']) && ! isset($recipient['bcc']) && ! isset($recipient['cc'])) {
            $recipient = ['to' => $recipient];
            if (empty($recipient['to'])) {
                throw MailException::forNoRecipient();
            }
        }

        if (isset($recipient['cc'])) {
            $recipient['cc'] = self::toArray($recipient['cc']);
        }

        if (isset($recipient['bcc'])) {
            $recipient['bcc'] = self::toArray($recipient['bcc']);
        }

        if (isset($recipient['to'])) {
            $recipient['to'] = self::toArray($recipient['to']);
        }

        if (is_null($locale)) {
            $locale = Cms::settings('core.env.defLocale');
        }

        if ( ! file_exists(APPPATH . 'Views/template/email/foundation.php')) {
            throw MailException::forNoEmailFolder();
        }

        if (empty($eTemplate = model(EmailTemplateModel::class)->getEmailTemplate($template))) {
            throw MailException::forTemplateNotFound();
        }

        if ( ! empty($eTemplate->view)) {
            if ( ! file_exists(APPPATH . 'Views/' . ($view = 'template/email/blocks/' . $eTemplate->view) . '.php')) {
                throw MailException::forNoViewTemplate($view);
            }
            $emailData['content'] = view($view, [...$data, ...['locale' => $locale]], ['debug' => false]);
        } else {
            $emailData['content'] = strtr($eTemplate->content[$locale], self::prepData($data));
        }

        $config = self::getConfig($myConfig);
        $email  = Services::email($config);

        $email->setFrom(
            $config['fromEmail'],
            $config['fromName'] ?? '',
            $config['protocol'] === 'smtp' ? null : ($config['returnEmail'] ?? null)
        )
            ->setSubject($eTemplate->subject[$locale] ?? '')
            ->setTo($recipient['to']);

        if ( ! empty($recipient['cc'] ?? '')) {
            $email->setCC($recipient['cc']);
        }

        if ( ! empty($recipient['bcc'] ?? '')) {
            $email->setBCC($recipient['bcc']);
        }

        if ( ! empty($config['replyEmail'])) {
            $email->setReplyTo($config['replyEmail'], $config['replyName'] ?? '');
        }

        $emailData['foundation'] = [
            ...$emailData['foundation'] ?? [],
            'autoSentEmail'      => $config['autoSentEmail'],
            'cancelSubscription' => $config['cancelSubscription']
        ];

        $email->setMessage(
            ($config['mailType'] === 'html') ? view('template/email/foundation', $emailData,
                ['debug' => false]) : $emailData['content']
        );

        if ( ! empty($attached ?? '')) {
            foreach ($attached as $item) {
                $email->attach(
                    $item['file'],
                    '',
                    $item['newName'] ?? '',
                    $item['mimeType'] ?? '',
                );
            }
        }

        if ( ! $email->send()) {
            log_message('error', $email->printDebugger(['headers']));
            throw MailException::forNoSendEmail();
        }
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

    /**
     * @param  array|null  $config
     * @return array
     * @throws ReflectionException
     */
    protected static function getConfig(?array $config = null): array
    {
        $defConfig = Cms::settings('core.email');

        return [

            'fromEmail'          => $config['fromEmail'] ?? $defConfig['fromEmail'],
            'fromName'           => $config['fromName'] ?? $defConfig['fromEmail'],
            'replyEmail'         => $config['replyEmail'] ?? $defConfig['replyEmail'],
            'replyName'          => $config['replyName'] ?? $defConfig['replyName'],
            'returnEmail'        => $config['returnEmail'] ?? $defConfig['returnEmail'],
            'userAgent'          => $config['userAgent'] ?? $defConfig['userAgent'],
            'protocol'           => strtolower(($config['protocol'] ?? $defConfig['protocol']) ?? 'mail'),
            'wordWrap'           => $config['wordWrap'] ?? 76,
            'validate'           => true,
            'mailType'           => strtolower($config['mailType'] ?? $defConfig['mailType']),
            'charset'            => $config['charset'] ?? $defConfig['charset'],
            'priority'           => $config['priority'] ?? $defConfig['priority'],

            // SMTP - настройки
            'smtpHost'           => $config['smtpHost'] ?? $defConfig['smtpHost'],
            'smtpUser'           => $config['smtpUser'] ?? $defConfig['smtpUser'],
            'smtpPass'           => $config['smtpPass'] ?? $defConfig['smtpPass'],
            'smtpPort'           => $config['smtpPort'] ?? $defConfig['smtpPort'],
            'smtpTimeout'        => $config['smtpTimeout'] ?? $defConfig['smtpTimeout'],
            'smtpKeepalive'      => $config['smtpKeepalive'] ?? $defConfig['smtpKeepalive'],
            'smtpCrypto'         => $config['smtpCrypto'] ?? $defConfig['smtpCrypto'],

            // Доп. настройки
            'autoSentEmail'      => $config['autoSentEmail'] ?? true,
            'cancelSubscription' => $config['cancelSubscription'] ?? false,
        ];
    }
}
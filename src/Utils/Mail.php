<?php

namespace AvegaCms\Utils;

use AvegaCms\Models\Admin\EmailTemplateModel;
use Config\Services;
use RuntimeException;

class Mail
{
    /**
     * @param  string  $slug
     * @param  string|array  $to
     * @param  int  $locale
     * @param  array  $data
     * @param  array  $attach
     * @param  array  $config
     */
    public static function send(
        string $slug,
        string|array $to,
        int $locale = 0,
        array $data = [],
        array $attach = [],
        array $config = []
    ): bool {
        if (empty($slug)) {
            throw new RuntimeException('Slug cannot be empty');
        }

        if (empty($to) || empty($to['recipient'] ?? '')) {
            throw new RuntimeException('The email address of the recipient of the letter is not specified');
        }

        $ETM = model(EmailTemplateModel::class);

        if (($tData = $ETM->getEmailTemplate($slug, $locale)) === null) {
            throw new RuntimeException('Email template not found');
        }

        if ( ! empty($tData->template) && ! file_exists($file = APPPATH . 'Views/' . ($tData->template = 'template/email/templates/' . $tData->template) . '.php')) {
            throw new RuntimeException("Email template file {$file} not found");
        }

        $config = self::getConfig($config);
        $parser = Services::parser();
        $email = Services::email($config);

        $email->setFrom(
            $config['fromEmail'],
            $config['fromName'] ?? [],
            $config['protocol'] === 'smtp' ? null : ($config['returnEmail'] ?? null)
        );

        $email->setSubject($parser->setData($data)->renderString($tData->subject));

        if ( ! empty($config['replyEmail'])) {
            $email->setReplyTo($config['replyEmail'], $config['replyName']);
        }

        $email->setTo($to['recipient']);

        if ( ! empty($to['ccRecipient'] ?? '')) {
            $email->setCC($to['ccRecipient']);
        }

        if ( ! empty($to['bccRecipient'] ?? '')) {
            $email->setBCC($to['bccRecipient']);
        }

        if ( ! empty($attach ?? '')) {
            foreach ($attach as $item) {
                $email->attach(
                    $item['file'],
                    '',
                    $item['newName'] ?? '',
                    $item['mimeType'] ?? '',
                );
            }
        }

        if ( ! empty($tData->template)) {
            $data['emailTemplateData'] = $parser->setData($data)->render($tData->template);
        } else {
            $data['emailTemplateData'] = $parser->setData($data)->renderString($tData->content);
        }

        $email->setMessage(
            $parser->setData($data)->render(
                'template/email/foundation',
                ['cascadeData' => true]
            )
        );

        return $email->send();
    }

    /**
     * @param  array  $config
     * @return array
     */
    protected static function getConfig(array $config): array
    {
        helper(['avegacms']);

        $defConfig = settings('core.email');

        return [

            'fromEmail'     => $config['fromEmail'] ?? $defConfig['fromEmail'],
            'fromName'      => $config['fromName'] ?? $defConfig['fromEmail'],
            'replyEmail'    => $config['replyEmail'] ?? $defConfig['replyEmail'],
            'replyName'     => $config['replyName'] ?? $defConfig['replyName'],
            'returnEmail'   => $config['returnEmail'] ?? $defConfig['returnEmail'],
            'userAgent'     => $config['userAgent'] ?? $defConfig['userAgent'],
            'protocol'      => strtolower(($config['protocol'] ?? $defConfig['protocol']) ?? 'mail'),
            'wordWrap'      => $config['wordWrap'] ?? 76,
            'validate'      => true,
            'mailType'      => strtolower($config['mailType'] ?? $defConfig['mailType']),
            'charset'       => $config['charset'] ?? $defConfig['charset'],
            'priority'      => $config['priority'] ?? $defConfig['priority'],

            // SMTP - насройки
            'smtpHost'      => $config['smtpHost'] ?? $defConfig['smtpHost'],
            'smtpUser'      => $config['smtpUser'] ?? $defConfig['smtpUser'],
            'smtpPass'      => $config['smtpPass'] ?? $defConfig['smtpPass'],
            'smtpPort'      => $config['smtpPort'] ?? $defConfig['smtpPort'],
            'smtpTimeout'   => $config['smtpTimeout'] ?? $defConfig['smtpTimeout'],
            'smtpKeepalive' => $config['smtpKeepalive'] ?? $defConfig['smtpKeepalive'],
            'smtpCrypto'    => $config['smtpCrypto'] ?? $defConfig['smtpCrypto']
        ];
    }
}
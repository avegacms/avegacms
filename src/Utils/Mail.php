<?php

namespace AvegaCms\Utils;

use CodeIgniter\Email\Email;
use AvegaCms\Models\Admin\EmailTemplateModel;
use RuntimeException;

class Mail
{
    /**
     * @param  string  $template
     * @param  array  $to
     * @param  int  $locale
     * @param  array  $data
     * @param  array  $attach
     * @param  array  $config
     * @return bool
     */
    public static function send(
        string $slug,
        array $to,
        int $locale = 0,
        array $data = [],
        array $attach = [],
        array $config = []
    ): bool {
        if (empty($slug)) {
            throw new RuntimeException('Slug cannot be empty');
        }

        if (empty($to) || empty($to['toEmail'] ?? '')) {
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
            'userAgent'     => $config['userAgent'] ?? $defConfig['userAgent'],
            'protocol'      => $config['protocol'] ?? $defConfig['protocol'],
            'wordWrap'      => $config['wordWrap'] ?? 76,
            'validate'      => true,
            'mailType'      => $config['mailType'] ?? $defConfig['mailType'],
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
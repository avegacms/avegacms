<?php

declare(strict_types=1);

namespace AvegaCms\Utilities;

use AvegaCms\Libraries\Authorization\Exceptions\AuthorizationException;
use AvegaCms\Models\Admin\LoginModel;

class Auth
{
    public static function setPassword(string $pass): string
    {
        return password_hash($pass, PASSWORD_BCRYPT);
    }

    public static function genPassword(int $length = 12): string
    {
        $allChars                  = $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $allChars .= $lowercase    = 'abcdefghijklmnopqrstuvwxyz';
        $allChars .= $numbers      = '0123456789';
        $allChars .= $specialChars = '@$!%?&';

        // Инициализация пароля
        $password = '';

        // Добавление по крайней мере одной заглавной буквы
        $password .= $uppercase[mt_rand(0, strlen($uppercase) - 1)];

        // Добавление по крайней мере одной строчной буквы
        $password .= $lowercase[mt_rand(0, strlen($lowercase) - 1)];

        // Добавление по крайней мере одной цифры
        $password .= $numbers[mt_rand(0, strlen($numbers) - 1)];

        // Добавление по крайней мере одного специального символа
        $password .= $specialChars[mt_rand(0, strlen($specialChars) - 1)];

        // Добавление остальных символов, чтобы длина пароля была больше 8 символов
        for ($i = 0; $i < $length - 4; $i++) {
            $password .= $allChars[mt_rand(0, strlen($allChars) - 1)];
        }

        // Перемешивание пароля
        $password = str_shuffle($password);

        // Проверка пароля с помощью регулярного выражения
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%?&])[A-Za-z\d@$!%?&]{8,20}$/';
        if (! preg_match($pattern, $password)) {
            // Если пароль не соответствует требованиям, повторяем процесс генерации
            $password = self::genPassword($length);
        }

        return $password;
    }

    /**
     * @throws AuthorizationException
     */
    public static function setProfile(int $userId, string $role, array $userData = []): array
    {
        if (($user = model(LoginModel::class)->getUser(['id' => $userId, 'role' => $role])) === null) {
            throw AuthorizationException::forUnknownUser();
        }

        $hashName = 'Profile' . ucfirst(strtolower($role)) . '_' . $userId;
        cache()->delete($hashName);

        return cache()->remember($hashName, 30 * DAY, static function () use ($user, $userData) {
            return [
                'userId'    => $user->id,
                'roleId'    => $user->roleId,
                'role'      => $user->role,
                'timezone'  => $user->timezone,
                'login'     => $user->login,
                'status'    => $user->status,
                'condition' => $user->condition,
                'avatar'    => $user->avatar,
                'phone'     => $user->phone,
                'email'     => $user->email,
                'userData'  => $user->profile,
                'module'    => $user->module,
                ...$userData,
            ];
        });
    }

    public static function getProfile(int $userId, string $role): ?array
    {
        return cache('Profile' . ucfirst(strtolower($role)) . '_' . $userId);
    }
}

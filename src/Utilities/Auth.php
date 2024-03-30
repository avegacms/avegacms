<?php

declare(strict_types = 1);

namespace AvegaCms\Utilities;

use AvegaCms\Libraries\Authorization\Exceptions\AuthorizationException;
use AvegaCms\Models\Admin\LoginModel;

class Auth
{
    /**
     * @param  string  $pass
     * @return string
     */
    public static function setPassword(string $pass): string
    {
        return password_hash($pass, PASSWORD_BCRYPT);
    }

    /**
     * @param  int  $length
     * @return string
     */
    public static function genPassword(int $length = 12): string
    {
        $allChars = $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $allChars .= $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $allChars .= $numbers = '0123456789';
        $allChars .= $specialChars = '@$!%?&';

        // Инициализация пароля
        $password = '';

        // Добавление по крайней мере одной заглавной буквы
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];

        // Добавление по крайней мере одной строчной буквы
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];

        // Добавление по крайней мере одной цифры
        $password .= $numbers[rand(0, strlen($numbers) - 1)];

        // Добавление по крайней мере одного специального символа
        $password .= $specialChars[rand(0, strlen($specialChars) - 1)];

        // Добавление остальных символов, чтобы длина пароля была больше 8 символов
        for ($i = 0; $i < $length - 4; $i++) {
            $password .= $allChars[rand(0, strlen($allChars) - 1)];
        }

        // Перемешивание пароля
        $password = str_shuffle($password);

        // Проверка пароля с помощью регулярного выражения
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%?&])[A-Za-z\d@$!%?&]{8,20}$/';
        if ( ! preg_match($pattern, $password)) {
            // Если пароль не соответствует требованиям, повторяем процесс генерации
            $password = self::genPassword($length);
        }

        return $password;
    }

    /**
     * @param  int  $userId
     * @param  string  $role
     * @param  array  $userData
     * @return array
     * @throws AuthorizationException
     */
    public static function setProfile(int $userId, string $role, array $userData = []): array
    {
        if (($user = model(LoginModel::class)->getUser(['id' => $userId, 'role' => $role])) === null) {
            throw AuthorizationException::forUnknownUser();
        }

        $hashName = 'Profile' . ucfirst(strtolower($role)) . '_' . $userId;
        cache()->delete($hashName);
        return cache()->remember($hashName, 30 * DAY, function () use ($user, $userData) {
            return [
                'timezone'  => $user->timezone,
                'login'     => $user->login,
                'status'    => $user->status,
                'condition' => $user->condition,
                'avatar'    => $user->avatar,
                'phone'     => $user->phone,
                'email'     => $user->email,
                'profile'   => $user->profile,
                'extra'     => $user->extra,
                ...$userData
            ];
        });
    }

    /**
     * @param  int  $userId
     * @param  string  $role
     * @return array|null
     */
    public static function getProfile(int $userId, string $role): array|null
    {
        return cache('Profile' . ucfirst(strtolower($role)) . '_' . $userId);
    }
}
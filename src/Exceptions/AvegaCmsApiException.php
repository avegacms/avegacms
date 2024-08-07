<?php

declare(strict_types = 1);

namespace AvegaCms\Exceptions;

use Exception;

class AvegaCmsApiException extends Exception
{
    /**
     * @return AvegaCmsApiException
     */
    public static function forNoData(): AvegaCmsApiException
    {
        return new static(message: lang('Api.errors.noData'));
    }

    /**
     * @param  string|null  $error
     * @return AvegaCmsApiException
     */
    public static function forInvalidJSON(string $error = null): AvegaCmsApiException
    {
        return new static(message: lang('Api.errors.invalidJSON', [$error]));
    }

    /**
     * @return AvegaCmsApiException
     */
    public static function forUndefinedData(): AvegaCmsApiException
    {
        return new static(message: lang('Api.errors.undefinedData'));
    }
}
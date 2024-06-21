<?php

declare(strict_types = 1);

namespace AvegaCms\Exceptions;

use Exception;

class AvegaCmsApiException extends Exception
{
    /**
     * @return AvegaCmsApiException
     */
    public function fonNoData(): AvegaCmsApiException
    {
        return new static(message: lang('Api.errors.noData'));
    }

    /**
     * @return AvegaCmsApiException
     */
    public function forInvalidJSON(): AvegaCmsApiException
    {
        return new static(message: lang('Api.errors.invalidJSON'));
    }

    /**
     * @return AvegaCmsApiException
     */
    public function forUndefinedData(): AvegaCmsApiException
    {
        return new static(message: lang('Api.errors.undefinedData'));
    }
}
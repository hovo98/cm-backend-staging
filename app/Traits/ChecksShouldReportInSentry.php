<?php

namespace App\Traits;

use App\Enums\OAuthExceptionCode;
use Illuminate\Support\Arr;
use Laravel\Passport\Exceptions\OAuthServerException;
use Throwable;

trait ChecksShouldReportInSentry
{
    public $checkBeforeReporting = [
        \Laravel\Passport\Exceptions\OAuthServerException::class,
    ];

    public function shouldReportInSentry(Throwable $exception): bool
    {
        // Check Don't Report
        if (! $this->shouldReport($exception)) {
            return false;
        }
        // Check Unique Lighthouse Exceptions
        if ($this->shouldSkipReporting($exception)) {
            return false;
        }
        if (! app()->bound('sentry')) {
            return false;
        }
        return true;
    }

    public function shouldSkipReporting(Throwable $exception): bool
    {
        if (is_null(Arr::first($this->checkBeforeReporting, fn ($type) => $exception instanceof $type))) {
            return false;
        }
        if ($exception instanceof OAuthServerException && OAuthExceptionCode::from($exception->getCode())->shouldntReport()) {
            return true;
        }
        return false;
    }
}

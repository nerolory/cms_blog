<?php

namespace App\Http\Requests\Concerns;

use App\Support\TypeCast;

/**
 * Валидация запроса normalizes input.
 */
trait NormalizesInput
{
    /**
     * trim input.
     *
     * @param  list<string>  $keys  список полей
     */
    protected function trimInput(array $keys): void
    {
        $normalized = [];
        foreach ($keys as $key) {
            if (! $this->has($key) || ! is_scalar($this->input($key))) {
                continue;
            }
            $normalized[$key] = trim((string) $this->input($key));
        }
        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /**
     * Optional fields: blank after trim become null (keep existing value on update).
     *
     * @param  list<string>  $keys
     */
    protected function nullIfBlankAfterTrim(array $keys): void
    {
        $normalized = [];
        foreach ($keys as $key) {
            if (! $this->has($key)) {
                continue;
            }
            $trimmed = trim(TypeCast::string($this->input($key)));
            if ($trimmed === '') {
                $normalized[$key] = null;
            }
        }
        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }
}

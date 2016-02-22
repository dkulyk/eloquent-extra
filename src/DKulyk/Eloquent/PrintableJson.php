<?php

namespace DKulyk\Eloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class PrintableJson
 *
 * @mixin Eloquent
 */
trait PrintableJson
{
    /**
     * Encode the given value as JSON.
     *
     * @param  mixed $value
     *
     * @return string
     */
    protected function asJson($value)
    {
        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
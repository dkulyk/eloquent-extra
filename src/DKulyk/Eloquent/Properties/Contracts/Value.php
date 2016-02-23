<?php

namespace DKulyk\Eloquent\Properties\Contracts;

/**
 * Value contract.
 */
interface Value extends \JsonSerializable
{
    /**
     * Get value int property type.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set value in property type.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value);

    /**
     * Get simple value
     *
     * @return mixed
     */
    public function getSimpleValue();
}

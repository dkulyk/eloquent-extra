<?php

/**
 * Class Contact
 *
 * @property string $email
 */
class Contact extends \Illuminate\Database\Eloquent\Model
{
    use \DKulyk\Eloquent\Validation;
    use \DKulyk\Eloquent\Logging;

    protected $fillable = ['email'];

    protected function rules()
    {
        return [
            'email' => 'required|email',
        ];
    }
}
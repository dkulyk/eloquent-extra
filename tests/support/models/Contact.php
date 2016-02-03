<?php

class Contact extends \Illuminate\Database\Eloquent\Model
{
    use \DKulyk\Eloquent\Validation;

    protected function rules()
    {
        return [
            'email' => 'required|email',
        ];
    }
}
<?php

/**
 * Class Contact
 *
 * @property string $email
 */
abstract class Contact extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'contacts';

    protected $fillable = ['email'];


}
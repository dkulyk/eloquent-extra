<?php


class ValidationTest extends TestCase
{

    /** @test */
    public function validation_failed()
    {
        $contact = new Contact();
        static::assertFalse($contact->validate());
        static::assertFalse($contact->save());

    }

    /** @test */
    public function validation_passed()
    {
        $contact = new Contact();
        $contact->email = 'test@example.com';
        static::assertTrue($contact->validate());
        static::assertTrue($contact->save());

    }

    /** @test */
    public function disabled_auto_validation()
    {
        $contact = new Contact();
        $contact->disableAutoValidation();
        $contact->email = ''; //not email
        static::assertFalse($contact->validate());
        static::assertTrue($contact->save());
    }
}
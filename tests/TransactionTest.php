<?php

class TransactionTest extends TestCase
{
    /** @test */
    public function rollback_test()
    {
        ContactTransaction::saved(
            function () {
                throw new Exception();
            }, 1
        );

        try {
            ContactTransaction::create(
                [
                    'email' => 'transaction@test.com',
                ]
            );
        } catch (Exception $e) {
        }

        static::assertNull(ContactTransaction::query()->where([
            'email' => 'transaction@test.com'
        ])->first());
    }
}

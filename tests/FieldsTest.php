<?php

use DKulyk\Eloquent\Propertier\Types;
use DKulyk\Eloquent\Propertier\Manager;

class FieldsTest extends TestCase
{
    /** @test */
    public function manage_property_test()
    {
        $contact = new ContactProperties();
        $single = $this->manager->addField($contact, 'single', Types::TYPE_INTEGER);

        static::assertTrue($single->exists);
        static::assertEquals($single, $this->manager->getFields($contact)->get('single'));
        static::assertNull($this->manager->getFields($contact)->get('nonexist'));
        static::assertEquals($single, $this->manager->getFields()->first(function ($id, $field) use ($single) {
            return $id === $single->id && $field->name === $single->name;
        }));

    }

    /** @test */
    public function it_should_make_a_casted_value()
    {

        $values = [
            Types::TYPE_STRING   => '0',
            Types::TYPE_DATETIME => '1970-01-01 00:00:00',
            Types::TYPE_DATE     => '1970-01-01',
            Types::TYPE_INTEGER  => 0,
            Types::TYPE_BOOLEAN  => false,
            Types::TYPE_FLOAT    => 0.0,
            Types::TYPE_JSON     => 0,
        ];

        $contact = new ContactProperties();
        $fields = $this->manager->getFields($contact);

        foreach ($values as $type => $value) {
            $field = $fields->get($type);
            $v = $this->manager->resolve($field);
            $v->setValue(0);
            static::assertEquals($value, $v->getSimpleValue());
        }

    }

    /** @test */
    public function test_dynamical_relations()
    {
        \DB::listen(function (\Illuminate\Database\Events\QueryExecuted $query) {
            $sql = $query->sql;
            $bindings = $query->connection->prepareBindings($query->bindings);
            if (count($bindings) > 0 && true) {

                $pdo = $query->connection->getPdo();
                foreach ($bindings as $binding) {
                    $sql = preg_replace('/\?/', $pdo->quote($binding), $sql, 1);
                }
            }
            echo($sql.' ('.$query->time.'ms)'.PHP_EOL);
        });
        $contacts = ContactProperties::with('fields')->first();
    }


}

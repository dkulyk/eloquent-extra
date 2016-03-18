<?php

namespace DKulyk\Eloquent\Propertier;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class Manager
{
    /**
     * The container instance.
     *
     * @var ContainerContract
     */
    protected $container;

    /**
     * @var array
     */
    protected $types = [];

    /**
     * @var Collection
     */
    protected $fields;

    /**
     * @var Collection
     */
    protected $fieldList;

    /**
     * @var CacheRepository
     */
    protected $cache;

    /**
     * @var ConfigRepository
     */
    protected $config;

    /**
     * Manager constructor.
     *
     * @param CacheRepository   $cache
     * @param ConfigRepository  $config
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        CacheRepository $cache,
        ConfigRepository $config
    ) {
        $this->cache = $cache;
        $this->config = $config;

        $this->register([
            Types::TYPE_STRING   => new Values\StringValue(),
            Types::TYPE_TEXT     => new Values\StringValue(),
            Types::TYPE_DATETIME => new Values\DateTimeValue(),
            Types::TYPE_DATE     => new Values\DateValue(),
            Types::TYPE_INTEGER  => new Values\IntegerValue(),
            Types::TYPE_BOOLEAN  => new Values\BooleanValue(),
            Types::TYPE_FLOAT    => new Values\FloatValue(),
            Types::TYPE_JSON     => new Values\JsonValue(),
        ]);
        $this->register((array)$this->config->get('eloquent-extra.fields_types', []));

        $this->fieldList = $this->cache->rememberForever('eav_fields', function () {
            return (new Field())
                ->setTable($this->config->get('eloquent-extra.fields_table'))
                ->newQuery()
                ->get()
                ->keyBy('id');
        });

        $this->groupFields();
    }

    protected function groupFields()
    {
        /* @var Collection $fields */
        $fields = $this->fieldList->groupBy('partner', true);
        $this->fields = $fields->map(
            function (Collection $fields) {
                return $fields->keyBy('name');
            }
        );
    }

    /**
     * Get the registered fields.
     *
     * @param Eloquent $partner
     *
     * @return Collection
     */
    public function getFields(Eloquent $partner = null)
    {
        return $partner === null
            ? $this->fieldList
            : $this->fields->get($partner->getMorphClass(), function () {
                return new Collection();
            });
    }

    /**
     * @param Eloquent        $entity
     * @param string          $name
     * @param string          $type
     * @param bool            $multiple
     * @param Eloquent|string $reference
     *
     * @return Field
     */
    public function addField(Eloquent $entity, $name, $type = Types::TYPE_STRING, $multiple = false, Eloquent $reference = null)
    {
        $field = new Field(
            [
                'partner'  => $entity->getMorphClass(),
                'name'     => $name,
                'type'     => $type,
                'multiple' => $multiple,
            ]
        );
        if ($type === Types::TYPE_REFERENCE) {
            $reference = $reference instanceof Eloquent ? $reference : new $reference();
            $field->reference = $reference->getMorphClass();
        }
        $field->save();

        $this->fieldList->put($field->getKey(), $field);
        $this->groupFields();

        return $field;
    }

    public function removeField(Field $field)
    {
        $field->delete();
        $this->fieldList->offsetUnset($field->getKey());
        $this->groupFields();
    }

    /**
     * @throws InvalidArgumentException
     *
     * @param array $types
     */
    public function register(array $types)
    {
        $tables = $this->config->get('eloquent-extra.values_tables', 'fields_values');
        foreach ($types as $type => $value) {
            /* @var FieldValue $value */
            if (is_int($type)) {
                throw new InvalidArgumentException('Allowed only named types');
            }
            if (!($value instanceof FieldValue)) {
                throw new InvalidArgumentException('Allowed only subclass of FieldValue');
            }

            if (is_array($tables) && array_key_exists($type, $tables)) {
                $value->setTable($tables[$type]);
            } else {
                $value->setTable($tables);
            }

            $this->types[$type] = $value;
        }
    }

    /**
     * @param Field $field
     *
     * @return FieldValue
     */
    public function resolve(Field $field)
    {
        return Arr::get($this->types, $field->type, function () {
            return $this->types[Types::TYPE_STRING];
        });
    }
}
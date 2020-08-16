<?php
/**
 * Define an uuid field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Ramsey\Uuid\Uuid as UuidGenerator;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Laramore\Facades\Option;
use Laramore\Contracts\Eloquent\LaramoreModel;

class Uuid extends BaseAttribute
{
    /**
     * Version used for generation.
     *
     * @var integer
     */
    protected $version;

    /**
     * Version 1 (time-based) UUID object constant identifier.
     *
     * @var integer
     */
    const VERSION_1 = UuidGenerator::UUID_TYPE_TIME;

    /**
     * Version 3 (name-based and hashed with MD5) UUID object constant identifier.
     *
     * @var integer
     */
    const VERSION_3 = UuidGenerator::UUID_TYPE_HASH_MD5;

    /**
     * Version 4 (random) UUID object constant identifier.
     *
     * @var integer
     */
    const VERSION_4 = UuidGenerator::UUID_TYPE_RANDOM;

    /**
     * Version 5 (name-based and hashed with SHA1) UUID object constant identifier.
     *
     * @var integer
     */
    const VERSION_5 = UuidGenerator::UUID_TYPE_HASH_SHA1;

    /**
     * Dry the value in a simple format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function dry($value)
    {
        if ($value instanceof UuidGenerator) {
            return $value->getBytes();
        }

        return $value;
    }

    /**
     * Hydrate the value in the correct format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function hydrate($value)
    {
        if (\is_null($value)) {
            return $value;
        }

        if (\is_string($value)) {
            try {
                return UuidGenerator::fromString($value);
            } catch (InvalidUuidStringException $_) {
                return UuidGenerator::fromBytes($value);
            }
        }

        throw new \Exception('The given value is not an uuid');
    }

    /**
     * Cast the value in the correct format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        if (\is_null($value) || ($value instanceof UuidGenerator)) {
            return $value;
        }

        if (\is_string($value)) {
            try {
                return UuidGenerator::fromString($value);
            } catch (InvalidUuidStringException $_) {
                return UuidGenerator::fromBytes($value);
            }
        }

        throw new \Exception('The given value is not an uuid');
    }

    /**
     * Serialize the value for outputs.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        return (string) $value;
    }

    /**
     * Return a new generated uuid.
     *
     * @return \Ramsey\Uuid\Uuid
     */
    public function generate(): \Ramsey\Uuid\Uuid
    {
        return $this->cast(UuidGenerator::{'uuid'.$this->version}(...$this->getConfig('generation')));
    }

    /**
     * Reet the value for the field.
     *
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function reset($model)
    {
        if ($this->hasDefault()) {
            return $this->set($model, $this->getDefault());
        }

        if ($this->hasOption(Option::autoGenerate())) {
            return $this->set($model, $this->generate());
        }

        return parent::reset($model);
    }

    /**
     * Check all properties and options before locking the field.
     *
     * @return void
     */
    public function checkOptions()
    {
        parent::checkOptions();

        if ($this->hasDefault() && $this->hasOption(Option::autoGenerate())) {
            throw new \LogicException("The field `{$this->getName()}` cannot have a default value and be auto generated");
        }
    }
}

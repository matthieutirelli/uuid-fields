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
use Laramore\Type;

class Uuid extends Field
{
    protected $type = Type::UUID;
    protected $autoGenerate = false;

    public function castValue($model, $value)
    {
        return is_null($value) ? $value : (string) $value;
    }

    public function setValue($model, $value)
    {
        if (!($value instanceof UuidGenerator)) {
            try {
                $value = UuidGenerator::fromString($value);
            } catch (InvalidUuidStringException $e) {
                throw new \Exception('The given value is not an uuid');
            }
        }

        return $this->castValue($model, $value);
    }

    public function generateUuid()
    {
        return $this->castValue(null, UuidGenerator::uuid4());
    }

    protected function locking()
    {
        parent::locking();

        if ($this->autoGenerate) {
            $this->observe('saving', function ($model) {
                if (is_null($model->{$this->name})) {
                    $model->{$this->name} = $this->generateUuid();
                }
            });
        }
    }
}

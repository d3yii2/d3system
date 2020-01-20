<?php

namespace invoicesreader\reader\optional;

use InvalidArgumentException;
use LogicException;

class Optional {

    private $value;
    private static $empty = [];

    public static function of($value)
    {
        if ($value === null) {
            throw new InvalidArgumentException("Value cannot be null");
        }

        $optional = new static();
        $optional -> value = $value;
        return $optional;
    }

    public static function ofEmpty()
    {
        if (!\array_key_exists(static::class, self::$empty)) {
            self::$empty[static::class] = new static();
        }
        return self::$empty[static::class];
    }

    public static function ofNullable($value)
    {
        return $value !== null
            ? self::of($value)
            : self::ofEmpty();
    }

    public function isPresent()
    {
        return $this->value !== null;
    }

    public function ifPresent(callable $action)
    {
        if ($this->value !== null) {
            $action($this->value);
        }
        return $this;
    }

    public function ifAbsent(callable $action)
    {
        if ($this->value === null) {
            $action();
        }
    }

    public function orElse($other)
    {
        return $this -> value !== null
            ? $this->value
            : $other;
    }

    public function orElseGet(callable $other)
    {
        return $this -> value !== null
                ? $this -> value
                : $other();
    }

    public function orElseThrow(callable $exceptionSupplier)
    {
        if ($this->value === null) {
            throw $exceptionSupplier();
        }
        return $this->value;
    }

    public function map(callable $mapper)
    {
        return null === $this->value
            ? self::ofEmpty()
            : self::ofNullable($mapper($this->value));
    }

    public function get()
    {
        if (null === $this->value) {
            throw new LogicException(sprintf('No value present for %s, use ::orElse instead', static::class));
        }
        return $this->value;
    }

    public function filter(callable $predicate)
    {
        if ($this->value === null) {
            return $this;
        }
        return $predicate($this->value)
            ? $this
            : self::ofEmpty();
    }
}

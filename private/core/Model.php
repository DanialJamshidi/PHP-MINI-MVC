<?php

class Model
{
    protected static function name(): string
    {
        return strtolower(
            basename(
                str_replace('\\', '/', static::class)
            )
        );
    }
}

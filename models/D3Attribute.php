<?php


namespace d3system\models;


interface D3Attribute
{
    public static function getName(): string;

    public static function getLabel(): string;

    public static function getColumn(): array;
}
<?php

namespace OvhSwift\Accessors;

use OvhSwift\App;

class AccessorResponse extends App
{
    public bool $success = true;

    public array $errors = [];
}
<?php

namespace Bnanan\LighthouseMonkey\Types;

class Directive
{
  public function __construct(public string $name, public string $type, public array $fields)
  {
  }
}
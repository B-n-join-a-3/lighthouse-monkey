<?php

namespace Bnanan\LighthouseMonkey\Types;

class Type
{
  public function __construct(public string $name, public array $fields)
  {
  }
}
<?php

namespace Bnanan\LighthouseMonkey\Types;

class Input
{
  public function __construct(public string $name, public array $fields)
  {
  }
}
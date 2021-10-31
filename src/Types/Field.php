<?php

namespace Bnanan\LighthouseMonkey\Types;

class Field
{
  public function __construct(
    public string $name,
    public string $type,
    public bool $isNullable,
    public bool $isList,
    public array $directives,
  ) {
  }
}
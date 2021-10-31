<?php

namespace Bnanan\LighthouseMonkey\Generators;

use Bnanan\LighthouseMonkey\Types\Field;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;


class MutationGenerator extends AbstractGenerator
{
  protected string $stub = 'mutation';
  protected string $filePath = 'GraphQL/Mutations';

  public function __construct(private Filesystem $files)
  {
    parent::__construct($files);
  }

  public function generate(string $name, Field $field): string
  {
    $this->name = Str::Studly($name);

    $this->replacements = [
      'name' => $this->name,
    ];

    return $this->store();
  }
  
  public function getFileName()
  {
    return "{$this->name}.php";
  }
}
<?php

namespace Bnanan\LighthouseMonkey\Generators;

use Bnanan\LighthouseMonkey\Types\Type;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;


class SeederGenerator extends AbstractGenerator
{
  protected array $fields = [];
  protected string $stub = 'seeder';
  protected string $filePath = '../database/seeders';

  public function __construct(private Filesystem $files)
  {
    parent::__construct($files);
  }

  public function generate(string $name, Type $type): string
  {
    $this->name = Str::Studly($name);

    $this->replacements = [
      'className' => $this->name,
    ];

    return $this->store();
  }
  
  public function getFileName()
  {
    return "{$this->name}Seeder.php";
  }
}
<?php

namespace Bnanan\LighthouseMonkey\Generators;

use Bnanan\LighthouseMonkey\Types\Type;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;


class FactoryGenerator extends AbstractGenerator
{
  protected array $fields = [];
  protected array $relationships = [];
  protected string $stub = 'factory';
  protected string $className = '';
  protected string $filePath = '../database/factories';

  public function __construct(private Filesystem $files)
  {
    parent::__construct($files);
  }

  public function generate(string $name, Type $type): string
  {
    $this->name = Str::Studly($name);

    foreach ($type->fields as $field) {
      $isRelation = false;
      foreach ($field->directives as $directive) {
        if (in_array($directive->name, ['belongsTo', 'hasMany', 'hasOne', 'belongsToMany'])) {
          $this->relationships[] = [$directive, $field];
          $isRelation = true;
          break;
        }
      }

      if (!$isRelation) {
        $this->fields[] = $field;
      }
    }

    $this->replacements = [
      'fields' => $this->getFields(),
      'relations' => $this->getRelations(),
      'modelName' => $this->name,
    ];

    return $this->store();
  }
  
  public function getFileName()
  {
    return "{$this->name}Factory.php";
  }

  public function getFields()
  {
    $stub = '';
    $mapping = [
      'String' => 'sentence(2)',
      'Int' => 'randomNumber()',
      'Bool' => 'boolean()',
      'DateTime' => 'datetime()',
      'Date' => 'date()',
    ];

    foreach ($this->fields as $field) {
      if ($field->name === 'id') {
        continue;
      }
      
      $fieldStub = $this->getStub('factory_field');
      $faker = $mapping[$field->type] ?? 'string';
      $stub .= $this->replace($fieldStub, ['field' => $field->name, 'faker' => $faker]);
    }

    return $stub;
  }

  public function getRelations()
  {
  }

  public function getRelationships()
  {
    //
  }
}
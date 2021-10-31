<?php

namespace Bnanan\LighthouseMonkey\Generators;

use Bnanan\LighthouseMonkey\Types\Type;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;


class MigrationGenerator extends AbstractGenerator
{
  protected array $fields = [];
  protected array $relationships = [];
  protected string $stub = 'migration';
  protected string $className = '';
  protected string $filePath = '../database/migrations';

  public function __construct(private Filesystem $files)
  {
    parent::__construct($files);
  }

  public function generate(string $name, Type $type): string
  {
    $this->name = Str::pluralStudly($name);
    $this->className = "Create{$this->name}Table";

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
      'tableName' => Str::snake($this->name),
      'className' => $this->className,
    ];

    return $this->store();
  }
  
  public function getFileName()
  {
    $datePrefix = date('Y_m_d_His');
    $snakedClass = Str::snake($this->className);
    return "{$datePrefix}_{$snakedClass}.php";
  }

  public function getFields()
  {
    $stub = '';
    $mapping = [
      'String' => 'string',
      'Int' => 'integer',
      'Bool' => 'boolean',
      'DateTime' => 'timestamp',
      'Date' => 'date',
    ];

    foreach ($this->fields as $field) {
      if ($field->name === 'id') {
        continue;
      }
      
      $fieldStub = $this->getStub('migration_field');
      $type = $mapping[$field->type] ?? 'string';
      $subs = [
        'type' => $type,
        'name' => Str::snake($field->name),
        'extra' => $field->isNullable ? '->nullable()' : '',
      ];
      $stub .= $this->replace($fieldStub, $subs);
    }
    return $stub;
  }

  public function getRelations()
  {
    $stub = '';

    foreach ($this->relationships as [$directive, $field]) {
      switch ($directive->name) {
        case 'belongsTo':
          $fieldStub = $this->getStub('migration_field');
          $subs = [
            'type' => 'foreignId',
            'name' => Str::snake($field->name . '_id'),
            'extra' => $field->isNullable ? '->nullable()' : '',
          ];
          $stub .= $this->replace($fieldStub, $subs);
      }
    }
    return $stub;
  }
}
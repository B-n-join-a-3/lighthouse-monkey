<?php

namespace Bnanan\LighthouseMonkey\Generators;

use Bnanan\LighthouseMonkey\Types\Type;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;


class ModelGenerator extends AbstractGenerator
{
  protected array $imports = [];
  protected array $replacements = [];
  protected array $relationships = [];
  protected string $filePath = 'Models';
  protected string $stub = 'model';

  public function __construct(private Filesystem $files)
  {
    parent::__construct($files);
  }

  public function generate(string $name, Type $type): string
  {
    $this->name = Str::studly($name);

    foreach ($type->fields as $field) {
      foreach ($field->directives as $directive) {
        if (in_array($directive->name, ['belongsTo', 'hasMany', 'hasOne', 'belongsToMany'])) {
          $this->imports[] = [$field, $directive];
          $this->relationships[] = [$directive, $field];
        }
      }
    }

    $this->replacements = [
      'class' => $name,
      'imports' => $this->getImports(),
      'relationships' => $this->getRelationships(),
    ];

    return $this->store();
  }

  public function getImports()
  {
    $seen = [];
    $relationImports = [];
    $modelImports = [];
    $stub = '';
    foreach ($this->imports as [$field, $directive]) {
      if (!($seen[$directive->name] ?? false)) {
        $className = Str::studly($directive->name);
        $relationImports[$directive->name] = "use Illuminate\Database\Eloquent\Relations\\{$className};\n";
      }
      if (!($seen[$field->type] ?? false) and $field->type != $this->name) {
        $className = Str::studly($field->type);
        $modelImports[$field->type] = "use App\Models\\{$className};\n";
      }
    }
    $imports = array_merge($relationImports, $modelImports);
    sort($imports);
    foreach ($imports as $import) {
      $stub .= $import;
    }
    return $stub;
  }

  public function getRelationships()
  {
    $stub = '';
    foreach ($this->relationships as [$directive, $field]) {
      $relationStub = $this->getStub('model_relationship');
      $singularSnakeRelation = Str::singular(Str::snake($field->name));
      $replacements = [
        'relationClass' => Str::studly($directive->name),
        'relationMethod' => $directive->name,
        'relation' => $field->name,
        'targetClass' => $this->name != Str::studly(Str::singular($field->type)) ? Str::studly(Str::singular($field->type)) : 'self',
        'foreignId' => Str::studly(Str::singular($field->name)) !== Str::studly(Str::singular($field->type)) ? ", '{$singularSnakeRelation}_id'" : '', 
      ];
      $stub .= $this->replace($relationStub, $replacements);
    }
    return trim($stub);
  }
}
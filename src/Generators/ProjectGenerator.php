<?php

namespace Bnanan\LighthouseMonkey\Generators;

use Bnanan\LighthouseMonkey\Monkey\Schema;
use GraphQL\Language\AST\DocumentNode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;

/** */
class ProjectGenerator
{
  public ?Schema $schema;
  public array $relationships = [];

  public function generate(Schema $schema)
  {
    $this->schema = $schema;

    $this->identifyRelations();

    $this->generateModels();
    $this->generateFactories();
    $this->generateSeeders();
    $this->generateMigrations();
    $this->generateQueries();
    $this->generateUnions();
    $this->generateMutations();
    $this->generateSubscriptions();
    // $this->generateDirectives();

    // $this->runMigrations();
    // $this->runSeeders();
  }

  public function identifyRelations()
  {
    foreach ($this->schema->types as $type) {
      foreach ($type->fields as $field) {
        $relationship = collect($field->directives)->whereIn('name', ['hasMany', 'belongsTo', 'belongsToMany', 'hasOne'])->first();
        if ($relationship) {
          $targetType = $this->schema->types[$field->type] ?? null;
          $this->relationships[$type->name][$field->name] = [$field, $targetType, $relationship];
        }
      }
    }
  }

  public function generateModels()
  {
    foreach ($this->schema->types as $name => $type) {
      $modelGenerator = app()->make(ModelGenerator::class);
      $modelGenerator->generate($name, $type);
      print_r("Writing the {$type->name} Model" . PHP_EOL);
    }
  }

  public function generateFactories()
  {
    foreach ($this->schema->types as $name => $type) {
      $factoryGenerator = app()->make(FactoryGenerator::class);
      $factoryGenerator->generate($name, $type);
      print_r("Writing the {$type->name} Factory" . PHP_EOL);
    }
  }

  public function generateSeeders()
  {
    foreach ($this->schema->types as $name => $type) {
      $seederGenerator = app()->make(SeederGenerator::class);
      $seederGenerator->generate($name, $type);
      print_r("Writing the {$type->name} Seeder" . PHP_EOL);
    }
  }

  public function generateMigrations()
  {
    foreach ($this->schema->types as $name => $type) {
      $migrationGenerator = app()->make(MigrationGenerator::class);
      $migrationGenerator->generate($name, $type);
      print_r("Writing the {$type->name} Migration" . PHP_EOL);
    }
  }

  public function generateQueries()
  {
    $skippables = collect(['create', 'find', 'update', 'upsert', 'delete', 'paginate']);
    foreach ($this->schema->queries as $name => $query) {
      $intersection = $skippables->intersect(collect($query->directives)->keys());
      if ($intersection->isEmpty()) {
        $queryGenerator = app()->make(QueryGenerator::class);
        $queryGenerator->generate($name, $query);
        print_r("Writing the {$query->name} GraphQL Query" . PHP_EOL);
      }
    }
  }

  public function generateUnions()
  {
    //
  }

  public function generateMutations()
  {
    $skippables = collect(['create', 'find', 'update', 'upsert', 'delete', 'paginate']);
    foreach ($this->schema->mutations as $name => $mutation) {
      $intersection = $skippables->intersect(collect($mutation->directives)->keys());
      if ($intersection->isEmpty()) {
        $mutationGenerator = app()->make(MutationGenerator::class);
        $mutationGenerator->generate($name, $mutation);
        print_r("Writing the {$mutation->name} GraphQL Mutation" . PHP_EOL);
      }
    }
  }

  public function generateSubscriptions()
  {
    //
  }

  public function generateDirectives()
  {
    $skippables = collect(['create', 'find', 'update', 'upsert', 'delete', 'paginate', 'eq', 'event']);
    
    foreach ($this->schema->directives as $name => $directive) {
      if (!$skippables->contains($directive->name)) {
        $directiveGenerator = app()->make(DirectiveGenerator::class);
        $directiveGenerator->generate($name, $directive);
        print_r("Writing the {$directive->name} GraphQL Directive" . PHP_EOL);
      }
    }
  }

  public function runMigrations()
  {
    print_r(PHP_EOL . 'Running Migrations' . PHP_EOL . PHP_EOL);
    Artisan::call('migrate:fresh');
  }

  public function runSeeders()
  {
    // Artisan::call('db:seed');
  }
}
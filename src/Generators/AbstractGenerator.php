<?php

namespace Bnanan\LighthouseMonkey\Generators;

use Bnanan\LighthouseMonkey\Types\Schema;
use Bnanan\LighthouseMonkey\Types\Type;
use GraphQL\Language\AST\DocumentNode;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;


/** */
class AbstractGenerator
{
  protected string $name = '';
  protected string $filePath = '';
  protected array $replacements = [];
  protected string $stub = '';


  public function __construct(private Filesystem $files)
  {
    // For DI purposes
  }

  public function store()
  {
    return $this->files->put(
      $this->getFilePath(), 
      $this->replace(
        $this->getStub($this->stub),
        $this->replacements));
  }

  public function getFilePath()
  {
    $folder = app_path($this->filePath);
    if (!$this->files->exists($folder)) {
      $this->files->makeDirectory($folder);
    }

    return app_path("{$this->filePath}/{$this->getFileName()}");
  }
  
  public function getFileName()
  {
    return "{$this->name}.php";
  }

  public function getStub(string $name): string
  {
    $path = __DIR__ . "/stubs/{$name}.stub";
    return $this->files->get($path);
  }

  public function replace(string $stub, array $replacements)
  {
    $stub = preg_replace_callback('/\{\{ ?(\w+) ?\}\}/', function ($matches) use ($replacements) {
      return $replacements[$matches[1]] ?? '';
    }, $stub);

    return $stub;
  }
}
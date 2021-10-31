<?php

namespace Bnanan\LighthouseMonkey\Monkey;

use GraphQL\Language\AST\DocumentNode;

class Monkey
{

  public function parse(DocumentNode $ast)
  {
    $schema = new Schema($ast);
    $schema->parse();

    return $schema;
  }
}
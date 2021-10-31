<?php

namespace Bnanan\LighthouseMonkey\Monkey;

use Bnanan\LighthouseMonkey\Types\Field;
use Bnanan\LighthouseMonkey\Types\Type;
use Bnanan\LighthouseMonkey\Types\Input;
use Bnanan\LighthouseMonkey\Types\Directive;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\NodeList;

class Schema
{
  public $queries = [];
  public $mutations = [];
  public $subscriptions = [];
  public $types = [];
  public $inputs = [];
  public $scalars = [];
  public $directives = [];
  public $enums = [];
  public $unions = [];
  public $relationships = [];

  public function __construct(private ?DocumentNode $ast)
  {
  }

  public function parse()
  {
    foreach ($this->ast->definitions as $node) {
      switch ($node->kind) {
        case 'ObjectTypeDefinition':
          $fields = [];
          foreach($node->fields as $field) {
            $directives = $this->parseDirectives($field->directives);
            $type = $field->type;
            if ($isNonNullable = $type->kind == 'NonNullType') {
              $type = $type->type;
            }
            if ($isList = $type->kind == 'ListType') {
              $type = $type->type;
              if ($type->kind == 'NonNullType') {
                $type = $type->type;
              }
            }
            $fieldType = $type->name->value;
            $fieldObj = new Field($field->name->value, $fieldType, !$isNonNullable, $isList, $directives);
            $fields[$field->name->value] = $fieldObj;
          }
          $type = new Type($node->name->value, $fields);
          $this->types[$node->name->value] = $type;
          print_r("Type: {$node->name->value}" . PHP_EOL);
          break;
        case 'ObjectTypeExtension':
          $fields = $this->types[$node->name->value]?->fields ?? [];
          foreach($node->fields as $field) {
            $directives = $this->parseDirectives($field->directives);
            $type = $field->type;
            if ($isNonNullable = $type->kind == 'NonNullType') {
              $type = $type->type;
            }
            if ($isList = $type->kind == 'ListType') {
              $type = $type->type;
              if ($type->kind == 'NonNullType') {
                $type = $type->type;
              }
            }
            $fieldType = $type->name->value;
            $fieldObj = new Field($field->name->value, $fieldType, !$isNonNullable, $isList, $directives);
            $fields[$field->name->value] = $fieldObj;
          }
          $type = new Type($node->name->value, $fields);
          $this->types[$node->name->value] = $type;
          print_r("Type Extension: {$node->name->value}" . PHP_EOL);
          break;
        case 'InputObjectTypeDefinition':
          $fields = [];
          foreach($node->fields as $field) {
            switch($field->type->kind) {
              case 'NamedType':
                $fields[$field->name->value] = $field->type->name->value;
                break;
              default:
                $fields[$field->name->value] = $field->type->type->name->value;
            }
          }
          $input = new Input($node->name->value, $fields);
          $this->inputs[$node->name->value] = $input;
          print_r("Input: {$node->name->value}" . PHP_EOL);
          break;
        default:
          print_r("Unknown ({$node->kind}): {$node->name->value}" . PHP_EOL);
      }

    }
    $this->queries = $this->types['Query']->fields ?? [];
    $this->mutations = $this->types['Mutation']->fields ?? [];
    $this->subscriptions = $this->types['Subscription'] ?? [];
    unset($this->types['Query']);
    unset($this->types['Mutation']);
    unset($this->types['Subscription']);
    $this->ast = null;

    $this->identifyRelationships();
  }

  public function parseDirectives(NodeList $ast): array {
    $directives = [];
    foreach ($ast as $node) {
      $fields = [];
      foreach ($node->arguments as $argument) {
        $fields[$argument->name->value] = $argument->value->value;
      }
      $directives[$node->name->value] = new Directive($node->name->value, 'field', $fields);
    }

    $this->directives = array_merge($this->directives, $directives);
    return $directives;
  }

  public function identifyRelationships()
  {
      
  }
}
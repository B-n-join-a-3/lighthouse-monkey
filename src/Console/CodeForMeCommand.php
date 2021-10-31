<?php

namespace Bnanan\LighthouseMonkey\Console;

use Bnanan\LighthouseMonkey\Generators\ProjectGenerator;
use Bnanan\LighthouseMonkey\Monkey\Monkey;
use GraphQL\Language\Parser;
use Illuminate\Console\Command;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;

class CodeForMeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lh-monkey:code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A command to generate lighthouse boilerplate code from a GraphQL Schema';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(protected Monkey $graphqlService, protected SchemaSourceProvider $schemaSourceProvider)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('<fg=yellow>Hello, Monkey here to assist you with the coding!</>');

        $this->line('<fg=yellow>Let me just read your schemas.</>');
        $schemaString = $this->schemaSourceProvider->getSchemaString();

        $this->line('<fg=yellow>That was some hell of a nice schema you got there! Let me just admire this for a moment before we continue.</>');
        $ast = Parser::parse($schemaString);

        $this->line('<fg=yellow>Awesome! Let me just figure out a plan for how to write some beatiful Laravel code for you.</>');
        $schema = $this->graphqlService->parse($ast);

        $this->line('<fg=yellow>Time to get coding! Hang on while I brew up some code for you.</>');
        $generator = new ProjectGenerator();
        $generator->generate($schema);
        
        $this->line('<fg=yellow>Done!</>');
        $this->line('<fg=yellow>I know it\'s not perfect, I did my best, please review my code before commiting it.</>');
        $this->line('<fg=yellow>Have a good day!</>');

        return 0;
    }
}

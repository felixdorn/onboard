<?php

namespace Felix\Onboard\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class CreateMiddlewareCommand extends GeneratorCommand
{
    protected $name = 'onboard:middleware';

    protected $description = 'Create a new Onboard middleware';

    protected $type = 'Middleware';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/middleware.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Http\Middleware';
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputOption::VALUE_REQUIRED, 'Name of the middleware that should be created', 'ResumeOnboarding'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the Middleware already exists'],
        ];
    }
}

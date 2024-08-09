<?php /** @noinspection PhpUnhandledExceptionInspection */


namespace App\Traits;

use App\Services\DockerService;
use App\Services\TerminalService;
use LaravelZero\Framework\Commands\Command;

/**
 * Trait ExecutesShellCommands
 *
 * @package App\Traits
 *
 * @mixin Command
 */
trait ExecutesShellCommands
{
    public function __construct()
    {
        parent::__construct();
        $this->ignoreValidationErrors();
    }

    public function handle(DockerService $docker_service, TerminalService $terminal): int
    {
        $target_service = empty($this->target_service)?$this->signature:$this->target_service;
        $target_command = empty($this->target_command)?'':$this->target_command;

        $terminal->init($this->output);

        $arguments = $this->input->__toString();


        if ($arguments==$target_command) {
            $this->info('Log into Shell');

            return $terminal->execute([
                env('DOCKER_COMPOSE_COMMAND', 'docker compose'),
                'run',
                '--rm',
                $target_service,
                'bash',
            ]);
        } else {
            $commands = collect (explode(' ', $arguments))->map(fn($command) => trim($command, "'"))->toArray();
            if(!empty($target_command)){
                $commands = array_merge([$target_command], $commands);
            }

            return $docker_service->service($target_service)->run($terminal, $commands);
        }
    }
}

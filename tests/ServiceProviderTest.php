<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Testing\PendingCommand;

it('publishes the config file under the correct tag', function () {
    $command = $this->artisan('vendor:publish', [
        '--tag' => 'healthcheck-mercure-config',
        '--force' => true,
    ]);

    if ($command instanceof PendingCommand) {
        $command->assertExitCode(0);
    } else {
        expect($command)->toBe(0);
    }

    expect(config_path('healthcheck-mercure.php'))->toBeFile();

    File::delete(config_path('healthcheck-mercure.php'));
});

it('loads config with the correct default', function () {
    expect(config('healthcheck-mercure.required'))->toBeFalse();
});

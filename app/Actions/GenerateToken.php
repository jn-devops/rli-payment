<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Jetstream\Jetstream;
use Illuminate\Console\Command;
use App\Models\User;

class GenerateToken
{
    use AsAction;

    public string $commandSignature = 'generate:api-token {device : name of the device} {abilities : permissions allowed for the device}';
    public string $commandDescription = 'Generate Token for API Access';

    /**
     * TODO: test this generate token action
     *
     * @param string $device
     * @param string $abilities
     * @return NewAccessToken
     */
    public function handle(string $device, string $abilities = 'collect:wallet'): NewAccessToken
    {
        return optional(User::first(), function ($user) use ($device, $abilities) {
            return $user->createToken($device, $this->sanitizePermissions($abilities));
        });
    }

    /**
     * @param Command $command
     * @return void
     */
    public function asCommand(Command $command): void
    {
        $device = $command->argument('device');
        $abilities = $command->argument('abilities');
        $token = $this->handle($device, $abilities);
        $command->info(__('New token for device :name with [:abilities] abilities:', [
            'name' => $token->accessToken->getAttribute('name'),
            'abilities' => implode(',', $token->accessToken->getAttribute('abilities'))
        ]));
        $command->info($token->plainTextToken);
    }

    /**
     * @return string
     */
    public function getCommandHelp(): string
    {
        $permissions = '[ ' . implode(', ', Jetstream::$permissions) . ' ]';

        return "This command will generate a new token, invalidating the old ones. \nAbilities should be quoted and comma-delimited i.e, 'ability 1, ability 2'. \nChoose abilities from the ff: $permissions.";
    }

    /**
     * @param string $abilities
     * @return array
     */
    protected function sanitizePermissions(string $abilities): array
    {
        $abilities = preg_replace('/\s+/', '', $abilities);
        $permissions = explode(',', $abilities);

        return Jetstream::validPermissions($permissions) ?: Jetstream::$defaultPermissions;
    }
}

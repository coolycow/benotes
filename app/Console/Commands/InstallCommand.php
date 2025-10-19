<?php

namespace App\Console\Commands;

use App\Enums\UserPermissionEnum;
use App\Services\PasswordService;
use App\Services\PostSeedService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install {--only-user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the application';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $createOnlyUser = $this->option('only-user');

        $this->line('Initiate installation...');
        $this->line(PHP_EOL);

        if (!$createOnlyUser) {
            $bar = $this->output->createProgressBar(4);
            $bar->start();
            $this->line(PHP_EOL);

            // APP_KEY
            $this->info('Generate APP_KEY:');
            $this->call('key:generate');
            $this->line(PHP_EOL);
            $bar->advance();
            $this->line(PHP_EOL);

            // jwt secret
            $this->info('Generate APP_SECRET:');
            $this->call('jwt:secret');
            $this->line(PHP_EOL);
            $bar->advance();
            $this->line(PHP_EOL);

            // database fresh and migration
            $this->info('Migrate database:');
            $this->call('migrate:fresh');
            $this->line(PHP_EOL);
            $bar->advance();
            $this->line(PHP_EOL);

            // create storage link
            $this->info('Create storage link:');
            $this->call('storage:link');
            $this->line(PHP_EOL);
            $bar->advance();
            $this->line(PHP_EOL);
        }

        // Create admin
        $this->info('Create your Admin account:');
        $email = $this->ask('Email');

        $validator = Validator::make([
            'email' => $email
        ], [
            'email' => 'email',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return;
        }

        $user = User::query()->create([
            'email' => $email,
            'name' => strstr($email, '@', true),
            'password' => app(PasswordService::class)->generateHashedPassword(),
            'permission' => UserPermissionEnum::Admin
        ]);

        app(PostSeedService::class)->seedIntroData($user->getKey());

        // Create user
        $this->info('Create your user account:');
        $email = $this->ask('Email');

        $validator = Validator::make([
            'email' => $email
        ], [
            'email' => 'email',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return;
        }

        $user = User::query()->create([
            'email' => $email,
            'name' => strstr($email, '@', true),
            'password' => app(PasswordService::class)->generateHashedPassword(),
            'permission' => UserPermissionEnum::Api
        ]);

        app(PostSeedService::class)->seedIntroData($user->getKey());

        if (!$createOnlyUser) {
            $bar->finish();
        }

        $this->line(PHP_EOL);
        $this->info('Installation complete.');
    }
}

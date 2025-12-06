<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateEmailStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:update status {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if($this->argument('type') == 'trial_expires') {
            $users = User::where('created_at', '<', Carbon::now()->subDays(7))
                ->where('is_subscribed', 0)
                ->get();

            $count = 0;

            foreach ($users as $user) {
                $customFields = [
                    'subscription_status' => 'Expired'
                ];
                $this->userService->updateSubscriberFields($user->email, $customFields);

                $count++;
            }

            $this->info("Ažuriran status za {$count} korisnika!");

        }
    }
}

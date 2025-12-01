<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Console\Command;

class SendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:send
                            {title=Obaveštenje}
                            {body=Imate novu poruku}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending push notifications';

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        parent::__construct();
        $this->authService = $authService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::find(576);

        $result = $this->authService->sendNotification(
            $user->notification_token,
            'Obaveštenje',
            'Neophodno je da štikliraš sve obroke za najbolje praćenje rezultata.'
        );

        if ($result) {
            $this->info('Notifikacija poslata!');
            return 0;
        } else {
            $this->error('Greška prilikom slanja notifikacije.');
            return 1;
        }
    }
}

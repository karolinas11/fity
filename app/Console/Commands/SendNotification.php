<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AuthService;
use Carbon\Carbon;
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
                            {type=test}';

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
        if($this->argument('type') == 'trial_expires') {
            $targetDate = Carbon::now()->subDays(7)->toDateString();

            $users = User::whereDate('created_at', $targetDate)
                ->where('is_subscribed', 0)
                ->whereNotNull('notification_token')
                ->get();

            $count = 0;

            foreach ($users as $user) {
                $result = $this->authService->sendNotification(
                    $user->notification_token,
                    'Hej, danas ti ističe probni period korišćenja Fity aplikacije.',
                    'Da bi nastavio/la da budeš korisnik, potrebno je da odabereš tip pretplate.'
                );

                if ($result) {
                    $count++;
                }
            }

            $this->info("Notifikacija poslata {$count} korisnika!");
        } else if($this->argument('type') == 'water') {
            $users = User::whereNotNull('notification_token')->get();
            $count = 0;

            foreach ($users as $user) {
                $result = $this->authService->sendNotification(
                    $user->notification_token,
                    $this->argument('title'),
                    'Bitno je da unosiš preporučene količine vode na dnevnom nivou za zdravlje.'
                );

                if ($result) {
                    $count++;
                }
            }

            $this->info("Notifikacija poslata {$count} korisnika!");
        } else if($this->argument('type') == 'improvement') {
            $users = User::whereNotNull('notification_token')->get();
            $count = 0;

            foreach ($users as $user) {
                $result = $this->authService->sendNotification(
                    $user->notification_token,
                    $this->argument('title'),
                    'Redovno beleženje napretka je najbolja motivacija i pokazatelj rezultata. Unesi svoje rezultate.'
                );

                if ($result) {
                    $count++;
                }
            }
        } else if($this->argument('type') == 'shopping') {
            $users = User::whereNotNull('notification_token')->get();
            $count = 0;

            foreach ($users as $user) {
                $result = $this->authService->sendNotification(
                    $user->notification_token,
                    $this->argument('title'),
                    'Kupovina namirnica unapred je ključna za doslednost ishrani. Spremi se za šoping.'
                );

                if ($result) {
                    $count++;
                }
            }

            $this->info("Notifikacija poslata {$count} korisnika!");
        } else if($this->argument('type') == 'new_recipe') {
            $users = User::whereNotNull('notification_token')->get();
            $count = 0;

            foreach ($users as $user) {
                $result = $this->authService->sendNotification(
                    $user->notification_token,
                    $this->argument('title'),
                    'Baci pogled na novi recept koji smo smislili za tebe.'
                );

                if ($result) {
                    $count++;
                }
            }

            $this->info("Notifikacija poslata {$count} korisnika!");
        } else if($this->argument('type') == 'checking') {
            $users = User::whereNotNull('notification_token')->get();
            $count = 0;

            foreach ($users as $user) {
                $result = $this->authService->sendNotification(
                    $user->notification_token,
                    $this->argument('title'),
                    'Neophodno je da štikliraš sve obroke za najbolje praćenje rezultata.'
                );

                if ($result) {
                    $count++;
                }
            }

            $this->info("Notifikacija poslata {$count} korisnika!");
        } else {
            $user = User::find(559);

            $result = $this->authService->sendNotification(
                $user->notification_token,
                'Obaveštenje',
                'Neophodno je da štikliraš sve obroke za najbolje praćenje rezultata..'
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
}

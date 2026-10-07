<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\CourierControllor;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImportCmsCouriersCustomers extends Command
{
    protected $signature = 'cms:import-couriers-customers';

    protected $description = 'Import missing CMS couriers and their customers into WMS.';

    public function handle()
    {
        $this->info('Starting CMS courier/customer import...');

        $result = app(CourierControllor::class)->importAllCmsUsers(new Request());

        $status = (bool) ($result['status'] ?? false);
        $message = (string) ($result['message'] ?? 'CMS import completed without a message.');

        Log::info('Scheduled CMS courier/customer import finished', [
            'status' => $status,
            'message' => $message,
            'result' => $result,
        ]);

        $status ? $this->info($message) : $this->error($message);

        foreach (($result['errors'] ?? []) as $error) {
            $this->warn($error);
        }

        return $status ? self::SUCCESS : self::FAILURE;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RichMenuGroup;
use App\Services\LineService;

class SyncToLine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocard:sync-to-line';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle(LineService $lineService)
    {
        $scheduledMenus = RichMenuGroup::getScheduledMenusWithGroupInfo();

        /**
         * 先下架, 再上架, 再判斷是否預設群組需要上架
         */

        $lineService->processDown($scheduledMenus);
        $lineService->processUp($scheduledMenus);
        $lineService->processDefaultGroupUp();
    }
}

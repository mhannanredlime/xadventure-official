<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PopulateOriginalAmounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prices:populate-original-amounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate original_amount field for existing variant prices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Populating original_amount for existing variant prices...');
        
        $prices = \App\Models\VariantPrice::whereNull('original_amount')->get();
        $count = 0;
        
        foreach ($prices as $price) {
            $price->original_amount = $price->amount;
            $price->save();
            $count++;
        }
        
        $this->info("Updated {$count} prices with original_amount.");
    }
}

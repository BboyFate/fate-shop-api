<?php

namespace App\Jobs;

use App\Models\Products\Product;

class SyncOneProductToEs extends Job
{
    protected $product;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->product->toESArray();
        app('es')->index([
            'index' => 'products',
            'type'  => '_doc',
            'id'    => $data['id'],
            'body'  => $data,
        ]);
    }
}

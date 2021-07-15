<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Elasticsearch\ClientBuilder as ESClientBuilder;
use Monolog\Logger;
use Yansongda\Pay\Pay;
use App\Models\Users\UserImage;

class AppServiceProvider extends ServiceProvider
{
    protected $validators = [
        'poly_exists' => \App\Validators\PolyExistsValidator::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 注册一个名为 es 的单例
        $this->app->singleton('es', function () {
            // 从配置文件读取 Elasticsearch 服务器列表
            $builder = ESClientBuilder::create()->setHosts(config('database.elasticsearch.hosts'));
            // 如果是开发环境
            if (app()->environment() === 'local') {
                // 配置日志，Elasticsearch 的请求和返回数据将打印到日志文件中，方便我们调试
                $builder->setLogger(app('log')->driver());
            }

            return $builder->build();
        });
    }

    public function boot()
    {
        // API 资源禁用顶层资源的包裹
        Resource::withoutWrapping();

        $this->bootEloquentMorphs();

        // 注册验证器
        // $this->registerValidators();

        // 商品观察者
        \App\Models\Products\Product::observe(\App\Observers\ProductObserver::class);

        // 微信支付
        $this->app->singleton('wechat_pay', function () {
            $config = config('pay.wechat');
            $config['notify_url'] = route('api.v1.payment.wechat.notify');

            if (app()->environment() !== 'production') {
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }

            return Pay::wechat($config);
        });

        // 只在本地开发环境启用 SQL 日志
        if (app()->environment('local')) {
            \DB::listen(function ($query) {
                \Log::info(Str::replaceArray('?', $query->bindings, $query->sql));
            });
        }
    }

    /**
     * Register validators.
     */
//    protected function registerValidators()
//    {
//        foreach ($this->validators as $rule => $validator) {
//            Validator::extend($rule, "{$validator}@validate");
//        }
//    }

    /**
    * 自定义多态关联的类型字段
    */
    private function bootEloquentMorphs()
    {
        Relation::morphMap([
            UserImage::MORPH_ORDER_REFUND => \App\Models\Orders\OrderItemRefund::class,
            UserImage::MORPH_ORDER_REVIEW => \App\Models\Orders\OrderItemReview::class,
        ]);
    }
}

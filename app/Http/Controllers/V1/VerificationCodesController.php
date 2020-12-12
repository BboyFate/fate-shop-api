<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Overtrue\EasySms\EasySms;

class VerificationCodesController extends Controller
{
    /**
     * 发送手机验证码
     *
     * @param Request $request
     * @param EasySms $easySms
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
     */
    public function smsStore(Request $request, EasySms $easySms)
    {
        $this->validateRequest($request);

        $phone = $request->phone;

        if (app()->environment('local')) {
            $code = '1234';
        } else {
            // 生成 4 位随机数，左侧补 0
            $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);

            try {
                $result = $easySms->send($phone, [
                    'template' => config('easysms.gateways.aliyun.templates.register'),
                    'data' => [
                        'code' => $code
                    ],
                ]);
            } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
                return $this->response->errorInternal($exception->getException('aliyun')->getMessage() ?: '短信发送异常');
            }
        }

        $key = 'verificationCode_' . Str::random(15);
        $expiredAt = Carbon::now()->addMinutes(5);
        // 缓存验证码 5 分钟过期。
        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expiredAt);

        return $this->response->created([
            'key'        => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
        ]);
    }
}

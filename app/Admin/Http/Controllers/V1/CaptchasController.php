<?php

namespace App\Admin\Http\Controllers\V1;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Gregwar\Captcha\CaptchaBuilder;

class CaptchasController extends Controller
{
    public function store(Request $request, CaptchaBuilder $captchaBuilder)
    {
        $key = 'captcha-'.Str::random(15);
        $phone = $request->phone;

        $captcha = $captchaBuilder->build();
        $expiredAt = now()->addMinutes(2);
        \Cache::put($key, ['phone' => $phone, 'code' => $captcha->getPhrase()], $expiredAt);

        $result = [
            'captcha_key'           => $key,
            'expired_at'            => $expiredAt->toDateTimeString(),
            'captcha_image_content' => $captcha->inline()
        ];

        return $this->response->created($result);
    }
}

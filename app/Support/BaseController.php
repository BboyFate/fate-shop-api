<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as LumenBaseController;
use App\Support\Traits\ResponseTrait;

class BaseController extends LumenBaseController
{
    use ResponseTrait;

    public function validateRequest(Request $request, $name = null)
    {
        if (! $validator = $this->getValidator($request, $name)) {
            return;
        }

        $rules = Arr::get($validator, 'rules', []);
        $messages = Arr::get($validator, 'messages', []);

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $errorMessages = $validator->errors()->messages();

            foreach ($errorMessages as $key => $value) {
                $errorMessages[$key] = $value[0];
                return $this->response->errorUnprocessableEntity($value[0]);
            }

            return $errorMessages;
        }

        return true;
    }

    protected function getValidator(Request $request, $name = null)
    {
        list($controller, $method) = explode('@', $request->route()[1]['uses']);

        $method = $name ?: $method;
        $class = str_replace('Controller', 'Validation', $controller);

        if (! class_exists($class) || ! method_exists($class, $method)) {
            return $this->response->errorInternal('验证异常');
        }

        return call_user_func([new $class, $method]);
    }

    /**
     * 从缓存验证手机验证码是否正确
     *
     * @param $verificationKey
     * @param $verificationCode
     */
    protected function validateSmsCode($verificationKey, $verificationCode)
    {
        $verifyData = \Cache::get($verificationKey);
        if (! $verifyData) {
            return $this->response->errorForbidden('验证码已失效');
        }

        if (! hash_equals($verifyData['code'], $verificationCode)) {
            return $this->response->errorUnauthorized('验证码错误');
        }

        return $verifyData;
    }

    /**
     * 清除验证码缓存
     *
     * @param $verificationKey
     * @return mixed
     */
    protected function forgetSmsCode($verificationKey)
    {
        return \Cache::forget($verificationKey);
    }
}

<?php

/*
 * This file is part of the Jiannei/lumen-api-starter.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Support\Contracts;

use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Lang;

class ResponseConstant
{
    // 定制/覆盖 HTTP 协议状态码
    const CUSTOM_HTTP_OK = HttpResponse::HTTP_OK;


    /**
     * 业务操作正确码：1xx、2xx、3xx 开头，后拼接 3 位
     * 200 + 001 => 200001，也就是有 001 ~ 999 个编号可以用来表示业务成功的情况，当然你可以根据实际需求继续增加位数，但必须要求是 200 开头
     * 例如： 001 ~ 099 表示系统状态；100 ~ 199 表示授权业务；200 ~ 299 表示用户业务...
     */
    const SERVICE_REGISTER_SUCCESS = 200101;
    const SERVICE_LOGIN_SUCCESS = 200102;

    /**
     * 客户端错误码：400 ~ 499 开头，后拼接 3 位
     */
    const CLIENT_SOCIAL_BIND_ERROR = 400001;
    // 快递 匹配不到快递公司
    const CLIENT_NO_EXPRESS_COMPANY_ERROR = 404201;
    // 快递 匹配不到快递公司运费模板
    const CLIENT_NO_EXPRESS_FEE_ERROR = 404202;
    // 快递 匹配不到快递公司运费模板的具体区域计费
    const CLIENT_NO_EXPRESS_FEE_ITEM_ERROR = 404203;

    /**
     * 服务端操作错误码：500 ~ 599 开头，后拼接 3 位
     */
    const SYSTEM_ERROR = 500001;
    const SYSTEM_UNAVAILABLE = 500002;

    /**
     * 业务操作错误码（外部服务或内部服务调用...）
     */
    const SERVICE_REGISTER_ERROR = 500101;
    const SERVICE_LOGIN_ERROR = 500102;

    public static function statusTexts($code = null)
    {
        $statusTexts = Lang::has('response') ? (__('response') + HttpResponse::$statusTexts) : HttpResponse::$statusTexts;

        if ($code) {
            return isset($statusTexts[$code]) ? $statusTexts[$code] : '';
        }

        return $statusTexts;
    }
}

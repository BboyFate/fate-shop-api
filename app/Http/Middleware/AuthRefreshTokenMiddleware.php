<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthRefreshTokenMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param $request
     * @param Closure $next
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|mixed
     *
     * @throws JWTException
     */
    public function handle($request, Closure $next)
    {
        // 检查此次请求中是否带有 token，如果没有则抛出异常
        $this->checkForToken($request);

        /**
         * 验证是否是专属于这个的 token
         */
        // 获取当前守护的名称
        $presentGuard = Auth::getDefaultDriver();
        // 获取当前 token
        $token = $this->auth->getToken();
        // 即使过期了，也能获取到 token 里的 载荷 信息。
        $payload = $this->auth->manager()->getJWTProvider()->decode($token->get());

        // 如果不包含 guard 字段或者 guard 所对应的值与当前的 guard 守护值不相同
        // 证明是不属于当前 guard 守护的 token
        if (empty($payload['guard']) || $payload['guard'] != $presentGuard) {
            throw new TokenInvalidException();
        }

        // 捕捉 token 过期所抛出的 TokenExpiredException  异常
        try {
            // 检测用户的登录状态，如果正常则通过
            if ($this->auth->authenticate()) {
                return $next($request);
            }

            throw new UnauthorizedHttpException('jwt-auth', '未登录');
        } catch (TokenExpiredException $e) {
            // 此处捕获到了 token 过期所抛出的 TokenExpiredException 异常
            // 在这里需要做的是刷新该用户的 token 并将它添加到响应头中
            try {
                $token = $this->auth->manager()->refresh($token);
                // 使用一次性登录以保证此次请求的成功
                Auth::onceUsingId($this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray()['sub']);
            } catch (JWTException $e) {
                // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
                throw new UnauthorizedHttpException('jwt-auth', $e->getMessage());
            }
        }

        // 在响应头中返回新的 token
        return $this->setAuthenticationHeader($next($request), $token);
    }
}

<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use App\Support\Traits\ResponseTrait;

class ExceptionReport
{
    use ResponseTrait;

    /**
     * @var Exception
     */
    public $exception;

    /**
     * @var Request
     */
    public $request;

    /**
     * @var
     */
    protected $report;

    public $doReport = [
        \Illuminate\Database\Eloquent\ModelNotFoundException::class => ['Not Found', 404],
        \Spatie\Permission\Exceptions\PermissionDoesNotExist::class => ['没有该权限名称', 404],
        \Spatie\Permission\Exceptions\RoleDoesNotExist::class => ['没有该角色名称', 404],
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class => ['Not Found', 404],
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class => ['访问方式不正确', 405],
    ];

    public function __construct(Request $request, Exception $exception)
    {
        $this->request = $request;
        $this->exception = $exception;
    }

    public function shouldReturn()
    {
        foreach (array_keys($this->doReport) as $report){
            if ($this->exception instanceof $report){
                $this->report = $report;
                return true;
            }
        }

        return false;
    }

    public static function make(Request $request, Exception $e)
    {
        return new static($request, $e);
    }

    /**
     * 检测异常，处理对应的自定义异常
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function report()
    {
        if ($this->exception instanceof ValidationException){
            $error = Arr::first($this->exception->errors());

            return $this->response->fail(Arr::first($error))->setHttpCode($this->exception->status);
        }

        $message = $this->doReport[$this->report];

        return $this->response->setHttpCode($message[1])->fail($message[0]);
    }

    /**
     * 线上的异常处理
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function prodReport()
    {
        return $this->response->errorInternal();
    }
}

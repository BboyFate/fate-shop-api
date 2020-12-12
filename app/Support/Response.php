<?php

namespace App\Support;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response as HttpResponse;
use App\Support\Contracts\ResponseConstant;

class Response
{
    /**
     * HTTP 响应状态码默认 200
     *
     * @var int
     */
    protected $httpCode = 200;

    /**
     * 自定义错误码
     *
     * @var null | int
     */
    protected $statusCode = null;

    /**
     * 获取 HTTP 状态码
     *
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * HTTP 状态码赋值
     *
     * @param $httpCode
     *
     * @return $this
     */
    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;

        return $this;
    }

    /**
     * 获取自定义错误码
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * 自定义错误码赋值
     *
     * @param $statusCode
     *
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->httpCode = $statusCode;

        return $this;
    }

    public function respond(string $message = 'Bad Request')
    {
        $this->fail($message);
    }

    public function errorUnprocessableEntity(string $message = 'Unprocessable Entity')
    {
        $this->setHttpCode(HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        $this->fail($message);
    }

    public function errorNotFound(string $message = 'Not Found')
    {
        $this->setHttpCode(HttpResponse::HTTP_NOT_FOUND);
        $this->fail($message);
    }

    public function errorBadRequest(string $message = 'Bad Request')
    {
        $this->setHttpCode(HttpResponse::HTTP_BAD_REQUEST);
        $this->fail($message);
    }

    public function errorForbidden(string $message = 'Forbidden')
    {
        $this->setHttpCode(HttpResponse::HTTP_FORBIDDEN);
        $this->fail($message);
    }

    public function errorInternal(string $message = 'Internal Error')
    {
        $this->setHttpCode(HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        $this->fail($message);
    }

    public function errorUnauthorized(string $message = 'Unauthorized')
    {
        $this->setHttpCode(HttpResponse::HTTP_UNAUTHORIZED);
        $this->fail($message);
    }

    public function errorMethodNotAllowed(string $message = 'Method Not Allowed')
    {
        $this->setHttpCode(HttpResponse::HTTP_METHOD_NOT_ALLOWED);
        $this->fail($message);
    }

    public function errorInvalidField($data, string $message = 'Invalid fields')
    {
        $this->setHttpCode(HttpResponse::HTTP_METHOD_NOT_ALLOWED);
        $this->fail($message);
    }

    public function noContent(string $message = 'No content')
    {
        $this->setHttpCode(HttpResponse::HTTP_NO_CONTENT);
        return $this->success(null, $message);
    }

    public function accepted(string $message = 'Accepted')
    {
        $this->setHttpCode(HttpResponse::HTTP_ACCEPTED);
        return $this->success(null, $message);
    }

    /**
     * @param JsonResource|array|null $data
     * @param string $message
     * @param string $location
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function created($data = null, $message = 'Created', string $location = '')
    {
        $this->setHttpCode(HttpResponse::HTTP_CREATED);
        $response = $this->success($data, $message);
        if ($location) {
            $response->header('Location', $location);
        }

        return $response;
    }

    /**
     * @param string $message
     * @param null $data
     * @param array $header
     * @param int $options
     */
    public function fail(string $message = '', $data = null, array $header = [], int $options = 0)
    {
        response()->json(
            $this->formatData($data, $message),
            $this->httpCode,
            $header,
            $options
        )->throwResponse();
    }

    /**
     * @param null $data
     * @param string $message
     * @param array $headers
     * @param int $options
     * @return \Illuminate\Http\JsonResponse|JsonResource
     */
    public function success($data = null, string $message = '', array $headers = [], $options = 0)
    {
        if ($data instanceof JsonResource) {
            $additionalData = [
                'status' => 'success',
                'message' => $this->formatMessage($message),
            ];
            return $data->additional($additionalData);
        }

        return response()->json(
            $this->formatData($data, $message),
            $this->httpCode,
            $headers,
            $options
        );
        //return response()->json(array_merge($additionalData, ['data' => $data ?: (object) $data]), $this->httpCode, $headers, $option);
    }

    protected function formatMessage($message)
    {
        if (! $message) {
            if ($this->statusCode) {
                $message = ResponseConstant::statusTexts($this->statusCode) ?: 'OK';
            } else {
                $message = HttpResponse::$statusTexts[$this->httpCode] ?: 'OK';
            }
        }

        return $message;
    }

    protected function formatData($data, $message)
    {
        if ($this->httpCode >= 400 && $this->httpCode <= 499) {
            // 客户端出错
            $status = 'error';
        } elseif ($this->httpCode >= 500 && $this->httpCode <= 599) {
            // 服务器出错
            $status = 'fail';
        } else {
            $status = 'success';
        }

        $result = [
            'status'  => $status,
            'message' => $this->formatMessage($message),
            'data'    => $data ?: (object) $data,
        ];

        if (! empty($this->statusCode)) {
            $result['code'] = $this->statusCode;
        }

        return $result;
    }
}

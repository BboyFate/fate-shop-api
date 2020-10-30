<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Exception;

class InvalidRequestException extends Exception
{
    public function __construct($message = "", $code = 0)
    {
        parent::__construct($message, $code);
    }

    /**
     * 异常出发时系统会调用这个方法
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->message], $this->code);
        }

        return view('pages.error', ['message' => $this->message]);
    }
}

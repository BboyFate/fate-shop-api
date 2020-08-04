<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Traits\ResponseTrait;

class Controller extends BaseController
{
    use ResponseTrait;

    /**
     * Validate HTTP request against the rules
     *
     * @param Request $request
     * @param array $rules
     * @return bool|array
     */
    protected function validateRequest(Request $request, array $rules)
    {
        $validator = \Validator::make($request->all(), $rules);
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
}

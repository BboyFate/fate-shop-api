<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\UserAddress;
use App\Http\Resources\UserAddressResource;

class UserAddressesController extends Controller
{
    /**
     * 用户收货地址列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->paginate();

        return UserAddressResource::collection($addresses);
    }

    /**
     * 用户新增地址
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validateRequest($request, $this->storeAndUpdateRequestValidationRules());

        $request->user()->addresses()->create($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        ]));

        return $this->response->created();
    }

    /**
     * 用户更新地址
     *
     * @param Request $request
     * @param $id
     *
     * @return UserAddressResource
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, $id)
    {
        $this->validateRequest($request, $this->storeAndUpdateRequestValidationRules());
        $userAddress = UserAddress::query()->findOrFail($id);

        $this->authorize('own', $userAddress);

        $userAddress->update($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        ]));

        return new UserAddressResource($userAddress);
    }

    /**
     * 用户删除地址
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($id)
    {
        $userAddress = UserAddress::query()->findOrFail($id);

        $this->authorize('own', $userAddress);

        $userAddress->delete();

        return $this->response->noContent();
    }

    public function storeAndUpdateRequestValidationRules()
    {
        return [
            'province'      => 'required',
            'city'          => 'required',
            'district'      => 'required',
            'address'       => 'required',
            'zip'           => 'required',
            'contact_name'  => 'required',
            'contact_phone' => 'required',
        ];
    }
}

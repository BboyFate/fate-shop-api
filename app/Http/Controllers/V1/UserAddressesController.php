<?php

namespace App\Http\Controllers\V1;

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
     * 用户默认地址
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function defaultAddress(Request $request)
    {
        $address = $request->user()->addresses()->default()->first();

        return $this->response->success(new UserAddressResource($address));
    }

    /**
     * 用户新增地址
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validateRequest($request, 'storeOrUpdate');

        if ($request->input('is_default') == true) {
            $request->user()->addresses()->update(['is_default', false]);
        }
        $request->user()->addresses()->create($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
            'is_default',
        ]));

        return $this->response->created();
    }

    /**
     * 用户更新地址
     *
     * @param Request $request
     * @param $addressId
     *
     * @return UserAddressResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, $addressId)
    {
        $this->validateRequest($request, 'storeOrUpdate');

        $userAddress = UserAddress::query()->findOrFail($addressId);

        $this->authorize('own', $userAddress);

        if ($request->input('is_default') == true) {
            $request->user()->addresses()->update(['is_default' => false]);
        }
        $userAddress->update($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
            'is_default',
        ]));

        return new UserAddressResource($userAddress);
    }

    /**
     * 用户删除地址
     *
     * @param $addressId
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($addressId)
    {
        $userAddress = UserAddress::query()->findOrFail($addressId);

        $this->authorize('own', $userAddress);

        $userAddress->delete();

        return $this->response->noContent();
    }
}

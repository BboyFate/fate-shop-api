<?php

namespace App\Services;

use App\Models\Expresses\ExpressCompany;
use App\Models\Expresses\ExpressFeeItem;

class ExpressService extends  BaseService
{
    /**
     * 根据物流公司获取运费模板
     *
     * @param $expressCompanyId 物流公司ID
     * @param $province
     */
    public function getFeeItemByCompany($expressCompanyId, $province)
    {
        $expressCompanyBuilder = ExpressCompany::query();

        if ($expressCompanyId) {
            // 指定物流公司
            $expressCompany = $expressCompanyBuilder->find($expressCompanyId);
        } else {
            // 默认物流公司
            $expressCompany = $expressCompanyBuilder->default()->first();
        }

        if (! $expressCompany) {
            return $this->response->errorNotFound('匹配不到物流公司');
        }

        // 查询物流公司的运费模板
        $expressFee = $expressCompany->fees()->default()->first();
        if (! $expressFee) {
            return $this->response->errorNotFound('匹配不到物流公司运费');
        }

        // 查询指定省份的运费
        $expressFeeItem = $expressFee->items()->whereJsonContains('provinces', $province)->first();
        if (! $expressFeeItem) {
            return $this->response->errorNotFound('该地区不支持派送，详情请咨询客服');
        }

        return $expressFeeItem;
    }

    /**
     * 计算运费
     *
     * $expressFeeItem['fees'] {"volume": {"first": 20, "first_fee": 20, "renew_fee": 0.8}, "weight": {"first": 20, "first_fee": 20, "renew_fee": 0.8}}
     *
     * @param ExpressFeeItem $expressFeeItem
     * @param array $calcValues
     *
     * @return float|int|void
     */
    public function calcFee(ExpressFeeItem $expressFeeItem, Array $calcValues)
    {
        $expressFeeItem->loadMissing('expressFee:id,fee_type');

        // 获取运费类型的 运费模板
        $fees = $expressFeeItem['fees'][$expressFeeItem->expressFee->fee_type];
        // 获取运费类型的 单位值，并向上取整
        $calcValue = ceil($calcValues[$expressFeeItem->expressFee->fee_type]);
        if (! $calcValue) {
            return $this->response->errorNotFound('匹配不到运费单位');
        }

        // 商品的计算单位 <= 首计算单位。则按首计算单位费
        if ($calcValue <= $fees['first']) {
            $expressFeeTotal = $fees['first_fee'];
        } else {
            // 超过首计算单位
            $exceedWeight = $calcValue - $fees['first'];

            $expressFeeTotal = $fees['first_fee'] + ($exceedWeight * $fees['renew_fee']);
        }

        return $expressFeeTotal;
    }
}

<?php

namespace App\SearchBuilders;

use App\Models\ProductCategory;

class ProductSearchBuilder
{
    protected $params = [
        'index' => 'products',
        'type'  => '_doc',
        'body'  => [
            'query' => [
                'bool' => [
                    'filter' => [],
                    'must'   => [],
                ]
            ]
        ]
    ];

    /**
     * 分页查询
     *
     * @param int $size 每页几条数据
     * @param int $page 当前第几页
     * @return $this
     */
    public function paginate(int $size, int $page)
    {
        $this->params['body']['from'] = ($page - 1) * $size;
        $this->params['body']['size'] = $size;

        return $this;
    }

    /**
     * 筛选上架的商品
     */
    public function onSale($onSale = true)
    {
        $this->params['body']['query']['bool']['filter'][] = ['term' => ['on_sale' => $onSale]];

        return $this;
    }

    /**
     * 筛选商品类型
     *
     * @param $type
     *
     * @return $this
     */
    public function productType($type)
    {
        $this->params['body']['query']['bool']['filter'][] = ['term' => ['type' => $type]];

        return $this;
    }

    /**
     * 按类目筛选商品
     *
     * @param ProductCategory $category
     * @return $this
     */
    public function category(ProductCategory $category)
    {
        if ($category->is_directory) {
            // 如果是一个父类目，则使用 category_path 来筛选
            $params['body']['query']['bool']['filter'][] = [
                'prefix' => ['category_path' => $category->path . $category->id . '-']
            ];
        } else {
            // 否则直接通过 category_id 筛选
            $params['body']['query']['bool']['filter'][] = ['term' => ['category_id' => $category->id]];
        }

        return $this;
    }

    /**
     * 添加搜索词
     *
     * @param $keywords
     * @return $this
     */
    public function keywords($keywords)
    {
        // 如果参数不是数组则转为数组
        $keywords = is_array($keywords) ? $keywords : [$keywords];
        foreach ($keywords as $keyword) {
            $this->params['body']['query']['bool']['must'][] = [
                'multi_match' => [
                    'query'  => $keyword,
                    'fields' => [
                        'title^3',
                        'long_title^2',
                        'category^2',
                        'description',
                        'skus_name',
                        'properties_value',
                    ],
                ],
            ];
        }

        return $this;
    }

    /**
     * 分面搜索的聚合
     *
     * @return $this
     */
    public function aggregateProperties()
    {
        $this->params['body']['aggs'] = [
            // 这个聚合操作的命名，和商品结构的 properties 没有必然联系
            'properties' => [
                // 要聚合的属性是在 nested 类型字段下的属性，需要在外面套一层 nested 聚合查询
                'nested' => [
                    // 要查询的 nested 字段名为 properties
                    'path' => 'properties'
                ],
                // 在 nested 聚合下嵌套聚合
                'aggs' => [
                    // 聚合的名称
                    'properties' => [
                        // terms 聚合，用于聚合相同的值
                        'terms' => [
                            // 要聚合的字段名
                            'field' => 'properties.name',
                        ],
                        // 第三层聚合
                        'aggs' => [
                            // 聚合的名称
                            'value' => [
                                'terms' => [
                                    'field' => 'properties.value'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $this;
    }

    /**
     * 添加一个按商品属性筛选的条件
     * 例如：内存容量:32GB。属性名为 内存容量，属性值为 32GB
     *
     * @param $name 属性名
     * @param $value 属性值
     * @return $this
     */
    public function propertyFilter($name, $value, $type = 'filter')
    {
        $this->params['body']['query']['bool'][$type][] = [
            'nested' => [
                'path'  => 'properties',
                'query' => [
                    ['term' => ['properties.search_value' => $name . ':' . $value]],
                ],
            ],
        ];

        return $this;
    }

    /**
     * 取出打分最高的几个商品
     *
     * @param $count
     * @return $this
     */
    public function minShouldMatch($count)
    {
        $this->params['body']['query']['bool']['minimum_should_match'] = (int)$count;

        return $this;
    }

    /**
     * 添加排序
     *
     * @param $field 要排序的字段
     * @param $direction 倒序 desc 或升序 asc
     * @return $this
     */
    public function orderBy($field, $direction)
    {
        if (! isset($this->params['body']['sort'])) {
            $this->params['body']['sort'] = [];
        }
        $this->params['body']['sort'][] = [$field => $direction];

        return $this;
    }

    /**
     * 返回构造好的查询参数
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}

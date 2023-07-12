<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['productCodeRules'] = [
    [
        'field' => 'productCode',
        'label' => '상품코드',
        'rules' => 'trim|required|max_length[20]',
        'errors' => [
            'required' => '상품코드를 입력해 주세요.',
            'max_length' => '상품코드는 20자 이내로 입력해 주세요.'
        ]
    ]
];

$config['receivingRules'] = [
    $config['productCodeRules'],
    [
        'field' => 'quantity',
        'label' => '입고 수량',
        'rules' => 'required|numeric|greater_than[0]',
        'errors' => [
            'required' => '입고 수량을 입력해 주세요.',
            'numeric' => '입고 수량은 숫자만 입력 가능합니다.',
            'greater_than' => '입고 수량을 1개 이상 입력해 주세요.'
        ]
    ]
];

$config['locationRules'] = [
    $config['receivingRules'],
    [
        'field' => 'locationId',
        'label' => '로케이션',
        'rules' => 'required|numeric|greater_than[0]',
        'errors' => [
            'required' => '로케이션을 선택해 주세요.',
            'numeric' => '유효하지 않은 로케이션입니다.',
            'greater_than' => '유효하지 않은 로케이션입니다.'
        ]
    ]
];
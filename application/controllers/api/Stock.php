<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock extends CI_Controller {

    /**
     * Constructor
     *
     * @throws JsonException
     */
    public function __construct()
    {
        parent::__construct();

        if (! $this->input->is_ajax_request()) {
            exit(json_encode([
                'status' => false,
                'msg' => '정상적인 접근이 아닙니다.'
            ], JSON_THROW_ON_ERROR));
        }

        $this->load->model('StockModel');
    }

    /**
     * Check the product code is exists
     *
     * @return void
     * @throws JsonException
     */
    public function exist(): void
    {
        $this->validateParams('productCodeRules');

        $productCode = $this->input->post('productCode');

        exit(json_encode([
            'status' => true,
            'isExist' => $this->StockModel->isExistProductCode($productCode)
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * Receive product stock or recommend available locations
     * 1. 대상 상품의 재고가 이미 있는 로케이션
     * 2. 빈 로케이션
     * 3. SKU 제한이 없는 로케이션
     *
     * @return void
     * @throws JsonException
     */
    public function receiving(): void
    {
        $this->validateParams('receivingRules');

        $productCode = $this->input->post('productCode');
        $quantity = $this->input->post('quantity');

        if (! $this->StockModel->isExistProductCode($productCode)) {
            exit(json_encode([
                'status' => false,
                'msg' => '상품코드가 존재하지 않습니다.'
            ], JSON_THROW_ON_ERROR));
        }

        // 1. 대상 상품의 재고가 이미 있는 로케이션
        // 동일 상품의 재고가 있는 로케이션이 여러개여도 입고가 가능한 로케이션은 0 ~ 1개
        if (!empty($locationId = $this->StockModel->getAvailableLocationWhereProductAlreadyIn($productCode))) {
            $this->receiveProductStock($productCode, $locationId, $quantity);
        }

        // 2. 빈 로케이션
        // 빈 로케이션은 N개일 수 있으며, N개가 조회되면 추천
        if (!empty($locations = $this->StockModel->getAvailableLocationsWithSkuLimit())) {
            if (count($locations) === 1) {
                $this->receiveProductStock($productCode, $locations[0]['location_id'], $quantity);
            } else {
                exit(json_encode([
                    'status' => true,
                    'isReceived' => false,
                    'locations' => $locations
                ], JSON_THROW_ON_ERROR));
            }
        }

        // 3. SKU 제한이 없는 로케이션
        // SKU 제한이 없는 로케이션은 N개일 수 있으며, N개가 조회되면 추천
        if (!empty($locations = $this->StockModel->getAvailableLocationsWithoutSkuLimit())) {
            if (count($locations) === 1) {
                $this->receiveProductStock($productCode, $locationId, $quantity);
            } else {
                exit(json_encode([
                    'status' => true,
                    'isReceived' => false,
                    'locations' => $locations
                ], JSON_THROW_ON_ERROR));
            }
        }

        exit(json_encode([
            'status' => false,
            'msg' => '입고 가능한 로케이션이 없습니다.'
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * Confirm the location to receive the product stock
     *
     * @return void
     * @throws JsonException
     */
    public function location(): void
    {
        $this->validateParams('locationRules');

        $productCode = $this->input->post('productCode');
        $quantity = $this->input->post('quantity');
        $locationId = $this->input->post('locationId');

        if (! $this->StockModel->isExistProductCode($productCode)) {
            exit(json_encode([
                'status' => false,
                'msg' => '상품코드가 존재하지 않습니다.'
            ], JSON_THROW_ON_ERROR));
        }

        if ($this->StockModel->isAvailableToReceiveProductStock($locationId, $quantity)) {
            $this->receiveProductStock($productCode, $locationId, $quantity);
        } else {
            // 경합으로 인해 입고 가능했던 로케이션이 현재 불가능한 경우
            exit(json_encode([
                'status' => true,
                'isReceived' => false
            ], JSON_THROW_ON_ERROR));
        }
    }

    /**
     * Receive product stock in the location
     *
     * @param string $productCode
     * @param int $locationId
     * @param int $quantity
     * @return void
     * @throws JsonException
     */
    private function receiveProductStock(string $productCode, int $locationId, int $quantity): void
    {
        if ($stockId = $this->StockModel->receiveProductStock($productCode, $locationId, $quantity)) {
            exit(json_encode([
                'status' => true,
                'isReceived' => true,
                'stockId' => $stockId
            ], JSON_THROW_ON_ERROR));
        } else {
            exit(json_encode([
                'status' => false,
                'msg' => '상품 입고에 실패하였습니다. 다시 시도해 주세요.'
            ], JSON_THROW_ON_ERROR));
        }
    }

    /**
     * Validate parameters from clients
     *
     * @param string $rule
     * @return void
     * @throws JsonException
     */
    private function validateParams(string $rule): void
    {
        if (! $this->form_validation->run($rule)) {
            exit(json_encode([
                'status' => false,
                'msg' => implode('\n', $this->form_validation->error_array())
            ], JSON_THROW_ON_ERROR));
        }
    }

}

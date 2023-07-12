<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock extends CI_Controller {

    /**
     * Receiving form page
     *
     * @return void
     */
    public function receiving(): void
    {
        $this->load->view('main_layout', [
            'container' => 'receiving_form',
            'container_css' => [
                '/assets/css/receiving.css'
            ],
            'container_scripts' => [
                '/assets/js/receiving.js'
            ]
        ]);
    }

    /**
     * Received page
     *
     * @param int $stockId
     * @return void
     */
    public function received(int $stockId): void
    {
        $this->load->model('StockModel');

        if ($receivedInfo = $this->StockModel->getReceivedStockInfo($stockId)) {
            $this->load->view('main_layout', [
                'container' => 'received',
                'container_css' => [
                    '/assets/css/receiving.css'
                ],
                'container_scripts' => [],
                'received_info' => $receivedInfo
            ]);
        } else {
            show_error('Wrong parameters. Go back to the first page.');
        }
    }

}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class StockModel extends CI_Model {

    /**
     * @var PDO $pdo
     */
    private $pdo;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->config('database', true);
        $this->load->library('PdoUtil', $this->config->item('pdo', 'database'));

        $this->pdo = $this->pdoutil->getConnection();
    }

    /**
     * Check the product code is exists
     *
     * @param string $productCode
     * @return bool
     */
    public function isExistProductCode(string $productCode): bool
    {
        $query = "
            SELECT
                1
            FROM
                shipping_product
            WHERE
                product_code = :productCode
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':productCode', $productCode);
        $stmt->execute();

        return !empty($stmt->fetch());
    }

    /**
     * Receive product stock
     *
     * @param string $productCode
     * @param int $locationId
     * @param int $quantity
     * @return int
     */
    public function receiveProductStock(string $productCode, int $locationId, int $quantity): int
    {
        $query = "
            INSERT INTO product_stock
                (shipping_product_id, location_id, quantity, create_date)
            SELECT
                shipping_product_id
                , :locationId
                , :quantity
                , NOW()
            FROM
                 shipping_product
            WHERE
                product_code = :productCode
        ";

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':locationId', $locationId, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':productCode', $productCode);
            $stmt->execute();
            $stockId = $this->pdo->lastInsertId();

            $this->pdo->commit();

            return $stockId;
        } catch (PDOException $e) {
            $this->pdo->rollback();

            return 0;
        }
    }

    /**
     * Get an available location where the target product is already in
     *
     * @param string $productCode
     * @return int|null
     */
    public function getAvailableLocationWhereProductAlreadyIn(string $productCode): ?int
    {
        $query = "
            SELECT
                ps.location_id
            FROM
                product_stock AS ps
                INNER JOIN shipping_product AS sp
                    ON ps.shipping_product_id = sp.shipping_product_id
                        AND sp.product_code = :productCode
            GROUP BY
                ps.location_id
            HAVING
                SUM(ps.quantity) > 0
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':productCode', $productCode);
        $stmt->execute();

        $locationId = $stmt->fetchColumn();

        return !empty($locationId) ? $locationId : null;
    }

    /**
     * Get available locations with sku limit
     *
     * @return array
     */
    public function getAvailableLocationsWithSkuLimit(): array
    {
        $query = "
            SELECT
                li.location_id
                , li.location_name
                , li.sku_limit
                , IFNULL(stock.sku_cnt, 0) AS sku_cnt
            FROM
                location_info AS li
                LEFT JOIN (
                    SELECT
                        sku.location_id
                        , COUNT(DISTINCT shipping_product_id) AS sku_cnt
                    FROM (
                        SELECT
                            ps.location_id
                            , ps.shipping_product_id
                        FROM
                            product_stock AS ps
                            INNER JOIN location_info AS li
                                ON ps.location_id = li.location_id
                                    AND li.sku_limit > 0
                        GROUP BY
                            ps.location_id,
                            ps.shipping_product_id
                        HAVING
                            SUM(ps.quantity) > 0
                    ) AS sku
                    GROUP BY
                        sku.location_id
                ) AS stock
                    ON li.location_id = stock.location_id
            WHERE
                li.sku_limit > 0
                AND li.sku_limit > IFNULL(stock.sku_cnt, 0)
            ORDER BY
                li.sku_limit - IFNULL(stock.sku_cnt, 0) ASC,
                li.location_name ASC,
                li.location_id ASC
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get available locations without sku limit
     *
     * @return array
     */
    public function getAvailableLocationsWithoutSkuLimit(): array
    {
        $query = "
            SELECT
                li.location_id
                , li.location_name
                , li.sku_limit
                , IFNULL(stock.sku_cnt, 0) AS sku_cnt
            FROM
                location_info AS li
                LEFT JOIN (
                    SELECT
                        sku.location_id
                        , COUNT(DISTINCT shipping_product_id) AS sku_cnt
                    FROM (
                        SELECT
                            ps.location_id
                            , ps.shipping_product_id
                        FROM
                            product_stock AS ps
                            INNER JOIN location_info AS li
                                ON ps.location_id = li.location_id
                                    AND li.sku_limit = 0
                        GROUP BY
                            ps.location_id,
                            ps.shipping_product_id
                        HAVING
                            SUM(ps.quantity) > 0
                    ) AS sku
                    GROUP BY
                        sku.location_id
                ) AS stock
                    ON li.location_id = stock.location_id
            WHERE
                li.sku_limit = 0
            ORDER BY
                IFNULL(stock.sku_cnt, 0) DESC,
                li.location_name ASC,
                li.location_id ASC
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check the location is available to receive product stock
     *
     * @param int $locationId
     * @return bool
     */
    public function isAvailableToReceiveProductStock(int $locationId): bool
    {
        $query = "
            SELECT
                1
            FROM
                location_info AS li
                LEFT JOIN (
                    SELECT
                        sku.location_id
                        , COUNT(DISTINCT shipping_product_id) AS sku_cnt
                    FROM (
                        SELECT
                            ps.location_id
                            , ps.shipping_product_id
                        FROM
                            product_stock AS ps
                            INNER JOIN location_info AS li
                                ON ps.location_id = li.location_id
                        WHERE
                            ps.location_id = :locationId
                        GROUP BY
                            ps.location_id,
                            ps.shipping_product_id
                        HAVING
                            SUM(ps.quantity) > 0
                    ) AS sku
                    GROUP BY
                        sku.location_id
                ) AS stock
                    ON li.location_id = stock.location_id
            WHERE
                li.location_id = :locationId
                AND IF(
                    li.sku_limit = 0,
                    TRUE,
                    li.sku_limit > IFNULL(stock.sku_cnt, 0)
                )
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':locationId', $locationId, PDO::PARAM_INT);
        $stmt->execute();

        return !empty($stmt->fetch());
    }

    /**
     * Get received product name, quantity, location name by stock id
     *
     * @param int $stockId
     * @return array|null
     */
    public function getReceivedStockInfo(int $stockId): ?array
    {
        $query = "
            SELECT
                sp.product_name
                , ps.quantity
                , li.location_name
            FROM
                product_stock AS ps
                INNER JOIN shipping_product AS sp
                    ON ps.shipping_product_id = sp.shipping_product_id
                INNER JOIN location_info AS li 
                    ON ps.location_id = li.location_id
            WHERE
                ps.stock_id = :stockId
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':stockId', $stockId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

}
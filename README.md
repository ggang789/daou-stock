***
# 상품 입고 프로그램 (이세진)

## 개발 소요 시간
* 설계: 2시간
* 개발: 15시간
* TEST: 2시간

## Version
* PHP 7.3
* Codeigniter 3.1.13
* MariaDB 11.0.2

## API
* /api/stock/exist: 상품코드 존재 여부 확인
* /api/stock/receiving: 우선순위에 따른 입고 처리 또는 로케이션 추천 (입고 처리시, 로케이션 추천 없음)
* /api/stock/location: 특정 로케이션에 입고 처리

## Database
* DDL
<pre><code>
CREATE TABLE `shipping_product` (
  `shipping_product_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '상품 ID',
  `product_code` VARCHAR(20) NOT NULL COMMENT '상품코드',
  `product_name` VARCHAR(100) NOT NULL COMMENT '상품명',
  PRIMARY KEY (`shipping_product_id`),
  UNIQUE KEY `UNIQUE_PRODUCT_CODE` (`product_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='상품 정보';

CREATE TABLE `location_info` (
  `location_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '로케이션 ID',
  `location_name` VARCHAR(50) NOT NULL COMMENT '로케이션명',
  `sku_limit` INT(10) UNSIGNED NOT NULL COMMENT 'SKU 제한',
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='로케이션 정보';

CREATE TABLE `product_stock` (
  `stock_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '재고 ID',
  `shipping_product_id` INT(10) UNSIGNED NOT NULL COMMENT '상품 ID',
  `location_id` INT(10) UNSIGNED NOT NULL COMMENT '로케이션 ID',
  `quantity` INT(10) NOT NULL COMMENT '수량',
  `create_date` DATE NOT NULL COMMENT '날짜',
  PRIMARY KEY (`stock_id`),
  KEY `FK_SHIPPING_PRODUCT_TO_PRODUCT_STOCK` (`shipping_product_id`),
  KEY `FK_LOCATION_INFO_TO_PRODUCT_STOCK` (`location_id`),
  CONSTRAINT `FK_SHIPPING_PRODUCT_TO_PRODUCT_STOCK` FOREIGN KEY (`shipping_product_id`) REFERENCES `shipping_product` (`shipping_product_id`),
  CONSTRAINT `FK_LOCATION_INFO_TO_PRODUCT_STOCK` FOREIGN KEY (`location_id`) REFERENCES `location_info` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='재고 정보';
</code></pre>

* DML
<pre><code>
INSERT INTO shipping_product
  (product_code, product_name)
VALUES
  ('C00000001', '상품 1'),
  ('C00000002', '상품 2'),
  ('C00000003', '상품 3'),
  ('C00000004', '상품 4'),
  ('C00000005', '상품 5'),
  ('C00000006', '상품 6'),
  ('C00000007', '상품 7'),
  ('C00000008', '상품 8'),
  ('C00000009', '상품 9'),
  ('C00000010', '상품 10');

INSERT INTO location_info
  (location_name, sku_limit)
VALUES
  ('로케이션 1', 1),
  ('로케이션 2', 2),
  ('로케이션 3', 3),
  ('로케이션 4', 4),
  ('로케이션 5', 5),
  ('로케이션 6', 6),
  ('로케이션 7', 7),
  ('로케이션 8', 8),
  ('로케이션 9', 9),
  ('로케이션 10', 0);
</code></pre>
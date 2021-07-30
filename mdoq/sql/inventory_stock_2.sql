/*
 Navicat MySQL Data Transfer

 Source Server         : barrdisplay.com M2 MDOQ Prod
 Source Server Type    : MySQL
 Source Server Version : 80023
 Source Host           : 11157-mysql:3306
 Source Schema         : magento

 Target Server Type    : MySQL
 Target Server Version : 80023
 File Encoding         : 65001

 Date: 30/07/2021 15:49:22
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for inventory_stock_2
-- ----------------------------
DROP TABLE IF EXISTS `inventory_stock_2`;
CREATE TABLE `inventory_stock_2` (
  `sku` varchar(64) NOT NULL COMMENT 'Sku',
  `quantity` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'Quantity',
  `is_salable` tinyint(1) NOT NULL COMMENT 'Is Salable',
  PRIMARY KEY (`sku`),
  KEY `index_sku_qty` (`sku`,`quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Inventory Stock item Table';

SET FOREIGN_KEY_CHECKS = 1;

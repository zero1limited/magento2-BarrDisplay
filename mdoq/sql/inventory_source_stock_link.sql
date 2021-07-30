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

 Date: 30/07/2021 15:50:21
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for inventory_source_stock_link
-- ----------------------------
DROP TABLE IF EXISTS `inventory_source_stock_link`;
CREATE TABLE `inventory_source_stock_link` (
  `link_id` int unsigned NOT NULL AUTO_INCREMENT,
  `stock_id` int unsigned NOT NULL,
  `source_code` varchar(255) NOT NULL,
  `priority` smallint unsigned NOT NULL,
  PRIMARY KEY (`link_id`),
  UNIQUE KEY `INVENTORY_SOURCE_STOCK_LINK_STOCK_ID_SOURCE_CODE` (`stock_id`,`source_code`),
  KEY `INV_SOURCE_STOCK_LNK_SOURCE_CODE_INV_SOURCE_SOURCE_CODE` (`source_code`),
  KEY `INVENTORY_SOURCE_STOCK_LINK_STOCK_ID_PRIORITY` (`stock_id`,`priority`),
  CONSTRAINT `INV_SOURCE_STOCK_LNK_SOURCE_CODE_INV_SOURCE_SOURCE_CODE` FOREIGN KEY (`source_code`) REFERENCES `inventory_source` (`source_code`) ON DELETE CASCADE,
  CONSTRAINT `INVENTORY_SOURCE_STOCK_LINK_STOCK_ID_INVENTORY_STOCK_STOCK_ID` FOREIGN KEY (`stock_id`) REFERENCES `inventory_stock` (`stock_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of inventory_source_stock_link
-- ----------------------------
BEGIN;
INSERT INTO `inventory_source_stock_link` VALUES (1, 1, 'default', 1);
INSERT INTO `inventory_source_stock_link` VALUES (2, 2, 'akron_oh', 15);
INSERT INTO `inventory_source_stock_link` VALUES (3, 2, 'baltimore_md', 16);
INSERT INTO `inventory_source_stock_link` VALUES (4, 2, 'birmingham_al', 17);
INSERT INTO `inventory_source_stock_link` VALUES (5, 2, 'charleston_sc', 18);
INSERT INTO `inventory_source_stock_link` VALUES (6, 2, 'chesapeake_va', 19);
INSERT INTO `inventory_source_stock_link` VALUES (7, 2, 'dania_beach', 20);
INSERT INTO `inventory_source_stock_link` VALUES (8, 2, 'fort_myers', 21);
INSERT INTO `inventory_source_stock_link` VALUES (9, 2, 'mobile_al', 22);
INSERT INTO `inventory_source_stock_link` VALUES (10, 2, 'nashville_tn', 23);
INSERT INTO `inventory_source_stock_link` VALUES (11, 2, 'oklahoma_city', 24);
INSERT INTO `inventory_source_stock_link` VALUES (12, 2, 'orlando_hq', 25);
INSERT INTO `inventory_source_stock_link` VALUES (13, 2, 'pittsburgh_pa', 26);
INSERT INTO `inventory_source_stock_link` VALUES (14, 2, 'tampa_fl', 27);
INSERT INTO `inventory_source_stock_link` VALUES (15, 2, 'tusla_ok', 28);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;

/*
 Navicat Premium Data Transfer

 Source Server         : 127.0.0.1
 Source Server Type    : MySQL
 Source Server Version : 50722
 Source Host           : 127.0.0.1
 Source Database       : km_wxapp_track

 Target Server Type    : MySQL
 Target Server Version : 50722
 File Encoding         : utf-8

 Date: 07/04/2018 18:15:42 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `wx_button`
-- ----------------------------
DROP TABLE IF EXISTS `wx_button`;
CREATE TABLE `wx_button` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wx_data_id` int(11) DEFAULT NULL COMMENT 'all_data_origin 主键',
  `uuid` varchar(64) DEFAULT NULL,
  `unionid` varchar(64) DEFAULT NULL COMMENT '用户的unionId',
  `openid` varchar(64) DEFAULT NULL COMMENT '用户在小程序的openid',
  `page` varchar(64) DEFAULT NULL COMMENT '页面名称',
  `button` varchar(64) DEFAULT NULL COMMENT 'button名称',
  `url` varchar(1024) DEFAULT NULL COMMENT '页面URL',
  `refer_url` varchar(1024) DEFAULT NULL COMMENT '来源url',
  `view_time` timestamp NULL DEFAULT NULL COMMENT '访问时间',
  `ip` varchar(16) DEFAULT NULL COMMENT 'ip地址',
  `srcopenid` varchar(64) DEFAULT NULL COMMENT '来源openid',
  `srcgroupid` varchar(64) DEFAULT NULL COMMENT '来源群id',
  `srcuuid` varchar(64) DEFAULT NULL COMMENT '来源 uuid',
  `platform` varchar(64) DEFAULT NULL COMMENT '在一次小程序进程（后台关闭微信、关机等会终止小程序进程）中首次访问的渠道',
  `sceneid` int(5) DEFAULT NULL COMMENT '用户每次动作的场景值id',
  `window_width` decimal(7,2) DEFAULT NULL COMMENT '可使用窗口宽度',
  `window_height` decimal(7,2) DEFAULT NULL COMMENT '可使用窗口高度',
  `status_bar_height` decimal(7,2) DEFAULT NULL COMMENT '状态栏的高度',
  `language` varchar(16) DEFAULT NULL COMMENT '微信设置的语言',
  `wechat_version` varchar(32) DEFAULT NULL COMMENT '微信版本号',
  `system` varchar(32) DEFAULT NULL COMMENT '操作系统版本',
  `s_platform` varchar(32) DEFAULT NULL COMMENT '客户端平台',
  `font_size_setting` tinyint(4) DEFAULT NULL COMMENT '用户字体大小设置。以“我-设置-通用-字体大小”中的设置为准，单位：px',
  `SDK_version` varchar(32) DEFAULT NULL COMMENT '客户端基础库版本',
  `network_type` varchar(16) DEFAULT NULL COMMENT '网络状态',
  `args` varchar(1024) DEFAULT NULL COMMENT '用户自定义参数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='按钮数据';

-- ----------------------------
--  Table structure for `wx_data`
-- ----------------------------
DROP TABLE IF EXISTS `wx_data`;
CREATE TABLE `wx_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(64) DEFAULT NULL,
  `et` varchar(32) DEFAULT NULL,
  `en` varchar(32) DEFAULT NULL,
  `pp` varchar(1024) DEFAULT NULL,
  `rpp` varchar(1024) DEFAULT NULL,
  `st` timestamp NULL DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `request_type` tinyint(4) DEFAULT NULL COMMENT '1 => 数据, 2 => 用户, 3 => 用户的对应关系(UUID 与 openID)',
  `rq_c` smallint(6) DEFAULT NULL,
  `rt` smallint(6) DEFAULT NULL,
  `pb` varchar(64) DEFAULT NULL,
  `pm` varchar(64) DEFAULT NULL,
  `pr` decimal(7,2) DEFAULT NULL,
  `scw` decimal(7,2) DEFAULT NULL,
  `ww` decimal(7,2) DEFAULT NULL,
  `wh` decimal(7,2) DEFAULT NULL,
  `sbh` decimal(7,2) DEFAULT NULL,
  `lg` varchar(16) DEFAULT NULL,
  `wxv` varchar(32) DEFAULT NULL,
  `system` varchar(32) DEFAULT NULL,
  `pl` varchar(32) DEFAULT NULL,
  `fss` tinyint(4) DEFAULT NULL,
  `sdkv` varchar(32) DEFAULT NULL,
  `nt` varchar(16) DEFAULT NULL,
  `l_token` varchar(64) DEFAULT NULL,
  `nk` varchar(64) DEFAULT NULL,
  `aurl` varchar(256) DEFAULT NULL,
  `gender` varchar(2) DEFAULT NULL,
  `city` varchar(128) DEFAULT NULL,
  `province` varchar(128) DEFAULT NULL,
  `country` varchar(32) DEFAULT NULL,
  `v` varchar(32) DEFAULT NULL,
  `token` varchar(32) DEFAULT NULL,
  `dr` varchar(32) DEFAULT NULL,
  `pc` smallint(6) DEFAULT NULL,
  `lopt` text,
  `asc` smallint(6) DEFAULT NULL,
  `ahc` smallint(6) DEFAULT NULL,
  `rsu` varchar(512) DEFAULT NULL,
  `ifo` varchar(8) DEFAULT NULL,
  `sc` smallint(6) DEFAULT NULL,
  `ec` smallint(6) DEFAULT NULL,
  `ag` varchar(512) DEFAULT NULL,
  `ifp` varchar(8) DEFAULT NULL,
  `pagedes` varchar(512) DEFAULT NULL,
  `lp` varchar(512) DEFAULT NULL,
  `fp` varchar(512) DEFAULT NULL,
  `lt` varchar(32) DEFAULT NULL,
  `ginfo` varchar(256) DEFAULT NULL,
  `err` varchar(512) DEFAULT NULL,
  `args` text,
  `sr` varchar(64) DEFAULT NULL,
  `s_arge` text,
  `path` text,
  `from` varchar(512) DEFAULT NULL,
  `title` varchar(512) DEFAULT NULL,
  `imageurl` varchar(512) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPRESSED COMMENT='所有埋点数据';

-- ----------------------------
--  Table structure for `wx_page`
-- ----------------------------
DROP TABLE IF EXISTS `wx_page`;
CREATE TABLE `wx_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wx_data_id` int(11) DEFAULT NULL COMMENT 'all_data_origin 主键',
  `uuid` varchar(64) DEFAULT NULL COMMENT '应用生成唯一标识',
  `openid` varchar(64) DEFAULT NULL COMMENT '小程序区分用户 ID 标识',
  `unionid` varchar(64) DEFAULT NULL COMMENT '公众平台区分用户 ID 标识',
  `et` varchar(32) DEFAULT NULL COMMENT '事件类型',
  `en` varchar(32) DEFAULT NULL COMMENT '事件名称',
  `page` varchar(64) DEFAULT NULL COMMENT '页面名称',
  `url` varchar(1024) DEFAULT NULL COMMENT '页面URL',
  `refer_url` varchar(1024) DEFAULT NULL COMMENT '来源url',
  `view_time` timestamp NULL DEFAULT NULL COMMENT '访问时间',
  `loading_seconds` int(11) DEFAULT NULL COMMENT '页面加载毫秒数',
  `stay_milliseconds` int(11) DEFAULT NULL COMMENT '页面停留毫秒数',
  `ip` varchar(16) DEFAULT NULL COMMENT '用户ip地址',
  `srcopenid` varchar(64) DEFAULT NULL COMMENT '来源openid（记录的是每个进程，未被覆盖的，最新来源）',
  `srcgroupid` varchar(64) DEFAULT NULL COMMENT '来源群id（记录的是每个进程，未被覆盖的，最新来源）',
  `srcuuid` varchar(64) DEFAULT NULL COMMENT '来源 uuid',
  `platform` varchar(64) DEFAULT NULL COMMENT '在一次小程序进程（后台关闭微信、关机等会终止小程序进程）中首次访问的渠道',
  `sceneid` int(5) DEFAULT NULL COMMENT '用户每次访问的场景值id',
  `window_width` decimal(7,2) DEFAULT NULL COMMENT '可使用窗口宽度',
  `window_height` decimal(7,2) DEFAULT NULL COMMENT '可使用窗口高度',
  `status_bar_height` decimal(7,2) DEFAULT NULL COMMENT '状态栏的高度',
  `language` varchar(16) DEFAULT NULL COMMENT '微信设置的语言',
  `wechat_version` varchar(32) DEFAULT NULL COMMENT '微信版本号',
  `system` varchar(32) DEFAULT NULL COMMENT '操作系统版本',
  `s_platform` varchar(32) DEFAULT NULL COMMENT '客户端平台',
  `font_size_setting` tinyint(4) DEFAULT NULL COMMENT '用户字体大小设置。以“我-设置-通用-字体大小”中的设置为准，单位：px',
  `SDK_version` varchar(32) DEFAULT NULL COMMENT '客户端基础库版本',
  `network_type` varchar(16) DEFAULT NULL COMMENT '网络状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='页面数据';

-- ----------------------------
--  Table structure for `wx_share`
-- ----------------------------
DROP TABLE IF EXISTS `wx_share`;
CREATE TABLE `wx_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wx_data_id` int(11) DEFAULT NULL COMMENT 'all_data_origin 主键',
  `uuid` varchar(64) DEFAULT NULL COMMENT '应用生成唯一标识',
  `openid` varchar(64) DEFAULT NULL COMMENT '用户在小程序的openid',
  `unionid` varchar(64) DEFAULT NULL COMMENT '用户的unionId',
  `opengid` varchar(64) DEFAULT NULL COMMENT '群id',
  `share_page` varchar(1024) DEFAULT NULL COMMENT '分享所在的页面',
  `share_type` varchar(32) DEFAULT NULL COMMENT '分享形式（ShareToFriend:分享给朋友、ShareToGroup分享给群）',
  `share_result` varchar(1024) DEFAULT NULL COMMENT '分享结果（OK：成功，cancel：取消）',
  `share_from` varchar(1024) DEFAULT NULL COMMENT '分享触发位置',
  `share_title` text COMMENT '分享标题',
  `share_path` text COMMENT '分享地址',
  `share_img_url` text COMMENT '分享中嵌入图片的url',
  `share_time` timestamp NULL DEFAULT NULL COMMENT '分享的时间',
  `ip` varchar(16) DEFAULT NULL COMMENT 'ip地址',
  `srcopenid` varchar(64) DEFAULT NULL COMMENT '来源openid',
  `srcgroupid` varchar(64) DEFAULT NULL COMMENT '来源群id',
  `srcuuid` varchar(64) DEFAULT NULL COMMENT '来源 uuid',
  `platform` varchar(128) DEFAULT NULL COMMENT '在一次小程序进程中首次访问的渠道（后台关闭微信、关机等会终止小程序进程）',
  `sceneid` int(5) DEFAULT NULL COMMENT '用户每次动作的场景值id',
  `window_width` decimal(7,2) DEFAULT NULL COMMENT '可使用窗口宽度',
  `window_height` decimal(7,2) DEFAULT NULL COMMENT '可使用窗口高度',
  `status_bar_height` decimal(7,2) DEFAULT NULL COMMENT '状态栏的高度',
  `language` varchar(16) DEFAULT NULL COMMENT '微信设置的语言',
  `wechat_version` varchar(32) DEFAULT NULL COMMENT '微信版本号',
  `system` varchar(32) DEFAULT NULL COMMENT '操作系统版本',
  `s_platform` varchar(32) DEFAULT NULL COMMENT '客户端平台',
  `font_size_setting` tinyint(4) DEFAULT NULL COMMENT '用户字体大小设置。以“我-设置-通用-字体大小”中的设置为准，单位：px',
  `SDK_version` varchar(32) DEFAULT NULL COMMENT '客户端基础库版本',
  `network_type` varchar(16) DEFAULT NULL COMMENT '网络状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分享数据';

-- ----------------------------
--  Table structure for `wx_users`
-- ----------------------------
DROP TABLE IF EXISTS `wx_users`;
CREATE TABLE `wx_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wx_data_id` int(11) DEFAULT NULL COMMENT 'wx_data 主键',
  `uuid` varchar(64) NOT NULL COMMENT '应用生成唯一标识',
  `openid` varchar(64) DEFAULT NULL COMMENT '小程序区分用户 ID 标识',
  `unionid` varchar(64) DEFAULT NULL COMMENT '公众平台区分用户 ID 标识',
  `nickname` varchar(64) DEFAULT NULL COMMENT '用户昵称',
  `srcopenid` varchar(64) DEFAULT NULL COMMENT '来源用户的小程序的openid（记录首次访问的来源openid，如果无则为空值）',
  `srcgroupid` varchar(64) DEFAULT NULL COMMENT '来源群id（同一群对应不同小程序有不同groupid，记录用户首次访问时的来源群id，如果无则为空值）',
  `platform` varchar(64) DEFAULT NULL COMMENT '来源渠道（用户首次进入的渠道）',
  `sceneid` int(5) DEFAULT NULL COMMENT '用户首次访问的场景值id',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '用户首次进入小程序时间',
  `gender` varchar(2) DEFAULT NULL COMMENT '性别',
  `city` varchar(128) DEFAULT NULL COMMENT '城市',
  `province` varchar(128) DEFAULT NULL COMMENT '省份',
  `country` varchar(32) DEFAULT NULL COMMENT '城市',
  `language` varchar(16) DEFAULT NULL COMMENT '语言',
  `avatar_url` varchar(256) DEFAULT NULL COMMENT '头像图片地址',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_unq_uuid` (`uuid`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户数据';

-- ----------------------------
--  Table structure for `wx_users_relations`
-- ----------------------------
DROP TABLE IF EXISTS `wx_users_relations`;
CREATE TABLE `wx_users_relations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wx_data_id` int(11) DEFAULT NULL COMMENT 'wx_data 主键',
  `uuid` varchar(64) NOT NULL COMMENT '应用生成唯一标识',
  `openid` varchar(64) DEFAULT NULL COMMENT '小程序区分用户 ID 标识',
  `unionid` varchar(64) DEFAULT NULL COMMENT '公众平台区分用户 ID 标识',
  `phone_brand` varchar(64) DEFAULT NULL COMMENT '手机品牌',
  `phone_model` varchar(64) DEFAULT NULL COMMENT '手机型号',
  `pixel_ratio` decimal(7,2) DEFAULT NULL COMMENT '设备像素比',
  `screen_width` decimal(7,2) DEFAULT NULL COMMENT '屏幕宽度',
  `screen_height` decimal(7,2) DEFAULT NULL COMMENT '屏幕高度',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_unq_uuid` (`uuid`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户与微信 openid 关联关系数据';

SET FOREIGN_KEY_CHECKS = 1;

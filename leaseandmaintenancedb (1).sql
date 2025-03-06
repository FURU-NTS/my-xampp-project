-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2025-03-07 07:58:50
-- サーバのバージョン： 10.4.32-MariaDB
-- PHP のバージョン: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `leaseandmaintenancedb`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `companies`
--

CREATE TABLE `companies` (
  `company_id` int(11) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `business_registration_number` varchar(13) DEFAULT NULL,
  `industry_type` varchar(50) DEFAULT NULL,
  `address` text NOT NULL,
  `postal_code` varchar(8) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `representative_name` varchar(100) DEFAULT NULL,
  `decision_maker_1` varchar(100) DEFAULT NULL,
  `decision_maker_2` varchar(100) DEFAULT NULL,
  `representative_income` decimal(15,2) DEFAULT NULL,
  `representative_contact` varchar(50) DEFAULT NULL,
  `employee_count` int(11) DEFAULT NULL,
  `capital` decimal(15,2) DEFAULT NULL,
  `revenue` decimal(15,2) DEFAULT NULL,
  `teikoku` decimal(10,2) DEFAULT NULL,
  `tosho` varchar(100) DEFAULT NULL,
  `memo` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのリレーション `companies`:
--

--
-- テーブルのデータのダンプ `companies`
--

INSERT INTO `companies` (`company_id`, `company_name`, `business_registration_number`, `industry_type`, `address`, `postal_code`, `phone_number`, `email`, `representative_name`, `decision_maker_1`, `decision_maker_2`, `representative_income`, `representative_contact`, `employee_count`, `capital`, `revenue`, `teikoku`, `tosho`, `memo`, `created_at`, `updated_at`) VALUES
(19, '（有）ライフクリエイト', '', '', '白石', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:26:28'),
(20, '峰建設（株）', '', '', '長崎市', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:26:38'),
(21, '（有）白石缶詰工場', '', '', '白石町', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:26:45'),
(22, ' souheki（株）', '', '', '福岡市', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:26:52'),
(23, '（株）溝上建設', '', '', '佐世保', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:27:00'),
(24, '新村エンジニアリング(株)', '', '', '霧島市', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:27:12'),
(25, '有）江崎工業', '', '', 'みやま市', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:27:19'),
(26, '夢木香', '', '', '鹿島市', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:27:29'),
(27, '（合）ヒロコーポレーション', '', '', '菊池市', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:27:36'),
(28, '有）南波多自動車', '', '', '伊万里', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:27:42'),
(29, 'NPO後藤会', '', '', '熊本南区', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:27:49'),
(30, '大輝組(株)', '', '', '鹿児島', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:27:57'),
(31, '(福)木場福祉会', '', '', '南島原', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:28:05'),
(32, 'サンセール不動産', '', '', '佐世保', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:28:12'),
(33, '前田電設工業(有)', '', '', '鹿児島市', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:28:20'),
(34, '(有)清武設備工業', '', '', '西都市', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:28:28'),
(35, '(有)ホンダ塗装工業', '', '', '熊本市北区', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:28:37'),
(36, '池本建築（株）', '', '', '筑紫野市', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:28:45'),
(37, '医療法人　心晴', '', '', '城南区', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:28:53'),
(38, '（有）春日浦溶接', '', '', '大分市', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:29:03'),
(39, '（株）一弘', '', '', 'みやき町', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:29:16'),
(40, '（有）木村設備', '', '', '九重町', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:29:23'),
(41, ' （株）川口瓦工業所', '', '', '菊池市', '', '', '', '', '', '', 0.00, '', 0, 0.00, 0.00, 0.00, '', '', '0000-00-00 00:00:00', '2025-02-28 10:29:27'),
(42, '美川漢方堂', '', '', '大牟田', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(43, '妙晃寺', '', '', '佐世保市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(44, 'NPOたすけあい佐賀', '', '', '佐賀', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(45, '（有）原田工務店', '', '', '宇佐市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(46, '（有）首藤牧場', '', '', '大分', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(47, ' 長崎新聞北諫早販売所', '', '', '諫早', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(48, '（株）山下電機', '', '', '佐世保市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(49, ' (有) 有吉工房', '', '', '苅田', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(50, ' (株)ウッドライフ', '', '', '鹿児島市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(51, ' (一社)chum works', '', '', '佐世保', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(52, '株）井本不動産', '', '', '朝倉市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(53, '（有）丸屋本店', '', '', '佐世保', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(54, ' (株)矢ケ部開発', '', '', '糸島', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(55, ' (株)インテント', '', '', '小林', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(56, '㈱NEXT　LEVEL', '', '', '福岡東区', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(57, '  (合)ウィンビレッヂ', '', '', '高原町', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(58, 'アムーン', '', '', '鹿児島市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(59, '武井工業', '', '', '久留米市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(60, '上村鶴崎測量事務所', '', '', '八幡', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(61, '（有）田中装飾', '', '', '佐賀市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(62, '（株）FUKUDA', '', '', '佐世保', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(63, '社福）まどか福祉会', '', '', '松浦市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(64, '大川平機械銀行', '', '', '小林市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(65, '（有）タケダ石材', '', '', '糟屋郡', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(66, '合）笑', '', '', '宮崎市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(67, '後田建具店', '', '', '諫早', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(68, '（有）ナリアイ金属', '', '', '宮崎市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(69, '織田電気商会', '', '', '太良町', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(70, '株）長政自動車', '', '', '長崎市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(71, ' (有)共成工業', '', '', '周南市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(72, ' NCFIELD（株）', '', '', '佐世保', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(73, '有）辻川ファーマ', '', '', '佐世保市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(74, '株）田邊土木', '', '', '美祢市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(75, '（株）庄島', '', '', '春日', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(76, '(有)相垣工務店', '', '', '菊池市', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(77, '（有）油田工務店', '', '', '綾町', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- テーブルの構造 `credit_applications`
--

CREATE TABLE `credit_applications` (
  `application_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `contract_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `provider_id` int(11) DEFAULT NULL,
  `application_date` date NOT NULL,
  `monthly_fee` decimal(10,2) DEFAULT NULL,
  `total_payments` int(11) DEFAULT NULL,
  `expected_payment` decimal(10,2) DEFAULT NULL,
  `expected_payment_date` date DEFAULT NULL,
  `status` enum('準備中','与信中','条件あり','与信OK','特案OK','与信NG','手続き待ち','手続きOK','承認待ち','承認完了','証明書待ち','入金待ち','入金完了','商談保留','商談キャンセル','承認後キャンセル') NOT NULL,
  `special_case` enum('','補償') NOT NULL DEFAULT '',
  `memo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのリレーション `credit_applications`:
--   `company_id`
--       `companies` -> `company_id`
--   `provider_id`
--       `lease_providers` -> `provider_id`
--   `contract_id`
--       `lease_contracts` -> `contract_id`
--   `order_id`
--       `orders` -> `id`
--   `order_id`
--       `orders` -> `id`
--

--
-- テーブルのデータのダンプ `credit_applications`
--

INSERT INTO `credit_applications` (`application_id`, `company_id`, `contract_id`, `order_id`, `provider_id`, `application_date`, `monthly_fee`, `total_payments`, `expected_payment`, `expected_payment_date`, `status`, `special_case`, `memo`) VALUES
(11, 40, NULL, 17, 2, '2025-02-27', 23400.00, 84, 1618430.00, '0000-00-00', '与信OK', '', 'ZAC　MRT　決算書\r\n'),
(12, 40, NULL, 17, 3, '2025-02-27', 23400.00, 84, 1505240.00, NULL, '入金完了', '', ''),
(14, 39, NULL, 18, 3, '2025-03-13', 22000.00, 84, 2500000.00, NULL, '手続き待ち', '', '');

-- --------------------------------------------------------

--
-- テーブルの構造 `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのリレーション `employees`:
--

--
-- テーブルのデータのダンプ `employees`
--

INSERT INTO `employees` (`employee_id`, `full_name`, `email`, `phone_number`, `department`, `position`, `created_at`, `updated_at`) VALUES
(2, '木上幹雄', '', '08056006603', '☆書換☆', '常務取締役', '2025-02-26 11:23:51', '2025-03-05 03:26:30'),
(3, '深川勝博', '', '08056006606', '☆書換☆', '係長', '2025-02-27 03:14:15', '2025-03-05 03:26:37'),
(4, '高岩', '', '', '◎営業久留米◎', '専務取締役', '2025-02-28 00:40:28', '2025-03-05 03:25:29'),
(5, '宮之原', '', '', '◎営業久留米◎', '課長', '2025-02-28 07:21:55', '2025-03-05 03:25:35'),
(6, '田畑', '', '', '◎営業久留米◎', '主任', '2025-02-28 07:22:14', '2025-03-05 03:25:44'),
(7, '倉満', '', '', '◎営業久留米◎', '車輛長', '2025-02-28 07:22:41', '2025-03-05 03:25:49'),
(8, '吉永', '', '', '◎営業久留米◎', '車輛長', '2025-02-28 07:23:33', '2025-03-05 03:25:55'),
(9, '田中', '', '', '◎営業都城◎', '車輛長', '2025-02-28 07:24:07', '2025-03-05 03:26:17'),
(10, '石原', '', '', '◎営業久留米◎', '主任', '2025-02-28 07:24:37', '2025-03-05 03:26:01'),
(11, '森高', '', '', '◎営業久留米◎', '主任', '2025-02-28 07:25:02', '2025-03-05 03:26:07'),
(12, '古屋敷', '', '', '◇管理◇', '主任', '2025-02-28 07:25:26', '2025-03-05 03:26:46'),
(13, '早野', '', '', '〇アポイント〇', '主査', '2025-02-28 07:31:31', '2025-03-05 03:27:04'),
(14, '浜田', '', '', '〇アポイント〇', '主任', '2025-02-28 07:31:56', '2025-03-05 03:27:09'),
(15, '三坂', '', '', '〇アポイント〇', '派遣社員', '2025-02-28 07:32:26', '2025-03-05 03:27:15'),
(16, '永井', '', '', '◇管理◇', '主査', '2025-02-28 07:33:54', '2025-03-05 03:27:24'),
(17, '上丸', '', '', '【SE】', '主任', '2025-02-28 07:34:15', '2025-03-04 09:44:11'),
(18, '松尾', '', '', '【SE】', '主任', '2025-02-28 07:34:48', '2025-03-04 09:44:24'),
(19, '菅野', '', '', '【SE】', 'リーダー', '2025-02-28 07:35:20', '2025-03-04 09:44:37'),
(20, '森', '', '', '【リモート】', '主任', '2025-02-28 07:36:44', '2025-03-04 09:45:21'),
(21, '土師', '', '', '【リモート】', '主任', '2025-02-28 07:37:10', '2025-03-04 09:45:32'),
(22, '清水', '', '', '【工事】', '主任', '2025-02-28 07:37:42', '2025-03-04 09:44:56'),
(23, '松崎', '', '', '【㈱オーバークロック】', '代表取締役', '2025-02-28 07:38:47', '2025-03-04 09:46:00'),
(24, '西田', '', '', '【㈱オーバークロック】', '社員', '2025-02-28 07:39:07', '2025-03-04 09:46:11'),
(25, '大浦', '', '', '【㈲イージーアイ】', '社員', '2025-02-28 07:40:40', '2025-03-04 09:46:21'),
(26, '手塚', '', '', '【PCQQ】', '代表', '2025-02-28 07:41:29', '2025-03-04 09:46:32'),
(27, '稲垣', '', '', '【レインボー】', '代表', '2025-02-28 07:42:01', '2025-03-04 09:46:42'),
(28, '小島', '', '', '【工事】', '主任', '2025-02-28 07:43:30', '2025-03-04 09:45:08');

-- --------------------------------------------------------

--
-- テーブルの構造 `equipment_master`
--

CREATE TABLE `equipment_master` (
  `equipment_id` int(11) NOT NULL,
  `equipment_name` varchar(255) NOT NULL,
  `equipment_type` varchar(255) NOT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `model_number` varchar(255) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのリレーション `equipment_master`:
--

--
-- テーブルのデータのダンプ `equipment_master`
--

INSERT INTO `equipment_master` (`equipment_id`, `equipment_name`, `equipment_type`, `manufacturer`, `model_number`, `price`) VALUES
(9, '情報通信機器保護用SPDーター', '雷対策', 'ｴｽｱｰﾙｴｽ', 'RSP-T64K', 130000.00),
(10, '対雷サージ付き電源起動制御装置', '雷対策', 'ALEXON', 'TSW2020', 150000.00),
(11, '対雷サージ付き電源起動制御装置', '雷対策', 'ALEXON', 'SSW2000', 150000.00),
(12, '個人情報ファイル検知システム機', '個人情報対策', 'ALEXON', 'DI1000L20', 1450000.00),
(13, 'LAN不正接続ブロッカー', '裏口対策', 'ALEXON', 'ZAC200', 728000.00),
(14, 'LAN不正接続ブロッカー　ｱﾝﾁﾏﾙｳｴｱ', '裏口対策', 'ALEXON', 'ZAC200M', 1288000.00),
(15, '高度ｾｷｭﾘﾃｨｽｲｯﾁ', '社内LANランサム対策', 'ALEXON', 'MRT200/L', 808000.00),
(16, '高度ｾｷｭﾘﾃｨｽｲｯﾁ', '社内LANランサム対策', 'ALEXON', 'MT280/S', 718000.00),
(17, '高度ｾｷｭﾘﾃｨｽｲｯﾁ', '社内LANランサム対策', 'ALEXON', 'MT280/M', 788000.00),
(18, '高度ｾｷｭﾘﾃｨｽｲｯﾁ', '社内LANランサム対策', 'ALEXON', 'MT280/L', 858000.00),
(19, 'NTS Series Server', 'NTSｻｰﾊﾞｰ', 'VALTEC', 'NTS-HS220', 1186500.00),
(20, 'クラウド対応サーバー　4TB', 'Driven Shelter　ｻｰﾊﾞｰ', 'ALEXON', 'DS-420', 500000.00),
(21, 'クラウド対応サーバー　8TB', 'Driven Shelter　ｻｰﾊﾞｰ', 'ALEXON', 'DS-440', 600000.00),
(22, 'クラウド対応サーバー　2TB', 'Driven Shelter　ｻｰﾊﾞｰ', 'ALEXON', 'DS-420T', 500000.00),
(23, 'クラウド対応サーバー　4TB', 'Driven Shelter　ｻｰﾊﾞｰ', 'ALEXON', 'DS-440T', 600000.00),
(24, 'クラウド対応サーバー　2TBｱﾝﾁﾏﾙｳｴｱ', 'Di Shelter　ｻｰﾊﾞｰ　　　　　　　　　　　　', 'ALEXON', 'Di-420T　', 1340000.00),
(25, 'クラウド対応サーバー　4TBｱﾝﾁﾏﾙｳｴｱ', 'Di Shelter　ｻｰﾊﾞｰ　　　　　　　　　　　　　', 'ALEXON', 'Di-420T　', 1436000.00),
(26, '電子帳簿対応サーバー', 'DDS Server　ｻｰﾊﾞｰ', 'ALEXON', 'DDS420', 897000.00),
(27, '電子帳簿対応サーバー', 'DDS Server　ｻｰﾊﾞｰ', 'ALEXON', 'DDS440', 1027000.00),
(28, '統合脅威管理機器', 'UTM', 'ALEXON', 'UTM250 std/L', 1058000.00),
(29, '統合脅威管理機器ｱﾝﾁﾏﾙｳｴｱ', 'UTM', 'ALEXON', 'UTM250WⅡ', 1508000.00);

-- --------------------------------------------------------

--
-- テーブルの構造 `installation_projects`
--

CREATE TABLE `installation_projects` (
  `project_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `status` enum('planning','in_progress','completed') NOT NULL,
  `memo` text DEFAULT NULL,
  `new_schedule_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `contract_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのリレーション `installation_projects`:
--   `order_id`
--       `orders` -> `id`
--   `contract_id`
--       `lease_contracts` -> `contract_id`
--

--
-- テーブルのデータのダンプ `installation_projects`
--

INSERT INTO `installation_projects` (`project_id`, `order_id`, `order_date`, `status`, `memo`, `new_schedule_date`, `end_date`, `contract_id`) VALUES
(2, 18, '2025-02-28', 'in_progress', '機器設置予定', '2025-03-01', '2025-05-31', NULL),
(5, 19, '2025-02-28', 'planning', '1233', '2025-03-05', NULL, NULL),
(6, 19, '2025-02-28', 'in_progress', '', '2025-03-08', NULL, NULL),
(7, 18, '2025-02-28', 'in_progress', '789', '2025-03-29', NULL, NULL),
(8, 19, '2025-02-28', 'planning', NULL, '2025-03-08', NULL, NULL),
(10, 17, '2025-02-27', 'planning', NULL, '2025-03-15', NULL, NULL),
(11, 19, '2025-02-28', 'in_progress', NULL, '2025-03-15', NULL, NULL);

-- --------------------------------------------------------

--
-- テーブルの構造 `installation_tasks`
--

CREATE TABLE `installation_tasks` (
  `task_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `task_name` enum('見積機器納品','見積機器設定','PC設定','配線整理','Sメッシュ設置','Sラック設置','Sカメラ設置','Sパソコン設置','VPN構築','他サービス品納品','機器撤去','機器預かり','書類預かり','各種コンサル','HP.SNSサポート','その他') DEFAULT NULL,
  `status` enum('not_started','in_progress','completed') NOT NULL,
  `memo` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `employee_id_1` int(11) DEFAULT NULL,
  `employee_id_2` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのリレーション `installation_tasks`:
--   `project_id`
--       `installation_projects` -> `project_id`
--   `employee_id_1`
--       `employees` -> `employee_id`
--   `employee_id_2`
--       `employees` -> `employee_id`
--

--
-- テーブルのデータのダンプ `installation_tasks`
--

INSERT INTO `installation_tasks` (`task_id`, `project_id`, `task_name`, `status`, `memo`, `start_date`, `end_date`, `employee_id_1`, `employee_id_2`) VALUES
(3, 2, '他サービス品納品', 'not_started', '撤去予定', '2025-03-01', '2025-03-02', 14, 9),
(4, 2, '見積機器納品', 'completed', '', '2025-03-06', '2025-03-03', 18, NULL),
(5, 2, '見積機器設定', 'completed', '', '2025-03-06', '2025-03-03', 18, NULL),
(7, 2, '配線整理', 'completed', '', '2025-03-06', '2025-03-03', 18, NULL),
(16, 5, '見積機器納品', 'in_progress', '', '2025-03-03', NULL, NULL, NULL),
(17, 5, '見積機器設定', 'not_started', '', NULL, NULL, NULL, NULL),
(18, 5, 'PC設定', 'not_started', '', NULL, NULL, NULL, NULL),
(19, 5, 'HP.SNSサポート', 'not_started', '', NULL, NULL, NULL, NULL),
(20, 5, 'その他', 'not_started', '', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- テーブルの構造 `leased_equipment`
--

CREATE TABLE `leased_equipment` (
  `leased_equipment_id` int(11) NOT NULL,
  `equipment_id` int(11) DEFAULT NULL,
  `contract_id` int(11) DEFAULT NULL,
  `installation_date` date DEFAULT NULL,
  `last_maintenance_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのリレーション `leased_equipment`:
--   `equipment_id`
--       `equipment_master` -> `equipment_id`
--   `contract_id`
--       `lease_contracts` -> `contract_id`
--

--
-- テーブルのデータのダンプ `leased_equipment`
--

INSERT INTO `leased_equipment` (`leased_equipment_id`, `equipment_id`, `contract_id`, `installation_date`, `last_maintenance_date`) VALUES
(74, 9, 18, NULL, NULL),
(75, 10, 18, NULL, NULL),
(76, 11, 18, NULL, NULL),
(77, 12, 18, NULL, NULL),
(78, 9, 16, NULL, NULL),
(79, 10, 16, NULL, NULL),
(80, 12, 16, NULL, NULL);

-- --------------------------------------------------------

--
-- テーブルの構造 `lease_contracts`
--

CREATE TABLE `lease_contracts` (
  `contract_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `provider_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `monthly_fee` decimal(10,2) DEFAULT NULL,
  `total_payments` int(11) DEFAULT NULL,
  `status` enum('contract_active','offsetting','early_termination','expired','lost_to_competitor') NOT NULL,
  `credit_application_id` int(11) DEFAULT NULL,
  `special_case` enum('','補償') DEFAULT '',
  `payments_made` int(11) DEFAULT 0,
  `memo` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのリレーション `lease_contracts`:
--   `company_id`
--       `companies` -> `company_id`
--   `provider_id`
--       `lease_providers` -> `provider_id`
--   `credit_application_id`
--       `credit_applications` -> `application_id`
--

--
-- テーブルのデータのダンプ `lease_contracts`
--

INSERT INTO `lease_contracts` (`contract_id`, `company_id`, `provider_id`, `start_date`, `end_date`, `monthly_fee`, `total_payments`, `status`, `credit_application_id`, `special_case`, `payments_made`, `memo`) VALUES
(16, 40, 2, '2025-03-03', '2025-03-22', 23400.00, 84, 'contract_active', 11, '補償', 0, NULL),
(18, 40, 3, '2025-03-08', '2025-04-05', 23400.00, 84, 'contract_active', 12, '', 0, '1234568');

-- --------------------------------------------------------

--
-- テーブルの構造 `lease_providers`
--

CREATE TABLE `lease_providers` (
  `provider_id` int(11) NOT NULL,
  `provider_name` varchar(255) NOT NULL,
  `business_registration_number` varchar(13) DEFAULT NULL,
  `industry_type` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `postal_code` varchar(8) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのリレーション `lease_providers`:
--

--
-- テーブルのデータのダンプ `lease_providers`
--

INSERT INTO `lease_providers` (`provider_id`, `provider_name`, `business_registration_number`, `industry_type`, `address`, `postal_code`, `phone_number`, `email`, `created_at`, `updated_at`) VALUES
(2, '株式会社エヌシーくまもと', '3330001000558', '割賦購入あっせん', '熊本市中央区坪井2丁目2番42号', '860-0863', '096-343-1234', '', '2025-02-26 11:23:51', '2025-02-27 03:01:59'),
(3, '株式会社バルテックネットワークス', '1011101061978', '総合リース事業（割賦販売含む）', '東京都新宿区西新宿六丁目22番1号　新宿スクエアタワー8階', '163-1108', '03-6279-0341', 'info@smart-l.co.jp', '2025-02-27 03:04:06', '2025-02-27 03:04:06'),
(4, '九州ネクスト株式会社', '8290001012520', '割賦・総合リース業', '福岡県糟屋郡宇美町宇美東3丁目8番24号', '811-2125', '092-719-1400', '', '2025-02-27 03:06:41', '2025-02-27 03:06:41'),
(5, '株式会社九州リースサービス', '2290001012609', 'リース・割賦事業', '福岡市博多区博多駅前四丁目３番１８号\r\nサンライフセンタービル', '812-0011', '09-2431-2530', '', '2025-02-27 03:09:22', '2025-02-27 03:09:22');

-- --------------------------------------------------------

--
-- テーブルの構造 `maintenance_records`
--

CREATE TABLE `maintenance_records` (
  `maintenance_id` int(11) NOT NULL,
  `lease_device_id` int(11) DEFAULT NULL,
  `maintenance_date` date NOT NULL,
  `maintenance_type` enum('regular','emergency','installation','removal') NOT NULL,
  `technician_name` varchar(50) DEFAULT NULL,
  `maintenance_details` text DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのリレーション `maintenance_records`:
--   `lease_device_id`
--       `leased_equipment` -> `leased_equipment_id`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `request_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `status` enum('new','in_progress','completed') NOT NULL,
  `request_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのリレーション `maintenance_requests`:
--   `order_id`
--       `orders` -> `id`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_type` enum('新規','既存','旧顧客') NOT NULL,
  `order_date` date NOT NULL,
  `monthly_fee` int(11) NOT NULL,
  `total_payments` int(11) NOT NULL,
  `revision_total` int(11) DEFAULT NULL COMMENT '見直し合計 (税込)',
  `negotiation_status` enum('未設定','進行中','与信怪しい','工事前再説','工事後再説','工事前キャンセル','工事後キャンセル','書換完了','承認完了','承認後キャンセル') DEFAULT NULL,
  `construction_status` enum('待ち','与信待ち','残あり','完了','回収待ち','回収完了') DEFAULT NULL,
  `credit_status` enum('待ち','与信中','再与信中','与信OK','与信NG') DEFAULT NULL,
  `document_status` enum('待ち','準備中','変更中','発送済','受取済') DEFAULT NULL,
  `rewrite_status` enum('待ち','準備中','アポOK','残あり','完了') DEFAULT NULL,
  `seal_certificate_status` enum('不要','取得待','回収待','完了') DEFAULT NULL,
  `memo` varchar(255) DEFAULT NULL,
  `sales_rep_id` int(11) DEFAULT NULL,
  `sales_rep_id_2` int(11) DEFAULT NULL,
  `sales_rep_id_3` int(11) DEFAULT NULL,
  `sales_rep_id_4` int(11) DEFAULT NULL,
  `appointment_rep_id_1` int(11) DEFAULT NULL,
  `appointment_rep_id_2` int(11) DEFAULT NULL,
  `rewriting_person_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `shipping_status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのリレーション `orders`:
--   `sales_rep_id`
--       `employees` -> `employee_id`
--   `sales_rep_id_2`
--       `employees` -> `employee_id`
--   `sales_rep_id_3`
--       `employees` -> `employee_id`
--   `sales_rep_id_4`
--       `employees` -> `employee_id`
--   `appointment_rep_id_1`
--       `employees` -> `employee_id`
--   `appointment_rep_id_2`
--       `employees` -> `employee_id`
--   `rewriting_person_id`
--       `employees` -> `employee_id`
--   `company_id`
--       `companies` -> `company_id`
--

--
-- テーブルのデータのダンプ `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `customer_type`, `order_date`, `monthly_fee`, `total_payments`, `revision_total`, `negotiation_status`, `construction_status`, `credit_status`, `document_status`, `rewrite_status`, `seal_certificate_status`, `memo`, `sales_rep_id`, `sales_rep_id_2`, `sales_rep_id_3`, `sales_rep_id_4`, `appointment_rep_id_1`, `appointment_rep_id_2`, `rewriting_person_id`, `company_id`, `shipping_status`) VALUES
(17, '（有）木村設備', '既存', '2025-02-27', 37800, 84, 30000, '承認後キャンセル', '回収完了', '与信OK', NULL, 'アポOK', NULL, '工事3/123　', 7, 10, NULL, NULL, NULL, NULL, 3, NULL, '準備中'),
(18, '（株）一弘', '既存', '2025-02-28', 39800, 84, NULL, '進行中', '回収完了', '与信中', NULL, NULL, NULL, NULL, 4, 6, NULL, NULL, NULL, NULL, NULL, 39, NULL),
(19, '（有）春日浦溶接', '既存', '2025-02-28', 35800, 84, NULL, '進行中', '待ち', '与信中', NULL, NULL, NULL, NULL, 7, 10, NULL, NULL, NULL, NULL, NULL, 38, '発送済'),
(23, '（有）ライフクリエイト', '既存', '2025-03-05', 45800, 84, NULL, '進行中', '回収完了', NULL, NULL, NULL, NULL, NULL, 5, 11, NULL, NULL, 13, NULL, NULL, 19, NULL),
(27, '峰建設（株）', '新規', '2025-03-14', 25000, 84, NULL, '工事前キャンセル', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20, NULL),
(28, '（株）溝上建設', '新規', '2025-03-05', 3000, 84, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 23, '準備中'),
(31, '峰建設（株）', '新規', '2025-03-05', 36000, 84, NULL, NULL, '回収待ち', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20, NULL),
(32, '峰建設（株）', '新規', '2025-03-13', 2000, 84, NULL, '承認後キャンセル', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20, NULL),
(33, '（有）ライフクリエイト', '新規', '2025-03-05', 35000, 84, NULL, '承認後キャンセル', '回収完了', NULL, NULL, NULL, NULL, NULL, 15, NULL, NULL, 8, NULL, NULL, NULL, 19, NULL);

-- --------------------------------------------------------

--
-- テーブルの構造 `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `sales_rep_id` int(11) DEFAULT NULL COMMENT '担当者1',
  `mobile_revision` int(11) DEFAULT NULL COMMENT '携帯見直し金額 (税込)',
  `mobile_content` text DEFAULT NULL COMMENT '携帯内容',
  `sales_rep` varchar(255) DEFAULT NULL,
  `order_point` text DEFAULT NULL,
  `mobile_monitor_fee_a` int(11) DEFAULT NULL COMMENT '携帯モニター費A (税込)',
  `monitor_content_a` text DEFAULT NULL COMMENT 'A内容',
  `monitor_fee_b` int(11) DEFAULT NULL COMMENT 'モニター費B',
  `monitor_content_b` text DEFAULT NULL COMMENT 'B内容',
  `monitor_fee_c` int(11) DEFAULT NULL COMMENT 'モニター費C',
  `monitor_content_c` text DEFAULT NULL COMMENT 'C内容',
  `monitor_total` int(11) DEFAULT NULL COMMENT 'モニター合計',
  `service_item_1` int(11) DEFAULT NULL COMMENT 'サービス品1金額 (税抜)',
  `service_content_1` text DEFAULT NULL COMMENT '1内容',
  `service_item_2` int(11) DEFAULT NULL COMMENT 'サービス品2金額 (税抜)',
  `service_content_2` text DEFAULT NULL COMMENT '2内容',
  `service_item_3` int(11) DEFAULT NULL COMMENT 'サービス品3金額 (税抜)',
  `service_content_3` text DEFAULT NULL COMMENT '3内容',
  `service_total` int(11) DEFAULT NULL COMMENT 'サービス合計',
  `others` text DEFAULT NULL COMMENT 'その他'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのリレーション `order_details`:
--   `order_id`
--       `orders` -> `id`
--   `sales_rep_id`
--       `employees` -> `employee_id`
--

--
-- テーブルのデータのダンプ `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `sales_rep_id`, `mobile_revision`, `mobile_content`, `sales_rep`, `order_point`, `mobile_monitor_fee_a`, `monitor_content_a`, `monitor_fee_b`, `monitor_content_b`, `monitor_fee_c`, `monitor_content_c`, `monitor_total`, `service_item_1`, `service_content_1`, `service_item_2`, `service_content_2`, `service_item_3`, `service_content_3`, `service_total`, `others`) VALUES
(22, 17, NULL, NULL, NULL, '倉満', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- テーブルの構造 `sales_points`
--

CREATE TABLE `sales_points` (
  `point_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `points` int(11) NOT NULL,
  `rewrite_date` date DEFAULT NULL,
  `removal_points` int(11) DEFAULT NULL,
  `points_revision` int(11) DEFAULT NULL,
  `points_granted_month` varchar(7) DEFAULT NULL,
  `points_changed_month` varchar(7) DEFAULT NULL,
  `memo` text DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのリレーション `sales_points`:
--   `order_id`
--       `orders` -> `id`
--   `employee_id`
--       `employees` -> `employee_id`
--

--
-- テーブルのデータのダンプ `sales_points`
--

INSERT INTO `sales_points` (`point_id`, `order_id`, `employee_id`, `points`, `rewrite_date`, `removal_points`, `points_revision`, `points_granted_month`, `points_changed_month`, `memo`, `date_added`) VALUES
(31, 17, 7, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-05 10:23:27'),
(32, 17, 10, 2, NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-05 10:23:27'),
(41, 23, 5, 15, '2025-03-05', NULL, NULL, '2025-03', '2025-05', NULL, '2025-03-06 10:38:11'),
(42, 23, 11, 0, '2025-03-05', NULL, NULL, '2025-03', '2025-05', NULL, '2025-03-06 10:38:11'),
(43, 23, 13, 0, '2025-03-05', NULL, NULL, '2025-03', '2025-05', NULL, '2025-03-06 10:38:11'),
(44, 18, 4, 25, '2025-02-06', NULL, NULL, NULL, '2025-04', NULL, '2025-03-06 11:11:16'),
(45, 18, 6, 40, '2025-02-06', NULL, NULL, NULL, '2025-04', NULL, '2025-03-06 11:11:16');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`company_id`);

--
-- テーブルのインデックス `credit_applications`
--
ALTER TABLE `credit_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `provider_id` (`provider_id`),
  ADD KEY `contract_id` (`contract_id`),
  ADD KEY `order_id` (`order_id`);

--
-- テーブルのインデックス `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`);

--
-- テーブルのインデックス `equipment_master`
--
ALTER TABLE `equipment_master`
  ADD PRIMARY KEY (`equipment_id`);

--
-- テーブルのインデックス `installation_projects`
--
ALTER TABLE `installation_projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `contract_id` (`contract_id`);

--
-- テーブルのインデックス `installation_tasks`
--
ALTER TABLE `installation_tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `employee_id_1` (`employee_id_1`),
  ADD KEY `employee_id_2` (`employee_id_2`);

--
-- テーブルのインデックス `leased_equipment`
--
ALTER TABLE `leased_equipment`
  ADD PRIMARY KEY (`leased_equipment_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `contract_id` (`contract_id`);

--
-- テーブルのインデックス `lease_contracts`
--
ALTER TABLE `lease_contracts`
  ADD PRIMARY KEY (`contract_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `provider_id` (`provider_id`),
  ADD KEY `credit_application_id` (`credit_application_id`);

--
-- テーブルのインデックス `lease_providers`
--
ALTER TABLE `lease_providers`
  ADD PRIMARY KEY (`provider_id`);

--
-- テーブルのインデックス `maintenance_records`
--
ALTER TABLE `maintenance_records`
  ADD PRIMARY KEY (`maintenance_id`),
  ADD KEY `lease_device_id` (`lease_device_id`);

--
-- テーブルのインデックス `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `order_id` (`order_id`);

--
-- テーブルのインデックス `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_rep_id` (`sales_rep_id`),
  ADD KEY `sales_rep_id_2` (`sales_rep_id_2`),
  ADD KEY `sales_rep_id_3` (`sales_rep_id_3`),
  ADD KEY `sales_rep_id_4` (`sales_rep_id_4`),
  ADD KEY `appointment_rep_id_1` (`appointment_rep_id_1`),
  ADD KEY `appointment_rep_id_2` (`appointment_rep_id_2`),
  ADD KEY `rewriting_person_id` (`rewriting_person_id`),
  ADD KEY `company_id` (`company_id`);

--
-- テーブルのインデックス `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `sales_rep_id` (`sales_rep_id`);

--
-- テーブルのインデックス `sales_points`
--
ALTER TABLE `sales_points`
  ADD PRIMARY KEY (`point_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `sales_rep_id` (`employee_id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- テーブルの AUTO_INCREMENT `credit_applications`
--
ALTER TABLE `credit_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- テーブルの AUTO_INCREMENT `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- テーブルの AUTO_INCREMENT `equipment_master`
--
ALTER TABLE `equipment_master`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- テーブルの AUTO_INCREMENT `installation_projects`
--
ALTER TABLE `installation_projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- テーブルの AUTO_INCREMENT `installation_tasks`
--
ALTER TABLE `installation_tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- テーブルの AUTO_INCREMENT `leased_equipment`
--
ALTER TABLE `leased_equipment`
  MODIFY `leased_equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- テーブルの AUTO_INCREMENT `lease_contracts`
--
ALTER TABLE `lease_contracts`
  MODIFY `contract_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- テーブルの AUTO_INCREMENT `lease_providers`
--
ALTER TABLE `lease_providers`
  MODIFY `provider_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- テーブルの AUTO_INCREMENT `maintenance_records`
--
ALTER TABLE `maintenance_records`
  MODIFY `maintenance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- テーブルの AUTO_INCREMENT `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- テーブルの AUTO_INCREMENT `sales_points`
--
ALTER TABLE `sales_points`
  MODIFY `point_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `credit_applications`
--
ALTER TABLE `credit_applications`
  ADD CONSTRAINT `credit_applications_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`),
  ADD CONSTRAINT `credit_applications_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `lease_providers` (`provider_id`),
  ADD CONSTRAINT `credit_applications_ibfk_3` FOREIGN KEY (`contract_id`) REFERENCES `lease_contracts` (`contract_id`),
  ADD CONSTRAINT `credit_applications_ibfk_4` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `credit_applications_ibfk_5` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- テーブルの制約 `installation_projects`
--
ALTER TABLE `installation_projects`
  ADD CONSTRAINT `installation_projects_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `installation_projects_ibfk_2` FOREIGN KEY (`contract_id`) REFERENCES `lease_contracts` (`contract_id`);

--
-- テーブルの制約 `installation_tasks`
--
ALTER TABLE `installation_tasks`
  ADD CONSTRAINT `installation_tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `installation_projects` (`project_id`),
  ADD CONSTRAINT `installation_tasks_ibfk_2` FOREIGN KEY (`employee_id_1`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `installation_tasks_ibfk_3` FOREIGN KEY (`employee_id_2`) REFERENCES `employees` (`employee_id`);

--
-- テーブルの制約 `leased_equipment`
--
ALTER TABLE `leased_equipment`
  ADD CONSTRAINT `leased_equipment_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment_master` (`equipment_id`),
  ADD CONSTRAINT `leased_equipment_ibfk_2` FOREIGN KEY (`contract_id`) REFERENCES `lease_contracts` (`contract_id`);

--
-- テーブルの制約 `lease_contracts`
--
ALTER TABLE `lease_contracts`
  ADD CONSTRAINT `lease_contracts_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`),
  ADD CONSTRAINT `lease_contracts_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `lease_providers` (`provider_id`),
  ADD CONSTRAINT `lease_contracts_ibfk_3` FOREIGN KEY (`credit_application_id`) REFERENCES `credit_applications` (`application_id`);

--
-- テーブルの制約 `maintenance_records`
--
ALTER TABLE `maintenance_records`
  ADD CONSTRAINT `maintenance_records_ibfk_1` FOREIGN KEY (`lease_device_id`) REFERENCES `leased_equipment` (`leased_equipment_id`);

--
-- テーブルの制約 `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD CONSTRAINT `maintenance_requests_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- テーブルの制約 `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`sales_rep_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`sales_rep_id_2`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`sales_rep_id_3`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`sales_rep_id_4`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `orders_ibfk_5` FOREIGN KEY (`appointment_rep_id_1`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `orders_ibfk_6` FOREIGN KEY (`appointment_rep_id_2`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `orders_ibfk_7` FOREIGN KEY (`rewriting_person_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `orders_ibfk_8` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`);

--
-- テーブルの制約 `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`sales_rep_id`) REFERENCES `employees` (`employee_id`);

--
-- テーブルの制約 `sales_points`
--
ALTER TABLE `sales_points`
  ADD CONSTRAINT `sales_points_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `sales_points_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

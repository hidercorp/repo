-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Фев 03 2017 г., 21:10
-- Версия сервера: 5.5.52-38.3
-- Версия PHP: 5.6.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `ch42458_telegram`
--

-- --------------------------------------------------------

--
-- Структура таблицы `sel_category`
--

CREATE TABLE IF NOT EXISTS `sel_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `time` int(11) NOT NULL,
  `mesto` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `sel_couriers`
--

CREATE TABLE IF NOT EXISTS `sel_couriers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(15) NOT NULL,
  `password` varchar(20) NOT NULL,
  `role` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `sel_keys`
--

CREATE TABLE IF NOT EXISTS `sel_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` mediumtext,
  `id_cat` tinyint(4) NOT NULL,
  `id_subcat` tinyint(4) NOT NULL,
  `time` int(11) NOT NULL,
  `sale` tinyint(4) NOT NULL,
  `block` tinyint(4) NOT NULL,
  `block_user` int(11) NOT NULL,
  `block_time` int(11) NOT NULL,
  `role` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1288 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `sel_orders`
--

CREATE TABLE IF NOT EXISTS `sel_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_key` int(11) NOT NULL,
  `code` text,
  `chat` text,
  `id_subcat` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=896 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `sel_qiwi`
--

CREATE TABLE IF NOT EXISTS `sel_qiwi` (
  `iID` text,
  `sDate` text,
  `sTime` text,
  `dAmount` text,
  `iOpponentPhone` text,
  `sComment` text,
  `sStatus` text,
  `chat` text,
  `iAccount` text,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `sel_set_bot`
--

CREATE TABLE IF NOT EXISTS `sel_set_bot` (
  `token` text,
  `verification` int(11) NOT NULL,
  `block` int(11) NOT NULL,
  `proxy` text NOT NULL,
  `proxy_login` text NOT NULL,
  `proxy_pass` text NOT NULL,
  `url` text NOT NULL,
  `limits` int(11) NOT NULL,
  `title_page` varchar(15) NOT NULL,
  `text_page` text NOT NULL,
  `hold_profit_qiwi` varchar(30) NOT NULL DEFAULT '0',
  `profit_qiwi` varchar(30) NOT NULL DEFAULT '0',
  `on_off` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `sel_set_bot`
--

INSERT INTO `sel_set_bot` (`token`, `verification`, `block`, `proxy`, `proxy_login`, `proxy_pass`, `url`, `limits`, `title_page`, `text_page`, `hold_profit_qiwi`, `profit_qiwi`, `on_off`) VALUES
('token', 10, 20, '', '', '', 'http://', 1000000, '', '', '0', '0', 'on');

-- --------------------------------------------------------

--
-- Структура таблицы `sel_set_qiwi`
--

CREATE TABLE IF NOT EXISTS `sel_set_qiwi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(256) NOT NULL,
  `password` varchar(256) NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `sel_set_qiwi`
--

INSERT INTO `sel_set_qiwi` (`id`, `number`, `password`, `active`) VALUES
(1, '555', '555', 1),
(2, '555', '555', 0),
(3, '555', '555', 0),
(4, '555', '555', 0),
(5, '555', '555', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `sel_subcategory`
--

CREATE TABLE IF NOT EXISTS `sel_subcategory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cat` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `amount` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `mesto` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `sel_users`
--

CREATE TABLE IF NOT EXISTS `sel_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` text,
  `first_name` text,
  `last_name` text,
  `chat` text,
  `time` int(11) NOT NULL,
  `id_key` int(11) NOT NULL,
  `verification` int(11) NOT NULL,
  `pay_number` varchar(55) NOT NULL,
  `balans` int(11) NOT NULL,
  `ban` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

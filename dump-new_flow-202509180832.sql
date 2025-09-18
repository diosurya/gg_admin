-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: new_flow
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `blog_categories`
--

DROP TABLE IF EXISTS `blog_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_categories` (
  `id` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `parent_id` char(36) DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `level` int NOT NULL DEFAULT '0',
  `path` text,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `show_in_menu` tinyint(1) NOT NULL DEFAULT '1',
  `posts_count` int NOT NULL DEFAULT '0',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_blog_categories_parent` (`parent_id`),
  KEY `idx_blog_categories_slug` (`slug`),
  KEY `idx_blog_categories_status` (`status`),
  KEY `idx_blog_categories_level` (`level`),
  KEY `idx_blog_categories_sort_order` (`sort_order`),
  KEY `idx_blog_categories_featured` (`is_featured`),
  KEY `idx_blog_categories_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_blog_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_categories`
--

LOCK TABLES `blog_categories` WRITE;
/*!40000 ALTER TABLE `blog_categories` DISABLE KEYS */;
INSERT INTO `blog_categories` VALUES ('78feb5ca-8d46-11f0-ae4e-b42e99edc3be','Technology','technology',NULL,NULL,NULL,NULL,NULL,NULL,1,0,'1',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78feb9ac-8d46-11f0-ae4e-b42e99edc3be','Fashion','fashion',NULL,NULL,NULL,NULL,NULL,NULL,2,0,'2',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78febb47-8d46-11f0-ae4e-b42e99edc3be','Lifestyle','lifestyle',NULL,NULL,NULL,NULL,NULL,NULL,3,0,'3',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78febca6-8d46-11f0-ae4e-b42e99edc3be','Business','business',NULL,NULL,NULL,NULL,NULL,NULL,4,0,'4',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78ffd32f-8d46-11f0-ae4e-b42e99edc3be','Mobile Technology','mobile-tech',NULL,NULL,NULL,NULL,NULL,'78feb5ca-8d46-11f0-ae4e-b42e99edc3be',1,1,'78feb5ca-8d46-11f0-ae4e-b42e99edc3be/mobile-tech',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78ffd948-8d46-11f0-ae4e-b42e99edc3be','Web Development','web-development',NULL,NULL,NULL,NULL,NULL,'78feb5ca-8d46-11f0-ae4e-b42e99edc3be',2,1,'78feb5ca-8d46-11f0-ae4e-b42e99edc3be/web-development',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78ffdafb-8d46-11f0-ae4e-b42e99edc3be','AI & Machine Learning','ai-ml',NULL,NULL,NULL,NULL,NULL,'78feb5ca-8d46-11f0-ae4e-b42e99edc3be',3,1,'78feb5ca-8d46-11f0-ae4e-b42e99edc3be/ai-ml',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78ffdcc8-8d46-11f0-ae4e-b42e99edc3be','Style Guide','style-guide',NULL,NULL,NULL,NULL,NULL,'78feb9ac-8d46-11f0-ae4e-b42e99edc3be',1,1,'78feb9ac-8d46-11f0-ae4e-b42e99edc3be/style-guide',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78ffde52-8d46-11f0-ae4e-b42e99edc3be','Fashion Trends','fashion-trends',NULL,NULL,NULL,NULL,NULL,'78feb9ac-8d46-11f0-ae4e-b42e99edc3be',2,1,'78feb9ac-8d46-11f0-ae4e-b42e99edc3be/fashion-trends',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78ffdfcd-8d46-11f0-ae4e-b42e99edc3be','Health & Wellness','health-wellness',NULL,NULL,NULL,NULL,NULL,'78febb47-8d46-11f0-ae4e-b42e99edc3be',1,1,'78febb47-8d46-11f0-ae4e-b42e99edc3be/health-wellness',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78ffe1dd-8d46-11f0-ae4e-b42e99edc3be','Travel','travel',NULL,NULL,NULL,NULL,NULL,'78febb47-8d46-11f0-ae4e-b42e99edc3be',2,1,'78febb47-8d46-11f0-ae4e-b42e99edc3be/travel',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL);
/*!40000 ALTER TABLE `blog_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_category_media`
--

DROP TABLE IF EXISTS `blog_category_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_category_media` (
  `id` char(36) NOT NULL,
  `category_id` char(36) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `media_type` enum('image','video','document','audio') DEFAULT 'image',
  `sort_order` int NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_blog_category_media_category` (`category_id`),
  KEY `idx_blog_category_media_type` (`media_type`),
  KEY `idx_blog_category_media_featured` (`is_featured`),
  CONSTRAINT `fk_blog_category_media_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_category_media`
--

LOCK TABLES `blog_category_media` WRITE;
/*!40000 ALTER TABLE `blog_category_media` DISABLE KEYS */;
/*!40000 ALTER TABLE `blog_category_media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_category_relationships`
--

DROP TABLE IF EXISTS `blog_category_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_category_relationships` (
  `id` char(36) NOT NULL,
  `blog_id` char(36) NOT NULL,
  `category_id` char(36) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_blog_category` (`blog_id`,`category_id`),
  KEY `idx_blog_categories_blog` (`blog_id`),
  KEY `idx_blog_categories_category` (`category_id`),
  KEY `idx_blog_categories_primary` (`is_primary`),
  CONSTRAINT `fk_blog_categories_blog` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_blog_categories_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_category_relationships`
--

LOCK TABLES `blog_category_relationships` WRITE;
/*!40000 ALTER TABLE `blog_category_relationships` DISABLE KEYS */;
INSERT INTO `blog_category_relationships` VALUES ('790f7da4-8d46-11f0-ae4e-b42e99edc3be','79084f51-8d46-11f0-ae4e-b42e99edc3be','78ffd32f-8d46-11f0-ae4e-b42e99edc3be',1,'2025-09-09 06:30:26'),('790f8951-8d46-11f0-ae4e-b42e99edc3be','7908561e-8d46-11f0-ae4e-b42e99edc3be','78ffde52-8d46-11f0-ae4e-b42e99edc3be',1,'2025-09-09 06:30:26'),('790f8fc9-8d46-11f0-ae4e-b42e99edc3be','79085961-8d46-11f0-ae4e-b42e99edc3be','78ffdcc8-8d46-11f0-ae4e-b42e99edc3be',1,'2025-09-09 06:30:26'),('790f965e-8d46-11f0-ae4e-b42e99edc3be','79085c59-8d46-11f0-ae4e-b42e99edc3be','78ffd32f-8d46-11f0-ae4e-b42e99edc3be',1,'2025-09-09 06:30:26');
/*!40000 ALTER TABLE `blog_category_relationships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_category_seo`
--

DROP TABLE IF EXISTS `blog_category_seo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_category_seo` (
  `id` char(36) NOT NULL,
  `category_id` char(36) NOT NULL,
  `store_id` char(36) DEFAULT NULL,
  `meta_title` varchar(70) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `og_title` varchar(70) DEFAULT NULL,
  `og_description` varchar(160) DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `og_type` varchar(50) DEFAULT 'website',
  `twitter_card` varchar(50) DEFAULT 'summary',
  `twitter_title` varchar(70) DEFAULT NULL,
  `twitter_description` varchar(160) DEFAULT NULL,
  `twitter_image` varchar(255) DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `robots` varchar(50) NOT NULL DEFAULT 'index,follow',
  `schema_markup` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_blog_category_store_seo` (`category_id`,`store_id`),
  KEY `idx_blog_category_seo_category` (`category_id`),
  KEY `idx_blog_category_seo_store` (`store_id`),
  CONSTRAINT `fk_blog_category_seo_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_blog_category_seo_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_category_seo`
--

LOCK TABLES `blog_category_seo` WRITE;
/*!40000 ALTER TABLE `blog_category_seo` DISABLE KEYS */;
/*!40000 ALTER TABLE `blog_category_seo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_media`
--

DROP TABLE IF EXISTS `blog_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_media` (
  `id` char(36) NOT NULL,
  `blog_id` char(36) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `caption` text,
  `description` text,
  `media_type` enum('image','video','document','audio') DEFAULT 'image',
  `sort_order` int NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_blog_media_blog` (`blog_id`),
  KEY `idx_blog_media_type` (`media_type`),
  KEY `idx_blog_media_featured` (`is_featured`),
  CONSTRAINT `fk_blog_media_blog` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_media`
--

LOCK TABLES `blog_media` WRITE;
/*!40000 ALTER TABLE `blog_media` DISABLE KEYS */;
/*!40000 ALTER TABLE `blog_media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_seo`
--

DROP TABLE IF EXISTS `blog_seo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_seo` (
  `id` char(36) NOT NULL,
  `blog_id` char(36) NOT NULL,
  `meta_title` varchar(70) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `og_title` varchar(70) DEFAULT NULL,
  `og_description` varchar(160) DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `og_type` varchar(50) DEFAULT 'article',
  `twitter_card` varchar(50) DEFAULT 'summary',
  `twitter_title` varchar(70) DEFAULT NULL,
  `twitter_description` varchar(160) DEFAULT NULL,
  `twitter_image` varchar(255) DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `robots` varchar(50) NOT NULL DEFAULT 'index,follow',
  `schema_markup` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_blog_seo` (`blog_id`),
  CONSTRAINT `fk_blog_seo_blog` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_seo`
--

LOCK TABLES `blog_seo` WRITE;
/*!40000 ALTER TABLE `blog_seo` DISABLE KEYS */;
/*!40000 ALTER TABLE `blog_seo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_tags`
--

DROP TABLE IF EXISTS `blog_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_tags` (
  `id` char(36) NOT NULL,
  `blog_id` char(36) NOT NULL,
  `tag_id` char(36) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_blog_tag` (`blog_id`,`tag_id`),
  KEY `idx_blog_tags_blog` (`blog_id`),
  KEY `idx_blog_tags_tag` (`tag_id`),
  CONSTRAINT `fk_blog_tags_blog` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_blog_tags_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_tags`
--

LOCK TABLES `blog_tags` WRITE;
/*!40000 ALTER TABLE `blog_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `blog_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blogs`
--

DROP TABLE IF EXISTS `blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blogs` (
  `id` char(36) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `excerpt` text,
  `content` longtext,
  `featured_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','scheduled','archived') DEFAULT 'draft',
  `publish_at` timestamp NULL DEFAULT NULL,
  `store_id` char(36) NOT NULL,
  `author_id` char(36) NOT NULL,
  `views_count` int NOT NULL DEFAULT '0',
  `likes_count` int NOT NULL DEFAULT '0',
  `comments_count` int NOT NULL DEFAULT '0',
  `reading_time` int DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `allow_comments` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_blog_slug_store` (`slug`,`store_id`),
  KEY `idx_blogs_store` (`store_id`),
  KEY `idx_blogs_author` (`author_id`),
  KEY `idx_blogs_status` (`status`),
  KEY `idx_blogs_publish_at` (`publish_at`),
  KEY `idx_blogs_featured` (`is_featured`),
  KEY `idx_blogs_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_blogs_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_blogs_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blogs`
--

LOCK TABLES `blogs` WRITE;
/*!40000 ALTER TABLE `blogs` DISABLE KEYS */;
INSERT INTO `blogs` VALUES ('79084f51-8d46-11f0-ae4e-b42e99edc3be','iPhone 15 Pro Review: A Game Changer','iphone-15-pro-review-game-changer','Our comprehensive review of the latest iPhone 15 Pro','<p>The iPhone 15 Pro represents a significant leap forward in smartphone technology...</p>',NULL,'published',NULL,'78fb1cb0-8d46-11f0-ae4e-b42e99edc3be','78f96e83-8d46-11f0-ae4e-b42e99edc3be',0,0,0,NULL,1,1,'2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('7908561e-8d46-11f0-ae4e-b42e99edc3be','Top 10 Fashion Trends for 2024','top-10-fashion-trends-2024','Discover the hottest fashion trends that will dominate 2024','<p>Fashion is constantly evolving, and 2024 brings some exciting trends...</p>',NULL,'published',NULL,'78fb17ec-8d46-11f0-ae4e-b42e99edc3be','78f96e83-8d46-11f0-ae4e-b42e99edc3be',0,0,0,NULL,1,1,'2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('79085961-8d46-11f0-ae4e-b42e99edc3be','How to Choose the Perfect Running Shoes','how-choose-perfect-running-shoes','A complete guide to finding the right running shoes for your needs','<p>Choosing the right running shoes is crucial for performance and injury prevention...</p>',NULL,'published',NULL,'78fb17ec-8d46-11f0-ae4e-b42e99edc3be','78f96e83-8d46-11f0-ae4e-b42e99edc3be',0,0,0,NULL,0,1,'2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('79085c59-8d46-11f0-ae4e-b42e99edc3be','The Future of Mobile Technology','future-mobile-technology','Exploring upcoming innovations in mobile tech','<p>Mobile technology continues to advance at breakneck speed...</p>',NULL,'published',NULL,'78fb1cb0-8d46-11f0-ae4e-b42e99edc3be','78f96e83-8d46-11f0-ae4e-b42e99edc3be',0,0,0,NULL,0,1,'2025-09-09 06:30:26','2025-09-09 06:30:26',NULL);
/*!40000 ALTER TABLE `blogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brands` (
  `id` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `logo` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `country` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` int NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_brands_slug` (`slug`),
  KEY `idx_brands_status` (`status`),
  KEY `idx_brands_sort_order` (`sort_order`),
  KEY `idx_brands_featured` (`is_featured`),
  KEY `idx_brands_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES ('78fc0936-8d46-11f0-ae4e-b42e99edc3be','Apple','apple',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',0,0,'2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fc0d01-8d46-11f0-ae4e-b42e99edc3be','Samsung','samsung',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',0,0,'2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fc0e58-8d46-11f0-ae4e-b42e99edc3be','Nike','nike',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',0,0,'2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fc0f7a-8d46-11f0-ae4e-b42e99edc3be','Adidas','adidas',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',0,0,'2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fc10a1-8d46-11f0-ae4e-b42e99edc3be','Sony','sony',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',0,0,'2025-09-09 06:30:26','2025-09-09 06:30:26',NULL);
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000001_create_cache_table',1),(2,'0001_01_01_000002_create_jobs_table',1),(3,'2025_09_09_081410_create_sessions_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_categories` (
  `id` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `parent_id` char(36) DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `level` int NOT NULL DEFAULT '0',
  `path` text,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `show_in_menu` tinyint(1) NOT NULL DEFAULT '1',
  `products_count` int NOT NULL DEFAULT '0',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_categories_parent` (`parent_id`),
  KEY `idx_product_categories_slug` (`slug`),
  KEY `idx_product_categories_status` (`status`),
  KEY `idx_product_categories_level` (`level`),
  KEY `idx_product_categories_sort_order` (`sort_order`),
  KEY `idx_product_categories_featured` (`is_featured`),
  KEY `idx_product_categories_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_product_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
INSERT INTO `product_categories` VALUES ('78fcf691-8d46-11f0-ae4e-b42e99edc3be','Electronics','electronics',NULL,NULL,NULL,NULL,NULL,NULL,1,0,'1',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fcfc26-8d46-11f0-ae4e-b42e99edc3be','Clothing','clothing',NULL,NULL,NULL,NULL,NULL,NULL,2,0,'2',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fcfead-8d46-11f0-ae4e-b42e99edc3be','Sports','sports',NULL,NULL,NULL,NULL,NULL,NULL,3,0,'3',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fdce08-8d46-11f0-ae4e-b42e99edc3be','Smartphones','smartphones',NULL,NULL,NULL,NULL,NULL,'78fcf691-8d46-11f0-ae4e-b42e99edc3be',1,1,'78fcf691-8d46-11f0-ae4e-b42e99edc3be/smartphones',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fdd30d-8d46-11f0-ae4e-b42e99edc3be','Laptops','laptops',NULL,NULL,NULL,NULL,NULL,'78fcf691-8d46-11f0-ae4e-b42e99edc3be',2,1,'78fcf691-8d46-11f0-ae4e-b42e99edc3be/laptops',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fdd53f-8d46-11f0-ae4e-b42e99edc3be','Tablets','tablets',NULL,NULL,NULL,NULL,NULL,'78fcf691-8d46-11f0-ae4e-b42e99edc3be',3,1,'78fcf691-8d46-11f0-ae4e-b42e99edc3be/tablets',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fdd727-8d46-11f0-ae4e-b42e99edc3be','Men Clothing','men-clothing',NULL,NULL,NULL,NULL,NULL,'78fcfc26-8d46-11f0-ae4e-b42e99edc3be',1,1,'78fcfc26-8d46-11f0-ae4e-b42e99edc3be/men-clothing',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fdd961-8d46-11f0-ae4e-b42e99edc3be','Women Clothing','women-clothing',NULL,NULL,NULL,NULL,NULL,'78fcfc26-8d46-11f0-ae4e-b42e99edc3be',2,1,'78fcfc26-8d46-11f0-ae4e-b42e99edc3be/women-clothing',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fddb5d-8d46-11f0-ae4e-b42e99edc3be','Kids Clothing','kids-clothing',NULL,NULL,NULL,NULL,NULL,'78fcfc26-8d46-11f0-ae4e-b42e99edc3be',3,1,'78fcfc26-8d46-11f0-ae4e-b42e99edc3be/kids-clothing',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fddd43-8d46-11f0-ae4e-b42e99edc3be','Footwear','footwear',NULL,NULL,NULL,NULL,NULL,'78fcfead-8d46-11f0-ae4e-b42e99edc3be',1,1,'78fcfead-8d46-11f0-ae4e-b42e99edc3be/footwear',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fddf55-8d46-11f0-ae4e-b42e99edc3be','Sportswear','sportswear',NULL,NULL,NULL,NULL,NULL,'78fcfead-8d46-11f0-ae4e-b42e99edc3be',2,1,'78fcfead-8d46-11f0-ae4e-b42e99edc3be/sportswear',0,1,0,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL);
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_category_media`
--

DROP TABLE IF EXISTS `product_category_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_category_media` (
  `id` char(36) NOT NULL,
  `category_id` char(36) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `media_type` enum('image','video','document','audio') DEFAULT 'image',
  `sort_order` int NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_category_media_category` (`category_id`),
  KEY `idx_product_category_media_type` (`media_type`),
  KEY `idx_product_category_media_featured` (`is_featured`),
  CONSTRAINT `fk_product_category_media_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_category_media`
--

LOCK TABLES `product_category_media` WRITE;
/*!40000 ALTER TABLE `product_category_media` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_category_media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_category_relationships`
--

DROP TABLE IF EXISTS `product_category_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_category_relationships` (
  `id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `product_category_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_categories_product` (`product_id`),
  KEY `idx_product_categories_category` (`product_category_id`),
  KEY `idx_product_categories_primary` (`is_primary`),
  CONSTRAINT `fk_product_categories_category` FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_categories_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_category_relationships`
--

LOCK TABLES `product_category_relationships` WRITE;
/*!40000 ALTER TABLE `product_category_relationships` DISABLE KEYS */;
INSERT INTO `product_category_relationships` VALUES ('26473eb0-ea5d-41ec-8a63-bf6c403577b7','7b0469a4-cc8d-4e8c-81b7-0ecc2fcf8a34','78fcfead-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 08:32:16'),('317c38fc-b478-4fce-a46c-33332269780c','fb3baed5-d83a-4cde-9bb7-992e74f8351e','78fcfc26-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 00:38:17'),('342c0a7e-9476-4ff0-815d-6327575bd291','dedcd2cb-c8c5-4076-9484-3a3a431439c0','78fdd961-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 07:03:48'),('4c380284-61db-4e98-b9d1-e900707079d5','c970f939-c02b-4c64-a2b9-ac8d8cbe7166','78fcfc26-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 04:32:24'),('4d143001-9494-4332-a9d1-64a643f10004','c970f939-c02b-4c64-a2b9-ac8d8cbe7166','78fcf691-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 04:32:24'),('4d3c58cc-bd6a-4732-95bb-1f4073588c63','7b0469a4-cc8d-4e8c-81b7-0ecc2fcf8a34','78fcf691-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 08:32:16'),('4fa97873-416c-42ce-b3c8-b79228d1c22a','7b0469a4-cc8d-4e8c-81b7-0ecc2fcf8a34','78fdd53f-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 08:32:16'),('5528c38b-9bb0-48a9-8f7b-b1e6027a56fb','fb3baed5-d83a-4cde-9bb7-992e74f8351e','78fcf691-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 00:38:17'),('740ab1c8-8880-4756-88d5-b04193d2e0f8','7b0469a4-cc8d-4e8c-81b7-0ecc2fcf8a34','78fdce08-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 08:32:16'),('75e50e14-e8a6-4adc-ac12-5ef5ef826909','dedcd2cb-c8c5-4076-9484-3a3a431439c0','78fcfead-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 07:03:48'),('77cfb216-9651-42e1-8ca0-093375c9789c','c970f939-c02b-4c64-a2b9-ac8d8cbe7166','78fdce08-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 04:32:24'),('79072261-8d46-11f0-ae4e-b42e99edc3be','7901feee-8d46-11f0-ae4e-b42e99edc3be','78fdce08-8d46-11f0-ae4e-b42e99edc3be',1,'2025-09-09 06:30:26'),('790729fd-8d46-11f0-ae4e-b42e99edc3be','79020736-8d46-11f0-ae4e-b42e99edc3be','78fdce08-8d46-11f0-ae4e-b42e99edc3be',1,'2025-09-09 06:30:26'),('79072d4f-8d46-11f0-ae4e-b42e99edc3be','79020b0d-8d46-11f0-ae4e-b42e99edc3be','78fddd43-8d46-11f0-ae4e-b42e99edc3be',1,'2025-09-09 06:30:26'),('79073063-8d46-11f0-ae4e-b42e99edc3be','79020ea8-8d46-11f0-ae4e-b42e99edc3be','78fdd30d-8d46-11f0-ae4e-b42e99edc3be',1,'2025-09-09 06:30:26'),('790734d0-8d46-11f0-ae4e-b42e99edc3be','790212a1-8d46-11f0-ae4e-b42e99edc3be','78fddd43-8d46-11f0-ae4e-b42e99edc3be',1,'2025-09-09 06:30:26'),('8468076e-bccd-4b26-8272-7a5acb8dcfc4','7b0469a4-cc8d-4e8c-81b7-0ecc2fcf8a34','78fdd30d-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 08:32:16'),('98a06d21-d39a-4e9a-9fa1-4ca8e408f520','dedcd2cb-c8c5-4076-9484-3a3a431439c0','78fcf691-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 07:03:48'),('990f0eef-e662-44fa-9639-ce43ba89505d','dedcd2cb-c8c5-4076-9484-3a3a431439c0','78fdd53f-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 07:03:48'),('9c9518f9-4556-4e26-8775-33a7d697b825','dedcd2cb-c8c5-4076-9484-3a3a431439c0','78fddf55-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 07:03:48'),('9d405ab8-237c-4f4e-afe0-9a82eba32dc2','dedcd2cb-c8c5-4076-9484-3a3a431439c0','78fdd30d-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 07:03:48'),('b38642df-a055-4ab7-a54b-2c88669c6993','dedcd2cb-c8c5-4076-9484-3a3a431439c0','78fddd43-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 07:03:48'),('b50181b5-85d9-4e6e-bc25-062624577f06','fb3baed5-d83a-4cde-9bb7-992e74f8351e','78fdd961-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 00:38:17'),('b59ba888-9948-40f8-930e-182e53385f44','dedcd2cb-c8c5-4076-9484-3a3a431439c0','78fdd727-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 07:03:48'),('c06c9d7e-2694-4afb-a390-da5d2a8804cc','c970f939-c02b-4c64-a2b9-ac8d8cbe7166','78fdd727-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 04:32:24'),('c96e424b-230f-45fa-9eef-e4ad2b956ba3','fb3baed5-d83a-4cde-9bb7-992e74f8351e','78fdd30d-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 00:38:17'),('d1bebbfe-9ec2-444f-a1ec-14c92e2f34ac','dedcd2cb-c8c5-4076-9484-3a3a431439c0','78fcfc26-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 07:03:48'),('d3e51989-a703-404a-9f0b-1b696ddbbf4d','7b0469a4-cc8d-4e8c-81b7-0ecc2fcf8a34','78fddd43-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 08:32:16'),('e4e0f6e3-d83d-4607-9add-119ae8979899','7b0469a4-cc8d-4e8c-81b7-0ecc2fcf8a34','78fddf55-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 08:32:16'),('ec79b65b-b1a8-4c08-83c3-4a72e433517e','dedcd2cb-c8c5-4076-9484-3a3a431439c0','78fddb5d-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 07:03:48'),('f0fdbc8e-dd2f-4ad1-8135-917cc2be33d1','dedcd2cb-c8c5-4076-9484-3a3a431439c0','78fdce08-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 07:03:48'),('f7c9bd22-f89f-496d-af1c-b6c4e0a45b82','fb3baed5-d83a-4cde-9bb7-992e74f8351e','78fddd43-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 00:38:17'),('fa2a71f1-3f0c-47bb-9f15-5f5abfe877bb','c970f939-c02b-4c64-a2b9-ac8d8cbe7166','78fdd30d-8d46-11f0-ae4e-b42e99edc3be',0,'2025-09-17 04:32:24');
/*!40000 ALTER TABLE `product_category_relationships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_category_seo`
--

DROP TABLE IF EXISTS `product_category_seo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_category_seo` (
  `id` char(36) NOT NULL,
  `category_id` char(36) NOT NULL,
  `store_id` char(36) DEFAULT NULL,
  `meta_title` varchar(70) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `og_title` varchar(70) DEFAULT NULL,
  `og_description` varchar(160) DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `og_type` varchar(50) DEFAULT 'website',
  `twitter_card` varchar(50) DEFAULT 'summary',
  `twitter_title` varchar(70) DEFAULT NULL,
  `twitter_description` varchar(160) DEFAULT NULL,
  `twitter_image` varchar(255) DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `robots` varchar(50) NOT NULL DEFAULT 'index,follow',
  `schema_markup` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_product_category_store_seo` (`category_id`,`store_id`),
  KEY `idx_product_category_seo_category` (`category_id`),
  KEY `idx_product_category_seo_store` (`store_id`),
  CONSTRAINT `fk_product_category_seo_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_category_seo_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_category_seo`
--

LOCK TABLES `product_category_seo` WRITE;
/*!40000 ALTER TABLE `product_category_seo` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_category_seo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_media`
--

DROP TABLE IF EXISTS `product_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_media` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_variant_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint unsigned NOT NULL,
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `media_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'image',
  `sort_order` int NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `is_temporary` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_media`
--

LOCK TABLES `product_media` WRITE;
/*!40000 ALTER TABLE `product_media` DISABLE KEYS */;
INSERT INTO `product_media` VALUES ('12010baa-dfa1-45c6-b3be-c5fe93db3e6d','dedcd2cb-c8c5-4076-9484-3a3a431439c0','a3d2e5bc-17d7-4bfa-b60e-139e21ebd183','/storage/products/1758117756_NXGNFdah1m.png','btglng.png','1758117756_NXGNFdah1m.png','png',130454,'image/png','image',1,0,1,0,'2025-09-17 07:03:48','2025-09-17 07:03:48'),('2e4be90f-972f-401f-b5c8-cf3b62520f77','7b0469a4-cc8d-4e8c-81b7-0ecc2fcf8a34',NULL,'/storage/products/1758123116_lGFvVS6dJm.png','btglng.png','1758123116_lGFvVS6dJm.png','png',130454,'image/png','image',1,0,1,0,'2025-09-17 08:32:16','2025-09-17 08:32:16'),('33e0b502-a962-4967-a0c0-a6fc143fc5ce','dedcd2cb-c8c5-4076-9484-3a3a431439c0',NULL,'/storage/products/1758117783_z0dApGfITF.jpg','Bangun Bangsa Pouch 25x20 (note! hanya buat hitung HPP).jpg','1758117783_z0dApGfITF.jpg','jpg',1549194,'image/jpeg','image',1,1,1,0,'2025-09-17 07:03:48','2025-09-17 07:03:48'),('49f74b73-b292-4507-a4be-6a5f7eb2ba4e','dedcd2cb-c8c5-4076-9484-3a3a431439c0','ba291332-0beb-402c-87a7-35f64fbe976a','/storage/products/1758117709_hE1nMszThI.png','btglng.png','1758117709_hE1nMszThI.png','png',130454,'image/png','image',1,0,1,0,'2025-09-17 07:03:48','2025-09-17 07:03:48');
/*!40000 ALTER TABLE `product_media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_seo`
--

DROP TABLE IF EXISTS `product_seo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_seo` (
  `id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `store_id` char(36) DEFAULT NULL,
  `meta_title` varchar(70) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `og_title` varchar(70) DEFAULT NULL,
  `og_description` varchar(160) DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `og_type` varchar(50) DEFAULT 'product',
  `twitter_card` varchar(50) DEFAULT 'summary',
  `twitter_title` varchar(70) DEFAULT NULL,
  `twitter_description` varchar(160) DEFAULT NULL,
  `twitter_image` varchar(255) DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `robots` varchar(50) NOT NULL DEFAULT 'index,follow',
  `schema_markup` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_product_store_seo` (`product_id`,`store_id`),
  KEY `idx_product_seo_product` (`product_id`),
  KEY `idx_product_seo_store` (`store_id`),
  CONSTRAINT `fk_product_seo_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_seo_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_seo`
--

LOCK TABLES `product_seo` WRITE;
/*!40000 ALTER TABLE `product_seo` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_seo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_stores`
--

DROP TABLE IF EXISTS `product_stores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_stores` (
  `id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `store_id` char(36) NOT NULL,
  `display_name` varchar(200) DEFAULT NULL,
  `short_description` text,
  `custom_description` longtext,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `featured_in_store` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_product_store` (`product_id`,`store_id`),
  KEY `idx_product_stores_product` (`product_id`),
  KEY `idx_product_stores_store` (`store_id`),
  KEY `idx_product_stores_active` (`is_active`),
  KEY `idx_product_stores_featured` (`featured_in_store`),
  CONSTRAINT `fk_product_stores_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_stores_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_stores`
--

LOCK TABLES `product_stores` WRITE;
/*!40000 ALTER TABLE `product_stores` DISABLE KEYS */;
INSERT INTO `product_stores` VALUES ('79062967-8d46-11f0-ae4e-b42e99edc3be','7901feee-8d46-11f0-ae4e-b42e99edc3be','78fb1cb0-8d46-11f0-ae4e-b42e99edc3be',NULL,NULL,NULL,1,1,0,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('79062e96-8d46-11f0-ae4e-b42e99edc3be','7901feee-8d46-11f0-ae4e-b42e99edc3be','78fb0c59-8d46-11f0-ae4e-b42e99edc3be',NULL,NULL,NULL,1,0,0,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('790630f2-8d46-11f0-ae4e-b42e99edc3be','79020736-8d46-11f0-ae4e-b42e99edc3be','78fb1cb0-8d46-11f0-ae4e-b42e99edc3be',NULL,NULL,NULL,1,1,0,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('790632d2-8d46-11f0-ae4e-b42e99edc3be','79020736-8d46-11f0-ae4e-b42e99edc3be','78fb0c59-8d46-11f0-ae4e-b42e99edc3be',NULL,NULL,NULL,1,0,0,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('790634a0-8d46-11f0-ae4e-b42e99edc3be','79020b0d-8d46-11f0-ae4e-b42e99edc3be','78fb17ec-8d46-11f0-ae4e-b42e99edc3be',NULL,NULL,NULL,1,1,0,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('79063687-8d46-11f0-ae4e-b42e99edc3be','79020b0d-8d46-11f0-ae4e-b42e99edc3be','78fb0c59-8d46-11f0-ae4e-b42e99edc3be',NULL,NULL,NULL,1,0,0,'2025-09-09 06:30:26','2025-09-09 06:30:26');
/*!40000 ALTER TABLE `product_stores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_tags`
--

DROP TABLE IF EXISTS `product_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_tags` (
  `id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `tag_id` char(36) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_product_tag` (`product_id`,`tag_id`),
  KEY `idx_product_tags_product` (`product_id`),
  KEY `idx_product_tags_tag` (`tag_id`),
  CONSTRAINT `fk_product_tags_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_tags_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_tags`
--

LOCK TABLES `product_tags` WRITE;
/*!40000 ALTER TABLE `product_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variants` (
  `id` char(36) NOT NULL,
  `store_id` char(36) DEFAULT NULL,
  `product_id` char(36) NOT NULL,
  `product_media_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `sku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `attribute_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `attribute_value` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `description` text,
  `weight` decimal(8,3) DEFAULT NULL,
  `dimensions_length` decimal(8,2) DEFAULT NULL,
  `dimensions_width` decimal(8,2) DEFAULT NULL,
  `dimensions_height` decimal(8,2) DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `price` decimal(15,2) DEFAULT NULL,
  `sale_price` decimal(15,2) DEFAULT NULL,
  `cost_price` decimal(15,2) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `stock_quantity` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_variants_sku` (`sku`),
  KEY `idx_variants_product` (`product_id`),
  KEY `idx_variants_status` (`status`),
  KEY `idx_variants_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES ('296f4283-be81-4f9c-a252-4dbf11216302',NULL,'fb3baed5-d83a-4cde-9bb7-992e74f8351e',NULL,'2346726424','Warma','Biru',NULL,NULL,NULL,NULL,NULL,0,10000.00,NULL,NULL,'active','2025-09-17 00:38:17','2025-09-17 00:38:17',NULL,'Tidak Ada Kaca',100),('369104b6-0884-4218-846b-6296d144ac88','78fb1cb0-8d46-11f0-ae4e-b42e99edc3be','7b0469a4-cc8d-4e8c-81b7-0ecc2fcf8a34',NULL,'20398402348','Warna','Peach',NULL,NULL,NULL,NULL,NULL,0,100000.00,NULL,NULL,'active','2025-09-17 08:32:16','2025-09-17 08:32:16',NULL,'Tanpa Kaca',80000),('3c4930bc-a272-4c11-be3c-d58cbbda1bc9',NULL,'fb3baed5-d83a-4cde-9bb7-992e74f8351e',NULL,'2346726421','Warna','Merah',NULL,NULL,NULL,NULL,NULL,0,10000.00,NULL,NULL,'active','2025-09-17 00:38:17','2025-09-17 00:38:17',NULL,'Tidak Ada Kaca',100),('4b6f99aa-d6d9-4b37-a02a-a8727e2ccb6c',NULL,'9fc42dda-d89d-480c-98ee-3bc60b6da056',NULL,'2346726428','Warna','Merah',NULL,NULL,NULL,NULL,NULL,0,100000.00,NULL,NULL,'active','2025-09-11 01:03:07','2025-09-11 01:03:07',NULL,'Kaca',100),('7903298e-8d46-11f0-ae4e-b42e99edc3be',NULL,'7901feee-8d46-11f0-ae4e-b42e99edc3be',NULL,'IPH15PRO-128-BLK','iPhone 15 Pro 128GB Black',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL,NULL,NULL),('79032e08-8d46-11f0-ae4e-b42e99edc3be',NULL,'7901feee-8d46-11f0-ae4e-b42e99edc3be',NULL,'IPH15PRO-256-BLK','iPhone 15 Pro 256GB Black',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL,NULL,NULL),('79032fbf-8d46-11f0-ae4e-b42e99edc3be',NULL,'7901feee-8d46-11f0-ae4e-b42e99edc3be',NULL,'IPH15PRO-128-WHT','iPhone 15 Pro 128GB White',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL,NULL,NULL),('79033101-8d46-11f0-ae4e-b42e99edc3be',NULL,'79020736-8d46-11f0-ae4e-b42e99edc3be',NULL,'SGS24-128-BLK','Galaxy S24 128GB Black',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL,NULL,NULL),('79033238-8d46-11f0-ae4e-b42e99edc3be',NULL,'79020736-8d46-11f0-ae4e-b42e99edc3be',NULL,'SGS24-256-BLK','Galaxy S24 256GB Black',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL,NULL,NULL),('7903337c-8d46-11f0-ae4e-b42e99edc3be',NULL,'79020b0d-8d46-11f0-ae4e-b42e99edc3be',NULL,'NAM270-US9-BLK','Air Max 270 US 9 Black',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL,NULL,NULL),('790334a2-8d46-11f0-ae4e-b42e99edc3be',NULL,'79020b0d-8d46-11f0-ae4e-b42e99edc3be',NULL,'NAM270-US10-BLK','Air Max 270 US 10 Black',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL,NULL,NULL),('790335c0-8d46-11f0-ae4e-b42e99edc3be',NULL,'79020b0d-8d46-11f0-ae4e-b42e99edc3be',NULL,'NAM270-US9-WHT','Air Max 270 US 9 White',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'active','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL,NULL,NULL),('7b99552f-0831-41f3-bb9e-1b4c3dd33e6a',NULL,'c970f939-c02b-4c64-a2b9-ac8d8cbe7166',NULL,'23827897','Warna','Biru',NULL,NULL,NULL,NULL,NULL,0,50000.00,NULL,NULL,'active','2025-09-17 04:32:24','2025-09-17 04:32:24',NULL,'Tanpa Kaca',100),('7d1dcf40-3221-498b-b9a7-d8cf11329b3c',NULL,'c970f939-c02b-4c64-a2b9-ac8d8cbe7166',NULL,'789797978','Warna','Putih',NULL,NULL,NULL,NULL,NULL,0,60000.00,NULL,NULL,'active','2025-09-17 04:32:24','2025-09-17 04:32:24',NULL,'Tanpa Kaca',55000),('a3d2e5bc-17d7-4bfa-b60e-139e21ebd183','78fb17ec-8d46-11f0-ae4e-b42e99edc3be','dedcd2cb-c8c5-4076-9484-3a3a431439c0','12010baa-dfa1-45c6-b3be-c5fe93db3e6d','7384467343643','Warna','Gold',NULL,NULL,NULL,NULL,NULL,0,60000.00,NULL,NULL,'active','2025-09-17 07:03:48','2025-09-17 07:03:48',NULL,'Tanpa Kaca',103),('ba291332-0beb-402c-87a7-35f64fbe976a','78fb1cb0-8d46-11f0-ae4e-b42e99edc3be','dedcd2cb-c8c5-4076-9484-3a3a431439c0','49f74b73-b292-4507-a4be-6a5f7eb2ba4e','7324632786','Warna','Putih',NULL,NULL,NULL,NULL,NULL,0,50000.00,NULL,NULL,'active','2025-09-17 07:03:48','2025-09-17 07:03:48',NULL,'Tanpa Kaca',100),('fcbc0539-89d9-4dc9-a9f6-67a46c30f873',NULL,'9fc42dda-d89d-480c-98ee-3bc60b6da056',NULL,'23721932139','Warna','Biru',NULL,NULL,NULL,NULL,NULL,0,100000.00,NULL,NULL,'active','2025-09-11 01:03:07','2025-09-11 01:03:07',NULL,'Kaca',100);
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` char(36) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `short_description` text,
  `description` longtext,
  `sku` varchar(100) DEFAULT NULL,
  `brand_id` char(36) DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'simple',
  `status` enum('draft','published','archived','out_of_stock') DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `weight` decimal(8,3) DEFAULT NULL,
  `dimensions_length` decimal(8,2) DEFAULT NULL,
  `dimensions_width` decimal(8,2) DEFAULT NULL,
  `dimensions_height` decimal(8,2) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `sale_price` decimal(15,2) DEFAULT NULL,
  `cost_price` decimal(15,2) DEFAULT NULL,
  `barcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `requires_shipping` tinyint(1) NOT NULL DEFAULT '1',
  `length` decimal(8,3) DEFAULT NULL,
  `is_digital` tinyint(1) NOT NULL DEFAULT '0',
  `download_limit` int DEFAULT NULL,
  `download_expiry` int DEFAULT NULL,
  `minimum_quantity` bigint DEFAULT NULL,
  `external_url` varchar(500) DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `model` varchar(150) DEFAULT NULL,
  `views_count` int NOT NULL DEFAULT '0',
  `sales_count` int NOT NULL DEFAULT '0',
  `track_stock` tinyint(1) DEFAULT '1',
  `created_by` char(36) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `width` decimal(8,3) DEFAULT NULL,
  `height` decimal(8,3) DEFAULT NULL,
  `tax_status` varchar(100) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text,
  `meta_keywords` text,
  `og_title` varchar(255) DEFAULT NULL,
  `og_description` text,
  `og_image` varchar(255) DEFAULT NULL,
  `og_type` varchar(100) DEFAULT NULL,
  `robots` varchar(100) DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `schema_markup` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_products_slug` (`slug`),
  UNIQUE KEY `unique_products_sku` (`sku`),
  UNIQUE KEY `products_barcode_unique` (`barcode`),
  KEY `idx_products_status` (`status`),
  KEY `idx_products_featured` (`is_featured`),
  KEY `idx_products_type` (`type`),
  KEY `idx_products_brand` (`brand_id`),
  KEY `idx_products_created_by` (`created_by`),
  KEY `idx_products_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_products_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_products_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES ('7901feee-8d46-11f0-ae4e-b42e99edc3be','iPhone 15 Pro','iphone-15-pro','Latest iPhone with pro features','The iPhone 15 Pro features a titanium design, A17 Pro chip, and advanced camera system.','IPH15PRO001','78fc0936-8d46-11f0-ae4e-b42e99edc3be','variable','published',1,NULL,NULL,NULL,NULL,0.00,NULL,NULL,NULL,1,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,1,'78f966df-8d46-11f0-ae4e-b42e99edc3be','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),('79020736-8d46-11f0-ae4e-b42e99edc3be','Samsung Galaxy S24','samsung-galaxy-s24','Flagship Android smartphone','Samsung Galaxy S24 with advanced AI features and stunning display.','SGS24001','78fc0d01-8d46-11f0-ae4e-b42e99edc3be','variable','published',1,NULL,NULL,NULL,NULL,0.00,NULL,NULL,NULL,1,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,1,'78f966df-8d46-11f0-ae4e-b42e99edc3be','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),('79020b0d-8d46-11f0-ae4e-b42e99edc3be','Nike Air Max 270','nike-air-max-270','Comfortable running shoes','Nike Air Max 270 with maximum air cushioning and modern design.','NAM270001','78fc0e58-8d46-11f0-ae4e-b42e99edc3be','variable','published',0,NULL,NULL,NULL,NULL,0.00,NULL,NULL,NULL,1,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,1,'78f966df-8d46-11f0-ae4e-b42e99edc3be','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),('79020ea8-8d46-11f0-ae4e-b42e99edc3be','MacBook Pro 14\"','macbook-pro-14','Professional laptop for creators','MacBook Pro 14-inch with M3 chip, perfect for professional work.','MBP14M3001','78fc0936-8d46-11f0-ae4e-b42e99edc3be','variable','published',1,NULL,NULL,NULL,NULL,0.00,NULL,NULL,NULL,1,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,1,'78f966df-8d46-11f0-ae4e-b42e99edc3be','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),('790212a1-8d46-11f0-ae4e-b42e99edc3be','Adidas Ultraboost 22','adidas-ultraboost-22','Premium running shoes','Adidas Ultraboost 22 with responsive cushioning and energy return.','AUB22001','78fc0f7a-8d46-11f0-ae4e-b42e99edc3be','variable','published',0,NULL,NULL,NULL,NULL,0.00,NULL,NULL,NULL,1,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,1,'78f966df-8d46-11f0-ae4e-b42e99edc3be','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),('7b0469a4-cc8d-4e8c-81b7-0ecc2fcf8a34','Porduct Test Five','porduct-test-five','Porduct Test Five','Porduct Test Five','PORDUCT-TEST-FIVE-4606','78fc0e58-8d46-11f0-ae4e-b42e99edc3be','Type Product','published',0,NULL,NULL,NULL,NULL,100000.00,90000.00,80000.00,'23982394u238942',1,NULL,0,NULL,NULL,1,NULL,NULL,'Product Model',0,0,1,'78f966df-8d46-11f0-ae4e-b42e99edc3be','2025-09-17 08:32:15','2025-09-17 08:32:15',NULL,NULL,NULL,'taxable','Porduct Test Five','Porduct Test Five','Porduct Test Five',NULL,NULL,NULL,NULL,NULL,NULL,NULL),('9fc42dda-d89d-480c-98ee-3bc60b6da056','Product Test','product-test','Product Test','<p>Buat para Bunda sekarang ngga perlu bingung lagi cari sarung bantal yang aman, gambarnya lucu dengan harga murah.</p><p>Karena Yuureco punya produk sarung bantal anak yang tidak hanya punya motif karakter yang lucu, tapi juga menggunakan kain katun yang pastinya aman buat kulit anak.</p><h2><span class=\"page_speed_1747117743\">Pilihan Motif Lucu yang Disukai Anak</span></h2><ul><li>Labubu â€“ Terbuat dari katun lokal yang lembut dan adem, cocok untuk anak aktif yang mudah berkeringat. &nbsp; &nbsp;</li><li>Buzz Lightyear â€“ Karakter kartun favorit ini hadir dengan bahan katun lokal , nyaman dan mudah dicuci. &nbsp; &nbsp;</li><li>Kelinci â€“ Hadir dalam katun Jepang yang lebih halus, cocok untuk anak dengan kulit sensitif. &nbsp; &nbsp;</li></ul><p><br>Gambar Hewan â€“ Motif lucu penuh warna dari katun Jepang berkualitas tinggi yang awet dan adem dipakai.</p><h2><span class=\"page_speed_1747117743\">Bahan Bantal Guling Anak &nbsp;</span></h2><ul><li>Katun Jepang (lebih halus, adem dan berkualitas) &nbsp;</li><li>Katun Lokal (lembut, ringan dan nyaman) &nbsp; &nbsp;</li></ul><p><br>Ukuran Sarung Bantal: 41 x 60 cm &nbsp;(Menggunakan model envelop â€“ praktis dan rapi saat dipasang)</p><p>Sarung bantal anak ini dibuat dari kain katun pilihan yang aman dan tidak panas saat dipakai. Jadi tidur si kecil lebih nyenyak dan bebas dari gerah.</p><p>Motif karakter dan hewan lucu di sarung bantal ini juga bisa jadi media belajar visual yang menyenangkan buat anak.</p><p>Sarung bantal anak dari Yuureco ini tidak mudah luntur atau rusak meski sering dicuci.</p><p>Produk sarung bantal anak karakter motif lucu dari Yuureco ini sudah tersedia di berbagai Marketplace kesayangan Kamu seperti Shopee, Tiktok, Tokopedia hingga Lazada.</p><p>Dapatkan harga sarung bantal anak termurah hanya di yuureco.co.id</p>','PRODUCT-TEST-4278','78fc0f7a-8d46-11f0-ae4e-b42e99edc3be','Product Test','published',1,10.000,NULL,NULL,NULL,100000.00,90000.00,80000.00,'123123213131',1,20.000,0,NULL,NULL,100,NULL,NULL,'Product Model',0,0,1,NULL,'2025-09-11 01:03:07','2025-09-12 03:35:59',NULL,30.000,40.000,'taxable','Product Test','Product Test','Product Test',NULL,NULL,NULL,NULL,NULL,NULL,NULL),('c970f939-c02b-4c64-a2b9-ac8d8cbe7166','Test Product 1','test-product-1','Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500','<h2 style=\"margin-top: 0px; margin-right: 0px; margin-left: 0px; padding: 0px; font-family: DauphinPlain; line-height: 24px;\">What is Lorem Ipsum?</h2><p style=\"margin-right: 0px; margin-bottom: 15px; margin-left: 0px; padding: 0px; text-align: justify; font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px;\"><strong style=\"margin: 0px; padding: 0px;\">Lorem Ipsum</strong>&nbsp;is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p><h2 style=\"margin-top: 0px; margin-right: 0px; margin-left: 0px; padding: 0px; font-family: DauphinPlain; line-height: 24px;\">Why do we use it?</h2><p style=\"margin-right: 0px; margin-bottom: 15px; margin-left: 0px; padding: 0px; text-align: justify; font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px;\">It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</p><p style=\"margin-right: 0px; margin-bottom: 15px; margin-left: 0px; padding: 0px; text-align: justify; font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px;\"><br></p><h2 style=\"margin-top: 0px; margin-right: 0px; margin-left: 0px; padding: 0px; font-family: DauphinPlain; line-height: 24px;\">Where does it come from?</h2><p style=\"margin-right: 0px; margin-bottom: 15px; margin-left: 0px; padding: 0px; text-align: justify; font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px;\">Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32.</p>','TEST-PRODUCT-1-1655','78fc0f7a-8d46-11f0-ae4e-b42e99edc3be','Type Product','published',1,NULL,NULL,NULL,NULL,60000.00,55000.00,50000.00,'82398u83929382782',1,NULL,0,NULL,NULL,100,NULL,NULL,'Product Model',0,0,0,NULL,'2025-09-17 04:32:24','2025-09-17 04:32:24',NULL,NULL,NULL,'taxable','Test Product 1','Test Product 1','Test Product 1',NULL,NULL,NULL,NULL,NULL,NULL,NULL),('dedcd2cb-c8c5-4076-9484-3a3a431439c0','Test Product Four','test-product-four','Test Product Four','Test Product Four','TEST-PRODUCT-FOUR-4253','78fc0f7a-8d46-11f0-ae4e-b42e99edc3be','Type Product','published',1,10.000,NULL,NULL,NULL,60000.00,50000.00,40000.00,'82398u8392938278223231',1,20.000,0,NULL,NULL,100,NULL,NULL,'Product Model',0,0,0,NULL,'2025-09-17 07:03:48','2025-09-17 07:03:48',NULL,30.000,40.000,'taxable','Test Product Four','Test Product Four','Test Product Four',NULL,NULL,NULL,NULL,NULL,NULL,NULL),('fb3baed5-d83a-4cde-9bb7-992e74f8351e','Product Variant','product-variant','Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC','<h2 style=\"margin-top: 0px; margin-right: 0px; margin-left: 0px; padding: 0px; font-family: DauphinPlain; line-height: 24px;\">What is Lorem Ipsum?</h2><p style=\"margin-right: 0px; margin-bottom: 15px; margin-left: 0px; padding: 0px; text-align: justify; font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px;\"><strong style=\"margin: 0px; padding: 0px;\">Lorem Ipsum</strong>&nbsp;is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p><h2 style=\"margin-top: 0px; margin-right: 0px; margin-left: 0px; padding: 0px; font-family: DauphinPlain; line-height: 24px;\">Why do we use it?</h2><p style=\"margin-right: 0px; margin-bottom: 15px; margin-left: 0px; padding: 0px; text-align: justify; font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px;\">It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</p><p style=\"margin-right: 0px; margin-bottom: 15px; margin-left: 0px; padding: 0px; text-align: justify; font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px;\"><br></p><h2 style=\"margin-top: 0px; margin-right: 0px; margin-left: 0px; padding: 0px; font-family: DauphinPlain; line-height: 24px;\">Where does it come from?</h2><p style=\"margin-right: 0px; margin-bottom: 15px; margin-left: 0px; padding: 0px; text-align: justify; font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px;\">Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32.</p>','PRODUCT-VARIANT-7460','78fc10a1-8d46-11f0-ae4e-b42e99edc3be','Type Products','published',1,NULL,NULL,NULL,NULL,10000.00,10000.00,10000.00,'239482942094230947',1,NULL,0,NULL,NULL,1000,NULL,NULL,'Model Products',0,0,0,NULL,'2025-09-17 00:38:17','2025-09-17 00:38:17',NULL,NULL,NULL,'taxable','Product Variant','Product Variant','Product Variant',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('YirzSwbXP3f2TNS9MmWrcAKdZw7A5i3Pfl20gk5F','78f966df-8d46-11f0-ae4e-b42e99edc3be','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTo2OntzOjY6Il90b2tlbiI7czo0MDoiRGxBTlczdk1sak9maEJGa2lHcW5ZZHZsSlowb0w0SHJ5RmxkeDAyYSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Nzg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9wcm9kdWN0cy83YjA0NjlhNC1jYzhkLTRlOGMtODFiNy0wZWNjMmZjZjhhMzQvZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtzOjM2OiI3OGY5NjZkZi04ZDQ2LTExZjAtYWU0ZS1iNDJlOTllZGMzYmUiO3M6MTM6ImFkbWluX3VzZXJfaWQiO3M6MzY6Ijc4Zjk2NmRmLThkNDYtMTFmMC1hZTRlLWI0MmU5OWVkYzNiZSI7czoxMDoiYWRtaW5fdXNlciI7TzoxNToiQXBwXE1vZGVsc1xVc2VyIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJteXNxbCI7czo4OiIAKgB0YWJsZSI7czo1OiJ1c2VycyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjIwOntzOjI6ImlkIjtzOjM2OiI3OGY5NjZkZi04ZDQ2LTExZjAtYWU0ZS1iNDJlOTllZGMzYmUiO3M6ODoidXNlcm5hbWUiO3M6NToiYWRtaW4iO3M6NToiZW1haWwiO3M6MTU6ImFkbWluQGFkbWluLmNvbSI7czoxNzoiZW1haWxfdmVyaWZpZWRfYXQiO047czo4OiJwYXNzd29yZCI7czo2MDoiJDJ5JDEyJGZrVFVldENuUUxKRUQ4a3hSdE4ua3VhVUh6cTV5OUdMZlJjRmJGVW5QOVV0MHh0Mno2YmV5IjtzOjEwOiJmaXJzdF9uYW1lIjtzOjM6IkRpbyI7czo5OiJsYXN0X25hbWUiO3M6NToiUHV0cmEiO3M6NToicGhvbmUiO047czo2OiJhdmF0YXIiO047czoxMzoiZGF0ZV9vZl9iaXJ0aCI7TjtzOjY6ImdlbmRlciI7TjtzOjY6InN0YXR1cyI7czo2OiJhY3RpdmUiO3M6NDoicm9sZSI7czo1OiJhZG1pbiI7czoxMzoibGFzdF9sb2dpbl9hdCI7czoxOToiMjAyNS0wOS0xMCAwNDoxMjowMiI7czoxNDoicmVtZW1iZXJfdG9rZW4iO047czoxNzoidHdvX2ZhY3Rvcl9zZWNyZXQiO047czoyNToidHdvX2ZhY3Rvcl9yZWNvdmVyeV9jb2RlcyI7TjtzOjEwOiJjcmVhdGVkX2F0IjtzOjE5OiIyMDI1LTA5LTA5IDEzOjMwOjI2IjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjE5OiIyMDI1LTA5LTEyIDExOjA2OjUyIjtzOjEwOiJkZWxldGVkX2F0IjtOO31zOjExOiIAKgBvcmlnaW5hbCI7YToyMDp7czoyOiJpZCI7czozNjoiNzhmOTY2ZGYtOGQ0Ni0xMWYwLWFlNGUtYjQyZTk5ZWRjM2JlIjtzOjg6InVzZXJuYW1lIjtzOjU6ImFkbWluIjtzOjU6ImVtYWlsIjtzOjE1OiJhZG1pbkBhZG1pbi5jb20iO3M6MTc6ImVtYWlsX3ZlcmlmaWVkX2F0IjtOO3M6ODoicGFzc3dvcmQiO3M6NjA6IiQyeSQxMiRma1RVZXRDblFMSkVEOGt4UnROLmt1YVVIenE1eTlHTGZSY0ZiRlVuUDlVdDB4dDJ6NmJleSI7czoxMDoiZmlyc3RfbmFtZSI7czozOiJEaW8iO3M6OToibGFzdF9uYW1lIjtzOjU6IlB1dHJhIjtzOjU6InBob25lIjtOO3M6NjoiYXZhdGFyIjtOO3M6MTM6ImRhdGVfb2ZfYmlydGgiO047czo2OiJnZW5kZXIiO047czo2OiJzdGF0dXMiO3M6NjoiYWN0aXZlIjtzOjQ6InJvbGUiO3M6NToiYWRtaW4iO3M6MTM6Imxhc3RfbG9naW5fYXQiO3M6MTk6IjIwMjUtMDktMTAgMDQ6MTI6MDIiO3M6MTQ6InJlbWVtYmVyX3Rva2VuIjtOO3M6MTc6InR3b19mYWN0b3Jfc2VjcmV0IjtOO3M6MjU6InR3b19mYWN0b3JfcmVjb3ZlcnlfY29kZXMiO047czoxMDoiY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wOS0wOSAxMzozMDoyNiI7czoxMDoidXBkYXRlZF9hdCI7czoxOToiMjAyNS0wOS0xMiAxMTowNjo1MiI7czoxMDoiZGVsZXRlZF9hdCI7Tjt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6Mjp7czoxNzoiZW1haWxfdmVyaWZpZWRfYXQiO3M6ODoiZGF0ZXRpbWUiO3M6ODoicGFzc3dvcmQiO3M6NjoiaGFzaGVkIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YToyOntpOjA7czo4OiJwYXNzd29yZCI7aToxO3M6MTQ6InJlbWVtYmVyX3Rva2VuIjt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6Njp7aTowO3M6MjoiaWQiO2k6MTtzOjQ6Im5hbWUiO2k6MjtzOjU6ImVtYWlsIjtpOjM7czo4OiJwYXNzd29yZCI7aTo0O3M6NDoicm9sZSI7aTo1O3M6Njoic3RhdHVzIjt9czoxMDoiACoAZ3VhcmRlZCI7YToxOntpOjA7czoxOiIqIjt9czoxOToiACoAYXV0aFBhc3N3b3JkTmFtZSI7czo4OiJwYXNzd29yZCI7czoyMDoiACoAcmVtZW1iZXJUb2tlbk5hbWUiO3M6MTQ6InJlbWVtYmVyX3Rva2VuIjt9fQ==',1758123712);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stores`
--

DROP TABLE IF EXISTS `stores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stores` (
  `id` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `domain` varchar(100) DEFAULT NULL,
  `description` text,
  `logo` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `timezone` varchar(50) NOT NULL DEFAULT 'UTC',
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `language` varchar(5) NOT NULL DEFAULT 'en',
  `tax_rate` decimal(5,2) DEFAULT '0.00',
  `shipping_fee` decimal(10,2) DEFAULT '0.00',
  `free_shipping_threshold` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive','maintenance') NOT NULL DEFAULT 'active',
  `owner_id` char(36) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_stores_slug` (`slug`),
  UNIQUE KEY `unique_stores_domain` (`domain`),
  KEY `idx_stores_status` (`status`),
  KEY `idx_stores_owner` (`owner_id`),
  KEY `idx_stores_country` (`country`),
  KEY `idx_stores_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_stores_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stores`
--

LOCK TABLES `stores` WRITE;
/*!40000 ALTER TABLE `stores` DISABLE KEYS */;
INSERT INTO `stores` VALUES ('78fb0c59-8d46-11f0-ae4e-b42e99edc3be','Main Store','main-store','main.example.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'UTC','USD','en',0.00,0.00,NULL,'active','78f966df-8d46-11f0-ae4e-b42e99edc3be','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fb17ec-8d46-11f0-ae4e-b42e99edc3be','Fashion Store','fashion-store','fashion.example.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'UTC','USD','en',0.00,0.00,NULL,'active','78f96c57-8d46-11f0-ae4e-b42e99edc3be','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL),('78fb1cb0-8d46-11f0-ae4e-b42e99edc3be','Electronics Store','electronics-store','electronics.example.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'UTC','USD','en',0.00,0.00,NULL,'active','78f96c57-8d46-11f0-ae4e-b42e99edc3be','2025-09-09 06:30:26','2025-09-09 06:30:26',NULL);
/*!40000 ALTER TABLE `stores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `id` char(36) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` text,
  `color` varchar(7) DEFAULT NULL,
  `type` enum('product','blog','general') DEFAULT 'general',
  `usage_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tags_slug` (`slug`),
  KEY `idx_tags_type` (`type`),
  KEY `idx_tags_usage_count` (`usage_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` VALUES ('7900ef65-8d46-11f0-ae4e-b42e99edc3be','New Arrival','new-arrival',NULL,NULL,'product',0,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('7900f473-8d46-11f0-ae4e-b42e99edc3be','Best Seller','best-seller',NULL,NULL,'product',0,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('7900f61a-8d46-11f0-ae4e-b42e99edc3be','On Sale','on-sale',NULL,NULL,'product',0,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('7900f764-8d46-11f0-ae4e-b42e99edc3be','Premium','premium',NULL,NULL,'product',0,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('7900f8ae-8d46-11f0-ae4e-b42e99edc3be','Trending','trending',NULL,NULL,'blog',0,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('7900f9ec-8d46-11f0-ae4e-b42e99edc3be','Tutorial','tutorial',NULL,NULL,'blog',0,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('7900fb15-8d46-11f0-ae4e-b42e99edc3be','News','news',NULL,NULL,'blog',0,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('7900fc3c-8d46-11f0-ae4e-b42e99edc3be','Review','review',NULL,NULL,'blog',0,'2025-09-09 06:30:26','2025-09-09 06:30:26');
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `role` enum('admin','manager','customer','author') NOT NULL DEFAULT 'customer',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `two_factor_secret` text,
  `two_factor_recovery_codes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_users_email` (`email`),
  UNIQUE KEY `unique_users_username` (`username`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('78f966df-8d46-11f0-ae4e-b42e99edc3be','admin','admin@admin.com',NULL,'$2y$12$fkTUetCnQLJED8kxRtN.kuaUHzq5y9GLfRcFbFUnP9Ut0xt2z6bey','Dio','Putra',NULL,NULL,NULL,NULL,'active','admin','2025-09-17 08:27:53',NULL,NULL,NULL,'2025-09-09 06:30:26','2025-09-17 08:27:53',NULL),('78f96c57-8d46-11f0-ae4e-b42e99edc3be','manager1','manager@example.com',NULL,'$2y$12$fkTUetCnQLJED8kxRtN.kuaUHzq5y9GLfRcFbFUnP9Ut0xt2z6bey','Store','Manager',NULL,NULL,NULL,NULL,'active','manager',NULL,NULL,NULL,NULL,'2025-09-09 06:30:26','2025-09-10 04:10:43',NULL),('78f96e83-8d46-11f0-ae4e-b42e99edc3be','author1','author@example.com',NULL,'$2y$12$fkTUetCnQLJED8kxRtN.kuaUHzq5y9GLfRcFbFUnP9Ut0xt2z6bey','Blog','Author',NULL,NULL,NULL,NULL,'active','author',NULL,NULL,NULL,NULL,'2025-09-09 06:30:26','2025-09-10 04:10:46',NULL),('78f96fc4-8d46-11f0-ae4e-b42e99edc3be','customer1','customer@example.com',NULL,'$2y$12$fkTUetCnQLJED8kxRtN.kuaUHzq5y9GLfRcFbFUnP9Ut0xt2z6bey','John','Doe',NULL,NULL,NULL,NULL,'active','customer',NULL,NULL,NULL,NULL,'2025-09-09 06:30:26','2025-09-10 04:10:49',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `variant_attributes`
--

DROP TABLE IF EXISTS `variant_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `variant_attributes` (
  `id` char(36) NOT NULL,
  `variant_id` char(36) NOT NULL,
  `attribute_name` varchar(50) NOT NULL,
  `attribute_value` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_variant_attributes_variant` (`variant_id`),
  KEY `idx_variant_attributes_name` (`attribute_name`),
  CONSTRAINT `fk_variant_attributes_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variant_attributes`
--

LOCK TABLES `variant_attributes` WRITE;
/*!40000 ALTER TABLE `variant_attributes` DISABLE KEYS */;
/*!40000 ALTER TABLE `variant_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `variant_stores`
--

DROP TABLE IF EXISTS `variant_stores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `variant_stores` (
  `id` char(36) NOT NULL,
  `variant_id` char(36) NOT NULL,
  `store_id` char(36) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `min_stock_level` int NOT NULL DEFAULT '0',
  `max_stock_level` int DEFAULT NULL,
  `manage_stock` tinyint(1) NOT NULL DEFAULT '1',
  `stock_status` enum('in_stock','out_of_stock','on_backorder') DEFAULT 'in_stock',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_variant_store` (`variant_id`,`store_id`),
  KEY `idx_variant_stores_variant` (`variant_id`),
  KEY `idx_variant_stores_store` (`store_id`),
  KEY `idx_variant_stores_active` (`is_active`),
  KEY `idx_variant_stores_stock` (`stock_status`),
  CONSTRAINT `fk_variant_stores_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_variant_stores_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variant_stores`
--

LOCK TABLES `variant_stores` WRITE;
/*!40000 ALTER TABLE `variant_stores` DISABLE KEYS */;
INSERT INTO `variant_stores` VALUES ('7904847b-8d46-11f0-ae4e-b42e99edc3be','7903298e-8d46-11f0-ae4e-b42e99edc3be','78fb1cb0-8d46-11f0-ae4e-b42e99edc3be',999.00,899.00,NULL,50,0,NULL,1,'in_stock',1,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('79048c11-8d46-11f0-ae4e-b42e99edc3be','79032e08-8d46-11f0-ae4e-b42e99edc3be','78fb1cb0-8d46-11f0-ae4e-b42e99edc3be',1199.00,NULL,NULL,30,0,NULL,1,'in_stock',1,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('79049022-8d46-11f0-ae4e-b42e99edc3be','79032fbf-8d46-11f0-ae4e-b42e99edc3be','78fb1cb0-8d46-11f0-ae4e-b42e99edc3be',999.00,899.00,NULL,25,0,NULL,1,'in_stock',1,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('790493df-8d46-11f0-ae4e-b42e99edc3be','79033101-8d46-11f0-ae4e-b42e99edc3be','78fb1cb0-8d46-11f0-ae4e-b42e99edc3be',799.00,749.00,NULL,40,0,NULL,1,'in_stock',1,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('7904978f-8d46-11f0-ae4e-b42e99edc3be','79033238-8d46-11f0-ae4e-b42e99edc3be','78fb1cb0-8d46-11f0-ae4e-b42e99edc3be',899.00,NULL,NULL,35,0,NULL,1,'in_stock',1,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('790529d3-8d46-11f0-ae4e-b42e99edc3be','7903337c-8d46-11f0-ae4e-b42e99edc3be','78fb17ec-8d46-11f0-ae4e-b42e99edc3be',150.00,129.99,NULL,20,0,NULL,1,'in_stock',1,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('7905316a-8d46-11f0-ae4e-b42e99edc3be','790334a2-8d46-11f0-ae4e-b42e99edc3be','78fb17ec-8d46-11f0-ae4e-b42e99edc3be',150.00,129.99,NULL,18,0,NULL,1,'in_stock',1,'2025-09-09 06:30:26','2025-09-09 06:30:26'),('790534b0-8d46-11f0-ae4e-b42e99edc3be','790335c0-8d46-11f0-ae4e-b42e99edc3be','78fb17ec-8d46-11f0-ae4e-b42e99edc3be',150.00,NULL,NULL,15,0,NULL,1,'in_stock',1,'2025-09-09 06:30:26','2025-09-09 06:30:26');
/*!40000 ALTER TABLE `variant_stores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'new_flow'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-18  8:32:37

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 14 oct. 2026 à 11:07
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `portfolio`
--

-- --------------------------------------------------------

--
-- Structure de la table `achievements`
--

CREATE TABLE `achievements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`title`)),
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`description`)),
  `achievement_date` date DEFAULT NULL,
  `url` varchar(191) DEFAULT NULL,
  `image_path` varchar(191) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(191) NOT NULL,
  `subject_type` varchar(191) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `articles`
--

CREATE TABLE `articles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `article_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`title`)),
  `slug` varchar(191) NOT NULL,
  `excerpt` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`excerpt`)),
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`content`)),
  `image_path` varchar(191) DEFAULT NULL,
  `author_name` varchar(191) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'draft',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `source_url` varchar(2048) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `article_categories`
--

CREATE TABLE `article_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`name`)),
  `slug` varchar(191) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `article_comments`
--

CREATE TABLE `article_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `author_name` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `article_likes`
--

CREATE TABLE `article_likes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `visitor_hash` varchar(64) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `article_tag`
--

CREATE TABLE `article_tag` (
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `tag_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(191) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('portfolio-cache-356a192b7913b04c54574d18c28d46e6395428ab', 'i:1;', 1791564971),
('portfolio-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1791564971;', 1791564971);

-- --------------------------------------------------------

--
-- Structure de la table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(191) NOT NULL,
  `owner` varchar(191) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `certifications`
--

CREATE TABLE `certifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`title`)),
  `issuer` varchar(191) DEFAULT NULL,
  `issuer_en` varchar(191) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `credential_id` varchar(191) DEFAULT NULL,
  `url` varchar(191) DEFAULT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`description`)),
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image_path` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `subject` varchar(191) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `educations`
--

CREATE TABLE `educations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `school` varchar(191) NOT NULL,
  `degree` varchar(191) NOT NULL,
  `degree_en` varchar(191) DEFAULT NULL,
  `field` varchar(191) DEFAULT NULL,
  `field_en` varchar(191) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`description`)),
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image_path` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `educations`
--

INSERT INTO `educations` (`id`, `profile_id`, `school`, `degree`, `degree_en`, `field`, `field_en`, `start_date`, `end_date`, `description`, `sort_order`, `published`, `created_at`, `updated_at`, `image_path`) VALUES
(1, 1, 'Université Aube Nouvelle', 'Master en Système Informatique', 'Master\'s Degree in Information Systems', NULL, NULL, '2024-01-01', '2026-01-01', NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54', NULL),
(2, 1, 'FORCE-N', 'Cybersécurité', 'Cybersecurity', NULL, NULL, '2024-01-01', '2024-01-01', NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54', NULL),
(3, 1, 'Université Aube Nouvelle', 'Licence en Génie logiciel', 'Bachelor\'s Degree in Software Engineering', NULL, NULL, '2023-01-01', '2024-01-01', NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `experiences`
--

CREATE TABLE `experiences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `company` varchar(191) NOT NULL,
  `company_en` varchar(191) DEFAULT NULL,
  `position` varchar(191) NOT NULL,
  `position_en` varchar(191) DEFAULT NULL,
  `location` varchar(191) DEFAULT NULL,
  `contract_type` varchar(191) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`description`)),
  `responsibilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`responsibilities`)),
  `achievements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`achievements`)),
  `technologies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`technologies`)),
  `logo_path` varchar(191) DEFAULT NULL,
  `website` varchar(191) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image_path` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `experiences`
--

INSERT INTO `experiences` (`id`, `profile_id`, `company`, `company_en`, `position`, `position_en`, `location`, `contract_type`, `start_date`, `end_date`, `is_current`, `description`, `responsibilities`, `achievements`, `technologies`, `logo_path`, `website`, `sort_order`, `published`, `created_at`, `updated_at`, `image_path`) VALUES
(1, 1, 'GROUPE LOGICIEL & SERVICES', 'GROUPE LOGICIEL & SERVICES', 'Consultant (Sage , MedSys, GMAO), formateur, requeteur sql', 'Consultant (Sage, MedSys, CMMS), trainer, SQL query developer', NULL, NULL, '2025-02-01', NULL, 1, NULL, '{\"fr\":[],\"en\":[]}', '{\"fr\":[],\"en\":[]}', NULL, NULL, NULL, 0, 1, '2026-10-09 16:32:17', '2026-10-09 16:32:17', NULL),
(2, 1, 'DJAGO INTERNATIONAL', 'DJAGO INTERNATIONAL', 'Consultant (Sage , MedSys, GMAO), formateur', 'Consultant (Sage, MedSys, CMMS), trainer', NULL, NULL, '2024-07-01', '2025-02-01', 0, NULL, '{\"fr\":[],\"en\":[]}', '{\"fr\":[],\"en\":[]}', NULL, NULL, NULL, 0, 1, '2026-10-09 16:32:17', '2026-10-09 16:32:17', NULL),
(3, 1, 'Total Innovation', 'Total Innovation', 'Technicien, Développeur web et mobile, Web Design, formateur en', 'Technician, Web and Mobile Developer, Web Designer, Instructor in', NULL, NULL, '2023-01-01', '2024-06-01', 0, '{\"fr\":\"Maintenance informatique\",\"en\":\"IT Maintenance\"}', '{\"fr\":[],\"en\":[]}', '{\"fr\":[],\"en\":[]}', NULL, NULL, NULL, 0, 1, '2026-10-09 16:32:17', '2026-10-09 16:32:17', NULL),
(4, 1, 'DAAN (ASECNA BF)', 'DAAN (ASECNA BF)', 'Stage de 04 mois au service informatique chargé de la gestion et', 'A 4-month internship in the IT department responsible for managing and', NULL, NULL, '2023-01-01', '2023-12-31', 0, '{\"fr\":\"maintenance du parc informatique de la Direction et de l\\u2019A\\u00e9roport internationale de Ouagadougou\",\"en\":\"Maintenance of the IT infrastructure for the Directorate and Ouagadougou International Airport\"}', '{\"fr\":[],\"en\":[]}', '{\"fr\":[],\"en\":[]}', NULL, NULL, NULL, 0, 1, '2026-10-09 16:32:17', '2026-10-09 16:32:17', NULL),
(5, 1, 'Afric Consultin Group', 'Afric Consultin Group', 'Développeur web et technicien de l’entreprise durant l’année 2022', 'Web developer and company technician in 2022', NULL, NULL, '2022-01-01', '2022-12-31', 0, NULL, '{\"fr\":[],\"en\":[]}', '{\"fr\":[],\"en\":[]}', NULL, NULL, NULL, 0, 1, '2026-10-09 16:32:17', '2026-10-09 16:33:54', NULL),
(6, 1, 'Afric Consultin Group', 'Afric Consultin Group', 'Stage de 6 mois en développement web et technicien de l’entreprise', '6-month internship in web development and as a company technician', NULL, NULL, '2021-07-01', '2022-01-01', 0, NULL, '{\"fr\":[],\"en\":[]}', '{\"fr\":[],\"en\":[]}', NULL, NULL, NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(191) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `languages`
--

CREATE TABLE `languages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`name`)),
  `locale` varchar(191) DEFAULT NULL,
  `level` varchar(191) DEFAULT NULL,
  `level_en` varchar(191) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image_path` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `languages`
--

INSERT INTO `languages` (`id`, `name`, `locale`, `level`, `level_en`, `sort_order`, `published`, `created_at`, `updated_at`, `image_path`) VALUES
(1, '{\"fr\":\"Mor\\u00e9e\",\"en\":\"Mor\\u00e9e\"}', NULL, NULL, NULL, 1, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54', NULL),
(2, '{\"fr\":\"Fran\\u00e7ais\",\"en\":\"French\"}', 'fr', NULL, NULL, 3, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54', NULL),
(3, '{\"fr\":\"Anglais\",\"en\":\"English\"}', 'en', NULL, NULL, 6, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `media`
--

CREATE TABLE `media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) DEFAULT NULL,
  `file_name` varchar(191) NOT NULL,
  `file_path` varchar(191) NOT NULL,
  `mime_type` varchar(191) NOT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `category` varchar(191) NOT NULL DEFAULT 'general',
  `alt_text` varchar(191) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `media`
--

INSERT INTO `media` (`id`, `title`, `file_name`, `file_path`, `mime_type`, `file_size`, `category`, `alt_text`, `sort_order`, `is_public`, `created_at`, `updated_at`) VALUES
(1, '4', '4.png', 'homepage-slides/vo7iA6k6exK5X10bkVFNd3LNrRa7kGzsPwCKc3Wa.png', 'image/png', 19808, 'homepage_slider', '4', 1, 1, '2026-10-09 18:21:41', '2026-10-09 18:21:41'),
(2, 'WhatsApp Image 2026-10-04 at 12.01.35', 'WhatsApp Image 2026-10-04 at 12.01.35.jpeg', 'homepage-slides/h2LABvBxRNqmpEwyUNU6cl5CpHfLiyMCS06ttlwM.jpg', 'image/jpeg', 150881, 'homepage_slider', 'WhatsApp Image 2026-10-04 at 12.01.35', 2, 1, '2026-10-09 18:22:39', '2026-10-09 18:22:39'),
(4, 'ChatGPT Image 4 oct. 2026, 18_36_34', 'ChatGPT Image 4 oct. 2026, 18_36_34.png', 'homepage-slides/jBScE3Pg6n9LiOzSeuLDcwPkmWMVRRsgpCkrMmyb.png', 'image/png', 1999644, 'homepage_slider', 'ChatGPT Image 4 oct. 2026, 18_36_34', 3, 1, '2026-10-09 18:23:01', '2026-10-09 18:23:01');

-- --------------------------------------------------------

--
-- Structure de la table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `label` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`label`)),
  `url` varchar(191) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_10_02_000001_create_portfolio_tables', 1),
(5, '2026_10_02_152829_create_permission_tables', 1),
(6, '2026_10_02_180000_extend_resume_template_library', 1),
(7, '2026_10_02_183000_create_professional_references_table', 1),
(8, '2026_10_04_000001_add_english_profile_translations', 1),
(9, '2026_10_04_000002_add_english_labels_to_public_entries', 1),
(10, '2026_10_04_000003_add_source_url_to_articles', 1),
(11, '2026_10_04_000004_create_article_interactions', 1),
(12, '2026_10_05_000001_add_image_path_to_skills_table', 1),
(13, '2026_10_05_130001_add_images_to_portfolio_content', 1),
(14, '2026_10_05_130002_create_achievements_table', 1),
(15, '2026_10_07_000001_add_submitted_by_client_to_testimonials_table', 1),
(16, '2026_10_09_000001_add_english_cv_fields', 2);

-- --------------------------------------------------------

--
-- Structure de la table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1);

-- --------------------------------------------------------

--
-- Structure de la table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `professional_references`
--

CREATE TABLE `professional_references` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `role` varchar(191) DEFAULT NULL,
  `role_en` varchar(191) DEFAULT NULL,
  `company` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image_path` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `professional_references`
--

INSERT INTO `professional_references` (`id`, `profile_id`, `name`, `role`, `role_en`, `company`, `email`, `phone`, `sort_order`, `published`, `created_at`, `updated_at`, `image_path`) VALUES
(1, 1, 'Louis NABALOUM', 'Dev', 'Dev', 'Total Avengers', NULL, '+226 56 92 06 71', 1, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54', NULL),
(2, 1, 'Ïmael OUIYA', 'Ingénieur Cloud', 'Cloud Engineer', 'TI', NULL, '+226 60 18 54 25', 3, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54', NULL),
(3, 1, 'Fatah Oumar OUEDRAOGO', 'IT', 'IT', 'Burkina Mining Compagny', NULL, '+226 70 19 50 32', 6, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `profiles`
--

CREATE TABLE `profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(191) DEFAULT NULL,
  `last_name` varchar(191) DEFAULT NULL,
  `title` varchar(191) DEFAULT NULL,
  `title_en` varchar(191) DEFAULT NULL,
  `subtitle` varchar(191) DEFAULT NULL,
  `subtitle_en` varchar(191) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `summary_en` text DEFAULT NULL,
  `bio` longtext DEFAULT NULL,
  `bio_en` longtext DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `location` varchar(191) DEFAULT NULL,
  `country` varchar(191) DEFAULT NULL,
  `availability` varchar(191) DEFAULT NULL,
  `professional_status` varchar(191) DEFAULT NULL,
  `years_experience` int(11) NOT NULL DEFAULT 0,
  `website` varchar(191) DEFAULT NULL,
  `linkedin` varchar(191) DEFAULT NULL,
  `github` varchar(191) DEFAULT NULL,
  `avatar_path` varchar(191) DEFAULT NULL,
  `cover_path` varchar(191) DEFAULT NULL,
  `cv_path` varchar(191) DEFAULT NULL,
  `custom_titles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_titles`)),
  `primary_skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`primary_skills`)),
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `profiles`
--

INSERT INTO `profiles` (`id`, `user_id`, `first_name`, `last_name`, `title`, `title_en`, `subtitle`, `subtitle_en`, `summary`, `summary_en`, `bio`, `bio_en`, `email`, `phone`, `location`, `country`, `availability`, `professional_status`, `years_experience`, `website`, `linkedin`, `github`, `avatar_path`, `cover_path`, `cv_path`, `custom_titles`, `primary_skills`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 1, 'Alain', 'GUIGMA', 'IT Maintenance & Cybersecurity', 'IT Maintenance & Cybersecurity', 'Sage X3 / ERP / SI', 'Sage X3 / ERP / IT systems', 'Passionné de la Sécurité Informatique, de la programmation web et Mobile, je suis créatif, curieux, rigoureux, déterminé, sociable et immédiatement disponible . Je fais de l\'autodidacte.', 'I am passionate about cybersecurity, web and mobile programming, and I am creative, curious, thorough, determined, sociable, and available immediately. I am self-taught.', NULL, NULL, 'guigmaa507@gmail.com', '+226 66 41 07 68 / 71 40 62 88', 'Songnaaba Secteur 29', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'cv-imports/MC2eIoG1zif8B1hYnPQ3ei52OSeuQeUN61Uvrt2y.pdf', NULL, NULL, 1, '2026-10-09 16:16:25', '2026-10-09 16:33:54');

-- --------------------------------------------------------

--
-- Structure de la table `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`title`)),
  `slug` varchar(191) NOT NULL,
  `short_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`short_description`)),
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`description`)),
  `image_path` varchar(191) DEFAULT NULL,
  `gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery`)),
  `project_date` date DEFAULT NULL,
  `client` varchar(191) DEFAULT NULL,
  `role` varchar(191) DEFAULT NULL,
  `technologies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`technologies`)),
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `results` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`results`)),
  `demo_url` varchar(191) DEFAULT NULL,
  `github_url` varchar(191) DEFAULT NULL,
  `documentation_url` varchar(191) DEFAULT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'published',
  `is_confidential` tinyint(1) NOT NULL DEFAULT 0,
  `project_type` varchar(191) NOT NULL DEFAULT 'professional',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `project_categories`
--

CREATE TABLE `project_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`name`)),
  `slug` varchar(191) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `resumes`
--

CREATE TABLE `resumes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `locale` varchar(191) NOT NULL DEFAULT 'fr',
  `resume_template_id` bigint(20) UNSIGNED NOT NULL,
  `length` varchar(191) NOT NULL DEFAULT 'standard',
  `format` varchar(191) NOT NULL DEFAULT 'pdf',
  `sections` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sections`)),
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `content_selection` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content_selection`)),
  `customization` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`customization`)),
  `target_role` varchar(191) DEFAULT NULL,
  `target_market` varchar(191) DEFAULT NULL,
  `summary_override` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `resume_templates`
--

CREATE TABLE `resume_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `layout` varchar(191) NOT NULL DEFAULT 'classic',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `style` varchar(191) DEFAULT NULL,
  `ats_friendly` tinyint(1) NOT NULL DEFAULT 0,
  `languages` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`languages`)),
  `recommended_pages` varchar(191) NOT NULL DEFAULT 'two_pages',
  `available_formats` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`available_formats`)),
  `available_colors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`available_colors`)),
  `blocks` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`blocks`)),
  `preview_image` varchar(191) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `resume_template_categories`
--

CREATE TABLE `resume_template_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super-admin', 'web', '2026-10-08 09:59:25', '2026-10-08 09:59:25');

-- --------------------------------------------------------

--
-- Structure de la table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sections`
--

CREATE TABLE `sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(191) NOT NULL,
  `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`title`)),
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `seo_metadata`
--

CREATE TABLE `seo_metadata` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`title`)),
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`description`)),
  `keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`keywords`)),
  `canonical_url` varchar(191) DEFAULT NULL,
  `og_image` varchar(191) DEFAULT NULL,
  `robots` varchar(191) NOT NULL DEFAULT 'index,follow',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`title`)),
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`description`)),
  `icon` varchar(191) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image_path` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(191) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `skills`
--

CREATE TABLE `skills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `skill_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`name`)),
  `level` varchar(191) DEFAULT NULL,
  `level_en` varchar(191) DEFAULT NULL,
  `percentage` tinyint(3) UNSIGNED DEFAULT NULL,
  `years_experience` int(11) NOT NULL DEFAULT 0,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`description`)),
  `icon` varchar(191) DEFAULT NULL,
  `image_path` varchar(191) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `skills`
--

INSERT INTO `skills` (`id`, `skill_category_id`, `name`, `level`, `level_en`, `percentage`, `years_experience`, `description`, `icon`, `image_path`, `sort_order`, `published`, `created_at`, `updated_at`) VALUES
(1, NULL, '{\"fr\":\"HTML\\/CSS \\/ Bootstrap\",\"en\":\"HTML\\/CSS \\/ Bootstrap\"}', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54'),
(2, NULL, '{\"fr\":\"JS\\/ JQuery \\/ TotalJs\",\"en\":\"JS \\/ jQuery \\/ TotalJS\"}', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54'),
(3, NULL, '{\"fr\":\"PHP\\/ Laravel\",\"en\":\"PHP\\/Laravel\"}', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54'),
(4, NULL, '{\"fr\":\"SQL\\/ MySQL \\/ PostgreSQL \\/Oracle\",\"en\":\"SQL \\/ MySQL \\/ PostgreSQL \\/ Oracle\"}', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54'),
(5, NULL, '{\"fr\":\"Java\",\"en\":\"Java\"}', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54'),
(6, NULL, '{\"fr\":\"C#\",\"en\":\"C#\"}', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54'),
(7, NULL, '{\"fr\":\"Windows\",\"en\":\"Windows\"}', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54'),
(8, NULL, '{\"fr\":\"Windows server\",\"en\":\"Windows Server\"}', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54'),
(9, NULL, '{\"fr\":\"Linux\",\"en\":\"Linux\"}', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54'),
(10, NULL, '{\"fr\":\"Maintenance\",\"en\":\"Maintenance\"}', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54'),
(11, NULL, '{\"fr\":\"Devops\",\"en\":\"DevOps\"}', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54'),
(12, NULL, '{\"fr\":\"Photoshop \\/ XD \\/Canva\",\"en\":\"Photoshop \\/ XD \\/ Canva\"}', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54'),
(13, NULL, '{\"fr\":\"Sage X3\",\"en\":\"Sage X3\"}', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54'),
(14, NULL, '{\"fr\":\"CyberS\\u00e9curit\\u00e9\",\"en\":\"Cybersecurity\"}', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 1, '2026-10-09 16:33:54', '2026-10-09 16:33:54');

-- --------------------------------------------------------

--
-- Structure de la table `skill_categories`
--

CREATE TABLE `skill_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`name`)),
  `slug` varchar(191) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `social_links`
--

CREATE TABLE `social_links` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `platform` varchar(191) NOT NULL,
  `label` varchar(191) DEFAULT NULL,
  `url` varchar(191) NOT NULL,
  `icon` varchar(191) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image_path` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tags`
--

CREATE TABLE `tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`name`)),
  `slug` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `technologies`
--

CREATE TABLE `technologies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`name`)),
  `category` varchar(191) DEFAULT NULL,
  `icon` varchar(191) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image_path` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `author` varchar(191) NOT NULL,
  `role` varchar(191) DEFAULT NULL,
  `company` varchar(191) DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`content`)),
  `rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `submitted_by_client` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `testimonials`
--

INSERT INTO `testimonials` (`id`, `author`, `role`, `company`, `content`, `rating`, `sort_order`, `published`, `submitted_by_client`, `created_at`, `updated_at`) VALUES
(1, 'Alain', 'Informaticien', 'LS', '{\"fr\":\"je suis fan\"}', 5, 0, 1, 0, '2026-10-09 16:46:11', '2026-10-09 16:46:22');

-- --------------------------------------------------------

--
-- Structure de la table `translations`
--

CREATE TABLE `translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `group` varchar(191) NOT NULL,
  `key` varchar(191) NOT NULL,
  `locale` varchar(191) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'ALAIN GUIGMA', 'guigmaa507@gmail.com', '2026-10-08 09:59:25', '$2y$12$Q44scf.fksH//6rzkZbPa.2yEyRsfb3uuuznHDpAPJL2aBK146ZMq', '9JLBZY0hMtF5W51IQn9nhTEXXvI2Fctom6qHRA8c7rWjqfBM02G3cOhmgI09', '2026-10-08 09:59:25', '2026-10-08 09:59:25');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `achievements_profile_id_foreign` (`profile_id`);

--
-- Index pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_user_id_foreign` (`user_id`);

--
-- Index pour la table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `articles_slug_unique` (`slug`),
  ADD UNIQUE KEY `articles_source_url_unique` (`source_url`) USING HASH,
  ADD KEY `articles_article_category_id_foreign` (`article_category_id`);

--
-- Index pour la table `article_categories`
--
ALTER TABLE `article_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `article_categories_slug_unique` (`slug`);

--
-- Index pour la table `article_comments`
--
ALTER TABLE `article_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_comments_article_id_foreign` (`article_id`);

--
-- Index pour la table `article_likes`
--
ALTER TABLE `article_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `article_likes_article_id_visitor_hash_unique` (`article_id`,`visitor_hash`);

--
-- Index pour la table `article_tag`
--
ALTER TABLE `article_tag`
  ADD PRIMARY KEY (`article_id`,`tag_id`),
  ADD KEY `article_tag_tag_id_foreign` (`tag_id`);

--
-- Index pour la table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Index pour la table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Index pour la table `certifications`
--
ALTER TABLE `certifications`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `educations`
--
ALTER TABLE `educations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `educations_profile_id_foreign` (`profile_id`);

--
-- Index pour la table `experiences`
--
ALTER TABLE `experiences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `experiences_profile_id_foreign` (`profile_id`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Index pour la table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Index pour la table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Index pour la table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Index pour la table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Index pour la table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Index pour la table `professional_references`
--
ALTER TABLE `professional_references`
  ADD PRIMARY KEY (`id`),
  ADD KEY `professional_references_profile_id_foreign` (`profile_id`);

--
-- Index pour la table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profiles_user_id_foreign` (`user_id`);

--
-- Index pour la table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `projects_slug_unique` (`slug`),
  ADD KEY `projects_project_category_id_foreign` (`project_category_id`);

--
-- Index pour la table `project_categories`
--
ALTER TABLE `project_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_categories_slug_unique` (`slug`);

--
-- Index pour la table `resumes`
--
ALTER TABLE `resumes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resumes_slug_unique` (`slug`),
  ADD KEY `resumes_profile_id_foreign` (`profile_id`),
  ADD KEY `resumes_resume_template_id_foreign` (`resume_template_id`);

--
-- Index pour la table `resume_templates`
--
ALTER TABLE `resume_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resume_templates_slug_unique` (`slug`),
  ADD KEY `resume_templates_category_id_foreign` (`category_id`);

--
-- Index pour la table `resume_template_categories`
--
ALTER TABLE `resume_template_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resume_template_categories_slug_unique` (`slug`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Index pour la table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Index pour la table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sections_key_unique` (`key`);

--
-- Index pour la table `seo_metadata`
--
ALTER TABLE `seo_metadata`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Index pour la table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `site_settings_key_unique` (`key`);

--
-- Index pour la table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `skills_skill_category_id_foreign` (`skill_category_id`);

--
-- Index pour la table `skill_categories`
--
ALTER TABLE `skill_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `skill_categories_slug_unique` (`slug`);

--
-- Index pour la table `social_links`
--
ALTER TABLE `social_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `social_links_profile_id_foreign` (`profile_id`);

--
-- Index pour la table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tags_slug_unique` (`slug`);

--
-- Index pour la table `technologies`
--
ALTER TABLE `technologies`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `translations_group_key_locale_unique` (`group`,`key`,`locale`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `article_categories`
--
ALTER TABLE `article_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `article_comments`
--
ALTER TABLE `article_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `article_likes`
--
ALTER TABLE `article_likes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `certifications`
--
ALTER TABLE `certifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `educations`
--
ALTER TABLE `educations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `experiences`
--
ALTER TABLE `experiences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `professional_references`
--
ALTER TABLE `professional_references`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `project_categories`
--
ALTER TABLE `project_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `resumes`
--
ALTER TABLE `resumes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `resume_templates`
--
ALTER TABLE `resume_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `resume_template_categories`
--
ALTER TABLE `resume_template_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `seo_metadata`
--
ALTER TABLE `seo_metadata`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `skill_categories`
--
ALTER TABLE `skill_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `social_links`
--
ALTER TABLE `social_links`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `technologies`
--
ALTER TABLE `technologies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `translations`
--
ALTER TABLE `translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `achievements`
--
ALTER TABLE `achievements`
  ADD CONSTRAINT `achievements_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_article_category_id_foreign` FOREIGN KEY (`article_category_id`) REFERENCES `article_categories` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `article_comments`
--
ALTER TABLE `article_comments`
  ADD CONSTRAINT `article_comments_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `article_likes`
--
ALTER TABLE `article_likes`
  ADD CONSTRAINT `article_likes_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `article_tag`
--
ALTER TABLE `article_tag`
  ADD CONSTRAINT `article_tag_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `educations`
--
ALTER TABLE `educations`
  ADD CONSTRAINT `educations_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `experiences`
--
ALTER TABLE `experiences`
  ADD CONSTRAINT `experiences_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `professional_references`
--
ALTER TABLE `professional_references`
  ADD CONSTRAINT `professional_references_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_project_category_id_foreign` FOREIGN KEY (`project_category_id`) REFERENCES `project_categories` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `resumes`
--
ALTER TABLE `resumes`
  ADD CONSTRAINT `resumes_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resumes_resume_template_id_foreign` FOREIGN KEY (`resume_template_id`) REFERENCES `resume_templates` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `resume_templates`
--
ALTER TABLE `resume_templates`
  ADD CONSTRAINT `resume_templates_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `resume_template_categories` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `skills`
--
ALTER TABLE `skills`
  ADD CONSTRAINT `skills_skill_category_id_foreign` FOREIGN KEY (`skill_category_id`) REFERENCES `skill_categories` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `social_links`
--
ALTER TABLE `social_links`
  ADD CONSTRAINT `social_links_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

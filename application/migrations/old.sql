-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 17 Okt 2024 pada 02.28
-- Versi server: 10.6.16-MariaDB-log
-- Versi PHP: 8.3.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_starter_codeigniter3`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `config_web`
--

CREATE TABLE `config_web` (
  `key` varchar(100) NOT NULL,
  `value` longtext DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `is_active` tinyint(1) UNSIGNED DEFAULT 0 COMMENT '0 = INACTIVE, 1 = ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `login` varchar(100) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL COMMENT '{resource}.{action}',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'user.create', 'Create user', '2024-06-25 16:21:05', '2024-06-25 16:21:05'),
(2, 'user.edit', 'Edit user', '2024-06-25 16:21:05', '2024-06-25 16:21:05'),
(3, 'user.delete', 'Delete user', '2024-06-25 16:21:05', '2024-06-25 16:21:05'),
(4, 'user.view', 'View user', '2024-06-25 16:21:05', '2024-06-25 16:21:05'),
(5, 'role.create', 'Create role', '2024-06-25 16:23:38', '2024-06-25 16:23:38'),
(6, 'role.edit', 'Edit role', '2024-06-25 16:23:38', '2024-06-25 16:23:38'),
(7, 'role.delete', 'Delete role', '2024-06-25 16:23:38', '2024-06-25 16:23:38'),
(8, 'role.view', 'View role', '2024-06-25 16:23:38', '2024-06-25 16:23:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'IT Support', 'IT Support Account', '2024-05-23 17:04:28', '2024-05-27 09:21:59'),
(2, 'Admin', 'Administrator Account', '2024-05-23 17:04:28', '2024-05-23 17:04:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2024-06-25 16:21:53', '2024-06-25 16:21:53'),
(1, 2, '2024-06-25 16:21:53', '2024-06-25 16:21:53'),
(1, 3, '2024-06-25 16:21:53', '2024-06-25 16:21:53'),
(1, 4, '2024-06-25 16:21:53', '2024-06-25 16:21:53'),
(1, 5, '2024-06-25 16:24:21', '2024-06-25 16:24:21'),
(1, 6, '2024-06-25 16:24:21', '2024-06-25 16:24:21'),
(1, 7, '2024-06-25 16:24:21', '2024-06-25 16:24:21'),
(1, 8, '2024-06-25 16:24:21', '2024-06-25 16:24:21'),
(2, 1, '2024-06-25 17:43:13', '2024-06-25 17:43:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(192) NOT NULL,
  `email` varchar(192) NOT NULL,
  `password` varchar(255) NOT NULL,
  `activation_selector` varchar(255) DEFAULT NULL,
  `activation_code` varchar(255) DEFAULT NULL,
  `activation_time` timestamp NULL DEFAULT NULL,
  `forgotten_password_selector` varchar(255) DEFAULT NULL,
  `forgotten_password_code` varchar(255) DEFAULT NULL,
  `forgotten_password_time` timestamp NULL DEFAULT NULL,
  `remember_selector` varchar(255) DEFAULT NULL,
  `remember_code` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) UNSIGNED DEFAULT 0 COMMENT '0 = INACTIVE, 1 = ACTIVE',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `activation_selector`, `activation_code`, `activation_time`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'IT', 'it@gmail.com', '$2y$10$AQnLZ4teOf6DhNsw/A2IcuzGgWQx10/hMdet0JNcuX0le9IZSeHPK', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2024-05-27 09:57:22', '2024-05-23 17:45:41', '2024-05-27 09:57:22'),
(2, 'Admin', 'admin@gmail.com', '$2y$10$AQnLZ4teOf6DhNsw/A2IcuzGgWQx10/hMdet0JNcuX0le9IZSeHPK', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2024-05-31 08:24:22', '2024-05-23 17:47:20', '2024-05-31 08:24:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` int(11) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2024-06-25 16:42:50', '2024-06-25 16:42:50'),
(1, 2, '2024-05-30 08:20:17', '2024-05-30 08:20:17'),
(2, 2, '2024-06-25 17:17:21', '2024-06-25 17:17:21');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `config_web`
--
ALTER TABLE `config_web`
  ADD PRIMARY KEY (`key`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indeks untuk tabel `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_uc` (`name`);

--
-- Indeks untuk tabel `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD UNIQUE KEY `role_permissions_unq` (`role_id`,`permission_id`),
  ADD KEY `role_permissions_fk_permission_id` (`permission_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_unq_email` (`email`),
  ADD UNIQUE KEY `users_unq_activation_selector` (`activation_selector`),
  ADD UNIQUE KEY `users_unq_forgotten_password_selector` (`forgotten_password_selector`),
  ADD UNIQUE KEY `users_unq_remember_selector` (`remember_selector`);

--
-- Indeks untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  ADD UNIQUE KEY `user_roles_unq` (`user_id`,`role_id`),
  ADD KEY `user_roles_fk_role_id` (`role_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_fk_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `role_permissions_fk_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Ketidakleluasaan untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_fk_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `user_roles_fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

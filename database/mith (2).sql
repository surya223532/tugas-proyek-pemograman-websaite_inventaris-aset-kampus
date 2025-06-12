-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 20 Bulan Mei 2025 pada 18.45
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mith`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `aset`
--

CREATE TABLE `aset` (
  `id_aset` int(11) NOT NULL,
  `nama_aset` varchar(255) NOT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `lokasi_id` int(11) DEFAULT NULL,
  `tanggal_perolehan` date DEFAULT NULL,
  `nilai_awal` decimal(15,0) DEFAULT NULL,
  `status` enum('Aktif','Tidak Aktif','Dalam Perbaikan') DEFAULT 'Aktif',
  `nilai_susut` double DEFAULT 0,
  `masa_manfaat` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `aset`
--

INSERT INTO `aset` (`id_aset`, `nama_aset`, `kategori_id`, `lokasi_id`, `tanggal_perolehan`, `nilai_awal`, `status`, `nilai_susut`, `masa_manfaat`) VALUES
(10, 'monitor', 1, 1, '2020-01-13', 10000000, 'Aktif', 200000, 5),
(11, 'komputer', 1, 2, '2021-01-13', 20000000, 'Aktif', 0, 15),
(12, 'hp', 1, 1, '2024-01-13', 1000000, 'Aktif', 0, 5),
(13, 'keyboard', 1, 1, '2023-01-14', 1000000, 'Aktif', 0, 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`, `parent_id`) VALUES
(1, 'elektronik', 'yang termasuk alat elektronik', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `lokasi`
--

CREATE TABLE `lokasi` (
  `id_lokasi` int(11) NOT NULL,
  `nama_lokasi` varchar(255) NOT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `lokasi`
--

INSERT INTO `lokasi` (`id_lokasi`, `nama_lokasi`, `alamat`) VALUES
(1, 'lab 1', 'laboratorium 1 kampus e'),
(2, 'lab 2 terpadu', 'jalan jalan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman_aset`
--

CREATE TABLE `peminjaman_aset` (
  `id_peminjaman` int(11) NOT NULL,
  `id_aset` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `tanggal_peminjaman` date NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `status` enum('Dipinjam','Kembali') DEFAULT 'Dipinjam'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penghapusan_aset`
--

CREATE TABLE `penghapusan_aset` (
  `id_penghapusan` int(11) NOT NULL,
  `id_aset` int(11) NOT NULL,
  `tanggal_penghapusan` date NOT NULL,
  `alasan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penyusutan`
--

CREATE TABLE `penyusutan` (
  `id_penyusutan` int(11) NOT NULL,
  `id_aset` int(11) NOT NULL,
  `tahun` year(4) NOT NULL,
  `nilai_susut` decimal(15,2) NOT NULL,
  `nilai_sisa` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penyusutan`
--

INSERT INTO `penyusutan` (`id_penyusutan`, `id_aset`, `tahun`, `nilai_susut`, `nilai_sisa`) VALUES
(44, 13, '2025', 333333.33, 333333.33),
(45, 11, '2025', 1333333.33, 14666666.67),
(47, 10, '2025', 2000000.00, 0.00),
(49, 12, '2025', 200000.00, 800000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','mahasiswa','dosen','staf','pimpinan') NOT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `role`, `foto`) VALUES
(2, 'Admin ', 'admin@mail.com', 'surya123', 'admin', NULL),
(3, 'Staf User', 'staf@mail.com', 'surya1234', 'staf', NULL),
(4, 'Pimpinan User', 'pimpinan@mail.com', 'surya123', 'pimpinan', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `aset`
--
ALTER TABLE `aset`
  ADD PRIMARY KEY (`id_aset`),
  ADD KEY `kategori_id` (`kategori_id`),
  ADD KEY `lokasi_id` (`lokasi_id`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD KEY `fk_parent_id` (`parent_id`);

--
-- Indeks untuk tabel `lokasi`
--
ALTER TABLE `lokasi`
  ADD PRIMARY KEY (`id_lokasi`);

--
-- Indeks untuk tabel `peminjaman_aset`
--
ALTER TABLE `peminjaman_aset`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `peminjaman_aset_ibfk_1` (`id_aset`),
  ADD KEY `peminjaman_aset_ibfk_2` (`id_user`);

--
-- Indeks untuk tabel `penghapusan_aset`
--
ALTER TABLE `penghapusan_aset`
  ADD PRIMARY KEY (`id_penghapusan`),
  ADD KEY `penghapusan_aset_ibfk_1` (`id_aset`);

--
-- Indeks untuk tabel `penyusutan`
--
ALTER TABLE `penyusutan`
  ADD PRIMARY KEY (`id_penyusutan`),
  ADD KEY `penyusutan_ibfk_1` (`id_aset`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `aset`
--
ALTER TABLE `aset`
  MODIFY `id_aset` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `lokasi`
--
ALTER TABLE `lokasi`
  MODIFY `id_lokasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `peminjaman_aset`
--
ALTER TABLE `peminjaman_aset`
  MODIFY `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penghapusan_aset`
--
ALTER TABLE `penghapusan_aset`
  MODIFY `id_penghapusan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penyusutan`
--
ALTER TABLE `penyusutan`
  MODIFY `id_penyusutan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `aset`
--
ALTER TABLE `aset`
  ADD CONSTRAINT `aset_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE,
  ADD CONSTRAINT `aset_ibfk_2` FOREIGN KEY (`lokasi_id`) REFERENCES `lokasi` (`id_lokasi`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD CONSTRAINT `fk_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `peminjaman_aset`
--
ALTER TABLE `peminjaman_aset`
  ADD CONSTRAINT `peminjaman_aset_ibfk_1` FOREIGN KEY (`id_aset`) REFERENCES `aset` (`id_aset`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_aset_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penghapusan_aset`
--
ALTER TABLE `penghapusan_aset`
  ADD CONSTRAINT `penghapusan_aset_ibfk_1` FOREIGN KEY (`id_aset`) REFERENCES `aset` (`id_aset`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penyusutan`
--
ALTER TABLE `penyusutan`
  ADD CONSTRAINT `penyusutan_ibfk_1` FOREIGN KEY (`id_aset`) REFERENCES `aset` (`id_aset`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

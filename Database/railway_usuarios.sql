-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: URL    Database: Name
-- ------------------------------------------------------
-- Server version	9.3.0

--
-- Table structure for table `usuarios`
--


CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(30) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `apellido` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `verificado` tinyint(1) DEFAULT '0',
  `activo` tinyint(1) DEFAULT '1',
  `token_verificacion` varchar(255) DEFAULT NULL,
  `created_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dump completed on 2025-06-06 16:05:40

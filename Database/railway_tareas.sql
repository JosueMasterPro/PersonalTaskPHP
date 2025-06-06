-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: URL    Database: Name
-- ------------------------------------------------------
-- Server version	9.3.0

--
-- Table structure for table `tareas`
--

DROP TABLE IF EXISTS `tareas`;

CREATE TABLE `tareas` (
  `id_Tarea` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `tipo` varchar(20) DEFAULT 'tarea',
  `descripcion` text,
  `completada` tinyint(1) DEFAULT '0',
  `fecha_final` date DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_Tarea`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `tareas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- Dump completed on 2025-06-06 16:05:36

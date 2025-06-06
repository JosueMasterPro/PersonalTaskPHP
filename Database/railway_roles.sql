-- MySQL
--
-- Host: url    Database: Name
-- ------------------------------------------------------
-- Server version	9.3.0

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id_Roles` int NOT NULL AUTO_INCREMENT,
  `id_usuario` varchar(50) NOT NULL,
  `rol` varchar(20) NOT NULL,
  PRIMARY KEY (`id_Roles`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `roles_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

-- Dump completed on 2025-06-06 16:05:38

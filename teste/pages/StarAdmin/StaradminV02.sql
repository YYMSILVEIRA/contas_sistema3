-- MySQL dump 10.13  Distrib 5.6.19, for linux-glibc2.5 (x86_64)
--
-- Host: 127.0.0.1    Database: estudo_php
-- ------------------------------------------------------
-- Server version	5.5.5-10.1.26-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Chamados`
--

DROP TABLE IF EXISTS `Chamados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Chamados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `setor` varchar(45) NOT NULL,
  `solicitacao` varchar(45) NOT NULL,
  `sla` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Chamados`
--

LOCK TABLES `Chamados` WRITE;
/*!40000 ALTER TABLE `Chamados` DISABLE KEYS */;
INSERT INTO `Chamados` VALUES (16,'Gabriel','Desenvolvimento','Problema no hardware','UrgÃªncia'),(17,'Joao','Beleza','Reset de Senha','PadrÃ£o'),(18,'Julia','Diretor','Resultado estÃ¡ incorreto','UrgÃªncia');
/*!40000 ALTER TABLE `Chamados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `atendimento`
--

DROP TABLE IF EXISTS `atendimento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `atendimento` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Nome` varchar(45) NOT NULL,
  `Sobrenome` varchar(45) NOT NULL,
  `Sexualidade` varchar(45) NOT NULL,
  `Data_nasc` varchar(45) NOT NULL,
  `Bairro` varchar(45) NOT NULL,
  `Cidade` varchar(45) NOT NULL,
  `End_mom` varchar(45) NOT NULL,
  `Cidade_mom` varchar(45) NOT NULL,
  `CEP` varchar(45) NOT NULL,
  `Nacionalidade` varchar(45) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=1504 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atendimento`
--

LOCK TABLES `atendimento` WRITE;
/*!40000 ALTER TABLE `atendimento` DISABLE KEYS */;
INSERT INTO `atendimento` VALUES (1503,'Julio','Santos','Masculino','12/01/2018','Ipem sao cristovao','Sao luis','Paris','Lisboa','56565656','Italiano');
/*!40000 ALTER TABLE `atendimento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(10) NOT NULL,
  `sobrenome` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `senha` varchar(30) DEFAULT NULL,
  `data_cadastro` date DEFAULT NULL,
  `funcao` varchar(45) DEFAULT NULL,
  `foto_perfil` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (21,'Gabriel','Sales','ghe_sales@hotmail.com','1234','2019-01-08','Programador','Array'),(22,'Joao','Sales','joao@teste.com','1234','2019-01-08','diretor','.JPG'),(23,'Gabriel','Sales','joao@teste.com','123','2019-01-08','diretor','83750ca0273bc95e1576a9748f2bff23jpeg'),(24,'Ana','beatriz','bia@teste.com','123','2019-01-08','biomedica','fc6f9edfcba3c4d7fd5d1aa30a2f000ejpeg');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendedor`
--

DROP TABLE IF EXISTS `vendedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendedor` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) DEFAULT NULL,
  `venda_mensal` int(45) NOT NULL,
  `venda_day` int(45) NOT NULL,
  `data_venda` date NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendedor`
--

LOCK TABLES `vendedor` WRITE;
/*!40000 ALTER TABLE `vendedor` DISABLE KEYS */;
INSERT INTO `vendedor` VALUES (1,'Gabriel Sales',22,21,'2018-09-09'),(2,'Paulo Sales Junho',9,200,'2018-09-10'),(3,'Consagrado',100,1,'2017-01-10'),(4,'Naruto',10,2,'2015-02-10'),(5,'Ronaldo da maria ronado',121,11,'2020-10-12'),(6,'Bruxo dos mesmes',1,101,'1997-03-01'),(7,'Chris',1,1123,'2011-08-10'),(8,'teste',89,82,'2018-09-29'),(9,'teste teste',89,82,'2018-09-29'),(10,'JJ jamerson',89,82,'2018-09-29'),(11,'JJjj jamerson',89,82,'2018-09-29'),(12,'Joao pe de feijao',111,11,'2017-11-09'),(13,'Ronaldo',11,44,'2017-11-09');
/*!40000 ALTER TABLE `vendedor` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-01-08 14:35:11

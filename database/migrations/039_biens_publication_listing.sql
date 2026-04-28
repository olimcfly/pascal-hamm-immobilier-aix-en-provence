-- Approbation vitrine après import scraping : jusqu’à validation, annonce hors liste publique.

SET NAMES utf8mb4;

ALTER TABLE `biens`
  ADD COLUMN `publier_vitrine` TINYINT(1) NULL DEFAULT NULL
    COMMENT 'NULL=anciennes fiches comme avant, 0=masquée vitrine jusqu’à approbation, 1=affichée'
    AFTER `statut`;

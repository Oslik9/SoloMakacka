-- Nepovinné logo ročníku: zachová původní typ i všechny uložené hodnoty.
ALTER TABLE race_year
MODIFY logo varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_czech_ci NULL DEFAULT NULL;

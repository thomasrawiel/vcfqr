CREATE TABLE tt_content
(
    `tx_vcfqr_address`  int(11)     NOT NULL DEFAULT '0',
    `tx_vcfqr_filename` varchar(50) NOT NULL DEFAULT ''
);

CREATE TABLE tt_address
(
    `hideqrcode` tinyint unsigned DEFAULT '0' NOT NULL
);
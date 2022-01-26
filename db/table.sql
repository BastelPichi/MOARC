CREATE TABLE IF NOT EXISTS `is_base` (
  `base_id` varchar(8) NOT NULL DEFAULT 'hostbase',
  `base_name` varchar(80) NOT NULL,
  `base_email` varchar(80) NOT NULL,
  `base_url` varchar(80) NOT NULL,
  `base_status` int(1) NOT NULL
);

CREATE TABLE IF NOT EXISTS `is_client` (
  `client_id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `client_name` varchar(40) NOT NULL,
  `client_email` varchar(80) NOT NULL,
  `client_password` varchar(64) NOT NULL,
  `client_status` int(1) NOT NULL,
  `client_date` int(22) NOT NULL,
  `client_key` varchar(10) NOT NULL,
  `client_recovery_key` varchar(16) NOT NULL
)

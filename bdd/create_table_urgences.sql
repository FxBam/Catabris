CREATE TABLE urgences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commune VARCHAR(100) NOT NULL,
    activateur_email VARCHAR(100) NOT NULL,
    activateur_nom VARCHAR(100) NOT NULL,
    date_activation DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_commune (commune),
    FOREIGN KEY (activateur_email) REFERENCES utilisateurs(adresse_mail) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

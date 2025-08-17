-- Create Database
CREATE DATABASE IF NOT EXISTS nextgen_finance_feed;
USE nextgen_finance_feed;

-- Table: companies
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    ticker VARCHAR(20) NOT NULL,
    index_name VARCHAR(50) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: news_articles
CREATE TABLE IF NOT EXISTS news_articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    headline VARCHAR(500) NOT NULL,
    url TEXT NOT NULL,
    summary TEXT,
    source VARCHAR(255) NOT NULL,
    published_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indexes
CREATE INDEX idx_company_id ON news_articles(company_id);
CREATE INDEX idx_ticker ON companies(ticker);

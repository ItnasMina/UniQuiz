-- Database creation with proper charset for full Unicode support (including emojis)
CREATE DATABASE IF NOT EXISTS uniquiz_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE uniquiz_db;

-- 1. USERS TABLE
-- Stores user authentication and profile data.
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, 
    role ENUM('user', 'admin') DEFAULT 'user' NOT NULL, 
    subscription_tier ENUM('basic', 'monthly', 'lifetime') DEFAULT 'basic' NOT NULL,
    subscription_expires_at TIMESTAMP NULL DEFAULT NULL,
    
    profile_picture_url VARCHAR(1024) DEFAULT NULL, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_subscription_expiration (subscription_expires_at)
) ENGINE=InnoDB;

-- 2. CATEGORIES TABLE
-- Master table for quiz subjects (e.g., 'Mathematics', 'History', 'Programming').
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE, -- Useful for SEO-friendly URLs (e.g., /category/history)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. QUIZZES TABLE
-- The core entity created by users.
CREATE TABLE quizzes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id BIGINT UNSIGNED NOT NULL,
    category_id INT UNSIGNED DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    is_published BOOLEAN DEFAULT FALSE, -- Allows drafts before publishing
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys ensure data integrity
    CONSTRAINT fk_quiz_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_quiz_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    
    -- Indexing for faster feed/search queries
    INDEX idx_quiz_published_created (is_published, created_at)
) ENGINE=InnoDB;

-- 4. QUESTIONS TABLE
-- Questions belonging to a specific quiz.
CREATE TABLE questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id BIGINT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    points INT UNSIGNED DEFAULT 1, -- Allows weighted questions for scoring
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_question_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. ANSWERS TABLE
-- Possible answers for each question.
CREATE TABLE answers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id BIGINT UNSIGNED NOT NULL,
    answer_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    
    CONSTRAINT fk_answer_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. QUIZ ATTEMPTS TABLE (The "Social/Gamification" Engine)
-- Tracks when a user takes a quiz and their score.
CREATE TABLE quiz_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    quiz_id BIGINT UNSIGNED NOT NULL,
    score INT UNSIGNED NOT NULL DEFAULT 0,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    
    CONSTRAINT fk_attempt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_attempt_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    
    -- Composite index to easily find a user's history or leaderboard for a quiz
    INDEX idx_quiz_user (quiz_id, user_id),
    INDEX idx_completed_score (completed_at, score)
) ENGINE=InnoDB;
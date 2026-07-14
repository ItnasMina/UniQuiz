```mermaid
---
title: UniQuiz database ER diagram
---
erDiagram
    users {
        bigint id PK 
        varchar username
        varchar email
        varchar password_hash
        enum role
        enum subscription_tier
        timestamp subscription_expires_at
        varchar profile_picture_url
        timestamp created_at
        timestamp updated_at
    }

    categories {
        int id PK
        varchar name
        varchar slug
        timestamp created_at
    }
    
    quizzes {
        bigint id PK
        bigint author_id FK
        int category_id FK
        varchar title
        text description
        boolean is_published
        timestamp created_at
        timestamp updated_at
    }

    questions {
        bigint id PK
        bigint quiz_id FK
        text question_text
        int points
        timestamp created_at
    }

    answers {
        bigint id PK
        bigint question_id FK
        text answer_text
        boolean is_correct
    }
  
    quiz_attempts {
        bigint id PK
        bigint user_id FK
        bigint quiz_id FK
        int score
        timestamp started_at
        timestamp completed_at
    }

    %% Relaciones
    users ||--|{ quizzes : "crea"
    users ||--|{ quiz_attempts : "realiza"
    categories ||--|{ quizzes : "clasifica"
    quizzes ||--|{ questions : "contiene"
    quizzes ||--|{ quiz_attempts : "tiene"
    questions ||--|{ answers : "tiene"
´´´

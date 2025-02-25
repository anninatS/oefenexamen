# Database Design - DreamScape Interactive

## SQLite Type System Notes
SQLite uses dynamic typing with the following storage classes:
- NULL
- INTEGER
- REAL
- TEXT
- BLOB

## Tables

### users
| Column     | Type    | Constraints                              | Description                  |
|------------|---------|------------------------------------------|------------------------------|
| id         | INTEGER | PRIMARY KEY                              | Unique identifier            |
| username   | TEXT    | UNIQUE NOT NULL                          | User's login name            |
| email      | TEXT    | UNIQUE NOT NULL                          | User's email address         |
| password   | TEXT    | NOT NULL                                 | Hashed password              |
| role       | TEXT    | NOT NULL DEFAULT 'speler'                | User role (speler/beheerder) |
| created_at | INTEGER | NOT NULL DEFAULT (strftime('%s', 'now')) | Unix timestamp for creation  |
| updated_at | INTEGER | NOT NULL DEFAULT (strftime('%s', 'now')) | Unix timestamp for update    |

### items
| Column           | Type    | Constraints                                   | Description                 |
|------------------|---------|-----------------------------------------------|-----------------------------|
| id               | INTEGER | PRIMARY KEY                                   | Unique identifier           |
| name             | TEXT    | NOT NULL                                      | Item name                   |
| description      | TEXT    | NOT NULL                                      | Item description            |
| type             | TEXT    | NOT NULL                                      | Wapen/Armor/Accessoire      |
| rarity           | TEXT    | NOT NULL                                      | Item rarity level           |
| strength         | INTEGER | NOT NULL CHECK (strength BETWEEN 0 AND 100)   | Strength stat (0-100)       |
| speed            | INTEGER | NOT NULL CHECK (speed BETWEEN 0 AND 100)      | Speed stat (0-100)          |
| durability       | INTEGER | NOT NULL CHECK (durability BETWEEN 0 AND 100) | Durability stat (0-100)     |
| magic_properties | TEXT    | NOT NULL                                      | Magic effects/bonuses       |
| created_at       | INTEGER | NOT NULL DEFAULT (strftime('%s', 'now'))      | Unix timestamp for creation |
| updated_at       | INTEGER | NOT NULL DEFAULT (strftime('%s', 'now'))      | Unix timestamp for update   |

### inventory
| Column      | Type    | Constraints                              | Description                    |
|-------------|---------|------------------------------------------|--------------------------------|
| id          | INTEGER | PRIMARY KEY                              | Unique identifier              |
| user_id     | INTEGER | NOT NULL REFERENCES users(id)            | Owner of the item              |
| item_id     | INTEGER | NOT NULL REFERENCES items(id)            | Item in inventory              |
| acquired_at | INTEGER | NOT NULL DEFAULT (strftime('%s', 'now')) | Unix timestamp for acquisition |

### trade_requests
| Column      | Type    | Constraints                              | Description                  |
|-------------|---------|------------------------------------------|------------------------------|
| id          | INTEGER | PRIMARY KEY                              | Unique identifier            |
| sender_id   | INTEGER | NOT NULL REFERENCES users(id)            | User sending trade request   |
| receiver_id | INTEGER | NOT NULL REFERENCES users(id)            | User receiving trade request |
| status      | TEXT    | NOT NULL DEFAULT 'pending'               | pending/accepted/rejected    |
| created_at  | INTEGER | NOT NULL DEFAULT (strftime('%s', 'now')) | Unix timestamp for creation  |
| updated_at  | INTEGER | NOT NULL DEFAULT (strftime('%s', 'now')) | Unix timestamp for update    |

### trade_items
| Column       | Type    | Constraints                            | Description           |
|--------------|---------|----------------------------------------|-----------------------|
| id           | INTEGER | PRIMARY KEY                            | Unique identifier     |
| trade_id     | INTEGER | NOT NULL REFERENCES trade_requests(id) | Related trade request |
| inventory_id | INTEGER | NOT NULL REFERENCES inventory(id)      | Item offered in trade |
| direction    | TEXT    | NOT NULL                               | 'offer' or 'request'  |

### notifications
| Column     | Type    | Constraints                              | Description                 |
|------------|---------|------------------------------------------|-----------------------------|
| id         | INTEGER | PRIMARY KEY                              | Unique identifier           |
| user_id    | INTEGER | NOT NULL REFERENCES users(id)            | User to notify              |
| type       | TEXT    | NOT NULL                                 | Notification type           |
| message    | TEXT    | NOT NULL                                 | Notification message        |
| read       | INTEGER | NOT NULL DEFAULT 0                       | Read status (0 or 1)        |
| created_at | INTEGER | NOT NULL DEFAULT (strftime('%s', 'now')) | Unix timestamp for creation |

## Key Changes from Previous Version:
1. Removed VARCHAR - SQLite uses TEXT for all string storage
2. Changed DATETIME to INTEGER with Unix timestamps
3. Simplified PRIMARY KEY syntax
4. Changed BOOLEAN to INTEGER (SQLite doesn't have a true boolean type)
5. Updated CHECK constraints syntax
6. Removed AUTOINCREMENT (usually unnecessary in SQLite)

## Relationships

1. users -> inventory (One-to-Many)
    - A user can have multiple inventory items
    - Each inventory item belongs to one user

2. items -> inventory (One-to-Many)
    - An item type can be in multiple inventories
    - Each inventory entry references one item type

3. users -> trade_requests (One-to-Many, both as sender and receiver)
    - A user can send/receive multiple trade requests
    - Each trade request has one sender and one receiver

4. trade_requests -> trade_items (One-to-Many)
    - A trade request can involve multiple items
    - Each trade item belongs to one trade request

5. inventory -> trade_items (One-to-Many)
    - An inventory item can be involved in multiple trades
    - Each trade item references one inventory item

6. users -> notifications (One-to-Many)
    - A user can have multiple notifications
    - Each notification belongs to one user

## Notes
- SQLite has dynamic typing - the declared type is a recommendation
- All IDs are automatically incremented by SQLite when declared as INTEGER PRIMARY KEY
- Timestamps are stored as Unix timestamps (seconds since epoch)
- Boolean values are stored as INTEGER (0 = false, 1 = true)
- Text fields have no length limits in SQLite
- Foreign key constraints must be enabled using PRAGMA foreign_keys = ON;

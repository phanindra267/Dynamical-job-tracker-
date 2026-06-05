
/* SQLite schema for Dynamical Job Tracker */

-- Users table with role column
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'user',
    bio TEXT,
    profile_picture TEXT,
    created_at DATETIME DEFAULT (datetime('now'))
);

-- Jobs table
CREATE TABLE jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    location TEXT,
    company_name TEXT,
    skills TEXT,
    deadline TEXT,
    posted_by INTEGER NOT NULL,
    posted_at DATETIME DEFAULT (datetime('now')),
    created_at DATETIME DEFAULT (datetime('now')),

    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Job applications table expected by code
CREATE TABLE job_applications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    job_id INTEGER NOT NULL,
    applicant_name TEXT NOT NULL,
    email TEXT NOT NULL,
    resume TEXT NOT NULL,
    message TEXT,
    match_score INTEGER DEFAULT 0,
    status TEXT DEFAULT 'Pending',
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    message TEXT NOT NULL,
    submitted_at DATETIME DEFAULT (datetime('now'))
);


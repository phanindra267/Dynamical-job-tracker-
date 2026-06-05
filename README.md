# 💼 JobTracker - Smart ATS & AI-Powered Career Hub

JobTracker is a modern, dynamic web application designed to simplify and automate recruitment pipelines. Built with PHP, MySQL, and Tailwind CSS, it offers candidates personalized vacancies matching their skills, provides recruiters with inline status controls, and utilizes local NLP algorithms to calculate ATS candidate match scores.

---

## 🚀 Key Features

### 🧠 1. Local AI ATS Candidate Matcher
- **Zero Latency Evaluation**: Parses candidate resumes and cover letters locally during submission to compute a **Match Score (0-100%)** against job description text and required skills.
- **Scoring Breakdown**:
  - **Skills Overlap (60%)**: Measures intersection of candidate attributes and required tools.
  - **Keyword Trigger Density (40%)**: Evaluates professional modifiers (e.g., *develop, analyze, manage, lead*).
- **Recruiter Filter**: Piles of applications are instantly sortable and color-coded by match percentage.

### ✨ 2. Personalization & User Portals
- **Dynamic Recommended Jobs**: Candidates receive a **"Recommended for You"** job drawer listing jobs that fit their background, complete with fit percentages.
- **Timelines & Status History**: Dynamic candidate tracking pages replace hardcoded mock tables to display actual real-time application updates.
- **AI Cover Letter Copilot**: Integrated writing assistant drafts custom application cover letters instantly using job requirements.

### 🤖 3. JobTracker AI Agent
- An offline, interactive floating chatbot assistant on the homepage.
- Answers FAQs, lists active vacancies, and informs candidates of their application status.

### 📊 4. Analytical Admin Dashboards
- **Recruitment Pipeline Breakdown**: Real-time status distribution metrics visually drawn via Chart.js.
- **Activity Stream**: Interactive feed showing recent application submissions.
- **Dynamic Action Board**: Inline controls to transition applicants between `Pending`, `Reviewing`, `Shortlisted`, and `Rejected` statuses.

---

## 🛠️ Project Structure & Architecture

```text
├── admin/
│   ├── admin.php               # Admin/Recruiter insights console
│   ├── job-applications.php    # Application status pipeline manager
│   ├── manage-users.php        # User access list
│   └── settings.php            # Platform settings
├── uploads/                    # Stores uploaded resumes (PDF/DOCX)
├── apply.php                   # Job application page & AI matcher
├── db.php                      # Database connectivity configuration
├── home.php                    # Landing page, recommendations & chatbot
├── profile.php                 # Candidate profile & submission logs
└── job_tracker.sql             # Full database initialization script
```

---

## 🚀 Getting Started

Follow these steps to run the JobTracker project locally using XAMPP or local Apache/MySQL stacks:

### 1. Prerequisites
- **Web Server**: XAMPP, WAMP, or any Apache + PHP + MySQL stack.
- **PHP Version**: PHP 7.4 or higher recommended.
- **MySQL**: MySQL 5.7 or higher.

### 2. Set Up the Project
Move the cloned project folder into your server's public root directory (e.g., `C:\xampp\htdocs\job-tracker`).

### 3. Database Setup
1. Open the MySQL control panel (e.g., visit `http://localhost/phpmyadmin`).
2. Create a new database named `job_tracker`.
3. Import the `job_tracker.sql` file located in the root of the project folder.

### 4. Run the Project
Navigate to the application in your browser:
```text
http://localhost/job-tracker/home.php
```

---

## 🔐 Credentials & Default Access

You can log in immediately using these pre-seeded credentials:

* **Administrator / Recruiter**:
  * **Email**: `admin@example.com`
  * **Password**: `admin123`
* **Applicant Candidate**:
  * **Email**: `user@example.com`
  * **Password**: `user123`

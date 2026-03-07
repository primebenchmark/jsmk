# 🎓 JFT Mock Test System

A professional, high-performance, and feature-rich Multiple Choice Question (MCQ) platform specialized for JFT (Japan Foundation Test) simulations. This system provides a premium testing environment for students and a comprehensive management suite for administrators.

---

## ✨ Features

### 👨‍🎓 For Students
*   **Premium UI/UX**: A modern, glassmorphic design with smooth micro-animations and responsive layouts.
*   **Organized Catalog**: Tests grouped by categories for easy navigation.
*   **Audio Integration**: Specialized support for listening sections with configurable playback limits.
*   **Dual Theme Support**: Persisted Light and Dark modes.
*   **Content Protection**: Secured environment to prevent unauthorized copying (Disabled right-click, selection, and dev-tools shortcuts).
*   **Live Countdown**: Stay updated on upcoming test dates with a real-time countdown timer.

### 🛡️ For Administrators
*   **Full Dashboard**: Visual overview of total tests, questions, and student submissions.
*   **Test Management**: Efficiently create, edit, download, or delete tests using a JSON-based system.
*   **Real-time Analytics**: Track student performance and device information instantly.
*   **Global Customization**: Control site-wide settings including branding, typography (Google Fonts), and security features.
*   **Mobile Admin**: Fully responsive admin panel for management on the go.

---

## 🛠️ Technology Stack

*   **Backend**: PHP 7.4+
*   **Database**: 
    *   **SQLite**: Reliable storage for analytics and results.
    *   **JSON**: Lightweight, file-based configuration for tests and site settings.
*   **Frontend**: 
    *   **Vanilla JS**: Native, fast performance without heavy framework overhead.
    *   **Modern CSS**: Flexbox, CSS Grid, and custom properties for a premium look.

---

## 📂 Project Structure

```text
├── admin/               # Administrative dashboard and controls
├── assets/              # Design assets (CSS, JS, Images, Icons)
├── config.php           # Core application configuration
├── data/                # Database (SQLite) and configuration (JSON)
├── index.php            # Student portal / Test landing page
├── media/               # Audio and Image assets for mock tests
└──quiz.php             # Core test-taking engine
```

---

## 🚀 Quick Start

1.  **Clone the Repository**:
    ```bash
    git clone https://github.com/primebenchmark/mcq.git
    cd mcq
    ```

2.  **Environment Setup**:
    *   Ensure you have a web server (Apache/Nginx) with **PHP 7.4+** and **PDO SQLite** support enabled.
    *   Point your web server's document root to the project directory.

3.  **Permissions**:
    Make sure the `data/` directory and its contents are writable by the web server:
    ```bash
    chmod -R 775 data/
    ```

4.  **Admin Access**:
    Navigate to `/admin` to start managing your tests. (Default credentials can be verified/set in the database or config).

---

## 📄 License

Distributed under the **Apache License 2.0**. See `LICENSE` for more information.

---

<p align="center">
  Developed with ❤️ for the JFT Community.
</p>
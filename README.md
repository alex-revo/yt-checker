# YOOtheme Version Checker 🚀

An automated PHP script that runs on GitHub Actions to monitor the YOOtheme API for new template releases. When a new version becomes available, it sends an instant notification directly to your Telegram bot.

## How it works
1. **Scheduled Check:** GitHub Actions automatically runs the `check.php` script every 5 minutes.
2. **Version Verification:** The script queries the YOOtheme API for the next anticipated version (e.g., `5.0.53`).
3. **Notification:** If the file exists (HTTP 200), it sends a beautifully formatted message to your Telegram chat.
4. **State Management:** It saves the checked version in `state.json` and commits it back to the repository. This ensures you only get notified **once per version** and automatically moves on to check the next one (e.g., `5.0.54`).

## Setup & Configuration
This repository is configured to run securely using GitHub Secrets. To deploy your own instance, you need to add the following secrets in your repository settings (**Settings** -> **Secrets and variables** -> **Actions**):
- `BOT_TOKEN`: Your Telegram bot token (from [@BotFather](https://t.me/BotFather)).
- `CHAT_ID`: Your Telegram chat ID where notifications will be sent.
- `API_KEY`: Your YOOtheme API access key.

## Tech Stack
- PHP 8.2
- GitHub Actions (Cron Scheduling)
- cURL (HTTP Requests)
- Telegram Bot API

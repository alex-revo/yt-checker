# YOOtheme Version Checker 🚀

An automated PHP script that runs on GitHub Actions to monitor the YOOtheme API for new template releases. When a new version becomes available, it sends an instant notification directly to your Telegram bot.

## How it works
1. **Scheduled Check:** GitHub Actions automatically runs the `check.php` script every 5 minutes.
2. **Version Verification:** The script queries the YOOtheme API for the next anticipated version (e.g., `5.0.53`).
3. **Notification:** If the file exists (HTTP 200), it sends a beautifully formatted message to your Telegram chat.
4. **State Management:** It saves the checked version in `state.json` and commits it back to the repository. This ensures you only get notified **once per version** and automatically moves on to check the next one (e.g., `5.0.54`).

## How to use this repository (Fork & Setup)

If you want to get YOOtheme release notifications using your own Telegram bot, follow these steps:

1. **Fork this repository** to your own GitHub account (click the "Fork" button in the top right corner).
2. **Set up your Telegram Bot:**
   - Create a new bot via [@BotFather](https://t.me/BotFather) and get your `BOT_TOKEN`.
   - Send a message to your bot, then visit `https://api.telegram.org/bot<YOUR_TOKEN>/getUpdates` to find your `CHAT_ID`.
3. **Add Secrets to your Fork:**
   - Go to your forked repository -> **Settings** -> **Secrets and variables** -> **Actions**.
   - Click **New repository secret** and add:
     - `BOT_TOKEN`: Your Telegram bot token.
     - `CHAT_ID`: Your Telegram chat ID.
     - `API_KEY`: Your YOOtheme API key.
4. **Enable Workflow Permissions:**
   - In your forked repo, go to **Settings** -> **Actions** -> **General**.
   - Scroll down to **Workflow permissions**, select **Read and write permissions**, and click **Save**.
5. **Run it:**
   - Go to the **Actions** tab in your fork, click on "Yootheme Version Checker" on the left, and hit **Run workflow**.

*Note: GitHub automatically disables scheduled workflows in public forks if the repository has been inactive for 60 days. To keep it running, you may need to manually run it once every couple of months or make a small commit.*

## Tech Stack
- PHP 8.2
- GitHub Actions (Cron Scheduling)
- cURL (HTTP Requests)
- Telegram Bot API

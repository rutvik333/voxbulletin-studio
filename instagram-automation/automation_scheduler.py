import os
import sys
import time
import datetime
import subprocess

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
CRON_SCRIPT = os.path.join(BASE_DIR, "cron.php")
PHP_EXE = r"C:\Users\Rutvik Pagi\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"

# Scheduled hours: 9:00 AM, 2:00 PM (14:00), 8:00 PM (20:00)
SCHEDULE_HOURS = [9, 14, 20]
last_ran_hour = -1

print("=" * 60)
print("🚀 VoxBulletin 3x/Day Autonomous Scheduler Started")
print(f"⏰ Scheduled Times: 9:00 AM, 2:00 PM, 8:00 PM daily")
print(f"🎯 Target Priorities: Tier 1: Marvel & DC Universe -> Tier 2: Franchises -> Tier 3: General")
print("=" * 60)

def run_cron_cycle(trigger_reason):
    print(f"\n[{datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] ⚡ Triggering Automation Cycle ({trigger_reason})...")
    cmd = [PHP_EXE, CRON_SCRIPT] if os.path.exists(PHP_EXE) else ["php", CRON_SCRIPT]
    try:
        res = subprocess.run(cmd, cwd=BASE_DIR, capture_output=True, text=True, timeout=180)
        print("Exit Code:", res.returncode)
        if res.stdout:
            print("Output:\n", res.stdout)
        if res.stderr:
            print("Errors:\n", res.stderr)
    except Exception as e:
        print("Execution Exception:", e)

while True:
    now = datetime.datetime.now()
    current_hour = now.hour
    current_minute = now.minute

    # Check if we are at the target hour (within the first 5 minutes) and haven't run for this hour today
    if current_hour in SCHEDULE_HOURS and current_minute < 5:
        if last_ran_hour != current_hour:
            last_ran_hour = current_hour
            run_cron_cycle(f"Scheduled Slot: {current_hour}:00")
    elif current_hour not in SCHEDULE_HOURS:
        last_ran_hour = -1

    time.sleep(30)

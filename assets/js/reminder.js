// assets/js/reminder.js
// JS timer popup: “Time to log today’s progress?” after 24h of inactivity
(function() {
    const REMINDER_KEY = 'fitishh_last_log';
    const ONE_DAY = 24 * 60 * 60 * 1000;
    function setLastLog() {
        localStorage.setItem(REMINDER_KEY, Date.now().toString());
    }
    function checkReminder() {
        const last = parseInt(localStorage.getItem(REMINDER_KEY) || '0', 10);
        if (Date.now() - last > ONE_DAY) {
            showReminder();
        }
    }
    function showReminder() {
        if (document.getElementById('fitishh-reminder')) return;
        const div = document.createElement('div');
        div.id = 'fitishh-reminder';
        div.style.position = 'fixed';
        div.style.bottom = '30px';
        div.style.right = '30px';
        div.style.background = '#222';
        div.style.color = '#fff';
        div.style.padding = '20px';
        div.style.borderRadius = '8px';
        div.style.zIndex = '9999';
        div.innerHTML = '<strong>Time to log today\'s progress!</strong> <button id="fitishh-reminder-close">Dismiss</button>';
        document.body.appendChild(div);
        document.getElementById('fitishh-reminder-close').onclick = function() {
            div.remove();
            setLastLog();
        };
    }
    document.addEventListener('DOMContentLoaded', function() {
        checkReminder();
        // Mark log when user adds workout or stats
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                setLastLog();
            });
        });
    });
})();

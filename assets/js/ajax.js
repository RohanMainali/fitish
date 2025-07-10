// assets/js/ajax.js
// AJAX helper for submitting forms and updating dashboard sections
function ajaxForm(formSelector, resultSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const xhr = new XMLHttpRequest();
        xhr.open('POST', form.action || window.location.href);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function() {
            if (xhr.status === 200) {
                if (resultSelector) {
                    document.querySelector(resultSelector).innerHTML = xhr.responseText;
                } else {
                    location.reload();
                }
            } else {
                alert('Error: ' + xhr.status);
            }
        };
        const formData = new FormData(form);
        xhr.send(formData);
    });
}
// Usage example (to be called in each form page):
// ajaxForm('#addWorkoutForm', '#workouts');

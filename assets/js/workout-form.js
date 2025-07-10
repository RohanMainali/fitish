// assets/js/workout-form.js
// Dynamically update workout form fields based on selected type
document.addEventListener('DOMContentLoaded', function() {
    const workoutTypeSelect = document.querySelector('select[name="type"]');
    const distanceField = document.querySelector('input[name="distance"]');
    const metField = document.querySelector('input[name="met"]');
    if (!workoutTypeSelect || !distanceField || !metField) return;
    function updateFields() {
        const type = workoutTypeSelect.value;
        // Only show distance for running, cycling, swimming
        if (["Running", "Cycling", "Swimming"].includes(type)) {
            distanceField.parentElement.style.display = '';
        } else {
            distanceField.parentElement.style.display = 'none';
            distanceField.value = '';
        }
        // Set MET value based on type
        const metValues = {
            "Running": 9.8,
            "Cycling": 7.5,
            "Swimming": 8.0,
            "Walking": 3.5,
            "Yoga": 2.5,
            "Strength Training": 6.0,
            "Other": 1.0
        };
        metField.value = metValues[type] || 1.0;
    }
    workoutTypeSelect.addEventListener('change', updateFields);
    updateFields();
});

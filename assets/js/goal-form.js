// assets/js/goal-form.js
// Dynamically update goal form fields based on selected goal type

document.addEventListener('DOMContentLoaded', function() {
    const goalTypeSelect = document.querySelector('select[name="goal_type"]');
    const targetValueField = document.querySelector('input[name="target_value"]');
    const currentValueField = document.querySelector('input[name="current_value"]');
    if (!goalTypeSelect || !targetValueField || !currentValueField) return;
    function updateFields() {
        const type = goalTypeSelect.value;
        // Example: Set placeholder and label for different goal types
        if (type === "Weight Loss") {
            targetValueField.placeholder = "Target Weight (kg)";
            currentValueField.placeholder = "Current Weight (kg)";
        } else if (type === "Distance") {
            targetValueField.placeholder = "Target Distance (km)";
            currentValueField.placeholder = "Current Distance (km)";
        } else if (type === "Calories Burned") {
            targetValueField.placeholder = "Target Calories";
            currentValueField.placeholder = "Current Calories";
        } else if (type === "Streak") {
            targetValueField.placeholder = "Target Days";
            currentValueField.placeholder = "Current Days";
        } else {
            targetValueField.placeholder = "Target Value";
            currentValueField.placeholder = "Current Value";
        }
    }
    goalTypeSelect.addEventListener('change', updateFields);
    updateFields();
});

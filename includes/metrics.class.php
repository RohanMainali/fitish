<?php
// /includes/metrics.class.php
class Metrics {
    // BMI = weight (kg) / (height (m))^2
    public static function calculateBMI($weight, $height) {
        if ($height <= 0) return 0;
        $heightM = $height / 100;
        return round($weight / ($heightM * $heightM), 2);
    }
    // BMR (Mifflin-St Jeor Equation)
    public static function calculateBMR($weight, $height, $age, $gender) {
        if ($gender === 'male') {
            return round(10 * $weight + 6.25 * $height - 5 * $age + 5, 2);
        } else {
            return round(10 * $weight + 6.25 * $height - 5 * $age - 161, 2);
        }
    }
    // TDEE = BMR * activity factor
    public static function calculateTDEE($bmr, $activityLevel) {
        $factors = [
            'sedentary' => 1.2,
            'light' => 1.375,
            'moderate' => 1.55,
            'active' => 1.725,
            'very_active' => 1.9
        ];
        $factor = $factors[$activityLevel] ?? 1.2;
        return round($bmr * $factor, 2);
    }
    // Calories burned per workout (MET formula)
    public static function calculateCalories($met, $weight, $duration) {
        // MET x weight (kg) x duration (hr)
        return round($met * $weight * ($duration / 60), 2);
    }
}
?>

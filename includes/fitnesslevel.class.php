<?php
// /includes/fitnesslevel.class.php
require_once 'db.php';
class FitnessLevel {
    public static function calculate($bmi, $calories, $streak) {
        // Simple logic: combine streak, BMI, calories
        if ($streak >= 30 && $calories >= 20000 && $bmi >= 18.5 && $bmi <= 24.9) {
            return 'Elite';
        } elseif ($streak >= 14 && $calories >= 10000) {
            return 'Advanced';
        } elseif ($streak >= 7 && $calories >= 5000) {
            return 'Intermediate';
        } else {
            return 'Beginner';
        }
    }
}
?>

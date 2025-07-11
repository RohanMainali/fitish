<?php
// /includes/validator.class.php
class Validator {
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    public static function validateUsername($username) {
        return preg_match('/^[A-Za-z0-9_]{3,20}$/', $username);
    }
    public static function validatePassword($password) {
        return strlen($password) >= 6;
    }
    public static function validateFloat($value) {
        return filter_var($value, FILTER_VALIDATE_FLOAT);
    }
    public static function validateInt($value) {
        return filter_var($value, FILTER_VALIDATE_INT);
    }
}
?>

<?php

class ApplicationType {
    // type
    const VISA_EUROPE = 'visa_europe';
    const VISA_T1 = 'visa_t1';
    const VISA_T2 = 'visa_t2';
    const VISA_T4 = 'visa_t4';
    const VISA_OTHER = 'visa_other';
    const LANGUAGE = 'language';
    const GCSE = 'gcse';
    const A_LEVEL = 'a-level';
    const PRE_BACHELOR = 'pre-bachelor';
    const BACHELOR = 'bachelor';
    const PRE_MASTER = 'pre-master';
    const MASTER = 'master';
    const DOCTOR = 'doctor';

    public static function getTypes() {
        return array(VISA_EUROPE, VISA_T1, VISA_T2, VISA_T4, VISA_OTHER, LANGUAGE, GCSE, A_LEVEL, PRE_BACHELOR, BACHELOR, PRE_MASTER, MASTER, DOCTOR);
    }

    public static function isVisa($type) {
        return strpos($type, 'visa_') !== false;
    }

    public static function isSchool($type) {
        return strpos($type, 'visa_') === false;
    }
}

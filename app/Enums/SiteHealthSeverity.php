<?php

namespace App\Enums;

/**
 * Перечисление site health severity.
 */
enum SiteHealthSeverity: string
{
    case Critical = 'critical';
    case Warning = 'warning';
    case Passed = 'passed';
}

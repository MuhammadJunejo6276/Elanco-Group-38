<?php

function getSelectedDay($maxDate, $minDate, $selectedDay = null) {
    $date = null;
    $selectedDayValue = null;

    try {
        if ($selectedDay) {
            $date = DateTime::createFromFormat('Y-m-d', $selectedDay);
            if (!$date) {
                throw new Exception("Invalid date format");
            }
            $selectedDayValue = $date->format('d-m-Y');
        } else {
            $date = clone $maxDate;
            $selectedDayValue = $maxDate->format('d-m-Y');
        }

        if ($date < $minDate || $date > $maxDate) {
            throw new Exception("Selected date is out of range");
        }
    } catch (Exception $e) {
        throw new Exception("Invalid day selected: " . $e->getMessage());
    }

    return [
        'date' => $date->format('d-m-Y'),
        'selectedDayValue' => $selectedDayValue
    ];
}

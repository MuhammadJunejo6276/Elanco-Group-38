<?php

function getSelectedWeekDates($maxDate, $minDate, $selectedWeek = null) {
    $dates = [];
    $selectedWeekValue = null;

    try {
        if ($selectedWeek) {
            $weekStart = new DateTime($selectedWeek);
            $weekStart->modify('monday this week');

            for ($i = 0; $i < 7; $i++) {
                $currentDate = clone $weekStart;
                $currentDate->modify("+$i days");
                $dates[] = $currentDate->format('d-m-Y');
            }
            $selectedWeekValue = $selectedWeek;
        } else {
            for ($i = 6; $i >= 0; $i--) {
                $date = clone $maxDate;
                $date->modify("-$i days");
                $dates[] = $date->format('d-m-Y');
            }
            $isoYear = $maxDate->format('o');
            $isoWeek = $maxDate->format('W');
            $selectedWeekValue = sprintf("%s-W%02d", $isoYear, $isoWeek);
        }
    } catch (Exception $e) {
        throw new Exception("Invalid week selected: " . $e->getMessage());
    }

    return [
        'dates' => $dates,
        'selectedWeekValue' => $selectedWeekValue
    ];
}

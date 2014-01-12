<?php
/**
 * Created by PhpStorm.
 * User: Leonardo
 * Date: 11/01/14
 * Time: 15:45
 */

namespace ebussola\goalr;


class Goalr {

    /**
     * @var \DateTime
     */
    public $current_date;

    public function __construct(\DateTime $current_date=null) {
        if ($current_date === null) {
            $this->current_date = new \DateTime('today');
        }
    }

    /**
     * @param Goal  $goal
     * @param       $spent
     * @param Event[] $events
     *
     * @return float
     */
    public function getDailyBudget(Goal $goal, $spent, array $events=array()) {
        $date_start = $goal->date_start;
        $date_end = $goal->date_end;
        if ($goal->date_start <= $this->current_date) {
            $date_start = $this->current_date;
        }

        $remaining_budget = $goal->total_budget - $spent;
        $remaining_days = $this->calcDiffDays($date_start, $date_end);
        $daily_budget = $remaining_budget / $remaining_days;

        $runned_event_category = array();
        foreach ($events as $event) {
            if (!in_array($event->category, $runned_event_category) && $this->current_date <= $event->date_end) {
                $daily_budget = $this->calcEvent($goal, $event, $daily_budget);
                $runned_event_category[] = $event->category;
            }
        }

        return $daily_budget;
    }

    private function calcDiffDays(\DateTime $date_start, \DateTime $date_end) {
        $date_start = clone $date_start;
        $date_end = clone $date_end;

        return $date_start->diff($date_end->add(new \DateInterval('P1D')))->days;
    }

    private function calcPercentage($daily_budget, $variation) {
        return $daily_budget + ($daily_budget * ($variation / 100));
    }

    /**
     * @param Goal  $goal
     * @param Event $event
     * @param float $daily_budget
     *
     * @return float
     */
    private function calcEvent(Goal $goal, Event $event, $daily_budget) {
        $days_to_event = $this->calcDiffDays($goal->date_start, $event->date_start);
        $days_of_event = $this->calcDiffDays($event->date_start, $event->date_end);

        if ($this->current_date < $event->date_start) {
            $variated_budget = $this->calcPercentage($daily_budget, ($event->variation * -1));

            if ($event->variation > 0) {
                $variated_budget *= -1;
            }

            $daily_budget += ($variated_budget * $days_of_event) / $days_to_event;

        } else if ($this->current_date >= $event->date_start && $this->current_date <= $event->date_end) {
            $daily_budget = $this->calcPercentage($daily_budget, $event->variation);
        }

        return $daily_budget;
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: Leonardo
 * Date: 11/01/14
 * Time: 16:43
 */

class GoalrTest extends PHPUnit_Framework_TestCase {

    /**
     * @var \ebussola\goalr\Goalr
     */
    private $goalr;

    public function setUp() {
        $this->goalr = new \ebussola\goalr\Goalr();
    }

    public function testGoalrAnotherDate()
    {
        $goal = new \ebussola\goalr\goal\Goal();
        $goal->id = 1;
        $goal->date_start = new DateTime('today');
        $goal->date_end = new DateTime('today + 29 days');
        $goal->total_budget = 37020;

        $goalr = new \ebussola\goalr\Goalr(new DateTime('+ 5 days'));
        $daily_budget = $goalr->getDailyBudget($goal, 0);
        $this->assertEquals(1542.5, $daily_budget);
    }

    public function testGetDailyBudget_level1() {
        $goal = new \ebussola\goalr\goal\Goal();
        $goal->id = 1;
        $goal->date_start = new DateTime('today');
        $goal->date_end = new DateTime('today + 29 days');
        $goal->total_budget = 37020;

        $daily_budget = $this->goalr->getDailyBudget($goal, 0);
        $this->assertEquals(1234, $daily_budget);

        $daily_budget = $this->goalr->getDailyBudget($goal, 15000);
        $this->assertEquals(734, $daily_budget);

        $this->goalr->current_date = new DateTime('today +5 days');
        $daily_budget = $this->goalr->getDailyBudget($goal, 0);
        $this->assertEquals(1480.8, $daily_budget);
    }

    public function testGetDailyBudget_level2() {
        $goal = new \ebussola\goalr\goal\Goal();
        $goal->id = 1;
        $goal->date_start = new DateTime('today - 10 days');
        $goal->date_end = new DateTime('today + 29 days');
        $goal->total_budget = 37020;

        $daily_budget = $this->goalr->getDailyBudget($goal, 0);
        $this->assertEquals(1234, $daily_budget);

        $daily_budget = $this->goalr->getDailyBudget($goal, 15000);
        $this->assertEquals(734, $daily_budget);

        $this->goalr->current_date = new DateTime('today +5 days');
        $daily_budget = $this->goalr->getDailyBudget($goal, 0);
        $this->assertEquals(1480.8, $daily_budget);
    }

    public function testGetDailyBudget_level3() {
        $goal = new \ebussola\goalr\goal\Goal();
        $goal->id = 1;
        $goal->date_start = new DateTime('today');
        $goal->date_end = new DateTime('today + 29 days');
        $goal->total_budget = 37020;

        $period = new DatePeriod($goal->date_start->setTime(0,0,0), new DateInterval('P1D'), $goal->date_end->setTime(0,0,0));
        $spent = 0;
        foreach ($period as $date) {
            $this->goalr->current_date = $date;
            $daily_budget = $this->goalr->getDailyBudget($goal, $spent);
            $this->assertEquals(1234, $daily_budget);
            $spent += $daily_budget;
        }

        $period = new DatePeriod($goal->date_start->setTime(0,0,0), new DateInterval('P1D'), $goal->date_end->setTime(0,0,0));
        $spent = 15000;
        foreach ($period as $date) {
            $this->goalr->current_date = $date;
            $daily_budget = $this->goalr->getDailyBudget($goal, $spent);
            $this->assertEquals(734, $daily_budget);
            $spent += $daily_budget;
        }
    }

    public function testGetDailyBudget_level4_1() {
        $goal = new \ebussola\goalr\goal\Goal();
        $goal->id = 1;
        $goal->date_start = new DateTime('2014-01-01');
        $goal->date_end = new DateTime('2014-01-30');
        $goal->total_budget = 37020;

        $event = new \ebussola\goalr\event\Event();
        $event->id = 1;
        $event->name = 'FDS';
        $event->date_start = new DateTime('2014-01-18');
        $event->date_end = new DateTime('2014-01-19');
        $event->variation = 50;

        $period = new DatePeriod($goal->date_start->setTime(0,0,0), new DateInterval('P1D'), $goal->date_end->setTime(0,0,0));
        $spent = 0;
        foreach ($period as $date) {
            $this->goalr->current_date = $date;
            $daily_budget = $this->goalr->getDailyBudget($goal, $spent, array($event));
            if ($date < $event->date_start) {
                $this->assertGreaterThanOrEqual(1165, $daily_budget);
                $this->assertLessThanOrEqual(1234, $daily_budget);
            } else if ($date <= $event->date_end && $date >= $event->date_start) {
                $this->assertLessThanOrEqual(1941.2777368245, $daily_budget);
            } else {
                $this->assertLessThan(1234, $daily_budget);
            }
            $spent += $daily_budget;
        }
    }

    public function testGetDailyBudget_level4_2() {
        $goal = new \ebussola\goalr\goal\Goal();
        $goal->id = 1;
        $goal->date_start = new DateTime('2014-01-01');
        $goal->date_end = new DateTime('2014-01-30');
        $goal->total_budget = 37020;

        $event = new \ebussola\goalr\event\Event();
        $event->id = 1;
        $event->name = 'FDS';
        $event->date_start = new DateTime('2014-01-18');
        $event->date_end = new DateTime('2014-01-19');
        $event->variation = -50;

        $period = new DatePeriod($goal->date_start->setTime(0,0,0), new DateInterval('P1D'), $goal->date_end->setTime(0,0,0));
        $spent = 0;
        foreach ($period as $date) {
            $this->goalr->current_date = $date;
            $daily_budget = $this->goalr->getDailyBudget($goal, $spent, array($event));
            if ($date < $event->date_start) {
                $this->assertLessThanOrEqual(1440, $daily_budget);
                $this->assertGreaterThanOrEqual(1234, $daily_budget);
            } else if ($date <= $event->date_end && $date >= $event->date_start) {
                $this->assertGreaterThanOrEqual(534, $daily_budget);
            } else {
                $this->assertLessThan(1234, $daily_budget);
            }
            $spent += $daily_budget;
        }
    }



    public function testGetDailyBudget_level5() {
        $goal = new \ebussola\goalr\goal\Goal();
        $goal->id = 1;
        $goal->date_start = new DateTime('2014-01-01');
        $goal->date_end = new DateTime('2014-01-30');
        $goal->total_budget = 37020;

        $events = array();

        $event = new \ebussola\goalr\event\Event();
        $event->id = 1;
        $event->name = 'FDS';
        $event->date_start = new DateTime('2014-01-11');
        $event->date_end = new DateTime('2014-01-12');
        $event->variation = 30;
        $event->category = 'fds';
        $events[] = $event;

        $event = new \ebussola\goalr\event\Event();
        $event->id = 2;
        $event->name = 'FDS';
        $event->date_start = new DateTime('2014-01-18');
        $event->date_end = new DateTime('2014-01-19');
        $event->variation = 50;
        $event->category = 'fds';
        $events[] = $event;

        $period = new DatePeriod($goal->date_start->setTime(0,0,0), new DateInterval('P1D'), $goal->date_end->setTime(0,0,0));
        $spent = 0;
        foreach ($period as $date) {
            $this->goalr->current_date = $date;
            $daily_budget = $this->goalr->getDailyBudget($goal, $spent, $events);
            if ($date < $events[0]->date_start) {
                $this->assertGreaterThanOrEqual(1061, $daily_budget);
                $this->assertLessThanOrEqual(1234, $daily_budget);
            } else if ($date <= $events[0]->date_end && $date >= $events[0]->date_start) {
                $this->assertGreaterThanOrEqual(1604, $daily_budget);

            } else if ($date < $events[1]->date_start) {
                $this->assertGreaterThanOrEqual(1050, $daily_budget);
                $this->assertLessThanOrEqual(1234, $daily_budget);
            } else if ($date <= $events[1]->date_end && $date >= $events[1]->date_start) {
                $this->assertGreaterThanOrEqual(1800, $daily_budget);

            }

            $spent += $daily_budget;
        }
    }


    /**
     * Just a visual test
     */
    public function testGetDailyBudget_level5_2() {
        $goal = new \ebussola\goalr\goal\Goal();
        $goal->id = 1;
        $goal->date_start = new DateTime('2014-01-01');
        $goal->date_end = new DateTime('2014-01-31');
        $goal->total_budget = 37020;

        $events = array();

        $event = new \ebussola\goalr\event\Event();
        $event->id = 1;
        $event->name = 'FDS';
        $event->date_start = new DateTime('2014-01-11');
        $event->date_end = new DateTime('2014-01-12');
        $event->variation = 20;
        $event->category = 'fds';
        $events[] = $event;

        $event = new \ebussola\goalr\event\Event();
        $event->id = 2;
        $event->name = 'FDS';
        $event->date_start = new DateTime('2014-01-18');
        $event->date_end = new DateTime('2014-01-19');
        $event->variation = 20;
        $event->category = 'fds';
        $events[] = $event;

        $event = new \ebussola\goalr\event\Event();
        $event->id = 3;
        $event->name = 'FDS';
        $event->date_start = new DateTime('2014-01-25');
        $event->date_end = new DateTime('2014-01-26');
        $event->variation = 20;
        $event->category = 'fds';
        $events[] = $event;

        $event = new \ebussola\goalr\event\Event();
        $event->id = 4;
        $event->name = 'Ending Month';
        $event->date_start = new DateTime('2014-01-27');
        $event->date_end = new DateTime('2014-01-30');
        $event->variation = -20;
        $event->category = 'ending_month';
        $events[] = $event;

        $dateTime = clone $goal->date_end;
        $dateTime->setTime(0, 0, 0)->add(new DateInterval('P1D'));
        $period = new DatePeriod($goal->date_start->setTime(0,0,0), new DateInterval('P1D'), $dateTime);
        $spent = 0;
        foreach ($period as $date) {
            $this->goalr->current_date = $date;
            $daily_budget = $this->goalr->getDailyBudget($goal, $spent, $events);

            echo $date->format('d/m/Y') . ' - ' . $daily_budget . PHP_EOL;

            $spent += $daily_budget;
        }

        echo 'Total gasto: ' . $spent;
    }

}
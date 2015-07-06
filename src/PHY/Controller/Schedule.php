<?php

    /**
     * Briggs Academy of Mixed Martial Arts
     *
     * LICENSE
     *
     * This source file is subject to the Open Software License (OSL 3.0)
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://opensource.org/licenses/osl-3.0.php
     * If you did not receive a copy of the license and are unable to
     * obtain it through the world-wide-web, please send an email
     * to license@phyneapple.com so we can send you a copy immediately.
     *
     */

    namespace PHY\Controller;

    use PHY\Model\Schedule as Model;

    /**
     * Instructor page.
     *
     * @package PHY\Controller\Schedule
     * @category PHY\Briggs
     * @copyright Copyright (c) 2014 Briggs Academy of Mixed Martial Arts (http://www.briggsmma.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Schedule extends AController
    {

        private static $descriptions = [
            'Kids BJJ' => 'This is a Brazilian Jiu Jitsu (BJJ) curriculum specifically designed for children, ages 6-12 years old. This class focuses on the fundamentals of Brazilian Jiu Jitsu, as well as principles of Judo, developing discipline, self-confidence, while working up a sweat and having fun.',
            'Gray Belt BJJ' => 'Your child will be invited to this class once he or she has graduated to the rank of gray belt. Not only does it focus on more advanced concepts of BJJ and Judo, but also helps the students develop leadership skills in a fun environment.',
            'Beginner Grappling' => 'This class is designed to introduce you to the fundamental concepts of Brazilian Jiu Jitsu and Judo. You will have time to focus on the basic concepts and skills that will set you up for success in the Adult BJJ and Adult Judo classes.',
            'Adult BJJ' => 'This class is for All Levels of Brazilian Jiu Jitsu practitioners. This class will focus on the ground techniques that have made BJJ one of the most effective martial arts. You will be divided into two groups: beginning and intermediate to better address your technical needs. This class will have more live training and position specific matches in order to help you develop your skills to a world-class level.',
            'Adult Judo' => 'All grappling matches, and self-defense situations, start on the feet. Judo is going to help you stay on yours, and put your opponent on the ground. In this class you will learn the throws and takedowns that make Judo such an effective Martial Art. This class will cover different gripping strategies and technique combinations that will help you win competitions or better defend yourself.',
            'Muay Thai' => 'This class will focus on the striking techniques, using all of the limbs of the body. Not only will you get a great workout, but will learn effective striking combinations used by some of the best fighters in the world. *Thai Pads, Focus Mitts and Heavy Bags will be available for the class. For sanitary reasons you may want to purchase your own gloves.*',
            'Open Mat' => 'This time will be used for drilling techniques or for BJJ timed matches and will be assigned at the Professor/coach\'s discretion.'
        ];

        /**
         * GET /instructor
         */
        public function index_get()
        {
            $layout = $this->getLayout();
            $head = $layout->block('head');
            $head->setVariable('title', 'Schedule');
            $head->setVariable('description', 'Check the latest class updates and schedules at Briggs MMA.');

            $app = $this->getApp();

            /* @var \PHY\Database\IDatabase $database */
            $database = $app->get('database');
            $manager = $database->getManager();

            $rows = [];
            $start = false;
            $end = false;

            $classes = [
                'bjj' => [
                    'kid' => 'success',
                    'all' => 'success',
                ],
                'judo' => 'info',
                'striking' => 'danger',
            ];

            /* @var \PHY\Model\Schedule\Collection $collection */
            $collection = $manager->getCollection('Schedule');

            foreach ($collection as $item) {
                $diff = $item->end - $item->start;
                $cols = $diff * 4;
                $col = 'c' . $item->start;
                if (!isset($rows[$col])) {
                    $rows[$col] = [];
                }
                if (isset($classes[$item->type])) {
                    if (is_array($classes[$item->type])) {
                        $class = $classes[$item->type][$item->level];
                    } else {
                        $class = $classes[$item->type];
                    }
                } else {
                    $class = 'info';
                }
                $rows[$col][$item->day
                    ? : 7] = [
                    'day' => $item->day
                        ? : 7,
                    'cols' => 1,
                    'rows' => $cols + 1,
                    'class' => $class,
                    'title' => $item->title,
                    'description' => 'Some class description',
                ];
                if ($item->start < $start || $start === false) {
                    $start = $item->start;
                }
                if ($item->end > $end || $end === false) {
                    $end = $item->end;
                }
                for (; $diff > 0; $diff -= .25) {
                    $col = $item->start + $diff;
                    if (!isset($rows['c' . $col][$item->day
                        ? : 7])
                    ) {
                        $rows['c' . $col][$item->day
                            ? : 7] = null;
                    }
                }
            }
            ksort($rows, SORT_NATURAL);
            $results = [];
            for ($i = $start; $i <= $end; $i += .25) {
                $s = 'c' . $i;
                $hoursFrom = explode('.', (string)$i);
                if (count($hoursFrom) > 1) {
                    $minutesFrom = str_pad(round(60 * $hoursFrom[1] / 100), 2, '0');
                } else {
                    $minutesFrom = '00';
                }
                $hoursFrom = $hoursFrom[0];
                if ($hoursFrom >= 12) {
                    $from = ($hoursFrom > 12
                            ? $hoursFrom - 12
                            : $hoursFrom) . ':' . $minutesFrom . 'pm';
                } else {
                    $from = (!$hoursFrom
                            ? 12
                            : $hoursFrom) . ':' . $minutesFrom . 'am';
                }
                $hoursTo = explode('.', (string)($i + .25));
                if (count($hoursTo) > 1) {
                    $minutesTo = str_pad(round(60 * $hoursTo[1] / 100), 2, '0');
                } else {
                    $minutesTo = '00';
                }
                $hoursTo = $hoursTo[0];
                if ($hoursTo >= 12) {
                    $to = ($hoursTo > 12
                            ? $hoursTo - 12
                            : $hoursTo) . ':' . $minutesTo . 'pm';
                } else {
                    $to = (!$hoursTo
                            ? 12
                            : $hoursTo) . ':' . $minutesTo . 'am';
                }
                $results[$s][0] = $from . '-' . $to;
                for ($d = 1; $d <= 7; ++$d) {
                    if (array_key_exists($s, $rows) && array_key_exists($d, $rows[$s])) {
                        if (is_array($rows[$s][$d])) {
                            $xsTo = explode('.', (string)($i + ($rows[$s][$d]['rows'] / 4)));
                            if (count($xsTo) > 1) {
                                $xsMinutesTo = str_pad(round(60 * $xsTo[1] / 100), 2, '0');
                            } else {
                                $xsMinutesTo = '00';
                            }
                            $xsHoursTo = $xsTo[0];
                            if ($xsHoursTo >= 12) {
                                $xsTo = ($xsHoursTo > 12
                                        ? $xsHoursTo - 12
                                        : $xsHoursTo) . ':' . $xsMinutesTo . 'pm';
                            } else {
                                $xsTo = (!$xsHoursTo
                                        ? 12
                                        : $xsHoursTo) . ':' . $xsMinutesTo . 'am';
                            }
                            $rows[$s][$d]['start'] = $from;
                            $rows[$s][$d]['end'] = $xsTo;
                        }
                        $results[$s][$d] = $rows[$s][$d];
                    } else {
                        $results[$s][$d] = false;
                    }
                }
            }

            $days = Model::getDays();
            $byDays = [];
            $byTimes = [];
            $current = false;
            foreach ($results as $row) {
                $notEmpty = false;
                for ($d = 1; $d <= 7; ++$d) {
                    if ($row[$d] !== false) {
                        if (is_array($row[$d])) {
                            if (!isset($byDays[$row[$d]['day']])) {
                                $byDays[$row[$d]['day']] = [
                                    'day' => $days[$row[$d]['day']],
                                    'classes' => []
                                ];
                            }
                            $byDays[$row[$d]['day']]['classes'][] = $row[$d];
                        }
                        $notEmpty = true;
                    }
                }
                if ($notEmpty) {
                    $current = false;
                    $byTimes[] = $row;
                    continue;
                } else {
                    if (!$current) {
                        $current = true;
                        $byTimes[] = [
                            [
                                'cols' => 8,
                                'rows' => 1,
                                'class' => '',
                                'title' => ''
                            ]
                        ];
                    } else {
                        continue;
                    }
                }
            }
            ksort($byDays);

            $body = $layout->block('content');
            $body->setVariable('byTimes', $byTimes);
            $body->setVariable('byDays', $byDays);
            $body->setVariable('descriptions', self::$descriptions);
        }

    }

<?php
/**
 * Created by PhpStorm.
 * User: Leonardo
 * Date: 11/01/14
 * Time: 16:53
 */

namespace ebussola\goalr\event;


class Event implements \ebussola\goalr\Event {

    public $id;
    public $date_start;
    public $date_end;
    public $variation;
    public $category;

} 
<?php

include BASEPATH.'..\Core\Controller.php';

/**
 * Home controller
 */
class HomeController extends Core\Controller
{
    /**
     * Show the index page
     *
     * @return void
     */
    public function index()
    {
    	echo "Hello world";
    }

    /**
     * Show the index page
     *
     * @return void
     */
    public function about()
    {
    	echo "About";
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController
 *
 * @author Romain DEROCLE <romain@yitoo.io>
 */
class HomeController extends Controller
{
    /**
     * @Route(name="app_home_index", path="/")
     */
    public function indexAction()
    {
        return $this->render('home/index.html.twig');
    }
}

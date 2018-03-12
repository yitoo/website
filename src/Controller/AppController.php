<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController extends Controller
{
    /**
     * @Route("/", name="app_home_page")
     */
    public function index()
    {
        return $this->render('app/index.html.twig');
    }

    /**
     * @Route("/connaitre-yitoo", name="app_about_us")
     */
    public function aboutUs()
    {
        return $this->render('app/about.html.twig');
    }

    /**
     * @Route("/nos-expertises", name="app_expertise")
     */
    public function expertise()
    {
        return $this->render('app/expertise.html.twig');
    }

    /**
     * @Route("/le-studio", name="app_studio")
     */
    public function studio()
    {
        return $this->render('app/studio.html.twig');
    }

    /**
     * @Route("/innovations", name="app_yitoo_labs")
     */
    public function yitooLabs()
    {
        return $this->render('app/labs.html.twig');
    }

    /**
     * @Route("/travailler-chez-yitoo", name="app_join_us")
     */
    public function jobs()
    {
        return $this->render('app/jobs.html.twig');
    }

    /**
     * @Route("/nous-contacter", name="app_contact_us")
     */
    public function contact()
    {
        return $this->render('app/contact.html.twig');
    }
}
